REDAXO AddOn :: MBlock
======

Mit MBlock ist es möglich, innerhalb eines Moduls beliebig viele Datenblöcke zu erzeugen. Diese können dann einfach per Button oder Drag & Drop sortiert werden. Die erweiterte Version bietet Copy & Paste Funktionalität und einen Offline/Online Toggle für einzelne Blöcke.

_English:_ MBlock lets you create an unlimited number of data blocks within a single module. These data blocks can be sorted per click or drag & drop. The enhanced version provides copy & paste functionality and an offline/online toggle for individual blocks.

> Please note: The examples are valid for MForm version 8 and higher. MBlock now requires the bloecks addon (^5.2.0) for modern drag & drop functionality. 

## Features / Funktionen

### Grundfunktionen / Core Features

- [x] **Beliebig viele Datenblöcke** pro Modul erstellen
- [x] **Drag & Drop Sortierung** mit bloecks addon (^5.2.0)
- [x] **Minimale/Maximale Anzahl** von Blöcken definierbar
- [x] **Collapsed/Expanded Darstellung** für bessere Übersicht
- [x] **MForm Integration** für professionelle Formulare
- [x] **Template System** mit Prioritätsladung
- [ x ] **Mehrsprachigkeit** (DE/EN)

### Erweiterte Funktionen / Advanced Features (MBlock 4.0)

- **Online/Offline Toggle** - Blöcke aktivieren/deaktivieren ohne löschen
- **Copy & Paste** - Komfortable Duplizierung von Inhalten
- **Frontend API Methoden** - `filterByField()`, `sortByField()`, `groupByField()`
- **Schema.org Support** - SEO-optimierte JSON-LD Generierung
- **Erweiterte Datenabfrage** - Online/Offline/All Modi
- **Template-Priorität** - Custom templates überschreiben defaults
- **Media-ID Konflikt-Schutz** - Bessere Warnung bei ID-Überschneidungen

## Namespace Migration (Version 4.0)

** 🙋‍♂️ MBlock 4.0 führt Namespaces ein!** Für neue Projekte wird die Verwendung des Namespace empfohlen:

```php
<?php
// Empfohlen: Neue Namespace-Syntax (MBlock 4.0+)
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getDataArray("REX_VALUE[1]");
echo MBlock::show(1, $mform->show());
```

**Vollständig rückwärtskompatibel!** Bestehende Module funktionieren weiterhin ohne Änderungen:

```php
<?php
// Weiterhin unterstützt: Legacy-Syntax (für Bestandscode)
$items = MBlock::getDataArray("REX_VALUE[1]");
echo MBlock::show(1, $mform->show());
```

### Migration Guide
- **Neue Module**: Verwenden die `use FriendsOfRedaxo\MBlock\MBlock;` Syntax
- **Bestehende Module**: Funktionieren ohne Änderungen weiter
- **Deprecated-Warnung**: Alte Syntax wird in Version 5.0 entfernt

## Installation

MBlock erfordert:

```bash
# Via REDAXO Installer
# 1. Gehe zu System > Installer
# 2. Suche nach "mblock"
# 3. Installiere mblock zusammen mit bloecks
```

## API & Datenabfrage

### MBlock 4.0 - Neue zentrale getDataArray() Methode

**Mit Namespace (empfohlen für neue Projekte):**
```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

// Alle MBlock-Daten abrufen
$allItems = MBlock::getDataArray("REX_VALUE[1]");

// Nur Online-Blöcke (für Frontend) - EMPFOHLEN
$onlineItems = MBlock::getDataArray("REX_VALUE[1]", 'online');

// Nur Offline-Blöcke (für Backend-Previews)
$offlineItems = MBlock::getDataArray("REX_VALUE[1]", 'offline');

// Convenience-Methoden
$onlineItems = MBlock::getOnlineDataArray("REX_VALUE[1]");
$offlineItems = MBlock::getOfflineDataArray("REX_VALUE[1]");
```

