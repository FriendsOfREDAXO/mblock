MBlock
======

Mit MBlock ist es möglich, innerhalb eines Moduls beliebig viele Datenblöcke zu erzeugen. Diese können dann einfach per Button oder Drag & Drop sortiert werden. Die erweiterte Version bietet Copy & Paste Funktionalität und einen Offline/Online Toggle für einzelne Blöcke.

_English:_ MBlock lets you create an unlimited number of data blocks within a single module. These data blocks can be sorted per click or drag & drop. The enhanced version provides copy & paste functionality and an offline/online toggle for individual blocks.

> Please note: The examples are valid for MForm version 8 and higher. MBlock now requires the bloecks addon (^5.2.0) for modern drag & drop functionality. 

## Features / Funktionen

### 🎯 Grundfunktionen / Core Features

### 🚀 Erweiterte Funktionen / Advanced Features (v3.5.0+)

## Installation

MBlock erfordert:

```bash
# Via REDAXO Installer
# 1. Gehe zu System > Installer
# 2. Suche nach "mblock"
# 3. Installiere mblock zusammen mit bloecks
```

## API & Datenabfrage

### Neue zentrale getDataArray() Methode

```php
// Alle MBlock-Daten abrufen
$allItems = MBlock::getDataArray("REX_VALUE[1]");

// Nur Online-Blöcke (für Frontend)
$onlineItems = MBlock::getDataArray("REX_VALUE[1]", 'online');

// Nur Offline-Blöcke (für Backend-Previews)
$offlineItems = MBlock::getDataArray("REX_VALUE[1]", 'offline');

// Convenience-Methoden
$onlineItems = MBlock::getOnlineDataArray("REX_VALUE[1]");
$offlineItems = MBlock::getOfflineDataArray("REX_VALUE[1]");
``` 

### Legacy Array-Filterung (falls Array schon vorhanden)

```php
$data = rex_var::toArray("REX_VALUE[1]");
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

MBlock enthält einige Modulbeispiele. Diese findest du auf der MBlock-Seite im REDAXO-Backend. An dieser Stelle möchten wir nur zwei Beispiele auflisten — mit Unterstützung durch [MForm](https://github.com/FriendsOfREDAXO/mform) und ohne —, um zu zeigen, wie MBlock funktioniert.

_English:_ MBlock contains several module examples. You’ll find them on the MBlock page within the REDAXO backend. At this point, we want to show two examples only — one with [MForm](https://github.com/FriendsOfREDAXO/mform) support and another one without — to demonstrate how MBlock works.

### Example 1: team members (requires [MForm](https://github.com/FriendsOfREDAXO/mform) addon)

__Input:__

```php
<?php

// base ID
$id = 1;

// init mform
$mform = new MForm();

// fieldset
$mform->addFieldsetArea('Team member');

// textinput
$mform->addTextField("$id.0.name", array('label'=>'Name')); // use string for x.0 json values

// media button
$mform->addMediaField(1, array('label'=>'Avatar')); // mblock will auto set the media file as json value

// parse form
echo MBlock::show($id, $mform->show(), array('min'=>2,'max'=>4)); // add settings min and max
```

__Output:__

```php
<?php

echo '<pre>';
dump(rex_var::toArray("REX_VALUE[1]")); // the Mediafield Values are in the "REX_MEDIA_n" Keys in the Array, REX_MEDIA[n] is not used
echo '</pre>';
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

// parse form
echo MBlock::show($id, $form);
```

__Output:__

```php
<?php

echo '<pre>';
dump(rex_var::toArray("REX_VALUE[1]"));
echo '</pre>';
```


