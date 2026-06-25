# MBlock - REDAXO Addon für Modul-Input-Blöcke

## Version 4.6.6 - 2026-06-25

### Bug Fixes
* **Checkbox-Werte bleiben rückwärtskompatibel**: Beim Reindexing wurden Checkbox-Values clientseitig auf `1`/`0` normalisiert. Dadurch gingen benutzerdefinierte Werte wie `checked` verloren und bestehende Modul-Logik konnte brechen. MBlock erhält nun den ursprünglichen Basiswert pro Checkbox und überschreibt nur noch echte boolesche Werte kontrolliert.

## Version 4.6.5 - 2026-06-24

### Bug Fixes
* **Media-Popup mit langer Widget-ID wieder korrekt**: In der MBlock-Bridge und beim Reinitialisieren der REDAXO-Widgets wurde die `REX_MEDIA_*`-ID bisher auf reine Ziffern gekürzt. Bei IDs wie `REX_MEDIA_1GBS...` führte das zu falschem `opener_input_field=REX_MEDIA_1`. Der vollständige ID-Suffix wird jetzt erhalten und an `openREXMedia`/`viewREXMedia`/`addREXMedia`/`deleteREXMedia` weitergegeben.
* **Nur erstes MBlock-Element wurde gespeichert**: Beim Initial-Render konnten mehrere Blöcke denselben Feldnamen-Index `[0]` behalten. Dadurch kollidierten POST-Keys und spätere Elemente wurden überschrieben. Beim ersten `mblock_init()` wird jetzt ein zusätzlicher `mblock_reindex()` ausgeführt, sodass Namen sofort korrekt auf `[0]`, `[1]`, ... verteilt sind.

## Version 4.6.4 - 2026-06-16

### Bug Fixes
* **CKEditor 5 Sync-Fehler beim Formular-Submit behoben**: In `mblock_sync_all_cke5_to_textareas()` wurde im DOM-Fallback eine Variable als `const` deklariert und anschließend neu zugewiesen. Das konnte in der Browser-Konsole zu `TypeError: invalid assignment to const` führen und die Synchronisierung von Editor-Inhalten in die Textarea stören. Die Variable ist nun korrekt als `let` definiert.

## Version 4.6.2 - 2026-05-30

### Bug Fixes
* **Warnings bei MForm custom/core Toggle behoben**: Die Replacer-Logik für `REX_MEDIALIST_SELECT_*` und `REX_LINKLIST_SELECT_*` ersetzt den Name-Identifier jetzt null-sicher. Dadurch treten keine PHP-Deprecations mehr auf (`str_replace(): Passing null to parameter #1`) wenn `systemId` in einzelnen Widget-HTML-Varianten noch nicht gesetzt ist.
* **Settings-Serialisierung gehärtet**: `MBlockSettingsHelper` normalisiert Array-Werte vor dem Rendern der `data-*`-Attribute. Dadurch wird die Warnung `Array to string conversion` vermieden.

## Version 4.6.1 - 2026-05-27

