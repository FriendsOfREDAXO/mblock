# MBlock API

MBlock erzeugt aus einem Modul-Formular beliebig viele sortierbare Datenblöcke und speichert sie als Array in einem `REX_VALUE`. Diese Seite beschreibt die PHP-API, die JavaScript-Schnittstelle, die Templates und den Build.

## Inhalt

- [Voraussetzungen](#voraussetzungen)
- [PHP-API](#php-api)
  - [Klassen](#klassen)
  - [MBlock::show()](#mblockshow)
  - [Daten auslesen](#daten-auslesen)
  - [Frontend-Helfer](#frontend-helfer)
  - [Schema.org](#schemaorg)
  - [rex_form und YForm](#rex_form-und-yform)
- [JavaScript](#javascript)
  - [Aufbau](#aufbau)
  - [Globale Funktionen](#globale-funktionen)
  - [MBlockClipboard](#mblockclipboard)
  - [MBlockOnlineToggle](#mblockonlinetoggle)
  - [Events](#events)
  - [Widgets in neuen Blöcken](#widgets-in-neuen-blöcken)
- [Templates](#templates)
- [Extension Points](#extension-points)
- [Build](#build)
- [Beispiele](#beispiele)

---

## Voraussetzungen

- REDAXO ^5.18
- bloecks (liefert Sortable.js für Drag & Drop; ohne bloecks lädt MBlock Sortable.js selbst nach)
- MForm (optional, für MForm-Formulare)

---

## PHP-API

### Klassen

Alle Klassen liegen im Namespace `FriendsOfRedaxo\MBlock`. Die alten globalen Namen (`MBlock`, `MBlockValueHandler`, `MBlockSystemButtonReplacer`, `mblock_rex_form`, ...) bleiben als Aliase erhalten, bestehender Code läuft also ohne `use`-Statement weiter.

```php
use FriendsOfRedaxo\MBlock\MBlock;
```

| Klasse | Aufgabe |
|---|---|
| `MBlock` | `show()` und die Daten-/Frontend-Helfer |
| `Handler\MBlockValueHandler` | Lädt die gespeicherten Werte (`REX_VALUE`, rex_form, YForm) |
| `Parser\MBlockParser` | Rendert Wrapper- und Element-Templates |
| `Provider\TemplateProvider`, `Provider\MBlockTemplateFileProvider` | Laden Templates aus `data/templates/` |
| `Replacer\*` | Schreiben Namen, Ids und Werte in das Formular-HTML |
| `Processor\mblock_rex_form` | rex_form-Variante mit MBlock-Feldern |
| `Utils\MBlockJsonHelper`, `Utils\MBlockSettingsHelper`, `Utils\MBlockSessionHelper`, `Utils\MBlockPageHelper` | Hilfsklassen |

### MBlock::show()

```php
MBlock::show($id, $form, array $settings = []): string
```

| Parameter | Beschreibung |
|---|---|
| `$id` | Nummer des `REX_VALUE` (1–20). Für YForm: `'yform::tabelle::feld'`. |
| `$form` | Formular-HTML als String, ein `MForm`-Objekt, ein `mblock_rex_form` oder ein `rex_yform`. |
| `$settings` | Optionen, siehe unten. |

Jeder Feldname im Formular muss dem Muster `REX_INPUT_VALUE[$id][0][feld]` folgen (MForm: `"$id.0.feld"`). MBlock ersetzt den Index `0` je Block. Native Widgets (`REX_MEDIA`, `REX_MEDIALIST`, `REX_LINK`, `REX_LINKLIST`) werden erkannt und je Block mit eindeutigen Ids versehen. Wichtig: Die Widget-Ids (1–10) müssen über alle MBlocks eines Moduls eindeutig sein.

Optionen:

| Option | Standard | Beschreibung |
|---|---|---|
| `min` | – | Mindestanzahl Blöcke; so viele werden initial angezeigt, Löschen-Buttons sind darunter gesperrt |
| `max` | – | Maximale Anzahl; der Hinzufügen-Button wird darüber gesperrt |
| `copy_paste` | Einstellung "Copy & Paste" | Kopieren/Einfügen-Buttons anzeigen |
| `delete_confirm` | Einstellung "Bestätigung vor dem Löschen" | `1` = Standardtext, `0` = aus, String = eigener Text |
| `input_delete` | Einstellung "Eingaben leeren" | Werte in neu hinzugefügten Blöcken leeren |
| `smooth_scroll` | Einstellung "Smooth Scroll" | Zum neuen Block scrollen |

Alle Optionen landen als `data-*`-Attribute am Wrapper (`data-min`, `data-max`, `data-copy_paste`, ...).

Der Online/Offline-Schalter hat keine eigene Option: Er erscheint, sobald das Formular ein Hidden-Feld `mblock_offline` enthält und die Einstellung "Online/Offline" aktiv ist.

```php
$mform->addHiddenField("$id.0.mblock_offline", '0');
```

Das aktive Template wird global in den Einstellungen gewählt (`mblock_theme`), nicht pro Aufruf.

```php
use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MBlock\MBlock;

$id = 1;
$mform = MForm::factory()
    ->addFieldsetArea('Teammitglied', MForm::factory()
        ->addTextField("$id.0.name", ['label' => 'Name'])
        ->addMediaField(1, ['label' => 'Foto'])
        ->addHiddenField("$id.0.mblock_offline", '0')
    );

echo MBlock::show($id, $mform, ['min' => 1, 'max' => 10]);
```

### Daten auslesen

Die Blöcke liegen als Array im `REX_VALUE`. `rex_var::toArray("REX_VALUE[1]")` liefert alle Blöcke; die MBlock-Methoden filtern zusätzlich nach dem Online/Offline-Feld.

```php
MBlock::getDataArray(string $rexValue, string $filter = 'all', string $offlineField = 'mblock_offline'): array
MBlock::getOnlineDataArray(string $rexValue, string $offlineField = 'mblock_offline'): array
MBlock::getOfflineDataArray(string $rexValue, string $offlineField = 'mblock_offline'): array

MBlock::filterByStatus(array $data, string $filter = 'all', string $offlineField = 'mblock_offline'): array
MBlock::getOnlineItems(array $data, string $offlineField = 'mblock_offline'): array
MBlock::getOfflineItems(array $data, string $offlineField = 'mblock_offline'): array
```

`$filter` ist `'all'`, `'online'` oder `'offline'`. Ein Block gilt als offline, wenn das Offline-Feld `'1'`, `true` oder `'true'` enthält.

```php
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

foreach ($items as $item) {
    echo rex_escape($item['name'] ?? '');
}
```

Es gibt keine Methode `isOnline()`. Für einzelne Blöcke reicht der Blick auf das Feld:

```php
$isOffline = ($item['mblock_offline'] ?? '') === '1';
```

### Frontend-Helfer

```php
MBlock::filterByField(array $items, string $field, mixed $value, bool $strict = false): array
MBlock::sortByField(array $items, string $field, string $direction = 'asc'): array
MBlock::groupByField(array $items, string $field): array
MBlock::limitItems(array $items, int $limit, int $offset = 0): array
```

- `filterByField` vergleicht mit `==`, bei `$strict = true` mit `===`. Blöcke ohne das Feld fallen weg. Für mehrere Werte mehrmals filtern oder `array_filter` nutzen.
- `sortByField` sortiert numerisch, wenn beide Werte numerisch sind, sonst mit `strcasecmp`. `$direction` ist `'asc'` oder `'desc'`. Die Schlüssel werden neu nummeriert.
- `groupByField` liefert `[feldwert => [blöcke]]`; fehlt das Feld, landet der Block unter `'undefined'`.
- `limitItems` ist ein `array_slice`.

```php
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");
$news  = MBlock::filterByField($items, 'category', 'news');
$news  = MBlock::sortByField($news, 'date', 'desc');
$top   = MBlock::limitItems($news, 5);

foreach (MBlock::groupByField($items, 'category') as $category => $group) {
    echo '<h2>' . rex_escape($category) . '</h2>';
}
```

### Schema.org

```php
MBlock::generateSchema(array $items, string $type = 'Article', array $fieldMapping = []): string
```

Liefert ein fertiges `<script type="application/ld+json">` mit einem `@graph` aus allen Blöcken, oder einen leeren String, wenn kein Block ein passendes Feld hat. Für `Article`, `Product` und `Event` gibt es Standard-Zuordnungen (z. B. `headline` aus `title`, `name` oder `headline`); `$fieldMapping` ergänzt oder überschreibt sie. Werte aus `REX_MEDIA_*`- und `REX_LINK_*`-Feldern werden in absolute URLs umgewandelt.

```php
echo MBlock::generateSchema($items, 'Person', [
    'name'     => 'name',
    'jobTitle' => 'position',
    'image'    => 'REX_MEDIA_1',
]);
```

### rex_form und YForm

`mblock_rex_form` ist ein `rex_form`, dessen Felder als MBlock gerendert werden können. Der Extension Point `REX_FORM_SAVED` (in `boot.php` registriert) speichert die Blöcke als JSON.

```php
$form = mblock_rex_form::factory('rex_meine_tabelle', '', 'id = ' . $id);
$form->addTextField('title');
echo MBlock::show('rex_meine_tabelle::mblock_field', $form);
```

Für YForm lautet die Id `'yform::tabelle::feld'`; das Formular ist das `rex_yform`-Objekt.

---

## JavaScript

### Aufbau

Das gesamte Verhalten steckt in `assets/mblock.js` (jQuery). `boot.php` lädt im Debug-Modus `mblock.js`, sonst `mblock.min.js`; über `$assetMode` in `boot.php` lässt sich das mit `'dev'` oder `'prod'` fest vorgeben. Die Datei enthält:

- Initialisierung und Sortable-Anbindung (`mblock_init`, `mblock_init_sort`, `mblock_sort`)
- Reindex der Feldnamen, Ids und Widget-Buttons je Block (`mblock_reindex`)
- Hinzufügen, Löschen, Verschieben (`mblock_add_item`, `mblock_remove_item`, `mblock_move`)
- Kopieren/Einfügen (`MBlockClipboard`) und Online/Offline (`MBlockOnlineToggle`)
- Popup-Bridges für `REX_MEDIA`, `REX_MEDIALIST`, `REX_LINK`, `REX_LINKLIST`, damit die Widget-Buttons neuer Blöcke mit der richtigen Id arbeiten
- Submit-Handler, der TinyMCE- und CKEditor-5-Inhalte in die Textareas schreibt

### Globale Funktionen

| Funktion | Beschreibung |
|---|---|
| `mblock_init($wrapper)` | Initialisiert einen `.mblock_wrapper` (wird für alle Wrapper bei `rex:ready` aufgerufen) |
| `mblock_init_sort($wrapper)` | Reindex und Sortable neu aufbauen |
| `mblock_add($wrapper)` | Klick-Handler der Buttons binden |
| `mblock_reindex($wrapper)` | Namen (`][n][`), Ids und Widget-Onclicks aller Blöcke neu vergeben |
| `mblock_smooth_scroll_to_element(el)` | Sanft zu einem Element scrollen |

Beim Minifizieren bleiben `mblock_init`, `mblock_init_sort`, `mblock_sort`, `mblock_add`, `MBlockClipboard`, `MBlockOnlineToggle` und `mblock_smooth_scroll_to_element` unter ihrem Namen erhalten; alle anderen Namen können sich in `mblock.min.js` ändern.

### MBlockClipboard

Kopieren legt den Block als HTML mit eingefrorenen Werten in der `sessionStorage` ab (Schlüssel `mblock_clipboard`, Inhalt `{html, moduleType, timestamp}`). Eingefügt werden kann nur im selben Modul; `moduleType` wird aus `module_id` des Slice-Formulars gebildet.

| Methode | Beschreibung |
|---|---|
| `MBlockClipboard.copy($wrapper, $item)` | Block kopieren |
| `MBlockClipboard.paste($wrapper, $afterItem)` | Nach `$afterItem` einfügen, sonst am Anfang |
| `MBlockClipboard.clear()` | Zwischenablage leeren |
| `MBlockClipboard.updatePasteButtons()` | Einfügen-Buttons je nach Modultyp aktivieren |

### MBlockOnlineToggle

| Methode | Beschreibung |
|---|---|
| `MBlockOnlineToggle.toggle($wrapper, $item)` | Block umschalten (nutzt den Button `.mblock-offline-toggle-btn` im Block) |
| `MBlockOnlineToggle.setOfflineState($item, isOffline)` | Hidden-Feld `mblock_offline` und Klasse `.mblock-offline` setzen |

### Events

MBlock feuert jQuery-Events auf den Blöcken:

| Event | Wann |
|---|---|
| `rex:ready` (mit dem Block als Argument) | Neuer, eingefügter oder verschobener Block ist im DOM; hier initialisieren Addons ihre Widgets (TinyMCE, CKEditor 5, Selectpicker, ...) |
| `mblock:change` (mit dem Block als Argument) | Nach dem Hinzufügen und nach dem Verschieben |
| `change` auf allen Feldern | Nach dem Einfügen aus der Zwischenablage |

```js
$(document).on('rex:ready', function (e, container) {
    container.find('.mein-widget').each(function () { /* init */ });
});
```

### Widgets in neuen Blöcken

Beim Klonen eines Blocks vergibt `mblock_reindex` neue Widget-Ids (`REX_MEDIA_<id>`, `REX_LINK_<id>`, ...) und schreibt sie in die `onclick`-Attribute der Buttons um. Die Popup-Bridges fangen die Klicks zusätzlich per Event-Delegation ab und lesen die Id aus dem Block, sodass auch Buttons ohne aktualisiertes `onclick` das richtige Feld treffen. Editor-Felder (`.tiny-editor`, `.cke5-editor`) behalten ihre `id`, solange sie eindeutig ist; sonst bekommen sie eine neue eindeutige Id.

---

## Templates

Templates liegen in `redaxo/data/addons/mblock/templates/<name>/` und werden in den Einstellungen ausgewählt. Mitgeliefert sind `standard`, `modern`, `akg_skin` und `retro_8bit`.

```
templates/mein_theme/
├── template.ini          # Name und Beschreibung
├── mblock_wrapper.ini    # Container
├── mblock_element.ini    # ein Block
└── mein_theme.css        # optional, gleicher Name wie der Ordner
```

Tags in `mblock_wrapper.ini`:

| Tag | Inhalt |
|---|---|
| `<mblock:settings/>` | `data-*`-Attribute aus den Optionen |
| `<mblock:copy_paste_toolbar/>` | Leiste "Zwischenablage leeren" |
| `<mblock:output/>` | Alle Blöcke |

Tags in `mblock_element.ini`:

| Tag | Inhalt |
|---|---|
| `<mblock:form/>` | Formular des Blocks |
| `<mblock:index/>` | Index des Blocks |
| `<mblock:offline_class/>` | ` mblock-offline`, wenn der Block offline ist |
| `<mblock:offline_button/>` | Online/Offline-Button |
| `<mblock:copy_paste_buttons/>` | Kopieren/Einfügen-Buttons |
| `{{mblock::sprachschluessel}}` | Übersetzung aus den MBlock-Sprachdateien |

Die Buttons erkennt das JavaScript an ihren Klassen: `.addme`, `.removeme`, `.moveup`, `.movedown`, `.mblock-copy-btn`, `.mblock-paste-btn`, `.mblock-offline-toggle-btn`; der Griff ist `.sorthandle`, jeder Block ist `div.sortitem`.

Farben, Abstände und Effekte des Standard-Stylesheets sind CSS-Variablen (`--mblock-*`), siehe README "Templates & Theming".

---

## Extension Points

MBlock registriert `REX_FORM_SAVED` (für `mblock_rex_form`). Eigene Extension Points bietet MBlock nicht; für eigene Logik nach dem Speichern eignen sich die REDAXO-Extension-Points `SLICE_UPDATED`, `SLICE_ADDED` und `REX_FORM_SAVED`.

---

## Build

`assets/mblock.js` ist die einzige Quelldatei. `build/minify.js` erzeugt daraus mit Terser `assets/mblock.min.js`:

```bash
cd redaxo/src/addons/mblock/build
npm install
node minify.js        # oder ./build.sh
```

Danach in der REDAXO-Installation `assets:sync` bzw. den Asset-Cache leeren, damit die neue Datei ausgeliefert wird.

---

## Beispiele

### Modul mit MForm

Input:

```php
<?php
use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MBlock\MBlock;

$id = 1;
$mform = MForm::factory()
    ->addFieldsetArea('Card', MForm::factory()
        ->addTextField("$id.0.title", ['label' => 'Titel'])
        ->addSelectField("$id.0.category", ['news' => 'News', 'events' => 'Events'], ['label' => 'Kategorie'])
        ->addTextAreaField("$id.0.text", ['label' => 'Text'])
        ->addMediaField(1, ['label' => 'Bild'])
        ->addHiddenField("$id.0.mblock_offline", '0')
    );

echo MBlock::show($id, $mform, ['min' => 1, 'max' => 5]);
```

Output:

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

foreach (MBlock::groupByField($items, 'category') as $category => $group) {
    echo '<h2>' . rex_escape($category) . '</h2>';
    foreach (MBlock::sortByField($group, 'title') as $item) {
        echo '<article><h3>' . rex_escape($item['title'] ?? '') . '</h3>';
        if (($file = $item['REX_MEDIA_1'] ?? '') && ($media = rex_media::get($file))) {
            echo '<img src="' . rex_media_manager::getUrl('rex_media_medium', $media->getFileName()) . '" alt="' . rex_escape($media->getTitle()) . '">';
        }
        echo '<p>' . nl2br(rex_escape($item['text'] ?? '')) . '</p></article>';
    }
}

echo MBlock::generateSchema($items, 'Article', ['headline' => 'title', 'articleBody' => 'text']);
```

### Modul ohne MForm

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$id = 1;
$form = <<<EOT
<fieldset class="form-horizontal">
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Name</label></div>
        <div class="col-sm-10"><input type="text" name="REX_INPUT_VALUE[$id][0][name]" value="" class="form-control"></div>
    </div>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Foto</label></div>
        <div class="col-sm-10">REX_MEDIA[id="1" widget="1"]</div>
    </div>
    <input type="hidden" name="REX_INPUT_VALUE[$id][0][mblock_offline]" value="0">
</fieldset>
EOT;

echo MBlock::show($id, $form);
```

Weitere Beispiele: `Addons > MBlock > Demo`.
