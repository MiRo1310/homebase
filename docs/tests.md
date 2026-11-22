# Tests

Alle Tests ausführen:

```
php bin/phpunit
```

Einzelne Testdatei ausführen:

```
php bin/phpunit tests/Device/Service/DeviceServiceTest.php
```

Einzelnen Test ausführen:

```
php bin/phpunit --filter test_unknown_device_returns_false
```

## Hinweise

- **`createStub` statt `createMock`**, solange keine Aufruf-Erwartungen geprüft werden. PHPUnit 13 meldet bei Mocks ohne
  Erwartungen einen Notice, und `phpunit.dist.xml` hat `failOnNotice="true"`. `createMock` nur zusammen mit
  `expects(...)`, z. B. `expects($this->never())->method('flush')`.
- Damit die IDE `->method()` kennt, die Properties als Schnittmengentyp deklarieren: `private SshConnection&Stub $ssh;`
- Reihenfolge im Test: **vorbereiten** (Stubs konfigurieren) → **ausführen** → **prüfen**. Stubs, die erst nach dem
  Aufruf konfiguriert werden, wirken nicht.
- Unterschiedliches Verhalten je nach Argument: `willReturnCallback(fn (string $host) => ...)`.
- **`BufferedOutput::fetch()` leert den Puffer.** Einmal in eine Variable lesen und dann mehrfach prüfen.
- Kein `dd()` in Tests: beendet den Prozess („Premature end of PHP process“). Stattdessen `dump()`.
- Unvollständige Tests mit `static::markTestIncomplete('TODO');` markieren – sie erscheinen dann als „Incomplete“
  statt grün.
