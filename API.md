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
    'min' => 1,              // Minimale Anzahl Items
    'max' => 10,             // Maximale Anzahl Items  
    'template' => 'modern',  // Template-Name
    'copy_paste' => true,    // Copy & Paste aktivieren
    'online_offline' => true // Online/Offline Toggle
]);
```

**Optionen:**
- `min` (int): Minimale Item-Anzahl
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

MBlock bietet ein **umfassendes Event-System** für erweiterte Integration mit Texteditoren und anderen AddOns:

#### 🔥 NEW: Vollständiges MBlock Event-System

**Alle neuen Events** sind sowohl **lokal** (auf dem Element) als auch **global** (auf document) verfügbar:

#### Block-Erstellung Events

```javascript
// Block wurde erstellt (vor jeder Initialisierung)
$(document).on('mblock:item:created', function(e, item, wrapper) {
    console.log('Neuer Block erstellt:', item);
    // Perfekt für erste Widget-Setup, bevor rex:ready feuert
});

// Initialisierung startet (nach rex:ready, vor Widget-Setup)
$(document).on('mblock:item:init:start', function(e, item, wrapper) {
    console.log('Block-Initialisierung startet:', item);
    // Für pre-widget Setup-Logik
});

// REX-Widgets sind fertig initialisiert
$(document).on('mblock:item:widgets:ready', function(e, item, wrapper) {
    console.log('REX-Widgets bereit:', item);
    // REX_MEDIA, REX_LINK, etc. sind nun funktional
});

// Block ist vollständig bereit für Third-Party AddOns
$(document).on('mblock:item:ready', function(e, item, wrapper) {
    console.log('Block bereit für Third-Party Integration:', item);
    // ⭐ HAUPTEVENT für Texteditor-Integration!
});

// Animation ist abgeschlossen
$(document).on('mblock:item:animated', function(e, item, wrapper) {
    console.log('Block-Animation abgeschlossen:', item);
    // Für Animationen die nach dem Glow-Effekt starten sollen
});

// Block ist vollständig fertig (gescrollt, animiert, alles)
$(document).on('mblock:item:complete', function(e, item, wrapper) {
    console.log('Block vollständig abgeschlossen:', item);
    // Für finale Cleanup- oder Analyse-Tasks
});
```

#### Block-Löschung Events

```javascript
// Vor dem Löschen (kann Cancel-Logic enthalten)
$(document).on('mblock:item:before:remove', function(e, item, wrapper) {
    console.log('Block wird gelöscht:', item);
    // Cleanup von Third-Party Widgets vor Löschung
    item.find('.ckeditor').each(function() {
        if (this.ckeditorInstance) {
            this.ckeditorInstance.destroy();
        }
    });
});

// Nach dem Löschen
$(document).on('mblock:item:removed', function(e, wrapper, prevItem) {
    console.log('Block wurde gelöscht, Focus auf:', prevItem);
    // Für Reindexierung oder Cleanup nach Löschung
});
```

#### Copy & Paste Events

```javascript
// Vor dem Kopieren
$(document).on('mblock:item:before:copy', function(e, item, wrapper) {
    console.log('Block wird kopiert:', item);
    // Texteditor-Inhalte für Copy vorbereiten
});

// Nach erfolgreichem Kopieren  
$(document).on('mblock:item:copied', function(e, item, wrapper, clipboardData) {
    console.log('Block kopiert, Clipboard:', clipboardData);
    // Copy-Feedback oder Analytics
});

// Vor dem Einfügen
$(document).on('mblock:item:before:paste', function(e, wrapper, afterItem) {
    console.log('Block wird eingefügt nach:', afterItem);
    // Pre-Paste Setup
});

// Nach erfolgreichem Einfügen
$(document).on('mblock:item:pasted', function(e, pastedItem, wrapper, clipboardData) {
    console.log('Block eingefügt:', pastedItem);
    // ⭐ WICHTIG für Texteditor nach Paste-Operation!
});
```

#### Legacy Events (weiterhin verfügbar)

```javascript
// Block-Änderung (Sortierung/Verschiebung) - bestehendes Event
$(document).on('mblock:change', function(e, element) {
    console.log('Block-Sortierung:', element);
});

