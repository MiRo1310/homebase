# Portfolio-Shop (Symfony/PHP)

Details zu Ziel und Architektur siehe [KONZEPT.md](KONZEPT.md), Aufgaben-Fortschritt siehe [TODO.md](TODO.md).

https://claude.ai/code/artifact/088bb3fd-438d-4cd9-9432-69c6112abc39

## Schnellstart

```
composer install
yarn install
php bin/console doctrine:migrations:migrate
php -S localhost:8000 -t public
```

Vorher `.env.local` mit der DB-Verbindung anlegen, siehe [Setup](docs/setup.md).

## Dokumentation

| Bereich | Inhalt |
|---|---|
| [Setup & lokal starten](docs/setup.md) | Installation, `.env.local`, Webserver, Routing |
| [Datenbank & Migrationen](docs/datenbank-migrationen.md) | Entities, Migrationen, Doctrine-Beziehungen, IDs, Fixtures |
| [Console-Commands](docs/console-commands.md) | eigene Commands (`app:device:sync-updates`), Commands auf Methoden |
| [Scheduler & Worker](docs/scheduler.md) | zeitgesteuerte Aufgaben, Worker starten, Cron-Syntax, Zeitzone |
| [Tests](docs/tests.md) | PHPUnit ausführen, Stubs/Mocks, Stolperfallen |
| [Frontend](docs/frontend.md) | Webpack Encore, Sass, Tailwind, Stimulus |
| [TypeScript](docs/typescript.md) | TypeScript-Einrichtung |
| [Sonstiges](docs/sonstiges.md) | Composer, Cache, Autowiring-Fehler |
