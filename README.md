MBlock
======

Mit MBlock ist es möglich, innerhalb eines Moduls beliebig viele Datenblöcke zu erzeugen. Diese können dann einfach per Button oder Drag & Drop sortiert werden.

_English:_ MBlock lets you create an unlimited number of data blocks within a single module. These data blocks can be sorted per click or drag & drop.

> Please note: The examples are valid for MForm version 7 and higher. When using older MForm versions, please refer to the documentation of the respective version. 


![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/mblock/assets/mblock.png)

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

## Large Data Handling / Große Datenmengen

MBlock stores data as JSON in REDAXO's article_slice table. When working with large amounts of data (> 4000 characters), you may encounter limitations due to database column size restrictions.

_German:_ MBlock speichert Daten als JSON in REDAXOs article_slice Tabelle. Bei großen Datenmengen (> 4000 Zeichen) können Beschränkungen durch die Datenbankspaltengröße auftreten.

### Symptoms / Symptome

- Form does not render with large JSON data
- Data appears to be truncated after saving
- Console errors about JSON decode failures

_German:_
- Formular wird bei großen JSON-Daten nicht gerendert  
- Daten scheinen nach dem Speichern abgeschnitten zu sein
- Konsolenfehler über JSON-Dekodierungsfehler

### Solutions / Lösungen

1. **Check REDAXO logs** for warnings about large data
2. **Monitor data size** - keep individual MBlock entries under 4000 characters when possible
3. **Use separate storage** for very large content (e.g., separate tables, files)
4. **Database optimization** - ensure REDAXO's value columns use TEXT instead of VARCHAR

_German:_
1. **REDAXO-Logs prüfen** auf Warnungen über große Daten
2. **Datengröße überwachen** - einzelne MBlock-Einträge wenn möglich unter 4000 Zeichen halten
3. **Separate Speicherung** für sehr große Inhalte verwenden (z.B. separate Tabellen, Dateien)
4. **Datenbankoptimierung** - sicherstellen, dass REDAXOs Wertspalten TEXT statt VARCHAR verwenden


