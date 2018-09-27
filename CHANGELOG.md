# MBlock - REDAXO Addon für Modul-Input-Blöcke

### Version 2.2.3

Selectfix  - danke @tbaddade 

https://github.com/FriendsOfREDAXO/mblock/pull/96

### Version 2.2.1

* use php xml parser
* remove completely PHPQuery
* Umstellung auf 'includeCurrentPageSubPath'
* Traducción en castellano
* start new session in show if not exist
* fix mblock page count index notice
* uset isset to fix property undefined problem
* other small fixes

### Version 2.1.0

* not reinit bug for markitup fixed
* hide delete, duplicate buttons and drag and drop bar is min 1 and max 1
* set php-xml as requires
* readme updated and code field closed

### Version 2.0.2

* set debug sql false to remove sql debug message by addon update

### Version 2.0.1

* Update sucht und ersetzt REX_INPUT_LINK, REX_INPUT_MEDIA, REX_INPUT_LINKLIST, REX_INPUT_MEDIALIST durch die neue Schreibweise ohne _INPUT.
* WICHTIG: Module müssen händisch angepasst werden.

### Version 2.0.0

* add MBlock for rex_form
* min 0 - add MBlock setting 'disable_null_view' and 'null_view_button_text'
* use multiple MBlocks in one Form
* diverse other fixes
* INPUT_ was removed in REX_INPUT_MEDIA, REX_INPUT_LINK and MEDIALIST, LINKLIST	

#### WICHTIG:
 
* für bestehende produktiv Seiten sollten MBlock nicht ohne ausgiebiges Testing upgedatet werden.
* In REX_INPUT_LINK und REX_INPUT_MEDIA wurde das _INPUT entfernt das wirkt sich auf bestehende Bild- und Link-Eingaben aus!
* Auch in REX_INPUT_LINKLIST und REX_INPUT_MEDIALIST wurde das _INPUT entfernt, das wirkt sich auf bestehende Bild- und Link-Listen-Eingaben aus!

### Version 1.7.2 

* fix checkboxes bugs
* fix radio-buttons bugs
* add new created item to javascript events
* other small changes

### Version 1.7.1

* add block delete confirmation
* fix mform custom link handling

### Version 1.7.0

* add block delete confirmation
* fix mform custom link handling

### Version 1.6.5

* add button support
* fix data-unique-id for more than one items

### Version 1.6.4

* add data-unique and data-unique-int

### Version 1.6.3

* final redactor2 fix thanks @alfa-gonzalez
* update license to MIT thanks @schuer

### Version 1.6.2

* fix redactor 3.0.x was not reinitialized	

### Version 1.6.1

* Fix for multiple selects without form	

### Version 1.6.0

* fix multiple selectbox problems
* fix redactor 2 bugs without id's
* remove for replacement in javascript
* add count
* fix textarea problems with &
* add callback (thanks @Adrian Kühnis)
* add mform customlink support

### Version 1.5.0

* diverse Javascript Fixes
* Smooth-Scroll integriert
* Sortable Kompatibilitätsprobleme mit jQuery UI behoben
* HTML Beispiele

### Version 1.4.0

* Markitup Support
* Redactor content remove error behoben

### Version 1.3.1

* Multiple System-Elemente (link, media, lists)
* Multiple Redaktor-Felder
* Fehler gefixt Übernahme modifizierter Redactor.Content (nach add move oder remove wurde dieser immer zurück gesetzt)

### Version 1.3.0

* Add Redactor Kompatibilität

### Version 1.2.0

* Mehr als ein MBlock in einem Modul-Input
* Redaxo System Buttons außerhalb der MBlock Area möglich
* diverse kleine Fixes

### Version 1.1.0

* GUI updates: bootstrap/redaxo buttons and slightly update colors
* Text verbesserungen . Besten Dank an Dirk Schürjohann von DECAF* https://decaf.de dafür!!	

### Version 1.0.0

* neue Beispiele
* überarbeitetes Javascript
* Text Buttons 
* CSS3 animationen
