# MBlock API Documentation

MBlock bietet eine umfassende API für PHP und JavaScript zur Verwaltung und Manipulation von Datenblöcken in REDAXO-Modulen.

## Features

### Core Features
- **Beliebig viele Datenblöcke** pro Modul erstellen
- **Drag & Drop Sortierung** mit bloecks addon (^5.2.0)
- **Minimale/Maximale Anzahl** von Blöcken definierbar
- **MForm Integration** für professionelle Formulare
- **Template System** mit automatischer CSS-Verwaltung
- **Mehrsprachigkeit** (DE/EN/ES/NL/PT/SV)

### Advanced Features
- **Online/Offline Toggle** - Blöcke aktivieren/deaktivieren ohne löschen
- **Copy & Paste** - Komfortable Duplizierung von Inhalten
- **Frontend API Methoden** - `filterByField()`, `sortByField()`, `groupByField()`
- **Schema.org Support** - SEO-optimierte JSON-LD Generierung
- **Erweiterte Datenabfrage** - Online/Offline/All Modi
- **Media-ID Konflikt-Schutz** - Bessere Warnung bei ID-Überschneidungen

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [PHP API](#php-api)
  - [Namespace und Klassen](#namespace-und-klassen)
  - [Datenabfrage](#datenabfrage)
  - [Frontend-Verarbeitung](#frontend-verarbeitung)
  - [Template-System](#template-system)
  - [Konfiguration](#konfiguration)
- [JavaScript API](#javascript-api)
  - [Modulare Architektur](#modulare-architektur)
  - [Events](#events)
  - [Methoden](#methoden)
  - [Clipboard API](#clipboard-api)
- [Extension Points](#extension-points)
- [Template API](#template-api)
- [Hooks und Events](#hooks-und-events)
- [Vollständige Beispiele](#vollständige-beispiele)
- [Build System](#build-system)

---

## Requirements

MBlock erfordert:

- **REDAXO**: ^5.18.0
- **bloecks**: ^5.2.4 (für moderne Drag & Drop Funktionalität)
- **MForm**: ^8.0 (optional, für erweiterte Formular-Features)

### Installation

```bash
# Via REDAXO Installer
# 1. Gehe zu System > Installer
# 2. Suche nach "mblock"
# 3. Installiere mblock zusammen mit bloecks
```

---

## PHP API

### Namespace und Klassen

```php
<?php
// Empfohlener Namespace-Import (ab Version 4.0)
use FriendsOfRedaxo\MBlock\MBlock;

// Legacy-Support (wird in v5.0 entfernt)
// Funktioniert ohne use-Statement
```

**Verfügbare Klassen:**
- `FriendsOfRedaxo\MBlock\MBlock` - Hauptklasse
- `FriendsOfRedaxo\MBlock\Provider\TemplateProvider` - Template-Verwaltung
- `FriendsOfRedaxo\MBlock\Utils\TemplateManager` - Template-Operationen
- `FriendsOfRedaxo\MBlock\Handler\ValueHandler` - Datenverarbeitung

### Datenabfrage

#### `MBlock::getDataArray(string $value, string $mode = 'all'): array`

Zentrale Methode zum Abrufen von MBlock-Daten.

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

// Alle Daten (Online + Offline)
$allItems = MBlock::getDataArray("REX_VALUE[1]");

// Nur Online-Items (für Frontend)
$onlineItems = MBlock::getDataArray("REX_VALUE[1]", 'online');

// Nur Offline-Items (für Backend-Previews)
$offlineItems = MBlock::getDataArray("REX_VALUE[1]", 'offline');
```

**Parameter:**
- `$value` (string): REX_VALUE String oder Array
- `$mode` (string): `'all'`, `'online'`, `'offline'`

**Rückgabe:** Array von MBlock-Items

#### Convenience-Methoden

```php
<?php
// Shortcut für Online-Items
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Shortcut für Offline-Items  
$items = MBlock::getOfflineDataArray("REX_VALUE[1]");
```

#### Status-Überprüfung

```php
<?php
// Prüft ob Item online ist
$isOnline = MBlock::isOnline($item);

// Prüft ob Item offline ist
$isOffline = MBlock::isOffline($item);
```

### Frontend-Verarbeitung

#### `MBlock::filterByField(array $items, string $field, mixed $value): array`

Filtert Items nach Feldwert.

```php
<?php
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Nach Kategorie filtern
$newsItems = MBlock::filterByField($items, 'category', 'news');

// Mehrere Werte (OR-Verknüpfung)
$importantItems = MBlock::filterByField($items, 'priority', ['high', 'urgent']);

// Boolean-Filterung
$activeItems = MBlock::filterByField($items, 'active', true);
```

#### `MBlock::sortByField(array $items, string $field, string $order = 'ASC', string $type = 'string'): array`

Sortiert Items nach Feldwert.

```php
<?php
// Alphabetisch sortieren
$sortedByTitle = MBlock::sortByField($items, 'title', 'ASC');

// Nach Datum sortieren  
$sortedByDate = MBlock::sortByField($items, 'date', 'DESC', 'date');

// Numerisch sortieren
$sortedByPrice = MBlock::sortByField($items, 'price', 'DESC', 'numeric');
```

**Sortiertypen:**
- `'string'` - Alphabetische Sortierung
- `'numeric'` - Numerische Sortierung  
- `'date'` - Datums-Sortierung

#### `MBlock::groupByField(array $items, string $field): array`

Gruppiert Items nach Feldwert.

```php
<?php
$grouped = MBlock::groupByField($items, 'category');

foreach ($grouped as $category => $categoryItems) {
    echo "<h2>" . rex_escape($category) . "</h2>";
    foreach ($categoryItems as $item) {
        echo "<p>" . rex_escape($item['title']) . "</p>";
    }
}
```

#### `MBlock::limitItems(array $items, int $limit, int $offset = 0): array`

Begrenzt die Anzahl der Items (Pagination).

```php
<?php
// Erste 5 Items
$topItems = MBlock::limitItems($items, 5);

// Items 6-10 (Pagination)
$nextItems = MBlock::limitItems($items, 5, 5);
```

#### `MBlock::generateSchema(array $items, string $schemaType, array $fieldMapping): array`

Generiert Schema.org JSON-LD Daten für SEO.

```php
<?php
$schema = MBlock::generateSchema($items, 'Person', [
    'name' => 'full_name',
    'jobTitle' => 'position', 
    'image' => 'photo',
    'email' => 'email_address',
    'url' => 'website'
]);

echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
```

**Unterstützte Schema-Typen:**
- `Person`, `Organization`, `Product`, `Article`, `Recipe`, `Event`

### Template-System

#### `MBlock::show(int $id, string $form, array $options = []): string`

Hauptmethode zur Anzeige von MBlock-Instanzen.

```php
<?php
echo MBlock::show(1, $mform->show(), [
    'min' => 1,              // Minimale Anzahl Items (werden initial angezeigt)
    'max' => 10,             // Maximale Anzahl Items  
    'template' => 'modern',  // Template-Name
    'copy_paste' => true,    // Copy & Paste aktivieren
    'online_offline' => true // Online/Offline Toggle
]);
```

**Optionen:**
- `min` (int): Minimale Item-Anzahl (wird initial angezeigt)
- `max` (int): Maximale Item-Anzahl
- `template` (string): Template-Name aus `data/templates/`
- `copy_paste` (bool): Copy & Paste Feature
- `online_offline` (bool): Online/Offline Toggle
- `delete_confirm` (bool): Lösch-Bestätigung
- `sort_handle` (bool): Sortier-Handle anzeigen

### Konfiguration

#### `MBlock::getConfig(string $key, mixed $default = null): mixed`

Ruft MBlock-Konfiguration ab.

```php
<?php
$currentTheme = MBlock::getConfig('mblock_theme', 'standard');
$copyPasteEnabled = MBlock::getConfig('mblock_copy_paste', true);
```

#### `MBlock::setConfig(string $key, mixed $value): void`

Setzt MBlock-Konfiguration.

```php
<?php
MBlock::setConfig('mblock_theme', 'modern');
MBlock::setConfig('mblock_delete_confirm', false);
```

---

## JavaScript API

### Modulare Architektur

MBlock verwendet eine **modulare JavaScript-Architektur** in drei logischen Modulen:

- **`mblock-core.js`** - Base utilities, Validierung, Übersetzungen (384 Zeilen)
- **`mblock-management.js`** - DOM-Manipulation, Sortable-Handling (1008 Zeilen)  
- **`mblock-features.js`** - Copy/Paste, Online/Offline Toggle, REDAXO Widgets (815 Zeilen)

Dies verbessert die **Wartbarkeit**, reduziert **Code-Redundanz** und ermöglicht besseres **Debugging**.

#### Asset Loading Modi

Das System unterstützt verschiedene Asset-Loading Modi (konfigurierbar in `boot.php`):

```php
$assetMode = 'auto'; // Options: 'auto', 'modular', 'combined', 'prod'
```

- **`auto`** (Standard) - Automatische Erkennung basierend auf Debug-Modus
  - **Development**: `mblock.js` (kombinierte Datei)
  - **Production**: `mblock.min.js` (minifiziert)
  
- **`modular`** - Lädt 3 separate Dateien (erweiterte Debugging-Möglichkeiten)
  - `mblock-core.js` → `mblock-management.js` → `mblock-features.js`
  
- **`combined`** - Immer kombinierte Datei (`mblock.js`)
- **`prod`** - Immer minifizierte Datei (`mblock.min.js`)

## Gridblock Integration

MBlock bietet eine umfassende Integration mit dem Gridblock-Addon, insbesondere für die korrekte Behandlung von REX_LINK Display-Feldern nach Copy-Operationen.

### REX_LINK Fix System

Das integrierte Fix-System repariert automatisch REX_LINK Display-Namen nach Copy-Operationen in Gridblock-Kontexten:

```javascript
// Automatische Registrierung in mblock_reinitialize_redaxo_widgets()
mblock_gridblock_rex_link_fix_hook(container);
```

**Features:**
- Automatische Gridblock-Erkennung
- Multi-Step-Reparatur (5 Schritte: 50ms, 200ms, 500ms, 1000ms, 2000ms)
- Multiple Suchstrategien für Display-Felder
- Tab-Support und Validierung
- Detailliertes Console-Logging

### Cardgrid-Beispiele

Vorgefertigte Beispiele für typische Gridblock-Layouts:

- **cardgrid_base.php**: Einfaches 3-Spalten Card-Grid
- **cardgrid_extended.php**: Card-Grid mit Tab-Navigation

### Hook-System

Registriere eigene Hooks für spezielle Nachbearbeitung:

```javascript
if (typeof window.mblock_gridblock_hooks === 'undefined') {
    window.mblock_gridblock_hooks = [];
}

window.mblock_gridblock_hooks.push(function(container) {
    // Deine spezielle Logik nach Copy-Operation
    console.log('Gridblock Copy-Operation erkannt');
});
```

### Events

MBlock feuert verschiedene Events für erweiterte Integration:

#### Core Events

```javascript
// Block wurde hinzugefügt
$(document).on('mblock:add', function(e, mblock, element) {
    console.log('Block hinzugefügt:', element);
});

// Block wurde entfernt
$(document).on('mblock:remove', function(e, mblock, element) {
    console.log('Block entfernt:', element);
});

// Sortierung geändert
$(document).on('mblock:sort', function(e, mblock, element, ui) {
    console.log('Sortierung geändert:', element);
});

// MBlock vollständig initialisiert
$(document).on('mblock:ready', function(e, mblock) {
    console.log('MBlock bereit:', mblock);
});
```

#### Copy & Paste Events

```javascript
// Element wurde kopiert
$(document).on('mblock:copy', function(e, mblock, element, data) {
    console.log('Element kopiert:', data);
});

// Element wurde eingefügt
$(document).on('mblock:paste', function(e, mblock, element, data) {
    console.log('Element eingefügt:', data);
});

// Zwischenablage wurde geleert
$(document).on('mblock:clipboard-clear', function(e) {
    console.log('Zwischenablage geleert');
});
```

#### Online/Offline Events

```javascript
// Status wurde geändert (online/offline)
$(document).on('mblock:status-change', function(e, mblock, element, status) {
    console.log('Status geändert zu:', status);
});
```

#### Legacy Events (deprecated)

```javascript
// Deprecated - verwenden Sie mblock:add stattdessen
$(document).on('rex:change', function(e) {
    // Wird in v5.0 entfernt
});
```

### Methoden

#### `MBlockCore.getItemCount(mblockName)`

Gibt die aktuelle Anzahl der Items zurück.

```javascript
var count = MBlockCore.getItemCount('mblock_1');
console.log('Aktuelle Item-Anzahl:', count);
```

#### `MBlockCore.validateLimits(mblockName)`

Prüft Min/Max-Limits und aktualisiert UI entsprechend.

```javascript
MBlockCore.validateLimits('mblock_1');
```

#### `MBlockCore.reinitializeWidgets(element)`

Reinitialisiert REDAXO-Widgets in einem Element.

```javascript
// Nach dynamischem Hinzufügen von Inhalten
MBlockCore.reinitializeWidgets($('#new-mblock-item'));
```

### Clipboard API

#### `MBlockClipboard.copy(element, mblockName, moduleId)`

Kopiert ein Element in die Zwischenablage.

```javascript
var element = $('.sortitem').first();
MBlockClipboard.copy(element, 'mblock_1', 'module_5');
```

#### `MBlockClipboard.paste(targetContainer, mblockName, moduleId)`

Fügt Element aus Zwischenablage ein.

```javascript
var container = $('.mblock_wrapper[data-mblock-name="mblock_1"]');
MBlockClipboard.paste(container, 'mblock_1', 'module_5');
```

#### `MBlockClipboard.hasData()`

Prüft ob Zwischenablage Daten enthält.

```javascript
if (MBlockClipboard.hasData()) {
    // Paste-Button aktivieren
}
```

#### `MBlockClipboard.clear()`

Leert die Zwischenablage.

```javascript
MBlockClipboard.clear();
```

---

## Extension Points

MBlock bietet verschiedene Extension Points für eigene Erweiterungen:

### PHP Extension Points

#### `MBLOCK_DATA_FILTER`

Filtert MBlock-Daten vor der Ausgabe.

```php
<?php
rex_extension::register('MBLOCK_DATA_FILTER', function($params) {
    $items = $params['items'];
    $mode = $params['mode'];
    
    // Custom filtering logic
    if ($mode === 'frontend') {
        $items = array_filter($items, function($item) {
            return !empty($item['published']);
        });
    }
    
    return $items;
});
```

#### `MBLOCK_TEMPLATE_RENDER`

Modifiziert Template-Rendering.

```php
<?php
rex_extension::register('MBLOCK_TEMPLATE_RENDER', function($params) {
    $template = $params['template'];
    $type = $params['type']; // 'element' oder 'wrapper'
    
    // Custom template modifications
    if ($type === 'element') {
        $template = str_replace('{{custom_var}}', 'Custom Value', $template);
    }
    
    return $template;
});
```

#### `MBLOCK_SCHEMA_GENERATE`

Erweitert Schema.org Generierung.

```php
<?php
rex_extension::register('MBLOCK_SCHEMA_GENERATE', function($params) {
    $schema = $params['schema'];
    $schemaType = $params['schemaType'];
    $items = $params['items'];
    
    // Add custom schema properties
    if ($schemaType === 'Person') {
        foreach ($schema as &$item) {
            $item['@context'] = 'https://schema.org';
            $item['nationality'] = 'German';
        }
    }
    
    return $schema;
});
```

### JavaScript Extension Points

#### `mblock.config`

Globale JavaScript-Konfiguration.

```javascript
// Vor MBlock-Initialisierung
window.mblock = window.mblock || {};
window.mblock.config = {
    debug: true,
    clipboard: {
        storage: 'sessionStorage', // oder 'localStorage'
        prefix: 'mblock_'
    },
    animations: {
        duration: 300,
        easing: 'swing'
    }
};
```

---

## Template API

### Template-System mit Dropdown-Auswahl

MBlock bietet ein modernes Template-System mit grafischer Auswahl über ein Dropdown-Menü in den AddOn-Einstellungen. Das System kopiert automatisch die CSS-Dateien in den `assets/` Ordner und sorgt für optimale Performance.

#### Template-Auswahl
Die Template-Auswahl erfolgt über die **MBlock-Einstellungen**:

1. **Gehe zu** `Addons > MBlock > Einstellungen`
2. **Wähle ein Template** aus der Dropdown-Liste
3. **Klicke "Speichern"** - Das CSS wird automatisch kopiert
4. **Das Template ist sofort aktiv**

#### Dark Mode Support
**Die mitgelieferten Templates** unterstützen Dark Mode:

- **REDAXO Theme Detection** (`body.rex-theme-dark`)
- **Browser Preference** (`@media (prefers-color-scheme: dark)`)
- **Bootstrap 5 Dark Mode** (`[data-bs-theme="dark"]`)

#### Template-Struktur

Verfügbare Templates im `data/templates/` Verzeichnis:
- **`standard`** - Standard-Template (wird bei Installation gesetzt)
- **`modern`** - Modernes Design
- **`akg_skin`** - AKG-Design mit Bootstrap Grid
- **`retro_8bit`** - Retro 8-Bit Style

```bash
redaxo/data/addons/mblock/templates/
├── standard/
│   ├── mblock_wrapper.ini     # HTML-Wrapper für alle Items
│   ├── mblock_element.ini     # HTML-Template für einzelne Items
│   └── theme.css              # Template-Styling (optional)
├── modern/
└── akg_skin/
```

**Wichtig:** Die CSS-Datei muss den **gleichen Namen wie der Template-Ordner** haben!

### Template Tags

MBlock-Templates unterstützen spezielle Tags für dynamische Inhalte:

#### Element Template Tags (`mblock_element.ini`)

```html
<!-- Formular-Inhalt -->
<mblock:form/>

<!-- Element-Index (0-basiert) -->
<span data-index="<mblock:index/>">

<!-- CSS-Klasse für Offline-Status -->  
<div class="item<mblock:offline_class/>">

<!-- Online/Offline Toggle Button -->
<mblock:offline_button/>

<!-- Copy & Paste Buttons -->
<mblock:copy_paste_buttons/>

<!-- Einzelne Buttons -->
<button class="btn btn-default addme">
<button class="btn btn-move moveup">
<button class="btn btn-move movedown">  
<button class="btn btn-delete removeme">
```

#### Wrapper Template Tags (`mblock_wrapper.ini`)

```html
<!-- Container-Einstellungen (data-Attribute) -->
<div class="mblock_wrapper"<mblock:settings/>>

<!-- Copy & Paste Toolbar -->
<mblock:copy_paste_toolbar/>

<!-- Alle MBlock-Elemente -->
<mblock:output/>
```

#### Sprachunterstützung

Templates unterstützen Sprachvariablen:

```html
<button title="{{mblock::mblock_add_element}}">+</button>
<button title="{{mblock::mblock_delete_confirm}}">×</button>
```

### Custom Template Entwicklung

```php
<?php
// Template-Provider erweitern
class CustomTemplateProvider extends \FriendsOfRedaxo\MBlock\Provider\TemplateProvider
{
    public static function getTemplate($type, $templateName = null)
    {
        // Custom template loading logic
        $template = parent::getTemplate($type, $templateName);
        
        // Add custom replacements
        $template = str_replace('{{custom_tag}}', 'Custom Content', $template);
        
        return $template;
    }
}
```

---

## Hooks und Events

### rex_extension Hooks

MBlock registriert sich in verschiedene REDAXO Extension Points:

#### `OUTPUT_FILTER`

Ersetzt MBlock-Platzhalter im finalen Output.

```php
<?php
rex_extension::register('OUTPUT_FILTER', function($params) {
    // MBlock processing happens here
    return $params['subject'];
});
```

#### `REX_FORM_SAVED`

Verarbeitet MBlock-Daten bei Formular-Speicherung.

```php
<?php  
rex_extension::register('REX_FORM_SAVED', function($params) {
    $form = $params['form'];
    $sql = $params['sql'];
    
    // Process MBlock data after form save
});
```

### Custom Event Listener

```javascript
// Custom Event Handler für MBlock-Integration
$(document).ready(function() {
    
    // Reagiere auf alle MBlock-Events
    $(document).on('mblock:add mblock:remove mblock:sort', function(e) {
        console.log('MBlock Event:', e.type);
        
        // Custom logic nach Block-Änderungen
        updatePreview();
        saveFormState();
    });
    
    // Custom Copy & Paste Handler
    $(document).on('mblock:paste', function(e, mblock, element, data) {
        // Reinitialisiere Custom-Widgets
        element.find('.custom-widget').each(function() {
            // Custom widget initialization
        });
    });
});

function updatePreview() {
    // Custom preview update logic
}

function saveFormState() {
    // Auto-save functionality
}
```

---

## Debugging und Entwicklung

### Debug-Modus

```php
<?php
// Debug-Informationen anzeigen
if (rex::isDebugMode()) {
    $items = MBlock::getDataArray("REX_VALUE[1]");
    dump($items); // Zeigt Datenstruktur
}
```

### JavaScript Debugging

```javascript
// Debug-Modus aktivieren
window.mblock.config.debug = true;

// Console-Ausgaben für alle Events
$(document).on('mblock:add mblock:remove mblock:copy mblock:paste', function(e) {
    if (window.mblock.config.debug) {
        console.log('MBlock Debug:', e.type, e);
    }
});
```

### Performance-Monitoring

```php
<?php
// Performance-Messung für große Datenmengen
$start = microtime(true);
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");
$filtered = MBlock::filterByField($items, 'category', 'news');
$sorted = MBlock::sortByField($filtered, 'date', 'DESC', 'date');
$duration = microtime(true) - $start;

if (rex::isDebugMode()) {
    echo "<!-- MBlock Processing: " . round($duration * 1000, 2) . "ms -->";
}
```

---

## Migration und Kompatibilität

### Von Version 3.x zu 4.x

```php
<?php
// Alt (v3.x)
$items = rex_var::toArray("REX_VALUE[1]");

// Neu (v4.x)  
$items = MBlock::getDataArray("REX_VALUE[1]");

// Mit Namespace (empfohlen)
use FriendsOfRedaxo\MBlock\MBlock;
$items = MBlock::getDataArray("REX_VALUE[1]");
```

### Template-Migration

```html
<!-- Alt (v3.x) -->
{elements}
{add_button}
{content}

<!-- Neu (v4.x) -->
<mblock:output/>
<!-- add_button wird automatisch eingefügt -->
<mblock:form/>
```

### JavaScript Event Migration

```javascript
// Alt (deprecated)
$(document).on('rex:change', function() {
    // Legacy handler
});

// Neu (v4.x)
$(document).on('mblock:add mblock:remove mblock:sort', function() {
    // Modern handler
});
```

---

## Vollständige Beispiele

### Example 1: Team Members mit MForm

**Input:**
```php
<?php
// Modernisiertes Beispiel mit MForm
use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MBlock\MBlock;

// base ID
$id = 1;

// init mform mit moderner MForm Syntax
$mform = MForm::factory();

// fieldset
$mform->addFieldsetArea('Team member');

// textinput
$mform->addTextField("$id.0.name", array('label'=>'Name'));

// media button
$mform->addMediaField(1, array('label'=>'Avatar'));

// Online/Offline Status (hidden field für Toggle-Funktion)
$mform->addHiddenField("$id.0.mblock_offline", '0');

// MBlock anzeigen (Copy & Paste ist automatisch aktiv)
echo MBlock::show($id, $mform->show(), array(
    'min' => 2,
    'max' => 4,
    'template' => 'modern'
));
```

**Output:**
```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

// Verbesserte Ausgabe - nur Online-Items
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

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
// echo '<pre>'; dump(MBlock::getDataArray("REX_VALUE[1]")); echo '</pre>';
```

### Example 2: Team Members ohne MForm

**Input:**
```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

// base ID
$id = 1;

// html form
$form = <<<EOT
    <fieldset class="form-horizontal">
        <legend>Team member</legend>
        <div class="form-group">
            <div class="col-sm-2 control-label"><label for="rv2_1_0_name">Name</label></div>
            <div class="col-sm-10"><input id="rv2_1_0_name" type="text" name="REX_INPUT_VALUE[$id][0][name]" value="" class="form-control"></div>
        </div>
        <div class="form-group">
            <div class="col-sm-2 control-label"><label>Avatar</label></div>
            <div class="col-sm-10">
                REX_MEDIA[id="1" widget="1"]
            </div>
        </div>
    </fieldset>
EOT;

// MBlock mit Features
echo MBlock::show($id, $form, array(
    'online_offline' => true,
    'copy_paste' => true
));
```

**Output:**
```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

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

### Example 3: Erweiterte Frontend-Verarbeitung

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Gruppierung nach Kategorie
$grouped = MBlock::groupByField($items, 'category');

foreach ($grouped as $category => $categoryItems) {
    echo '<div class="category-' . $category . '">';
    echo '<h2>' . rex_escape($category) . '</h2>';
    
    // Sortierung nach Titel
    $sorted = MBlock::sortByField($categoryItems, 'title');
    
    foreach ($sorted as $item) {
        echo '<article>';
        echo '<h3>' . rex_escape($item['title']) . '</h3>';
        echo '<p>' . nl2br(rex_escape($item['text'])) . '</p>';
        echo '</article>';
    }
    
    echo '</div>';
}

// SEO Schema.org JSON-LD
$schema = MBlock::generateSchema($items, 'Article', [
    'headline' => 'title',
    'articleBody' => 'text',
    'articleSection' => 'category'
]);
echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
```

---

## Build System

Das Build-System kombiniert automatisch die modularen JavaScript-Dateien und erstellt optimierte Versionen.

### Voraussetzungen
- **Node.js** (Version 14 oder höher)  
- **npm** (wird normalerweise mit Node.js installiert)

### Build-Prozess ausführen

1. **Terminal öffnen** und in das Build-Verzeichnis wechseln:
   ```bash
   cd redaxo/src/addons/mblock/build
   ```

2. **Build-Script ausführen**:
   ```bash
   ./build.sh
   ```

3. **Automatischer Prozess**:
   - Kombiniert die 3 modularen Dateien zu einer einzigen Datei
   - Erstellt `mblock-combined.js` (Zwischenergebnis)
   - Aktualisiert `mblock.js` (Development-Version) 
   - Erstellt `mblock.min.js` (Production-Version mit Terser-Minifizierung)
   - Generiert Source Map für Debugging

### Build-Features

- **🔗 Smart Combining** - Intelligente Kombination der modularen Dateien
- **⚙️  Advanced Minification** - Terser mit optimierten Settings (2 Compression-Passes)
- **🗺️  Source Maps** - Für einfaches Debugging der minifizierten Datei
- **📊 Performance Stats** - Detaillierte Größen- und Kompressions-Statistiken
- **🔧 Error Handling** - Robuste Fehlerbehandlung und Validierung
- **♻️  Auto-Update** - Synchronisation zwischen Development- und Production-Dateien

### Enhanced REX_LINK/REX_MEDIA Support

Das neue System bietet **verbesserte REDAXO Widget-Unterstützung**:

- ✅ **REX_LINK Copy/Paste** - Artikel-IDs und Namen werden korrekt kopiert
- ✅ **REX_MEDIA Copy/Paste** - Media-Dateien mit Metadaten  
- ✅ **Widget-Reinitialization** - Onclick-Handler werden automatisch aktualisiert
- ✅ **Auto Name Fetching** - Artikel-Namen werden automatisch per AJAX geholt

### Development Workflow

**Für MBlock-Entwicklung**:

1. **Bearbeite die modularen Dateien**:
   - `assets/mblock-core.js`
   - `assets/mblock-management.js` 
   - `assets/mblock-features.js`

2. **Build ausführen** nach Änderungen:
   ```bash
   cd build && ./build.sh
   ```

3. **Testen** in REDAXO (Debug-Modus nutzt automatisch die Development-Version)

4. **Production-Deploy**: Die minifizierte Version wird automatisch generiert

---

## Beispiele und Best Practices

### Vollständiges Modul-Beispiel

```php
<?php
// === INPUT ===
use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MBlock\MBlock;

$mform = MForm::factory();
$mform->addFieldsetArea('Content Card');
$mform->addTextField('1.0.title', ['label' => 'Titel']);
$mform->addSelectField('1.0.category', [
    'news' => 'News', 
    'events' => 'Events'
], ['label' => 'Kategorie']);
$mform->addTextAreaField('1.0.text', ['label' => 'Text']);
$mform->addHiddenField('1.0.mblock_offline', '0');

echo MBlock::show(1, $mform->show(), [
    'min' => 1,
    'max' => 5,
    'template' => 'modern'
]);

// === OUTPUT ===
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Gruppierung nach Kategorie
$grouped = MBlock::groupByField($items, 'category');

foreach ($grouped as $category => $categoryItems) {
    echo '<div class="category-' . $category . '">';
    echo '<h2>' . rex_escape($category) . '</h2>';
    
    // Sortierung nach Titel
    $sorted = MBlock::sortByField($categoryItems, 'title');
    
    foreach ($sorted as $item) {
        echo '<article>';
        echo '<h3>' . rex_escape($item['title']) . '</h3>';
        echo '<p>' . nl2br(rex_escape($item['text'])) . '</p>';
        echo '</article>';
    }
    
    echo '</div>';
}

// SEO Schema.org
$schema = MBlock::generateSchema($items, 'Article', [
    'headline' => 'title',
    'articleBody' => 'text',
    'articleSection' => 'category'
]);
echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
```

Diese API-Dokumentation bietet einen vollständigen Überblick über alle verfügbaren Features und Methoden von MBlock für sowohl PHP- als auch JavaScript-Entwicklung.