// Standard REDAXO-Event (funktioniert weiterhin)
$(document).on('rex:ready', function(e, container) {
    if (container) {
        console.log('REX-Ready Event:', container);
    }
});
```

## 💡 Integration mit Texteditoren

### CKEditor 4/5 Integration

**⭐ NEU: Verwende die spezifischen MBlock-Events für optimale Integration:**

```javascript
// ✅ BESTE PRAXIS: Neue Texteditor-Integration
$(document).on('mblock:item:ready', function(e, item, wrapper) {
    console.log('Block bereit für Texteditor-Setup:', item);
    
    // CKEditor 4 - Item ist komplett initialisiert
    item.find('textarea.ckeditor').each(function() {
        if (!this.ckeditorInstance && this.id) {
            CKEDITOR.replace(this.id, {
                // Deine CKEditor Config
                height: 200,
                toolbar: 'Basic'
            });
            this.ckeditorInstance = CKEDITOR.instances[this.id];
        }
    });
    
    // CKEditor 5 - Item ist komplett initialisiert
    item.find('.ckeditor5').each(function() {
        if (!this.ckeditorInstance) {
            ClassicEditor.create(this, {
                // Deine CKEditor 5 Config
            }).then(editor => {
                this.ckeditorInstance = editor;
            });
        }
    });
});

// ✅ BESTE PRAXIS: Texteditor nach Copy & Paste neu initialisieren
$(document).on('mblock:item:pasted', function(e, pastedItem, wrapper, clipboardData) {
    console.log('Eingefügten Block für Texteditoren setup:', pastedItem);
    
    // CKEditor für kopierten Block neu initialisieren
    pastedItem.find('textarea.ckeditor').each(function() {
        // Neue eindeutige ID generieren
        const newId = this.id + '_copy_' + Date.now();
        this.id = newId;
        
        CKEDITOR.replace(newId);
        this.ckeditorInstance = CKEDITOR.instances[newId];
    });
});

// ✅ BESTE PRAXIS: Cleanup bei Block-Löschung
$(document).on('mblock:item:before:remove', function(e, item, wrapper) {
    console.log('Cleanup Texteditoren vor Löschung:', item);
    
    // CKEditor 4 Instances zerstören
    item.find('.ckeditor').each(function() {
        if (this.ckeditorInstance) {
            this.ckeditorInstance.destroy();
            this.ckeditorInstance = null;
        }
    });
    
    // CKEditor 5 Instances zerstören  
    item.find('.ckeditor5').each(function() {
        if (this.ckeditorInstance) {
            this.ckeditorInstance.destroy();
            this.ckeditorInstance = null;
        }
    });
});
```

### Form Builder & Custom Widget Integration

```javascript
// Multi-Select, Datepicker, Custom Komponenten
$(document).on('mblock:item:ready', function(e, item, wrapper) {
    
    // Select2 / Chosen Reinitialisiern
    item.find('.select2').each(function() {
        $(this).select2('destroy').select2();
    });
    
    // Datepicker
    item.find('.datepicker').datepicker({
        format: 'dd.mm.yyyy'
    });
    
    // Custom Form Components
    item.find('.custom-widget').each(function() {
        $(this).customWidget({
            // Config
        });
    });
});

// Color Picker, File Uploader, etc.
$(document).on('mblock:item:widgets:ready', function(e, item, wrapper) {
    
    // Color Picker nach REX-Widgets
    item.find('.colorpicker').colorpicker();
    
    // File Uploader
    item.find('.file-uploader').fileupload({
        // Uploader Config
    });
});
```

### Third-Party AddOn Integration Pattern

```javascript
// Vollständige AddOn-Integration
(function($) {
    'use strict';
    
    // Dein AddOn Namespace  
    const MyAddOn = {
        
        // Komponente initialisieren
        init: function(container) {
            container.find('.my-component').each(function() {
                const $elem = $(this);
                if (!$elem.data('my-addon-ready')) {
                    $elem.myPlugin();
                    $elem.data('my-addon-ready', true);
                }
            });
        },
        
        // Cleanup vor Löschung
        cleanup: function(container) {
            container.find('.my-component').each(function() {
                const $elem = $(this);
                if ($elem.data('my-addon-ready')) {
                    $elem.myPlugin('destroy');
                    $elem.removeData('my-addon-ready');
                }
            });
        }
    };
    
    // Event Bindings
    $(document).on('mblock:item:ready', function(e, item) {
        MyAddOn.init(item);
    });
    
    $(document).on('mblock:item:pasted', function(e, item) {
        MyAddOn.init(item);
    });
    
    $(document).on('mblock:item:before:remove', function(e, item) {
        MyAddOn.cleanup(item);
    });
    
})(jQuery);
```


### 🔄 Legacy Integration (rex:ready)

**Für bestehende AddOns ohne Event-Update:**

```javascript
// 🔄 LEGACY: Alte rex:ready Integration (funktioniert weiterhin)
$(document).on('rex:ready', function(e, container) {
    if (container) {
        // Fallback für existierende Implementierungen
        container.find('textarea.ckeditor').each(function() {
            if (!this.ckeditorInstance) {
                CKEDITOR.replace(this.id);
                this.ckeditorInstance = CKEDITOR.instances[this.id];
            }
        });
    }
});

