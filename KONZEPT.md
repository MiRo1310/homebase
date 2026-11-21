# Portfolio-Projekt: PHP-Shop mit Symfony

## Ziel

Zwei Ziele parallel verfolgen:

1. **Lernziel:** PHP-OOP (Klassen, Methoden, Interfaces, Design Patterns) wirklich verstehen — bewusst mit mehr
   Komplexität als für einen simplen Shop nötig, weil das der Übungszweck ist.
2. **Portfolio:** ein vorzeigbares Projekt, das echte Berufspraxis widerspiegelt (Symfony/Sylius-Umfeld).

Code wird komplett selbst geschrieben. Dieses Dokument beschreibt nur die Struktur und Entscheidungen, [[TODO.md]]
bricht das in bearbeitbare Schritte herunter.

## Tech-Stack

- **PHP 8.2+** (Enums, readonly properties, typed properties konsequent nutzen)
- **Symfony 7 (Full-Stack)** — `symfony/website-skeleton`: Routing, Security, Forms, Validator, EventDispatcher,
  Console, Mailer, Twig-Bridge
- **Doctrine ORM + Migrations** — Persistenz
- **MySQL 8** — lokal oder über bestehenden Proxmox-Container (siehe eigene Doku)
- **Twig** — Templates
- **Composer / PSR-4** — Autoloading, Namespaces
- **PHPUnit** — Unit- und Functional-Tests

## Architektur-Überblick

Feature-basierte Struktur (Stand 2026-08-09): Code wird nach fachlichem Bereich gruppiert, nicht nach technischer
Schicht — jeder Bereich bringt seine eigenen `Controller/`, `Repository/`, `Enum/`, `Form/`, `Service/` usw. mit, je
nachdem was er tatsächlich braucht. `Entity/` und `DataFixtures/` bleiben bewusst als gemeinsame Top-Level-Ordner
bestehen (Doctrine-Mapping-Pfad in `config/packages/doctrine.yaml` zeigt fest auf `src/Entity`, Fixtures betreffen
ohnehin mehrere Bereiche gleichzeitig):

```
src/
  Home/             Portfolio-Startseite (unabhängig vom Shop)
    Controller/

  Account/          Benutzerkonto: Login/Registrierung, Profil, Adressen
    Controller/     AccountController, AuthController
    Repository/     UserRepository, AddressRepository
    Enum/           UserRole, Country, Gender
    Form/           RegistrationFormType, AddressFormType

  Shop/             Katalog: Produkte, Kategorien, Breadcrumbs, Pagination
    Controller/     ShopHomeController, ShopProductController, ProductDetailsController
    Repository/     ProductRepository, CategoryRepository
    Enum/           ProductSort
    Extension/      CategoryExtension (Twig-Funktion shop_categories())
    Service/        BreadcrumbService
    ValueObject/    Breadcrumb, PaginatedResult

  Entity/           Doctrine-Entities aller Bereiche (gemeinsamer Mapping-Pfad)
  DataFixtures/     Testdaten aller Bereiche
templates/          Twig-Views
tests/              PHPUnit (Unit + Functional)
```

Neue fachliche Bereiche (Cart, Checkout, Coupon, Admin, ...) bekommen ab Phase 4 jeweils einen eigenen Top-Level-Ordner
nach demselben Muster (`Controller/`, `Service/`, `Enum/`, ... innerhalb des Bereichs), sobald sie in Angriff genommen
werden — siehe [[TODO.md]]. Controller bleiben dabei dünn und delegieren an Services — Business-Logik gehört in
`Service/`, nicht in den Controller. Das ist selbst der wichtigste OOP-Lerneffekt des ganzen Projekts:
Verantwortlichkeiten sauber trennen.

## Domänenmodell (Kern-Entities)

