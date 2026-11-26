# Console-Commands

## Eigene Commands

| Command | Beschreibung |
|---|---|
| `php bin/console app:device:sync-updates` | Update-Infos aller Geräte per SSH abrufen und in `device_update` speichern |
| `php bin/console app:device:sync-updates "<Gerätename>"` | nur ein Gerät (Name aus der Tabelle `device`) |

Verhalten: Ist ein Gerät nicht erreichbar, wird der Fehler ausgegeben, die gespeicherten Updates dieses Geräts bleiben
unverändert, und die übrigen Geräte werden trotzdem abgearbeitet. Der Exit-Code ist dann `1` (in PowerShell:
`$LASTEXITCODE`).

Alle eigenen Commands anzeigen:

```
php bin/console list app
```

## Mehrere Commands in einer Klasse

Seit Symfony 8.1 kann `#[AsCommand]` auf **Methoden** stehen. Ein `#[AsCommand]` auf der **Klasse** ist dann das
Präfix für die Methoden-Commands:

```php
#[AsCommand('app:device')]
class DeviceCommands
{
    #[AsCommand('sync-updates', description: '...')]
    public function syncUpdates(OutputInterface $output): int { /* ... */ }
}
```

→ registriert `app:device:sync-updates`.

**Stolperfallen:**

- Das Präfix gibt es erst in einem späteren 8.1-**Patch**-Release (in 8.1.1 noch nicht, in 8.1.11 vorhanden). Bei
  älterem Stand kommt: *„must either be a subclass of Command or have an `__invoke()` method“* → `composer update
  "symfony/*"`.
- Mit Klassen-Präfix an den Methoden nur den relativen Namen angeben (`'sync-updates'`), nicht den vollen – sonst wirft
  Symfony einen Fehler.
