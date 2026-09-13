# Best Practices

Empfehlungen für MBlock in REDAXO-Projekten.

## 1. Widget-Ids eindeutig halten

Das häufigste Problem: "Die Medialist im zweiten Block bleibt leer." Ursache sind gleiche Ids für `REX_MEDIA`, `REX_MEDIALIST`, `REX_LINK` oder `REX_LINKLIST` in verschiedenen MBlocks desselben Moduls. REDAXO bietet je Widget-Typ die Ids 1 bis 10; MBlock leitet daraus die eindeutigen Ids je Block ab.

Falsch:

```php
// MBlock 1
$mform1->addMediaField(2, ['label' => 'Profilbild']);
// MBlock 2 im selben Modul
$mform2->addMediaField(2, ['label' => 'News-Bild']); // Konflikt
```

Richtig:

```php
// MBlock 1: Media-Ids 1 und 2
$mform1->addMediaField(1, ['label' => 'Profilbild']);
// MBlock 2: Media-Ids 3 und 4
$mform2->addMediaField(3, ['label' => 'News-Bild']);
$mform2->addMedialistField(4, ['label' => 'Galerie']);
```

Das Schema am besten im Projekt dokumentieren:

```
REX_VALUE[1]  Teammitglieder   Media-Ids 1, 2
REX_VALUE[2]  News-Cards       Media-Ids 3, 4
REX_VALUE[3]  Galerie          Media-Ids 5, 6
```

## 2. Frontend-Ausgabe

Nur Online-Blöcke laden und jede Ausgabe escapen:

```php
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

foreach ($items as $item) {
    $title = rex_escape($item['title'] ?? '');
    $file  = $item['REX_MEDIA_1'] ?? '';
    if ($file && ($media = rex_media::get($file))) {
        $src = rex_media_manager::getUrl('rex_media_medium', $media->getFileName());
        echo '<img src="' . $src . '" alt="' . rex_escape($media->getTitle()) . '">';
    }
    echo '<h3>' . $title . '</h3>';
}
```

Erst filtern, dann sortieren, dann begrenzen:

```php
$active = MBlock::filterByField($items, 'status', 'active');
$sorted = MBlock::sortByField($active, 'date', 'desc');
$top    = MBlock::limitItems($sorted, 5);
```

Strukturierte Daten für Suchmaschinen liefert `generateSchema()` als fertiges Script-Tag:

```php
echo MBlock::generateSchema($items, 'Person', ['name' => 'name', 'jobTitle' => 'position', 'image' => 'REX_MEDIA_1']);
```

## 3. Backend-Konfiguration

```php
echo MBlock::show($id, $mform, [
    'min' => 1,   // mindestens ein Block, wird initial angezeigt
    'max' => 10,  // Hinzufügen ab 10 Blöcken gesperrt
]);
```

Der Online/Offline-Schalter erscheint automatisch, sobald das Formular ein Hidden-Feld `mblock_offline` enthält (und die Option in den Einstellungen aktiv ist):

```php
$mform->addHiddenField("$id.0.mblock_offline", '0');
```

Copy & Paste ist standardmäßig aktiv und lässt sich global in den Einstellungen oder je Aufruf mit `'copy_paste' => false` abschalten.

Sprechende Feldnamen erleichtern die Ausgabe:

```php
$mform->addTextField("$id.0.title", ['label' => 'Titel']);     // gut
$mform->addTextField("$id.0.f1", ['label' => 'Titel']);        // schwer zu lesen
```

Fieldsets strukturieren lange Formulare:

```php
$mform = MForm::factory()
    ->addFieldsetArea('Inhalt', MForm::factory()
        ->addTextField("$id.0.title", ['label' => 'Titel']))
    ->addFieldsetArea('Bild', MForm::factory()
        ->addMediaField(1, ['label' => 'Bild', 'category' => 1]));
```

## 4. Eigene Templates

Eigene Templates liegen in `redaxo/data/addons/mblock/templates/<name>/` und überstehen Addon-Updates. Für reine Farbanpassungen reicht es, die CSS-Variablen `--mblock-*` in eigenem CSS zu überschreiben (siehe README, "Templates & Theming").

## 5. Fehlersuche

| Problem | Ursache | Lösung |
|---|---|---|
| Media-/Link-Felder im zweiten Block leer | gleiche Widget-Ids | Ids je MBlock eindeutig vergeben |
| Blöcke nicht sortierbar | Sortable.js fehlt | bloecks installieren (MBlock lädt sonst Sortable.js nach, prüfe die Browser-Konsole) |
| Kein Online/Offline-Button | Hidden-Feld fehlt oder Option deaktiviert | `mblock_offline` ergänzen, Einstellungen prüfen |
| Einfügen-Button gesperrt | Zwischenablage stammt aus anderem Modul | Block im selben Modul kopieren |
| Editor-Inhalt im falschen Block | Editor-Addon initialisiert nicht bei `rex:ready` | Addon aktualisieren; MBlock feuert `rex:ready` für jeden neuen Block |

Datenstruktur im Zweifel anzeigen:

```php
if (rex::isDebugMode()) {
    dump(MBlock::getDataArray("REX_VALUE[1]"));
}
```

Im Browser: Konsole auf JavaScript-Fehler prüfen und im Elements-Tab kontrollieren, dass jeder Block einen eigenen Index in den Feldnamen (`REX_INPUT_VALUE[1][n][...]`) hat.

## Kurzfassung

- Widget-Ids 1 bis 10 pro Modul eindeutig verteilen
- Im Frontend `getOnlineDataArray()` und `rex_escape()` verwenden
- `mblock_offline` als Hidden-Feld für den Online/Offline-Schalter
- `min`/`max` setzen, sprechende Feldnamen verwenden
- Eigene Templates und CSS-Variablen statt Änderungen im Addon
