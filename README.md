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
dump(rex_var::toArray("REX_VALUE[1]"));
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