**Legacy-Syntax (weiterhin unterstützt):**
```php
<?php
// Alle MBlock-Daten abrufen
$allItems = MBlock::getDataArray("REX_VALUE[1]");

// Nur Online-Blöcke (für Frontend) - EMPFOHLEN
$onlineItems = MBlock::getDataArray("REX_VALUE[1]", 'online');

// Nur Offline-Blöcke (für Backend-Previews)
$offlineItems = MBlock::getDataArray("REX_VALUE[1]", 'offline');

// Convenience-Methoden
$onlineItems = MBlock::getOnlineDataArray("REX_VALUE[1]");
$offlineItems = MBlock::getOfflineDataArray("REX_VALUE[1]");
``` 

### Frontend API - Datenverarbeitung (MBlock 4.0)

**Mit Namespace (empfohlen):**
```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Nach Feld filtern
$newsItems = MBlock::filterByField($items, 'category', 'news');
$activeItems = MBlock::filterByField($items, 'status', 'active');

// Nach Feld sortieren
$sortedByName = MBlock::sortByField($items, 'name', 'ASC');
$sortedByDate = MBlock::sortByField($items, 'date', 'DESC', 'date');
$sortedByPrice = MBlock::sortByField($items, 'price', 'DESC', 'numeric');

// Nach Feld gruppieren
$grouped = MBlock::groupByField($items, 'category');
foreach ($grouped as $category => $categoryItems) {
    echo "<h2>" . rex_escape($category) . "</h2>";
    foreach ($categoryItems as $item) {
        echo "<p>" . rex_escape($item['title']) . "</p>";
    }
}

// Anzahl begrenzen (Pagination)
$topItems = MBlock::limitItems($items, 5);
$nextItems = MBlock::limitItems($items, 5, 5);

// SEO Schema.org JSON-LD generieren
$schema = MBlock::generateSchema($items, 'Person', [
    'name' => 'name',
    'jobTitle' => 'position',
    'image' => 'photo',
    'email' => 'email'
]);
echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
```

### Legacy Array-Filterung (falls Array schon vorhanden)

```php
$data = rex_var::toArray("REX_VALUE[1]");

// Online/Offline Check (MBlock 4.0)
foreach ($data as $item) {
    if (MBlock::isOnline($item)) {
        // Item ist online
        echo rex_escape($item['title']);
    }
}

// Legacy-Methoden (deprecated, bitte getDataArray() verwenden)
$onlineItems = MBlock::getOnlineItems($data);
$offlineItems = MBlock::getOfflineItems($data);
```

## Templates & Themes

### Template-System
MBlock verwendet ein flexibles Template-System mit automatischer Prioritätsverwaltung:

**Template-Priorität (in dieser Reihenfolge):**
1. **Custom Templates** (höchste Priorität): `redaxo/data/addons/mblock/templates/custom_theme/`
2. **Default Templates** (Fallback): `redaxo/src/addons/mblock/templates/default_theme/`

### Eigene Templates erstellen
Eigene Templates sollten immer im `data/` Ordner abgelegt werden:

```bash
redaxo/data/addons/mblock/templates/
├── my_custom_theme/
│   ├── mblock_wrapper.ini    # Container für alle Items
│   ├── mblock_element.ini    # Template für einzelne Items
│   └── theme.css            # CSS-Styling (optional)
```

### Template-Dateien
- **mblock_wrapper.ini**: Container für alle MBlock-Items
- **mblock_element.ini**: Template für einzelne MBlock-Items  
- **theme.css**: Styling für das Theme (optional)

**Wichtig:** Custom Templates überschreiben automatisch die Default Templates mit dem gleichen Namen.

## Modulbeispiele / Module examples

