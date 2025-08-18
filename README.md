MBlock
======

Mit MBlock ist es möglich, innerhalb eines Moduls beliebig viele Datenblöcke zu erzeugen. Diese können dann einfach per Button oder Drag & Drop sortiert werden. Die erweiterte Version bietet Copy & Paste Funktionalität und einen Offline/Online Toggle für einzelne Blöcke.

_English:_ MBlock lets you create an unlimited number of data blocks within a single module. These data blocks can be sorted per click or drag & drop. The enhanced version provides copy & paste functionality and an offline/online toggle for individual blocks.

> Please note: The examples are valid for MForm version 8 and higher. MBlock now requires the bloecks addon (^5.2.0) for modern drag & drop functionality. 

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/mblock/assets/mblock.png)

## Features / Funktionen

### 🎯 Grundfunktionen / Core Features
- **Sortable Blocks**: Per Drag & Drop oder Button sortieren
- **Min/Max Settings**: Minimale und maximale Anzahl von Blöcken festlegen
- **Modern UI**: Basierend auf bloecks für konsistente UX
- **Mehrsprachig**: Vollständige Unterstützung für Deutsch und Englisch

### 🚀 Erweiterte Funktionen / Advanced Features (v3.5.0+)
- **Copy & Paste**: Blöcke zwischen verschiedenen Instanzen kopieren und einfügen
- **Offline/Online Toggle**: Einzelne Blöcke als Entwurf markieren oder veröffentlichen
- **Session/Local Storage**: Persistente Zwischenablage zwischen Seitenaufrufen
- **Modultyp-Validierung**: Paste nur innerhalb desselben Moduls möglich
- **Farbkodierte UI**: Grün für Online, Rot für Offline-Blöcke

## Installation

MBlock erfordert:
- REDAXO ^5.18.0
- bloecks ^5.2.0
- MForm ^8.0 (für die Beispiele)

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


![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/mblock/assets/mblock.png)

### Legacy Array-Filterung (falls Array schon vorhanden)

```php
$data = rex_var::toArray("REX_VALUE[1]");
$onlineItems = MBlock::getOnlineItems($data);
$offlineItems = MBlock::getOfflineItems($data);
```

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


