# Setup & lokal starten

## Installation

```
composer install
```

`.env.local` anlegen (git-ignored) mit der DB-Verbindung:

```
DATABASE_URL="mysql://USER:PASSWORT@HOST:PORT/DATENBANKNAME?serverVersion=8.0.32&charset=utf8mb4"
```

Datenbank anlegen:

```
php bin/console doctrine:database:create
```

Danach die Migrationen ausführen, siehe [Datenbank & Migrationen](datenbank-migrationen.md).

## Lokal starten

Symfony-CLI ist hier nicht installiert, daher PHP's eingebauter Webserver:

```
php -S localhost:8000 -t public
```

Danach im Browser z. B. `http://localhost:8000/products` aufrufen.

Für das Frontend zusätzlich in einem zweiten Terminal:

```
yarn run dev-server
```

Details siehe [Frontend](frontend.md).

Für zeitgesteuerte Aufgaben (z. B. den nächtlichen Geräte-Sync) muss außerdem der Scheduler-Worker laufen, siehe
[Scheduler & Worker](scheduler.md).

## Routing

**Wichtig bei Attribut-Routing:** Ein `#[Route(...)]` auf der **Klasse** registriert für sich allein noch keine
erreichbare Route — es dient nur als Präfix/Default für Routen auf einzelnen **Methoden**. Ohne zusätzliches
`#[Route(...)]` direkt über der Controller-Methode bleibt die Seite ein 404, selbst wenn der Server läuft. Mit
`php bin/console debug:router` prüfen, ob eine Route tatsächlich registriert wurde.
