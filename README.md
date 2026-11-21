# Portfolio-Shop (Symfony/PHP)

Details zu Ziel und Architektur siehe [KONZEPT.md](KONZEPT.md), Aufgaben-Fortschritt siehe [TODO.md](TODO.md).

https://claude.ai/code/artifact/088bb3fd-438d-4cd9-9432-69c6112abc39

## Setup

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

## Lokal starten

Symfony-CLI ist hier nicht installiert, daher PHP's eingebauter Webserver:

```
php -S localhost:8000 -t public
```

Danach im Browser z. B. `http://localhost:8000/products` aufrufen.

```
yarn run dev-server
```

**Wichtig bei Attribut-Routing:** Ein `#[Route(...)]` auf der **Klasse** registriert für sich allein noch keine
erreichbare Route — es dient nur als Präfix/Default für Routen auf einzelnen **Methoden**. Ohne zusätzliches
`#[Route(...)]` direkt über der Controller-Methode bleibt die Seite ein 404, selbst wenn der Server läuft. Mit
`php bin/console debug:router` prüfen, ob eine Route tatsächlich registriert wurde.

## Entities & Migrations

Neue Entity per Wizard erzeugen:

```
php bin/console make:entity
```

Migration aus den aktuellen Entity-Mappings generieren:

```
php bin/console make:migration
```

Migration ausführen:

```
php bin/console doctrine:migrations:migrate
```

Migrations-Status anzeigen:

```
php bin/console doctrine:migrations:status
```

Mapping gegen die DB validieren (rein lesend, ändert nichts):

```
php bin/console doctrine:schema:validate
```

## Fixtures / Testdaten

Bundle (bereits installiert): `doctrine/doctrine-fixtures-bundle`, erzeugt `src/DataFixtures/ProductFixtures.php`.

Für generische Testdaten (Preis, Lagerbestand, Text) zusätzlich `fakerphp/faker` (dev-only):

```
composer require --dev fakerphp/faker
```

```php
$faker = \Faker\Factory::create('de_DE');
```

Fixtures laden (**Achtung:** fragt standardmäßig, ob die DB vorher komplett geleert werden soll):

```
php bin/console doctrine:fixtures:load
```

Daten ergänzen statt DB zu leeren:

```
php bin/console doctrine:fixtures:load --append
```

**Bekannte Einschränkung bei diesem Schema:** Sowohl der Standard-Purge (`DELETE`) als auch `--purge-with-truncate`
scheitern bei uns an Fremdschlüssel-Constraints — `Category` ist selbstreferenzierend (`parent_id`) und
`product_category` (die ManyToMany-Join-Tabelle) referenziert zusätzlich `product`/`category`. MySQL lässt weder ein
einfaches `DELETE` in sinnvoller Reihenfolge noch `TRUNCATE` einer referenzierten Tabelle zu. Zuverlässiger Ersatz — DB
komplett neu aufbauen, dann ohne Purge befüllen:

```
composer db-reset
```

(entspricht: `doctrine:database:drop --force` → `doctrine:database:create` → `doctrine:migrations:migrate` →
`doctrine:fixtures:load --append`, siehe `composer.json`)

## Frontend / Assets (Webpack Encore, Sass, Tailwind)

Bundle: `symfony/webpack-encore-bundle` (Node.js-basiert, bewusst statt AssetMapper gewählt — u. a. wegen späterer
Vue-Komponenten, die mit AssetMapper/Importmaps nicht gut funktionieren). Einstiegspunkt: `assets/app.js`, das zwei
Stylesheets importiert:

- `assets/styles/tailwind.css` — reines CSS (kein Sass!), enthält nur `@import "tailwindcss";` +
  `@source not "../../public";`. Bewusst *nicht* über Sass geführt, siehe Stolperfalle unten.
- `assets/styles/index.scss` — eigene Komponenten-Styles (`button.scss`, `layout.scss`), nutzt
  `@reference "./tailwind.css";` um innerhalb von `@apply`-Regeln auf Tailwinds Utilities zugreifen zu können.

Konfiguration in `webpack.config.js` (`.enableSassLoader()`, `.enablePostCssLoader()`), Tailwind-PostCSS-Plugin in
`postcss.config.js`.

Abhängigkeiten installieren:

```
yarn install
```

**Dev-Server mit Live-Reload (empfohlen für aktive Frontend-Arbeit):**

```
yarn dev-server
```

Läuft unter `http://localhost:8080`. Symfony erkennt automatisch, dass der Dev-Server aktiv ist, und lädt Assets von
dort statt aus `public/build/` — **zusätzlich** weiterhin `php -S localhost:8000 -t public` in einem zweiten Terminal
laufen lassen und die Seite über Port 8000 aufrufen, nicht über 8080.

Alternativen ohne Live-Reload:

```
yarn watch   # baut bei jeder Änderung automatisch neu, kein Browser-Auto-Reload
yarn dev     # einmaliger Dev-Build
yarn build   # Produktions-Build (minifiziert, gehashte Dateinamen)
```