// Alte Universal-Integration
$(document).on('mblock:change', function(e, element) {
    // Sortierte/verschobene Blöcke (Legacy Event)
    if (element && element.length) {
        reinitializeMyComponents(element);
    }
});
```

### Event-Parameter Referenz

#### Neue Events Parameter

```javascript
### Event-Parameter Referenz

#### Alle Event-Parameter im Detail

```javascript
// mblock:item:created - Block wurde erstellt (vor rex:ready)
function(event, item, wrapper) {
    // item: jQuery-Element des neuen Blocks (<div class="mblock_item">)
    // wrapper: jQuery-Element des mblock_wrapper
}

// mblock:item:init:start - Initialisierung startet (nach rex:ready)
function(event, item, wrapper) {
    // item: Block nach rex:ready Event
    // wrapper: Parent mblock_wrapper  
}

// mblock:item:widgets:ready - REX-Widgets sind initialisiert
function(event, item, wrapper) {
    // item: Block mit funktionalen REX_MEDIA/REX_LINK Widgets
    // wrapper: Parent Container
}

// ⭐ mblock:item:ready - HAUPTEVENT für Third-Party Integration
function(event, item, wrapper) {
    // item: Vollständig initialisierter Block, bereit für Texteditoren
    // wrapper: Parent mblock_wrapper  
}

// mblock:item:animated - Animation abgeschlossen
function(event, item, wrapper) {
    // item: Block nach Glow-Animation
    // wrapper: Parent Container
}

// mblock:item:complete - Block vollständig fertig
function(event, item, wrapper) {
    // item: Block komplett initialisiert, animiert, gescrollt
    // wrapper: Parent Container
}

// mblock:item:before:remove - Vor Block-Löschung
function(event, item, wrapper) {
    // item: Block der gelöscht wird (für Cleanup)
    // wrapper: Parent Container
}

// mblock:item:removed - Nach Block-Löschung
function(event, wrapper, prevItem) {
    // wrapper: Parent Container nach Löschung
    // prevItem: Vorheriger Block (für Focus-Management)
}

// mblock:item:before:copy - Vor Copy-Operation
function(event, item, wrapper) {
    // item: Block der kopiert wird
    // wrapper: Source Container
}

// mblock:item:copied - Nach Copy-Operation  
function(event, item, wrapper, clipboardData) {
    // item: Kopierter Block
    // wrapper: Source Container
    // clipboardData: Clipboard-Inhalt als String
}

// mblock:item:before:paste - Vor Paste-Operation
function(event, wrapper, afterItem) {
    // wrapper: Ziel-Container
    // afterItem: Block nach dem eingefügt wird (oder null für Ende)
}

// ⭐ mblock:item:pasted - WICHTIG für Texteditor nach Paste
function(event, pastedItem, wrapper, clipboardData) {
    // pastedItem: Eingefügter Block (neue IDs!)
    // wrapper: Ziel-Container
    // clipboardData: Original Clipboard-Daten
}
```

## 🚀 Quick Start Guide

### Für Texteditor-Integration

```javascript
// Minimaler Setup für CKEditor/TinyMCE
$(document).on('mblock:item:ready mblock:item:pasted', function(e, item) {
    item.find('.wysiwyg').each(function() {
        if (!this.editorReady) {
            // Dein Editor Setup
            CKEDITOR.replace(this.id);
            this.editorReady = true;
        }
    });
});

// Cleanup bei Löschung
$(document).on('mblock:item:before:remove', function(e, item) {
    item.find('.wysiwyg').each(function() {
        if (this.editorReady && CKEDITOR.instances[this.id]) {
            CKEDITOR.instances[this.id].destroy();
        }
    });
});
```

### Für Custom Widget Integration

```javascript
// Setup für Select2, Datepicker, etc.
$(document).on('mblock:item:ready', function(e, item) {
    // Select2
    item.find('.select2').select2();
    
    // Datepicker  
    item.find('.datepicker').datepicker();
    
    // Custom Widgets
    item.find('.my-widget').myWidget();
});
```

### Für Analytics & Tracking

