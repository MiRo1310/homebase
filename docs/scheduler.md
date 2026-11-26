# Scheduler & Worker

Zeitgesteuerte Aufgaben laufen über den **Symfony Scheduler** (`symfony/scheduler`), der auf Messenger aufbaut. Der
Zeitplan steht im Code in `src/Schedule.php`.

**Wichtig:** Der Zeitplan allein führt nichts aus. Es muss dauerhaft ein **Worker** laufen, der die fälligen Aufgaben
abarbeitet. Läuft kein Worker, passiert zum geplanten Zeitpunkt nichts (und es gibt auch keine Fehlermeldung).

## Aktuelle Aufgaben

| Zeitpunkt | Aufgabe |
|---|---|
| täglich 23:00 (`0 23 * * *`) | `app:device:sync-updates` – Update-Infos aller Geräte abrufen und speichern |

## Worker starten

```
php bin/console messenger:consume scheduler_default -vv
```

- `scheduler_default` ist der Transport des Schedules mit `#[AsSchedule]` ohne Namen. Bei einem benannten Schedule
  (`#[AsSchedule('name')]`) heißt er `scheduler_name`.
- `-vv` zeigt an, welche Messages verarbeitet werden. Für den Dauerbetrieb kann es weggelassen werden.
- Der Worker läuft, bis er mit `Strg+C` beendet wird.

**Nach Code-Änderungen den Worker neu starten.** Ein laufender Worker hat den alten Code geladen. Lokal: `Strg+C` und neu
starten. Auf dem Server alle Worker sauber beenden lassen (sie werden dann vom Prozessmanager neu gestartet):

```
php bin/console messenger:stop-workers
```

## Zeitplan prüfen

```
php bin/console debug:scheduler
```

Zeigt alle Aufgaben mit Message und **nächstem Ausführungszeitpunkt**. Ein ungültiger Cron-Ausdruck fällt hier bereits
auf.

## Ausprobieren

1. In `src/Schedule.php` den Ausdruck vorübergehend auf `'* * * * *'` (jede Minute) stellen.
2. Worker mit `-vv` starten.
3. Nach spätestens einer Minute sollte die Ausgabe des Commands erscheinen.
4. Ausdruck wieder auf den eigentlichen Wert zurückstellen.

## Aufgabe hinzufügen

In `src/Schedule.php` mit `->with(...)`:

```php
// Console-Command zu festen Zeiten (braucht dragonmantank/cron-expression)
RecurringMessage::cron('0 23 * * *', new RunCommandMessage('app:device:sync-updates'))

// oder als Intervall in Klartext (kein Zusatzpaket nötig), gezählt ab Start des Workers
RecurringMessage::every('1 hour', new RunCommandMessage('app:device:sync-updates'))
```

`RunCommandMessage` kommt aus `Symfony\Component\Console\Messenger\RunCommandMessage`; der String ist der Command so,
wie man ihn in der Konsole eintippen würde (inkl. Argumente).

### Cron-Syntax

```
┌──────── Minute     (0–59)
│ ┌────── Stunde     (0–23)
│ │ ┌──── Tag        (1–31)
│ │ │ ┌── Monat      (1–12)
│ │ │ │ ┌ Wochentag  (0–6, 0 = Sonntag)
0 23 * * *
```

| Ausdruck | Bedeutung |
|---|---|
| `0 23 * * *` | täglich um 23:00 |
| `*/10 * * * *` | alle 10 Minuten |
| `10 * * * *` | stündlich um xx:10 |
| `* 23 * * *` | **jede Minute** zwischen 23:00 und 23:59 (Stolperfalle!) |

`dragonmantank/cron-expression` ist nur der Parser für diese Schreibweise, kein System-Cron.

### Zeitzone

Cron-Ausdrücke werden in der Zeitzone von PHP ausgewertet. In Docker-Containern ist das meist **UTC** – dann läuft
„23:00“ im Winter um 0:00 und im Sommer um 1:00 deutscher Zeit. Prüfen:

```
php -r "echo date_default_timezone_get();"
```

Bei Bedarf die Zeitzone explizit als drittes Argument mitgeben:

```php
RecurringMessage::cron('0 23 * * *', new RunCommandMessage('app:device:sync-updates'), new \DateTimeZone('Europe/Berlin'))
```

## Betrieb auf dem Server

Der Worker muss dauerhaft laufen und nach einem Absturz oder Neustart automatisch wieder starten. Übliche Wege:

- eigener Docker-Container mit `php bin/console messenger:consume scheduler_default` als Befehl und
  `restart: unless-stopped`
- systemd-Service oder Supervisor

Optional begrenzen, damit der Worker regelmäßig frisch startet (der Prozessmanager startet ihn dann neu):

```
php bin/console messenger:consume scheduler_default --time-limit=3600 --memory-limit=128M
```