- **Product** — Name, Beschreibung, Preis (als `Money`-Value-Object), SKU, Lagerbestand, Bilder, Slug
- **Category** — selbstreferenzierend (Parent/Children) → Baumstruktur, gutes Übungsfeld für rekursive Methoden
- **User** — E-Mail, Passwort-Hash, Rollen, Adressen
- **Address** — Value Object (Straße, PLZ, Stadt, Land)
- **Cart / CartItem** — zunächst Session-basiert, später optional an User gebunden
- **Order / OrderItem** — mit `OrderStatus`-Enum und State-artigen Übergängen
- **Coupon** — Code, Typ, Gültigkeitsregeln

## Bewusst eingebaute Komplexität (Lernkatalog)

Diese Muster sind absichtlich drin, auch wenn ein "echter" Mini-Shop sie nicht zwingend bräuchte — genau das ist der
Punkt:

| Pattern / Konzept | Wo im Projekt                                                           | Warum lernenswert                                             |
|-------------------|-------------------------------------------------------------------------|---------------------------------------------------------------|
| Value Objects     | `Money`, `Address`                                                      | Immutability, `equals()`, keine Floats für Geld               |
| Strategy          | Rabattarten (`DiscountInterface` + Implementierungen)                   | Austauschbares Verhalten ohne if/else-Kaskaden                |
| State             | `Order`-Status-Übergänge                                                | Gültige Zustandswechsel erzwingen, nicht per String-Vergleich |
| Factory           | Cart → Order Konvertierung                                              | Objekterzeugung kapseln, Invarianten sichern                  |
| Repository        | Doctrine-Repositories                                                   | Query-Logik von Entities trennen                              |
| Service Layer     | `OrderService`, `CheckoutService`, `DiscountService`                    | Business-Logik testbar & wiederverwendbar halten              |
| Observer / Events | `OrderPlacedEvent` + Listener (Lagerbestand reduzieren, Mail versenden) | Entkopplung von Nebeneffekten                                 |
| Command Pattern   | Symfony-Console-Commands                                                | CLI-Teil des Projekts, z. B. `app:orders:cancel-expired`      |
| Security Voter    | z. B. "darf User diese Order sehen/bearbeiten?"                         | Autorisierungslogik als eigene Klasse statt Controller-if     |
| Custom Exceptions | `OutOfStockException`, `InvalidCouponException`                         | Sprechende Fehler statt generischer Exceptions                |
| Enums             | `OrderStatus`, `UserRole`, `DiscountType`                               | PHP-8.1-Enums statt magischer Strings                         |
| Traits            | z. B. `TimestampableTrait` (createdAt/updatedAt)                        | Geteiltes Verhalten ohne Vererbungshierarchie                 |
| Interfaces + DI   | `DiscountInterface`, `StockManagerInterface`                            | Symfony-DI-Container, Testbarkeit durch Austauschbarkeit      |

## Nicht-Ziele / bewusste Abgrenzung

- Kein echtes Payment-Gateway — ein simulierter/"Fake"-Zahlungsschritt reicht als Lernbeispiel
- Keine Mehrsprachigkeit/Multi-Tenancy — kann als spätere Erweiterung folgen, ist aber nicht Teil des Kernumfangs
- Kein fertiges Admin-Bundle (z. B. Sonata/EasyAdmin) — der Admin-Bereich wird bewusst selbst gebaut, weil genau das den
  OOP-Lerneffekt bringt

## Grober Phasenplan

Details und einzelne Schritte stehen in `TODO.md`. Kurzüberblick:

0. Setup (Symfony-Skeleton, DB, Git)
1. Domänen-Fundament (Money, Product, Category, Migrations)
2. Katalog-Frontend (Twig, Kategoriebaum, Produktlisten)
3. Benutzerkonten (Security, Rollen)
4. Warenkorb
5. Rabatte/Coupons
6. Checkout & Bestellungen (State + Factory)
7. Events & CLI-Commands
8. Eigener Admin-Bereich
9. Tests
10. Portfolio-Feinschliff (README, Screenshots, Deploy-Hinweise)