MBlock enthält einige Modulbeispiele. Diese finden sich auf der MBlock-Seite im REDAXO-Backend. An dieser Stelle werden nur zwei Beispiele aufgelistet — mit Unterstützung durch [MForm](https://github.com/FriendsOfREDAXO/mform) und ohne —, um zu zeigen, wie MBlock funktioniert.

_English:_ MBlock contains several module examples. You’ll find them on the MBlock page within the REDAXO backend. At this point, we want to show two examples only — one with [MForm](https://github.com/FriendsOfREDAXO/mform) support and another one without — to demonstrate how MBlock works.

### Example 1: team members (requires [MForm](https://github.com/FriendsOfREDAXO/mform) addon)

__Input:__

```php
<?php
// MBlock 4.0 - Modernisiertes Beispiel mit MForm 8

use FriendsOfRedaxo\MForm;

// base ID
$id = 1;

// init mform mit moderner MForm 8 Syntax
$mform = MForm::factory();

// fieldset
$mform->addFieldsetArea('Team member');

// textinput
$mform->addTextField("$id.0.name", array('label'=>'Name'));

// media button
$mform->addMediaField(1, array('label'=>'Avatar'));

// MBlock 4.0 - Online/Offline Status (hidden field für Toggle-Funktion)
$mform->addTextField("$id.0.mblock_offline", array(
    'type' => 'hidden',
    'value' => '0'  // 0 = online, 1 = offline
));

// MBlock anzeigen (Copy & Paste ist automatisch aktiv)
echo MBlock::show($id, $mform->show(), array(
    'min' => 2,
    'max' => 4
));
```

__Output:__

```php
<?php
// MBlock 4.0 - Verbesserte Ausgabe
$items = MBlock::getOnlineDataArray("REX_VALUE[1]"); // Nur Online-Items

foreach ($items as $item) {
    $name = rex_escape($item['name'] ?? '');
    $mediaId = $item['REX_MEDIA_1'] ?? '';
    
    echo '<div class="team-member">';
    if ($name) {
        echo '<h3>' . $name . '</h3>';
    }
    if ($mediaId) {
        $media = rex_media::get($mediaId);
        if ($media) {
            echo '<img src="' . rex_media_manager::getUrl('rex_media_medium', $media->getFileName()) . '" 
                       alt="' . rex_escape($media->getTitle()) . '" class="img-responsive" />';
        }
    }
    echo '</div>';
}

// Debug (nur während Entwicklung)
// echo '<pre>';
// dump(MBlock::getDataArray("REX_VALUE[1]")); // Alle Items inkl. Offline
// echo '</pre>';
```

### Example 2: team members (without [MForm](https://github.com/FriendsOfREDAXO/mform))

__Input:__

```php
<?php

// base ID
$id = 1;

// html form
$form = <<<EOT
    <fieldset class="form-horizontal ">
        <legend>Team member</legend>
        <div class="form-group">
            <div class="col-sm-2 control-label"><label for="rv2_1_0_name">Name</label></div>
            <div class="col-sm-10"><input id="rv2_1_0_name" type="text" name="REX_INPUT_VALUE[$id][0][name]" value="" class="form-control "></div>
        </div>
        <div class="form-group">
            <div class="col-sm-2 control-label"><label>Avatar</label></div>
            <div class="col-sm-10">
                REX_MEDIA[id="1" widget="1"]
            </div>
        </div>
    </fieldset>
EOT;

// MBlock 4.0 mit Features
echo MBlock::show($id, $form, array(
    'online_offline' => true,
    'copy_paste' => true
));
```

__Output:__

```php
<?php
// Sicher und modern
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

foreach ($items as $item) {
    $name = rex_escape($item['name'] ?? '');
    $mediaId = $item['REX_MEDIA_1'] ?? '';
    
    echo '<div class="team-member">';
    echo '<h3>' . $name . '</h3>';
    
    if ($mediaId && ($media = rex_media::get($mediaId))) {
        echo '<img src="' . rex_media_manager::getUrl('rex_media_small', $media->getFileName()) . '" 
                   alt="' . rex_escape($media->getTitle()) . '" />';
    }
    echo '</div>';
}
```

---

## Author

**Friends Of REDAXO**

* [REDAXO](http://www.redaxo.org)
* [FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)


## Credits

**Project Leads**

* [Joachim Dörr](https://github.com/joachimdoerr)  
* [Thomas Skerbis](https://github.com/skerbis)  





