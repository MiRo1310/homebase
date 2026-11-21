# TODO — PHP-Shop Portfolio-Projekt

Details/Hintergrund siehe [[KONZEPT.md]]. Reihenfolge ist als Empfehlung gedacht, Phasen dürfen sich überlappen. Häkchen
selbst setzen, sobald ein Punkt erledigt ist.

## Phase 0 — Setup

- [x] Alte Lern-Dateien (`index.php`, `file.php`, `fileWatcher.php`, `text.txt`) aus dem Projekt entfernen bzw. in einen
  `legacy/`-Ordner verschieben (bewusster Neustart)
- [x] Composer installieren (noch nicht vorhanden): Installer
  von [getcomposer.org/download](https://getcomposer.org/download/), danach `composer --version` prüfen
- [x] Composer global statt nur als `composer.phar` im
  Projekt: [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe) von getcomposer.org herunterladen und
  ausführen (richtet PATH automatisch ein); danach Terminal neu starten und `composer --version` prüfen; anschließend
  `composer.phar` aus dem Projekt entfernen
- [x] `zip`-Extension in `C:\php\php.ini` aktiviert (war auskommentiert, `php_zip.dll` war schon vorhanden) — Fix für
  `composer create-project`-Fehler "zip extension and unzip/7z commands are both missing"
- [x] ~~Symfony-Skeleton aufsetzen: `composer create-project symfony/website-skeleton .`~~ **funktioniert nicht** —
  `symfony/website-skeleton` pinnt intern noch auf die EOL/unsichere Symfony-6.1-Reihe; Composer 2.10 blockiert seit
  neuestem standardmäßig Pakete mit bekannten Security-Advisories, die Abhängigkeitsauflösung schlägt fehl ("Your
  requirements could not be resolved..."), der Befehl bricht aber nicht sauber ab, sondern hinterlässt nur
  `composer.json`/`composer.lock`/`vendor/` ohne `src/`, `config/`, `public/`, `bin/` etc.
    - **Stattdessen (getestet, funktioniert):**
        1. `portfolio`-Ordner leeren (auch `.env` vorher sichern/Inhalt kopieren)
        2. `composer create-project symfony/skeleton .` (aktuell installiert das Symfony 8.1 — falls laut `KONZEPT.md`
           lieber Symfony 7.x gewünscht: `composer create-project symfony/skeleton:"7.*" .`)
        3. `composer require webapp` — fügt das Full-Stack-Paket nach (Twig, Doctrine, Forms, Security, Mailer etc.,
           entspricht dem, was `website-skeleton` sonst mitgebracht hätte)
        4. DATABASE_URL wieder eintragen — **wichtig:** gehört in `.env.local` (git-ignored, siehe Punkt unten), nicht
           in die von Composer erzeugte `.env` (die wird versioniert und enthält nur Platzhalter/Defaults)
- [x] MySQL-Zugang einrichten (lokal oder bestehender Proxmox-Container), `.env.local` mit `DATABASE_URL` befüllen —
  Format: `DATABASE_URL="mysql://USER:PASSWORT@HOST:PORT/DATENBANKNAME?serverVersion=8.0.32&charset=utf8mb4"`,
  DB-Name-Vorschlag: `portfolio_shop`
- [x] `doctrine/doctrine-bundle` + `doctrine/orm` prüfen (im `webapp`-Pack meist schon dabei),
  `bin/console doctrine:database:create` testen
- [x] Git: `.env.local` in `../.gitignore`, sauberer erster Commit mit Skeleton
- [x] PHPUnit installieren (`symfony/test-pack`) `composer require --dev symfony/test-pack`, leeren Beispieltest zum
  Laufen bringen

**Lernfokus:** Projekt-Setup, Composer/PSR-4, Symfony-Grundstruktur verstehen.

## Phase 1 — Domänen-Fundament

- [x] `ValueObject\Money` bauen (immutable, intern in Cent rechnen, `add()`, `equals()`, `__toString()`)
- [x] `Entity\Category` (selbstreferenzierend: `parent`, `children`), Doctrine-Mapping
- [x] `Entity\Product` (Name, Slug, Preis als `Money` via Doctrine-Embeddable oder Custom Type, Lagerbestand,
  Kategorie-Zuordnung)
    - ~~Kategorie-Zuordnung zunächst als `ManyToOne` (ein Produkt → eine Kategorie)~~ — nachträglich (während Phase 2)
      auf `ManyToMany` umgebaut (ein Produkt → mehrere Kategorien), da realistischer für einen Shop; siehe Punkt in
      Phase 2
- [x] `TimestampableTrait` (createdAt/updatedAt) und in Entities einsetzen
- [x] Migrations generieren & anwenden (`bin/console make:migration`, `doctrine:migrations:migrate`)
- [x] Fixtures/Testdaten für Produkte & Kategorien (`doctrine/doctrine-fixtures-bundle`)

**Lernfokus:** Value Objects, Doctrine-Relationen (Self-Referencing, ManyToOne → später ManyToMany), Traits.

## Phase 2 — Katalog-Frontend

- [x] `Repository\ProductRepository` mit eigenen Query-Methoden (z. B. `findActiveByCategory`, `findActive`)
- [x] Product/Category-Beziehung von `ManyToOne` auf `ManyToMany` umbauen (Entity-Mapping, Migration, Fixtures,
  Repository-Query, Controller/Template anpassen)
- [x] Controller + Twig-Template: Produktliste mit Kategorie-Filter
- [x] Kategoriebaum rekursiv rendern (Twig-Include oder rekursive PHP-Methode)
- [x] Produkt-Detailseite
- [x] Einfache Sortierung/Pagination (manuell über `QueryBuilder`, bewusst gegen `KnpPaginatorBundle` entschieden, siehe
  `PaginatedResult`-VO)

**Lernfokus:** Repository-Pattern, Twig-Vererbung/Includes, rekursive Methoden.

## Phase 3 — Benutzerkonten

- [x] `Entity\User` (Symfony Security), `Enum\UserRole`
- [x] Registrierung + Login (Symfony Security Component, Passwort-Hashing)
- [x] `Entity\Address` für Lieferadressen (Entity, Repository, Formular inkl. Validierung)
- [x] Profilseite (Anzeigen/Ändern von E-Mail & Passwort über `AccountController`, Event-Dispatching bei
  Passwort-Änderung)
- [x] Rollenbasierte Zugriffssteuerung (Kunde vs. Admin) testen
- [x] `remember_me` konfigurieren ("Angemeldet bleiben"-Checkbox beim Login, eigenes signiertes Cookie unabhängig von
  der PHP-Session)

**Lernfokus:** Security Component, Value Objects vs. Entities, Autorisierung.

## Phase 4 — Warenkorb

- [ ] `Entity`/DTO `OrderItem`, `Service\CartService` (Session-basiert)
- [ ] Hinzufügen/Entfernen/Menge ändern, Zwischensumme berechnen
- [ ] `OutOfStockException` bei Lagerbestand-Verletzung, im Controller sauber abfangen
- [ ] Warenkorb-Widget/-Seite in Twig

**Lernfokus:** Service-Layer, Custom Exceptions, zustandsbehaftete Logik.

## Phase 5 — Rabatte/Coupons

- [ ] `Pricing\DiscountInterface` definieren
- [ ] Implementierungen: `PercentageDiscount`, `FixedAmountDiscount`, `MinimumOrderValueDiscount`
- [ ] `Entity\Coupon` + `Enum\DiscountType`, Zuordnung Coupon → Discount-Strategie
- [ ] `Service\DiscountService` wendet passende Strategie auf Cart an
- [ ] `InvalidCouponException` für abgelaufene/ungültige Codes

**Lernfokus:** Strategy-Pattern, Interfaces + Dependency Injection.

## Phase 6 — Checkout & Bestellungen

- [ ] Steuer/USt. berücksichtigen: `Product::price` gilt bis dahin als **Nettopreis** (bewusste Vorentscheidung);
  `taxRate` kommt später pro Produkt dazu (nicht global, unterschiedliche Sätze möglich) und wird dann auf `OrderItem`
  -Ebene draufgeschlagen (nicht auf `Money`/`Product` selbst). Bei Ausweitung auf Business-Customer zusätzlich
  Reverse-Charge (USt-IdNr., keine USt. bei B2B-EU-Ausland) und Netto-statt-Brutto-Anzeige für Geschäftskunden — braucht
  vorher eine Unterscheidung Privat-/Geschäftskunde auf `User` (aktuell nur `UserRole::Customer`/`Admin`)
- [ ] Multi-Currency: `Product::price`/`Money::currency` bleibt bewusst die Basiswährung des Angebots
  (Verkäufer-Perspektive), das ändert sich auch bei Multi-Currency nicht. Zusätzlich käme eine **bevorzugte
  Kunden-/Anzeigewährung** dazu (Feld auf `User`, für Gäste Session) sowie ein `CurrencyConverter`/
  `ExchangeRateProvider`-Service, den `OrderService`/`Order` nutzt, um `Product::price` in die Kundenwährung umzurechnen
  (Wechselkurs-Quelle, Aktualisierungsintervall, Rundungsregeln noch offen)
- [ ] `Enum\OrderStatus` (z. B. Created, Paid, Shipped, Completed, Cancelled)
- [ ] Zustandsübergänge kapseln (State-artig: nur erlaubte Übergänge zulassen, sonst Exception)
- [ ] `Service\CheckoutService` / Factory: Cart → Order (inkl. Adress-Snapshot, Rabatt übernehmen)
- [ ] Mehrstufiger Checkout-Flow (Adresse → Zahlungsart (Fake) → Bestätigung)
- [ ] Bestellübersicht für User ("Meine Bestellungen")

**Lernfokus:** State-Pattern, Factory-Pattern, Transaktionssicherheit (Doctrine Transactions).

## Phase 7 — Events & CLI-Commands

- [ ] `OrderPlacedEvent` + EventDispatcher nutzen
- [ ] Listener: Lagerbestand reduzieren
- [ ] Listener: Bestätigungs-Mail versenden (Symfony Mailer, lokal z. B. Mailtrap/Mailpit)
- [ ] Console-Command `app:orders:cancel-expired` (z. B. unbezahlte Bestellungen nach X Minuten stornieren)
- [ ] Console-Command `app:catalog:report` (z. B. Bestandsübersicht als CLI-Ausgabe)

**Lernfokus:** Observer/Event-Pattern, Command-Pattern, Entkopplung von Nebeneffekten.

## Phase 8 — Eigener Admin-Bereich

- [ ] Security Voter für "darf Bestellung sehen/bearbeiten" (Admin vs. Owner)
- [ ] Admin-CRUD für Produkte/Kategorien (Symfony Forms + Validator)
- [ ] Admin-Ansicht für Bestellungen inkl. Status ändern (über die gekapselten Übergänge aus Phase 6)

**Lernfokus:** Security Voter, Symfony Forms/Validation, Wiederverwendung der Domänenlogik im Admin-Kontext.

## Phase 9 — Tests

- [ ] Unit-Tests: `Money`, Discount-Strategien, `OrderStatus`-Übergänge (reine Logik, ohne DB)
- [ ] Functional-Tests: Katalog-Controller, Checkout-Flow (Symfony `WebTestCase`)
- [ ] Test-Coverage für Security Voter (darf/darf nicht)

**Lernfokus:** Testbarkeit als Resultat guter OOP-Trennung — hier zeigt sich, ob Phase 1–8 sauber entkoppelt waren.

## Frontend-Tooling (Backlog, phasenübergreifend)

- [ ] `.enableTypeScriptLoader()` in `webpack.config.js` einkommentieren + `typescript` und `ts-loader` als
  devDependency installieren (beides nur peerDependency von Encore, wird nicht automatisch mitinstalliert)
- [ ] `.enableVueLoader()` in `webpack.config.js` ergänzen (aktuell nicht mal auskommentiert vorhanden) + `vue`,
  `vue-loader`, `@vue/compiler-sfc` als devDependency installieren, sobald erste Vue-Komponente geplant ist

## Phase 10 — Portfolio-Feinschliff

- [ ] README: Projektbeschreibung, Screenshots, Setup-Anleitung
- [ ] Kurzer Architektur-Abschnitt im README (Verweis auf Patterns aus `KONZEPT.md`)
- [ ] Optional: Docker-Compose für einfaches lokales Setup (MySQL + PHP)
- [ ] Deploy-Hinweise oder Live-Demo-Link, falls gehostet
