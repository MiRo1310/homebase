# TypeScript

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