### Bug Fixes
* **rex_select mit optgroup verursacht TypeError**: In den Select-Replacern werden innerhalb von `optgroup` jetzt nur noch echte `option`-Elemente verarbeitet. Whitespace-Textknoten (`DOMText`) werden ignoriert, dadurch tritt der Crash `Argument #2 ($option) must be of type DOMElement, DOMText given` nicht mehr auf. Siehe [Issue #227](https://github.com/FriendsOfREDAXO/mblock/issues/227), gemeldet von @tbaddade.
* **Defektes Link-Beispiel repariert**: Die Beispiel-Datei `base2_link_example.ini` enthielt beschädigte/duplizierte Startzeilen und ungültigen PHP-Code. Das Beispiel ist wieder lauffähig.

## Version 4.6.0 - 2026-05-07

### Bug Fixes
* **MForm 9 Widgets in MBlock sauber reindexiert**: `custom_link`, `custom_medialist`, `custom_linklist` und `imglist` werden nach Add/Sort jetzt mit ihren aktuellen Widget-IDs, Popup-Callbacks und internen Referenzen neu initialisiert. Dadurch funktionieren Picker nach dem Verschieben und in neu angelegten Blöcken zuverlässig weiter.
* **Neue MBlocks starten mit leeren MForm-Widgets**: Beim Klonen eines neuen Blocks werden MForm-Widgetzustände jetzt gezielt zurückgesetzt. Betroffen sind `custom_link` sowie die `mform-list-widget`-Familie (`medialist`, `linklist`, `imglist`). Sichtbare Texte, Hidden-Values, Select-Optionen und gerenderte Listen werden nicht mehr aus dem Quellblock übernommen.

### Rückwärtskompatibilität
* **Kein Datenbank- oder Content-Break**: Bestehende MBlock-Module, gespeicherte Inhalte und Datenstrukturen bleiben unverändert kompatibel.
* **Kein harter Addon-Conflict erforderlich**: MBlock selbst bleibt zu älteren MForm-Versionen lauffähig. Die Fixes in 4.5.9 fokussieren auf `custom_link` und die `mform-list-widget`-Familie.

## Version 4.5.8 - 2026-05-06

### Bug Fixes
* **SourceMap-Fehler (404) auf Live-Servern**: Der `sourceMappingURL`-Kommentar in der `mblock.min.js` wurde entfernt, um 404-Fehler in den Browser-Entwicklertools auf Live-Servern zu vermeiden, die keine `.map` Dateien ausliefern. Die Map-Datei bleibt weiterhin für Entwicklungszwecke in den Sourcen erhalten.

## Version 4.5.7 - 2026-02-27

### Bug Fixes
* **REX_LINK – 3. Block überschreibt Link von Block 2**: `mblock_update_rex_ids` hat `REX_LINK_`-Inputs beim Reindexieren nicht berücksichtigt, sodass alle neuen Blöcke dieselbe Link-ID aus dem Template behielten. Link-Popup-Callbacks schrieben dadurch stets in Block 2. `REX_LINK_` wird nun korrekt mit dem Block-Index reindexiert.
* **REX_LINK – `_NAME`-Input nach Reindexierung nicht auffindbar** (`null is not an object evaluating getElementById("REX_LINK_X_NAME")`): Der textuelle Anzeigefeld-Input (`_NAME`) bekam beim generischen Durchlauf einen anderen `eindex` als der hidden-Input → ID-Mismatch. `_NAME`-Inputs werden jetzt im generischen Loop übersprungen und synchron zum hidden-Input mit derselben ID-Basis aktualisiert.
* **`deleteREXLink` onclick nach Drag & Drop kaputt**: Das generische Regex-Replace in `mblock_update_rex_buttons` hat bei `deleteREXLink('X')` die schließende Quote entfernt → ungültiges JavaScript. Onclick-Handler werden jetzt per spezifischem Pattern korrekt mit Quotes ersetzt.
* **REX_LINK Buttons in Modulen ohne `rex-js-widget-link`-Klasse nicht aktualisiert**: `closest('.rex-js-widget-link')` schlug bei HTML-basierten Link-Feldern ohne diese Klasse fehl. Die Widget-Suche verwendet jetzt eine Fallback-Kette (`.input-group`, `.rex-js-widget`), die auch bei eigenen Templates greift.

## Version 4.5.6 - 2026-02-24

### Bug Fixes
* **Radio-Button (Copy & Paste) – Wert nicht übernommen**: Alle Radio-Inputs einer Gruppe teilen denselben `name`-Attribut. Bisher wurde der `formData`-Eintrag bei jeder Iteration überschrieben, sodass immer das letzte (nicht ausgewählte) Radio gewann. Die Erfassung speichert jetzt pro Gruppe nur den tatsächlich gewählten Wert als neuen `radio_group`-Typ.
* **Radio-Button (Copy & Paste) – `active`-Klasse als Fallback**: MForm Bild-Radios und Color-Radios markieren die aktive Option zusätzlich mit `class="active"` am `<label>`. Diese Klasse wird nun beim Erfassen als Fallback ausgewertet, falls `prop('checked')` im DOM-Klon nicht gesetzt ist.
* **Radio-Button (Copy & Paste) – Tab-Panel-Reset**: Nach dem Einfügen triggert mblock `rex:ready`, wodurch Tab-Panel- und MForm-Initialisierungen alle `active`-Klassen auf Labels zurücksetzen. Die Radio-Wiederherstellung wird deshalb zweistufig durchgeführt: einmal direkt beim Einfügen und nochmals nach dem `rex:ready`/`trigger('change')`-Zyklus über die neue `restoreRadioGroups()`-Methode.

## Version 4.5.5 - 2026-02-15

### Bug Fixes
* **CKEditor 5 Filler Content**: Added automatic cleanup of replacement content for CKEditor 5 (`<p><br data-cke-filler="true"></p>`) and placeholders (`<p class="ck-placeholder" ...>`) on form submission. This prevents empty blocks from being saved with invisible HTML artifacts.

## Version 4.5.1 - 2025-11-26

### Bug Fixes
* **Double Icons**: Fixed duplicate icons in copy/paste buttons when Bloecks addon is active.
* **TinyMCE Drag & Drop**: Fixed issue where TinyMCE editors became unresponsive after moving blocks (Drag & Drop or Arrow Buttons).

## Version 4.5.0 - 2025-11-26

### Features
* **TinyMCE Support**: Added full support for TinyMCE (v4, v5, v6, v7, v8) in MBlock instances.
* **CKEditor 5 Improvements**: Enhanced copy & paste functionality for CKEditor 5, ensuring content is correctly preserved and restored.

### Improvements
* **Code Modernization**: Updated PHP classes to use strict types and modern array syntax.
* **Performance**: Optimized JavaScript for better editor initialization and cleanup.
* **Cleanup**: Removed debug code and unnecessary files.

## Version 4.2.0-dev

### Bug Fixes
* **Media Field ID Mismatch**: Fixed issue where media field IDs became inconsistent after moving blocks, causing media selection from media pool to fail ([#issue](https://github.com/FriendsOfREDAXO/mblock/issues/XXX))
  - Root cause: Inconsistent string concatenation in ID generation
  - Input field IDs and button onclick handlers now use identical ID values
  - Affects REX_MEDIA, REX_LINK, REX_MEDIALIST, and REX_LINKLIST fields

### Template System Improvements
* **Template Migration**: `default_theme` renamed to `standard` for consistency
* **Template Location**: All templates now in `data/templates/` directory for unified handling
* **Template Selection**: Simplified dropdown selection in addon settings
* **Automatic Updates**: Templates are refreshed on every addon update
* **Default Configuration**: `standard` theme is always set on install/update
* **Simplified Management**: Only built-in templates are available, no custom template support

### Configuration
* **Standard Theme**: Default theme is now called `standard` instead of `default_theme`
* **Theme Reset**: Theme configuration is reset to `standard` on every update/install
* **Package Config**: Added default `mblock_theme: 'standard'` to package.yml

## Version 4.0.0 - 2025-01-18

### Major New Features

#### Copy & Paste Functionality
* **MBlock Element Clipboard**: Copy and paste blocks between different MBlock instances
* **Session/Local Storage**: Persistent clipboard data survives page reloads
* **Module Type Validation**: Paste only works within the same module type
* **Visual Feedback**: Copy button shows confirmation animation
* **Smart UI**: Paste buttons automatically enable/disable based on clipboard state
* **Toolbar Management**: Clear clipboard toolbar only appears when clipboard contains data

#### Offline/Online Toggle System
* **Automatic Field Detection**: System automatically detects `mblock_offline` fields
* **Visual Status Indicators**: Color-coded buttons (Green = Online, Red = Offline)
* **Template Integration**: New template tags `<mblock:offline_class/>` and `<mblock:offline_button/>`
* **CSS Styling**: Offline blocks get reduced opacity and visual distinction
* **Data Filtering API**: New methods to filter MBlock data by online/offline status

#### Enhanced API Methods
* **`MBlock::getDataArray()`**: Central method combining `rex_var::toArray()` and filtering
* **`MBlock::getOnlineDataArray()`**: Direct access to online-only items
* **`MBlock::getOfflineDataArray()`**: Direct access to offline-only items
* **Legacy Support**: Existing filter methods maintained for backward compatibility

#### Internationalization & UX
* **Multi-language Support**: Extended language files for German and English
* **Template Translation**: New `{{mblock::language_key}}` syntax in templates
* **Enhanced Parser**: `MBlockParser` supports automatic language placeholder replacement
* **Modern UI**: Updated to use bloecks ^5.2.0 for consistent drag & drop
* **Dark Mode**: Full compatibility with REDAXO dark theme

### Technical Improvements
* **Minimum REDAXO Version**: Now requires REDAXO ^5.18.0
* **Bloecks Dependency**: Added bloecks ^5.2.0 as required dependency
* **Integrated CSS**: All styles now in main `mblock.css` file
* **Enhanced Error Handling**: Better validation and error recovery
* **Code Cleanup**: Removed all debug code and improved performance

### Documentation
* **Comprehensive README**: Complete feature overview and API documentation
* **Usage Examples**: Practical examples for offline toggle and copy/paste
* **Migration Guide**: Instructions for upgrading from previous versions

### Version 3.4.0 - 3.4.3
* rex_version::compare fixed for REDAXO >= 5.12
* dark-mode support for REDAXO >= 5.13
* don't remove \' in media widget onclick id
* add mblock:change and set rex:change to deprecated. rex:change will remove in next minor version
* refresh disabled button status by drag and drop movements
* don't add empty link and media list option
* dark mode support thx to: @eaCe, @skerbis

### Version 3.2.0

* fixes: https://github.com/FriendsOfREDAXO/mblock/issues/137
* Widget fixes for REDAXO 5.12.1

### Version 3.1.0

* added rex:change event after item movements


### Version 3.0.1

* fix exactly 2 parameters, 3 given in `MBlockFormItemDecorator`
* fix ensure demo table on update

### Version 3.0.0

* add saveHtml method to trait and remove libxml special
* use saveHtml from trait in replacer and decorator classes
* use for iclone item a hidden sortitem container
* use rex:ready by add block
* remove all mblock callback events
* fix `Call to a member function getAttribute() on null` bug in bootstrap replacer
* fix bug by multiple selects `DOMElement::setAttribute() expects parameter 2 to be string, array given`
* fix `Invalid argument supplied for foreach()` issue in `MBlockRexFormProcessor`
* added initial_hidden option for initial without form element, it will be add only a [+] button
* remove default setting to empty form content by duplication 
* added initial_button_text optional
    ```
    <?php
    $mform = new MForm();
    $mform->addFieldset('Text');
    $mform->addTextField('1.0.1', ['label' => 'Text']);
    echo MBlock::show(1, $mform->show(), ['initial_hidden' => 1, 'min' => 0, 'initial_button_text' => 'Press [+] to create MBlock']);
    ```

### Version 2.2.3

* Installfix for 2.2.2
* Selectfix  - danke @tbaddade 
* https://github.com/FriendsOfREDAXO/mblock/pull/96

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