**Tailwind-Klassen im Template geändert/neu gesetzt → neu bauen nötig.** Tailwind v4 scannt beim Build automatisch die
Projektdateien (auch `.twig`) nach tatsächlich verwendeten Klassennamen und erzeugt nur dafür die passende CSS-Regel —
das passiert beim Build-Schritt, nicht automatisch beim Speichern der Twig-Datei. Ohne Rebuild bleibt die Seite optisch
auf dem alten Stand (kein Fehler, nur "noch nicht kompiliert"). Für aktive Arbeit an Templates deshalb `yarn dev-server`
bzw. `yarn watch` im Hintergrund laufen lassen, statt manuell nach jeder Änderung `yarn dev` aufzurufen.

**Stolperfalle: Endlos-Rebuild-Loop.** `webpack-dev-server` fehlte ursprünglich in `package.json`
(`yarn add -D webpack-dev-server` nötig). Zusätzlich scannt Tailwinds automatische Source-Erkennung standardmäßig auch
`public/` (wegen `public/index.php`) — da Encore bei jedem Build `manifest.json`/`entrypoints.json` nach `public/build/`
schreibt, hat das den Watcher immer wieder neu ausgelöst → sofortiger erneuter Build → Endlosschleife, mehrere Rebuilds
pro Sekunde, Browser lädt pausenlos neu. Fix ist die `@source not "../../public";`-Zeile in `tailwind.css`.

**Stolperfalle: fehlende peerDependencies unter Yarn.** `@symfony/webpack-encore` deklariert `webpack`, `webpack-cli`,
`@babel/core`, `@babel/preset-env` u. a. nur als `peerDependencies`, nicht als eigene Dependencies. npm 7+ installiert
fehlende Peers automatisch mit, Yarn Classic (1.x) tut das nicht — Folge ist `ERR_MODULE_NOT_FOUND` beim Ausführen von
`yarn dev`/`yarn watch`. Fehlende Pakete einzeln nachinstallieren, z. B.:

```
yarn add -D webpack webpack-cli @babel/core @babel/preset-env
```

**Wichtig:** Damit kompiliertes CSS/JS überhaupt geladen wird, muss ein Template `base.html.twig` erweitern
(`{% extends 'base.html.twig' %}`) — nur dort stehen `encore_entry_link_tags('app')`/`encore_entry_script_tags('app')`.
Ein eigenständiges Template ohne `extends` bekommt keine Stylesheets/Scripts, Tailwind-Klassen darin bleiben unsichtbar
ohne Fehlermeldung.

**Stimulus-Controller:** `symfony/stimulus-bundle`s eigener Loader (`@symfony/stimulus-bundle`) ist für AssetMapper
gebaut (löst `controllers.json` über einen Composer/vendor-Pfad auf) und von Webpack aus nicht auflösbar.
`assets/stimulus_bootstrap.js` startet Stimulus stattdessen direkt über `@hotwired/stimulus` und registriert Controller
manuell (`app.register('hello', HelloController)`) — bei neuen Controllern dort ergänzen.

## TypeScript

`.enableTypeScriptLoader()` ist in `webpack.config.js` bereits aktiv. Fehlende Pakete installieren:

```
yarn add -D typescript ts-loader
```

Optional (empfohlen): Type-Checking läuft dann in einem separaten Prozess statt den Webpack-Build zu blockieren:

```
yarn add -D fork-ts-checker-webpack-plugin
```

Bei aktiviertem `fork-ts-checker-webpack-plugin` zusätzlich in `webpack.config.js` einbinden:

```js
.
enableTypeScriptLoader((tsConfig) => {
    // z. B. tsConfig.configFile = './tsconfig.json'
})
    .enableForkedTypeScriptTypesChecking()
```

`tsconfig.json` im Projektroot anlegen:

```json
{
    "compilerOptions": {
        "target": "ES2020",
        "module": "ESNext",
        "moduleResolution": "bundler",
        "strict": true,
        "sourceMap": true,
        "noEmit": true,
        "esModuleInterop": true,
        "skipLibCheck": true,
        "allowImportingTsExtensions": true
    },
    "include": [
        "assets/**/*.ts"
    ]
}
```

`noEmit: true`, weil Webpack/ts-loader das eigentliche Kompilieren übernimmt — `tsconfig.json` dient hier nur der
IDE/Type-Checking-Konfiguration.

Danach `.ts`-Dateien unter `assets/` anlegen und ganz normal importieren, z. B. in `assets/app.js`:

```js
import './pagination';
```

mit `assets/pagination.ts` daneben. Nach dem Anlegen neuer `.ts`-Dateien `yarn dev-server` (bzw. `yarn watch`) neu
starten, falls es vorher schon lief.

## Tests

Alle Tests ausführen:

```
php bin/phpunit
```

Einzelne Testdatei ausführen:

```
php bin/phpunit phpUnit/ValueObject/MoneyTest.php
```

## Sonstiges

Paket hinzufügen:

```
composer require <paket>
```

Dev-Paket hinzufügen (z. B. Testing-Tools):

```
composer require --dev <paket>
```

Cache leeren (z. B. nach Config-Änderungen):

```
php bin/console cache:clear
```
