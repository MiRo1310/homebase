# Datenbank, Entities & Migrationen

## Entities & Migrationen

Neue Entity per Wizard erzeugen:

```
php bin/console make:entity
```

Migration aus den aktuellen Entity-Mappings generieren:

```
php bin/console make:migration
```

Die erzeugte Datei in `migrations/` vor dem Ausführen lesen: Stimmen Spalten, `NULL`/`NOT NULL`, Fremdschlüssel und
`ON DELETE`?

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

Der Abschnitt „Mapping“ prüft die Entities, der Abschnitt „Database“ ob die DB dazu passt. Vor einer neuen Migration ist
„Database: not in sync“ normal, danach sollten beide `[OK]` sein.

## SQL direkt ausführen

```
php bin/console dbal:run-sql "SELECT COUNT(*) FROM device_update"
```

## Doctrine-Beziehungen (Merkzettel)

- Beziehungs-Attribute (`ManyToOne`, `OneToMany`, `JoinColumn`) stehen immer **über der Property**, nie über der Klasse.
- `ManyToOne` steht auf der Seite mit dem Fremdschlüssel (z. B. `DeviceUpdate::$device` → Spalte `device_id`).
- `inversedBy` (auf der `ManyToOne`-Seite) und `mappedBy` (auf der `OneToMany`-Seite) nennen jeweils den Namen der
  Property auf der anderen Seite.
- Collections: Typ ist das Interface `Doctrine\Common\Collections\Collection`, im Konstruktor wird eine
  `new ArrayCollection()` angelegt.
- `cascade: ['persist']` gehört auf die Seite, **von der aus** man arbeitet (z. B. `$device->getUpdates()->add(...)` →
  am `OneToMany` in `Device`). Reines Doctrine-Verhalten, keine Migration nötig.
- `orphanRemoval: true`: Aus der Collection entfernte Einträge werden beim `flush()` gelöscht.
- `onDelete: 'CASCADE'` im `JoinColumn` ist dagegen eine Regel **in der Datenbank** (braucht eine Migration).

## IDs & `AUTO_INCREMENT`

- Gelöschte IDs werden nicht wiederverwendet, der Zähler läuft nur hoch. `INT` reicht bis ca. 2,1 Mrd., `BIGINT`
  praktisch unbegrenzt (Doctrine liefert `bigint` in PHP als String).
- `TRUNCATE TABLE <tabelle>` leert die Tabelle und setzt den Zähler auf 1 zurück. Funktioniert nur bei Tabellen, auf die
  kein Fremdschlüssel zeigt.
- **Nicht** für `device_update` als Routine verwenden: Damit gingen die Updates von Geräten verloren, die beim nächsten
  Sync nicht erreichbar sind.

## Fixtures / Testdaten

Bundle (bereits installiert): `doctrine/doctrine-fixtures-bundle`, Fixtures liegen in `src/DataFixtures/`.

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
