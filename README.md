REDAXO AddOn :: MBlock
======

> **Mai 2026 – Maintenance-Status:** Ab Version 4.6 werden keine neuen Features mehr entwickelt.
> MBlock ist stabil und weiterhin voll funktionsfähig, befindet sich aber im reinen Wartungsmodus.
> Fehlerbehebungen und wichtige Stabilitätskorrekturen werden weiterhin umgesetzt.

---

Mit MBlock ist es möglich, innerhalb eines Moduls beliebig viele Datenblöcke zu erzeugen. Diese können per Button oder Drag & Drop sortiert werden. Die erweiterte Version bietet Copy & Paste und einen Online/Offline-Toggle für einzelne Blöcke.

_English:_ MBlock lets you create an unlimited number of data blocks within a single module, sortable by click or drag & drop. The enhanced version provides copy & paste and an offline/online toggle for individual blocks.

> **MForm >= 9:** Für neue Module ist [MForm 9](https://github.com/FriendsOfREDAXO/mform) mit dem integrierten Flex-Repeater die modernere Wahl. MBlock bleibt für bestehende Projekte vollständig nutzbar.

## 🚨 Hinweis für markitup- und ckeditor-Nutzer

Copy & Paste funktioniert mit diesen Editoren nicht und sollte in den betreffenden Modulen deaktiviert werden:

```php
echo MBlock::show(1, $form, [
    'copy_paste' => false,
]);
```

## Features

- **Beliebig viele Datenblöcke** pro Modul
- **Drag & Drop Sortierung**
- **Min/Max Anzahl** von Blöcken definierbar
- **MForm Integration** – alle MForm-9-Felder werden unterstützt
- **Template-System** mit Custom-Template-Unterstützung
- **Online/Offline Toggle** – Blöcke aktivieren/deaktivieren ohne löschen
- **Copy & Paste** – Duplizierung von Blöcken
- **Frontend API** – `filterByField()`, `sortByField()`, `groupByField()`
- **TinyMCE / CKEditor 5** – volle Editor-Unterstützung
- **Mehrsprachigkeit** (DE/EN)

## Installation

```bash
# Via REDAXO Installer:
# System > Installer > "mblock" suchen > installieren
```

## Namespace

Seit Version 4.0 steht der Namespace `FriendsOfRedaxo\MBlock` zur Verfügung. Die Legacy-Syntax ohne Namespace ist weiterhin vollständig kompatibel.

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getOnlineDataArray("REX_VALUE[1]");
echo MBlock::show(1, $mform->show());
```

## API & Datenabfrage

### Daten auslesen

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

// Alle Blöcke (inkl. offline)
$allItems = MBlock::getDataArray("REX_VALUE[1]");

// Nur Online-Blöcke – empfohlen für das Frontend
$onlineItems = MBlock::getOnlineDataArray("REX_VALUE[1]");
// oder:
$onlineItems = MBlock::getDataArray("REX_VALUE[1]", 'online');

// Nur Offline-Blöcke
$offlineItems = MBlock::getOfflineDataArray("REX_VALUE[1]");
```

### Frontend-Datenverarbeitung

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Filtern
$newsItems = MBlock::filterByField($items, 'category', 'news');

// Sortieren
$sorted = MBlock::sortByField($items, 'date', 'desc');

// Gruppieren
$grouped = MBlock::groupByField($items, 'category');
foreach ($grouped as $category => $categoryItems) {
    echo '<h2>' . rex_escape($category) . '</h2>';
    foreach ($categoryItems as $item) {
        echo '<p>' . rex_escape($item['title']) . '</p>';
    }
}

// Anzahl begrenzen (für Pagination)
$topItems  = MBlock::limitItems($items, 5);
$nextItems = MBlock::limitItems($items, 5, 5);
```

### Online/Offline-Status prüfen

```php
// Nur Online-Blöcke (Offline-Feld leer oder 0)
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Bereits geladene Daten filtern
$online = MBlock::getOnlineItems(rex_var::toArray("REX_VALUE[1]"));

// Einzelnen Block prüfen (seit 4.7.0)
if (MBlock::isOnline($item)) { /* ... */ }
$isOffline = MBlock::isOffline($item);
```

## MForm Integration

MBlock arbeitet am besten zusammen mit [MForm](https://github.com/FriendsOfREDAXO/mform). **MForm 9 ist vollständig kompatibel** – alle Felder funktionieren in MBlock.

### Feldkompatibilität MForm 9 + MBlock

| Methode | Kategorie | Hinweis |
|---------|-----------|---------|
| `addTextField()` | Text | ✅ uneingeschränkt |
| `addTextAreaField()` | Text | ✅ uneingeschränkt |
| `addHiddenField()` | Text | ✅ uneingeschränkt |
| `addSelectField()` | Auswahl | ✅ uneingeschränkt |
| `addMultiSelectField()` | Auswahl | ✅ uneingeschränkt |
| `addCheckboxField()` | Auswahl | ✅ uneingeschränkt |
| `addToggleCheckboxField()` | Auswahl | ✅ uneingeschränkt |
| `addRadioField()` | Auswahl | ✅ uneingeschränkt |
| `addRadioImgField()` | Auswahl | ✅ uneingeschränkt |
| `addRadioIconField()` | Auswahl | ✅ uneingeschränkt |
| `addRadioColorField()` | Auswahl | ✅ uneingeschränkt |
| `addMediaField(n)` | Media/Link | ✅ Ausgabe: `$item['REX_MEDIA_n']` · mit `useCustomLinkForClassicWidgets(true)` kein Klonen-Problem |
| `addMedialistField(n)` | Media/Link | ✅ Ausgabe: `$item['REX_MEDIALIST_n']` |
| `addLinkField(n)` | Media/Link | ✅ Ausgabe: `$item['REX_LINK_n']` · mit `useCustomLinkForClassicWidgets(true)` kein Klonen-Problem |
| `addLinklistField(n)` | Media/Link | ✅ Ausgabe: `$item['REX_LINKLIST_n']` |
| `addMFormMediaField("$id.0.key")` | Media/Link | ✅ MForm-native, kein Reindex-Problem · Ausgabe: `$item['key']` |
| `addCustomLinkField("$id.0.key")` | Media/Link | ✅ Ausgabe: `$item['key']` · Normalisierung via `MFormOutputHelper::createLinkData()` empfohlen |
| `addCustomLinkMultipleField("$id.0.key")` | Media/Link | ✅ Neu in MForm 9 · gibt JSON-Array mit mehreren Links zurück |
| `addConditionalFieldsetArea()` | Layout | ✅ Neu in MForm 9 · regelbasierte Feldanzeige funktioniert in MBlock |
| `addFieldsetArea()` | Layout | ✅ uneingeschränkt |

> **Hinweis zu `addMediaField` / `addLinkField` mit numerischer ID:**  
> Beim **Klonen** von Blöcken in MBlock kann es ohne `useCustomLinkForClassicWidgets(true)` zu Reindex-Problemen kommen, weil REDAXO-Widgets intern per Widget-ID referenziert werden. Mit dem Flag wird das custom_link-Widget verwendet – das Speicherformat (`REX_MEDIA_n` / `REX_LINK_n`) bleibt dabei identisch.

### Beispiel: Team-Mitglieder (mit MForm)

**Moduleingabe:**

```php
<?php
use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MBlock\MBlock;

$id = 1;

// useCustomLinkForClassicWidgets(true) sorgt dafür, dass addMediaField / addLinkField
// über das custom_link-Widget gerendert werden → kein Reindex-Problem beim Klonen.
// Das Speicherformat (REX_MEDIA_1 / REX_LINK_n) bleibt identisch.
MForm::useCustomLinkForClassicWidgets(true);

$mform = MForm::factory()
    ->addFieldsetArea('Team-Mitglied', MForm::factory()
        ->addTextField("$id.0.name", ['label' => 'Name'])
        ->addMediaField(1, ['label' => 'Avatar'])                                          // → $item['REX_MEDIA_1']
        ->addCustomLinkField("$id.0.link", ['label' => 'Profil-Link', 'intern' => 1, 'extern' => 1]) // → $item['link']
        ->addHiddenField("$id.0.mblock_offline", '0')   // Online/Offline-Toggle
    );

MForm::useCustomLinkForClassicWidgets(false); // nur nötig wenn im selben Request weitere MForm-Instanzen ohne Flag folgen

echo MBlock::show($id, $mform->show(), [
    'min'            => 1,
    'max'            => 10,
    'copy_paste'     => true,
    'online_offline' => true,
]);
```

**Modulausgabe:**

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

foreach ($items as $item) {
    $name      = rex_escape($item['name'] ?? '');
    $mediaName = $item['REX_MEDIA_1'] ?? '';

    // Unified Link-Normalisierung (funktioniert mit String und Array-Format)
    $link = MFormOutputHelper::createLinkData($item['link'] ?? '');

    echo '<div class="team-member">';

    if ($mediaName && ($media = rex_media::get($mediaName))) {
        echo '<img src="' . rex_media_manager::getUrl('rex_media_medium', $media->getFileName()) . '"'
           . ' alt="' . rex_escape($media->getTitle()) . '" class="img-responsive">';
    }

    if ($name) {
        echo '<h3>' . $name . '</h3>';
    }

    if ('' !== $link['customlink_url']) {
        echo '<a href="' . rex_escape($link['customlink_url']) . '"'
           . $link['customlink_target'] . '>'
           . rex_escape($link['customlink_text']) . '</a>';
    }

    echo '</div>';
}
```

### Beispiel: Ohne MForm (reines HTML-Formular)

Für Projekte ohne MForm-Addon kann das Formular als HTML-String übergeben werden:

**Moduleingabe:**

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$id = 1;

$form = <<<EOT
<fieldset>
    <legend>Team-Mitglied</legend>
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="REX_INPUT_VALUE[$id][0][name]" value="" class="form-control">
    </div>
    <div class="form-group">
        <label>Avatar</label>
        REX_MEDIA[id="1" widget="1"]
    </div>
</fieldset>
EOT;

echo MBlock::show($id, $form, [
    'online_offline' => true,
    'copy_paste'     => true,
]);
```

**Modulausgabe:**

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

foreach ($items as $item) {
    $name    = rex_escape($item['name'] ?? '');
    $mediaId = $item['REX_MEDIA_1'] ?? '';

    echo '<div class="team-member">';
    echo '<h3>' . $name . '</h3>';

    if ($mediaId && ($media = rex_media::get($mediaId))) {
        echo '<img src="' . rex_media_manager::getUrl('rex_media_small', $media->getFileName()) . '"'
           . ' alt="' . rex_escape($media->getTitle()) . '">';
    }

    echo '</div>';
}
```

## MForm Field-Key-Konventionen in MBlock

Je nach Methode und ID-Typ landen die Werte unter unterschiedlichen Schlüsseln im `$item`-Array.

### Numerische IDs – native REDAXO-Widgets

`addMediaField`, `addLinkField`, `addMedialistField`, `addLinklistField` **benötigen** eine numerische ID. MBlock leitet daraus intern den REDAXO-Variablennamen ab:

| Methode | ID | Ausgabe-Schlüssel |
|---------|----|-------------------|
| `addMediaField(1)` | `1` | `$item['REX_MEDIA_1']` |
| `addLinkField(2)` | `2` | `$item['REX_LINK_2']` |
| `addMedialistField(3)` | `3` | `$item['REX_MEDIALIST_3']` |
| `addLinklistField(4)` | `4` | `$item['REX_LINKLIST_4']` |

> **Tipp:** Mit `MForm::useCustomLinkForClassicWidgets(true)` werden diese Felder intern über das `custom_link`-Widget gerendert (moderner Widget-Stil, kein Reindex-Problem beim Klonen). Das Speicherformat bleibt identisch. In einem Modul-Input reicht der einmalige Aufruf – `MForm::useCustomLinkForClassicWidgets(false)` ist nur nötig, wenn im selben Request weitere MForm-Instanzen ohne das Flag folgen.

### String-Pfad-IDs – MForm Custom-Widgets

`addCustomLinkField`, `addMFormMediaField` sollten mit einem **String-Pfad** als ID verwendet werden. Das letzte Pfad-Segment wird zum JSON-Schlüssel:

| Methode | ID | Ausgabe-Schlüssel |
|---------|----|-------------------|
| `addCustomLinkField("$id.0.link")` | `"$id.0.link"` | `$item['link']` |
| `addMFormMediaField("$id.0.bild")` | `"$id.0.bild"` | `$item['bild']` |

> Eine numerische ID wie `addCustomLinkField(6)` erzeugt den wenig lesbaren Schlüssel `$item['6']`. String-Pfade sind immer vorzuziehen.

### Link-Ausgabe normalisieren

Custom-Link-Felder können je nach MForm-Version als String (`redaxo://10`) oder als Array mit `id`/`name` ankommen. `MFormOutputHelper::createLinkData()` normalisiert beide Formate:

```php
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$link = MFormOutputHelper::createLinkData($item['link'] ?? '');
// $link['customlink_url']    – fertige href-URL
// $link['customlink_text']   – Linktext (Artikelname, Media-Titel, URL-Fallback)
// $link['customlink_target'] – target-Attribut (z. B. ' target="_blank" ...')
// $link['customlink_class']  – CSS-Klasse (intern / external / media / mail / tel)
```

## MBlock::show() – Optionen

```php
echo MBlock::show($id, $form, [
    'min'            => 1,     // Mindestanzahl Blöcke (werden initial angezeigt)
    'max'            => 10,    // Maximale Anzahl Blöcke
    'copy_paste'     => true,  // Kopieren/Einfügen-Buttons (Standard: Einstellung)
    'delete_confirm' => 1,     // Rückfrage vor dem Löschen: 1, 0 oder eigener Text
    'input_delete'   => true,  // Eingaben in neuen Blöcken leeren
    'smooth_scroll'  => true,  // Zum neuen Block scrollen
]);
```

> Der Online/Offline-Schalter erscheint automatisch, sobald das Formular ein Hidden-Feld `mblock_offline` enthält (`addHiddenField("$id.0.mblock_offline", '0')`) und die Option in den Einstellungen aktiv ist. Das Template wird global in den Einstellungen gewählt.

## Templates & Theming

### Template auswählen

Die Template-Auswahl erfolgt unter `Addons > MBlock > Einstellungen`. Das CSS wird automatisch in den `assets/`-Ordner kopiert. Mitgeliefert sind:

| Template | Optik |
|---|---|
| `standard` | Die bekannte MBlock-Darstellung, Buttons oben rechts im Block |
| `modern` | Wie der Flex-Repeater von MForm: Kopfzeile mit Griff, Status, Nummer und Aktionen, darunter der Inhalt, am Ende ein Hinzufügen-Streifen. Nutzt die MForm-Farbvariablen, wenn MForm installiert ist |
| `focus` | Nur der Inhalt ist sichtbar; Griff, Nummer, Aktionen und Hinzufügen-Streifen erscheinen bei Hover oder Fokus im Block. Auf Touch-Geräten bleiben sie sichtbar |
| `retro_8bit` | Brotkasten: beiges Gehäuse, dunkle Tasten mit farbigen Legenden, blauer C64-Bildschirm für Kopfzeile und Felder |
| `akg_skin` | Bootstrap-Grid-Variante |

Die mitgelieferten Templates werden bei Installation und Update nach `redaxo/data/addons/mblock/templates/` kopiert und dort überschrieben. Eigene Templates bekommen deshalb einen eigenen Ordnernamen.

### Dark Mode

Die mitgelieferten Templates unterstützen Dark Mode über:

- `body.rex-theme-dark` (REDAXO Theme)
- `@media (prefers-color-scheme: dark)` (Browser, solange kein helles Theme gewählt ist)
- `html[data-bs-theme="dark"]` (Bootstrap 5)

### CSS-Variablen

Farben, Abstände und Effekte des Standard-Stylesheets sind Variablen in `:root` (`assets/mblock.css`). Für eigene Farben reicht es, sie in eigenem CSS nach dem MBlock-CSS zu überschreiben:

```css
:root {
    --mblock-background: rgba(255, 240, 245, 0.1);
    --mblock-border-color: rgba(220, 20, 60, 0.3);
    --mblock-add-background: #e91e63;
    --mblock-add-background-hover: #c2185b;
    --mblock-toggle-width: 80px;
}
```

Im Dark Mode setzt MBlock dieselben Variablen aus den `--mblock-dark-*`-Werten neu, z. B. `--mblock-dark-block-background`, `--mblock-dark-border-color`, `--mblock-dark-control-background` oder `--mblock-dark-add-background`. Wer beide Modi anpassen will, überschreibt die hellen Variablen in `:root` und die `--mblock-dark-*`-Werte daneben. Die vollständige Liste steht am Anfang von `assets/mblock.css`; die Gruppen sind Block (`--mblock-background`, `--mblock-border-color`, `--mblock-shadow`, `--mblock-padding`, ...), Griff (`--mblock-drag-*`), Buttons (`--mblock-button-*`, `--mblock-control-*`, `--mblock-add-*`, `--mblock-copy-hover-*`, `--mblock-paste-hover-*`), Online/Offline (`--mblock-toggle-*`, `--mblock-offline-block-*`), Zwischenablage-Leiste (`--mblock-toolbar-*`), Sortier-Platzhalter (`--mblock-ghost-*`, `--mblock-chosen-*`) und Leuchteffekte (`--mblock-glow-*`).

### Custom Templates

Eigene Templates in `redaxo/data/addons/mblock/templates/`:

```
my_theme/
├── template.ini           # Konfiguration
├── mblock_wrapper.ini     # HTML-Wrapper für alle Items
├── mblock_element.ini     # HTML-Template für einzelne Items
└── my_theme.css           # CSS (gleicher Name wie der Ordner!)
```

## Development & Build

Das Verhalten im Backend steckt in einer Datei, `assets/mblock.js`. Solange der Debug-Modus oder das Debug-Addon aktiv ist, lädt `boot.php` diese Datei, sonst die minifizierte `mblock.min.js`.

```bash
cd redaxo/src/addons/mblock/build
npm install
node minify.js   # erzeugt assets/mblock.min.js
```

Nach Änderungen an `assets/` in der Installation `assets:sync` ausführen bzw. den Asset-Cache leeren. Die JavaScript-Schnittstelle (Funktionen, `MBlockClipboard`, Events) ist in der API-Dokumentation beschrieben.

---

## Author

**Friends Of REDAXO**

* [REDAXO](http://www.redaxo.org)
* [FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)

## Credits

**Project Leads**

* [Joachim Dörr](https://github.com/joachimdoerr)
* [Thomas Skerbis](https://github.com/skerbis)
