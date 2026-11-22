# Sonstiges

Paket hinzufügen:

```
composer require <paket>
```

Dev-Paket hinzufügen (z. B. Testing-Tools):

```
composer require --dev <paket>
```

Symfony-Pakete innerhalb der erlaubten Version (z. B. `8.1.*`) auf den neuesten Patch-Stand bringen:

```
composer update "symfony/*"
```

Verfügbare neuere Versionen eines Pakets anzeigen:

```
composer show -l symfony/console
```

Cache leeren (z. B. nach Config-Änderungen):

```
php bin/console cache:clear
```

## Autowiring-Fehler

Jeder `bin/console`-Aufruf baut zuerst den Service-Container. Ein falscher Typ im Konstruktor irgendeiner Klasse in
`src/` legt deshalb **alle** Commands lahm.

Typischer Fall: falscher Import durch die IDE, z. B. `Symfony\Bridge\Doctrine\ManagerRegistry` statt
`Doctrine\Persistence\ManagerRegistry`. Faustregel: Für Abhängigkeiten im Konstruktor das **Interface** verwenden.
