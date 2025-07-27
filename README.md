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

MBlock can handle large datasets, but you may encounter limitations due to PHP configuration settings when working with many items or complex nested data.

_German:_ MBlock kann große Datenmengen verarbeiten, aber bei vielen Elementen oder komplexen verschachtelten Daten können PHP-Konfigurationsbeschränkungen auftreten.

### Common Issue: max_input_vars Limit

**Problem:** PHP's `max_input_vars` setting (default: 1000) limits the number of form variables that can be processed. MBlock forms with many items can easily exceed this limit, causing data truncation.

**Symptoms / Symptome:**
- Only partial MBlock data is saved (first ~100-200 items depending on complexity)
- No error messages visible to users
- Data appears to be "lost" during saving
- REDAXO logs show warnings about approaching max_input_vars limit

**Solutions / Lösungen:**

1. **Increase PHP max_input_vars** (recommended):
   ```ini
   ; In php.ini or .htaccess
   max_input_vars = 3000
   ```

2. **Monitor your data size**:
   - Check REDAXO error logs for MBlock warnings
   - Each MBlock item with multiple fields counts as several variables
   - Nested arrays (like multi-select fields) multiply the variable count

3. **Restructure large datasets**:
   - Split very large MBlock instances into multiple smaller ones
   - Use separate database tables for bulk data
   - Consider pagination for user interface

### Legacy Note

Previous versions of this documentation mentioned 4000-character JSON limits and database column restrictions. These were incorrect assumptions. The actual limitation is PHP's `max_input_vars` setting, not database storage or JSON size.

_German:_
Frühere Versionen dieser Dokumentation erwähnten 4000-Zeichen-JSON-Limits und Datenbankspaltenbeschränkungen. Dies waren falsche Annahmen. Die tatsächliche Begrenzung ist PHPs `max_input_vars`-Einstellung, nicht die Datenbankspeicherung oder JSON-Größe.