```javascript
// Event-Tracking
$(document).on('mblock:item:created mblock:item:copied mblock:item:removed', 
function(e, item) {
    gtag('event', 'mblock_' + e.type.split(':').pop(), {
        'event_category': 'MBlock'
    });
});
```
```

## 🎯 Callbacks & Hooks

### Callback-Funktionen für PHP

```php
// settings.php - Globale MBlock-Callbacks
rex_mblock::addCallback('beforeAdd', function($mblockName, $data) {
    // Vor dem Hinzufügen eines Blocks
    return $data; // Modifizierte Daten zurückgeben
});

rex_mblock::addCallback('afterAdd', function($mblockName, $data) {
    // Nach dem Hinzufügen
    // Logging, Analytics, etc.
});

rex_mblock::addCallback('beforeSave', function($mblockName, $allData) {
    // Vor dem Speichern aller Blöcke
    return $allData; // Validation, Sanitizing
});

rex_mblock::addCallback('afterSave', function($mblockName, $allData) {
    // Nach dem Speichern
    // Cache-Invalidierung, Webhook-Calls
});
```

### JavaScript Event-Callbacks

```javascript
// Callback-Funktionen für Events registrieren
const MBlockCallbacks = {
    
    // Texteditor-Integration
    setupTexteditors: function(item) {
        item.find('.wysiwyg').each(function() {
            if (!this.editorInstance) {
                this.editorInstance = new WYSIWYG(this);
            }
        });
    },
    
    // Analytics & Tracking  
    trackBlockAction: function(action, item, data = {}) {
        gtag('event', 'mblock_action', {
            'event_category': 'MBlock',
            'event_label': action,
            'custom_data': data
        });
    },
    
    // Form Validation
    validateBlock: function(item) {
        const isValid = item.find('input[required]').every(function() {
            return $(this).val().trim() !== '';
        });
        
        item.toggleClass('has-errors', !isValid);
        return isValid;
    }
};

// Event-Bindings mit Callbacks
$(document).on('mblock:item:ready', function(e, item, wrapper) {
    MBlockCallbacks.setupTexteditors(item);
    MBlockCallbacks.validateBlock(item);
});

$(document).on('mblock:item:created', function(e, item, wrapper) {
    MBlockCallbacks.trackBlockAction('created', item);
});

$(document).on('mblock:item:pasted', function(e, item, wrapper, data) {
    MBlockCallbacks.trackBlockAction('pasted', item, data);
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
<button title="{{mblock_add_element}}">+</button>
<button title="{{mblock_delete_confirm}}">×</button>
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
    
    // ✅ Korrekte MBlock-Events (nur diese existieren!)
    $(document).on('mblock:change', function(e, element) {
        console.log('MBlock Sortierung:', e.type);
        
        // Custom logic nach Block-Sortierung
        updatePreview();
        saveFormState();
    });
    
    $(document).on('rex:ready', function(e, container) {
        if (container) {
            console.log('Neue Blöcke hinzugefügt');
            updatePreview();
            saveFormState();
        }
    });
    
    // ✅ Copy & Paste Detection über DOM-Events
    $(document).on('change input', '.mblock_wrapper textarea, .mblock_wrapper input', function() {
        const $wrapper = $(this).closest('.mblock_wrapper');
        if ($wrapper.length) {
            console.log('Copy/Paste möglicherweise erkannt');
            
            // Reinitialisiere Custom-Widgets nach Copy/Paste
            setTimeout(() => {
                $wrapper.find('.custom-widget').each(function() {
                    // Custom widget initialization
                    $(this).customWidget();
                });
            }, 100);
        }
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
// ❌ Alt (deprecated) - funktioniert nicht mehr
$(document).on('rex:change', function() {
    // Legacy handler - entfernt
});

// ❌ Falsche Dokumentation (diese Events existieren nicht!)
$(document).on('mblock:add mblock:remove mblock:sort', function() {
    // Diese Events gibt es NICHT im Code!
});

// ✅ Korrekt (v4.x) - nur diese Events sind verfügbar
$(document).on('rex:ready', function(e, container) {
    // Für neue Blöcke (Add-Button)
    if (container) {
        // Initialisierung
    }
});

$(document).on('mblock:change', function(e, element) {
    // Für Sortierung/Verschiebung
    if (element) {
        // Reinitialisierung
    }
});

// Copy/Paste über DOM-Events
$(document).on('change input', '.mblock_wrapper textarea, .mblock_wrapper input', function() {
    // Copy/Paste Detection
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
