# Frontend / Assets (Webpack Encore, Sass, Tailwind)

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

TypeScript-Einrichtung siehe [TypeScript](typescript.md).
