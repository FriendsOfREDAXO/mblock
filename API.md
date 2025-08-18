# MBlock API-Dokumentation

## Übersicht
MBlock ermöglicht die Erstellung wiederholbarer Formularblöcke innerhalb von REDAXO-Modulen. Diese API-Dokumentation beschreibt alle verfügbaren Klassen, Methoden und Verwendungsmöglichkeiten.

---

## Hauptklassen

### MBlock
Die zentrale Klasse für MBlock-Funktionalität.

#### Statische Methoden

##### `MBlock::show($id, $form, $settings = [], $theme = null)`
Erstellt und zeigt einen MBlock-Container an.

**Parameter:**
- `$id` (int|string): Block-ID oder Feldname
- `$form` (string|MForm|rex_yform): Formular-HTML oder Formular-Objekt
- `$settings` (array): Konfigurationsoptionen
- `$theme` (string): Template-Theme (optional)

**Settings-Optionen:**
```php
$settings = [
    'min' => 1,           // Mindestanzahl Blöcke
    'max' => 10,          // Maximale Anzahl Blöcke
    'sortable' => true,   // Sortierbar per Drag & Drop
    'delete_confirm' => 'Block löschen?', // Löschbestätigung
];
```

**Beispiel:**
```php
$mblock = MBlock::show(1, $mform->show(), ['min' => 2, 'max' => 5]);
echo $mblock;
```

##### `MBlock::getDataArray($value, $filter = null)`
**Neu in v4.0** - Zentrale Methode zum Abrufen von MBlock-Daten.

**Parameter:**
- `$value` (string): REX_VALUE oder JSON-String
- `$filter` (string): 'online', 'offline' oder null (alle)

**Rückgabe:** Array mit MBlock-Items

**Beispiele:**
```php
// Alle Items
$allItems = MBlock::getDataArray("REX_VALUE[1]");

// Nur Online-Items (für Frontend)
$onlineItems = MBlock::getDataArray("REX_VALUE[1]", 'online');

// Nur Offline-Items (für Backend-Previews)  
$offlineItems = MBlock::getDataArray("REX_VALUE[1]", 'offline');
```

##### `MBlock::getOnlineDataArray($value)`
Convenience-Methode für Online-Items.

**Beispiel:**
```php
$onlineItems = MBlock::getOnlineDataArray("REX_VALUE[1]");
```

##### `MBlock::getOfflineDataArray($value)`
Convenience-Methode für Offline-Items.

**Beispiel:**
```php
$offlineItems = MBlock::getOfflineDataArray("REX_VALUE[1]");
```

##### `MBlock::getOnlineItems($data)` (Legacy)
Filtert Online-Items aus vorhandenem Array.

**Beispiel:**
```php
$data = rex_var::toArray("REX_VALUE[1]");
$onlineItems = MBlock::getOnlineItems($data);
```

##### `MBlock::getOfflineItems($data)` (Legacy)
Filtert Offline-Items aus vorhandenem Array.

---

## Datenstruktur

### MBlock-Item Aufbau
Jedes MBlock-Item ist ein assoziatives Array:

```php
$item = [
    // Formularfelder (entsprechend der Template-Definition)
    'title' => 'Beispieltitel',
    'content' => 'Beispielinhalt',
    
    // REX_MEDIA-Felder (automatisch konvertiert)
    'REX_MEDIA_1' => 'beispielbild.jpg',
    
    // REX_LINK-Felder
    'REX_LINK_1' => '5',
    
    // Online/Offline-Status (wenn aktiviert)
    'mblock_offline' => '0', // '0' = online, '1' = offline
    
    // Weitere benutzerdefinierte Felder...
];
```

---

## Template-System

### Template-Verzeichnisse
**Template-Priorität (in dieser Reihenfolge):**
1. `redaxo/data/addons/mblock/templates/custom_theme/` (Custom Templates - höchste Priorität)
2. `redaxo/src/addons/mblock/templates/default_theme/` (Addon Default Templates)

**Empfehlung:**
- Eigene Templates immer im `data/` Ordner ablegen
- Default Templates aus dem Addon nicht direkt bearbeiten
- Custom Templates überschreiben automatisch die Default Templates

### Template-Provider API

#### `MBlockTemplateFileProvider::loadTemplate($templateType, $subPath, $theme, $stop)`
Lädt Template-Dateien mit automatischer Prioritätsverwaltung.

**Parameter:**
- `$templateType` (string): 'wrapper' oder 'element'
- `$subPath` (string): Zusätzlicher Unterordner-Pfad
- `$theme` (string): Theme-Name (default: aus Konfiguration)
- `$stop` (bool): Verhindert Rekursion bei Default-Theme

**Beispiel:**
```php
// Lädt mblock_wrapper.ini aus dem konfigurierten Theme
$wrapperTemplate = MBlockTemplateFileProvider::loadTemplate('wrapper');

// Lädt mblock_element.ini aus spezifischem Theme
$elementTemplate = MBlockTemplateFileProvider::loadTemplate('element', '', 'my_theme');
```

### Template-Platzhalter und Replacer

#### Verfügbare Platzhalter
Templates können verschiedene Platzhalter verwenden:

**Wrapper Template (mblock_wrapper.ini):**
```html
<div class="mblock_wrapper"<mblock:settings/>>
    <!-- Copy & Paste Toolbar -->
    <div class="mblock-copy-paste-toolbar">
        <button type="button" class="btn btn-default mblock-clear-clipboard" 
                title="{{mblock_clear_clipboard}}">
            <i class="rex-icon rex-icon-delete"></i> {{mblock_clear_clipboard}}
        </button>
    </div>
    
    <!-- MBlock Items Container -->
    <mblock:output/>
</div>
```

**Element Template (mblock_element.ini):**
```html
<div class="sortitem" data-mblock_index="{{count}}">
    <!-- Item Toolbar -->
    <div class="mblock-item-toolbar">
        <button class="btn btn-xs btn-default mblock-copy-btn" 
                title="{{mblock_copy_element}}">
            <i class="rex-icon rex-icon-copy"></i>
        </button>
        <button class="btn btn-xs btn-success mblock-online-toggle btn-online" 
                title="Set offline">
            <i class="rex-icon rex-icon-online"></i>
        </button>
    </div>
    
    <!-- Form Content -->
    <mblock:item/>
    
    <!-- Control Buttons -->
    <div class="mblock-controls">
        <button class="btn btn-xs btn-primary addme" title="Add">
            <i class="rex-icon rex-icon-add"></i>
        </button>
        <button class="btn btn-xs btn-danger removeme" title="Remove">
            <i class="rex-icon rex-icon-delete"></i>
        </button>
    </div>
</div>
```

#### Template-Replacer
- `<mblock:output/>`: Container für alle MBlock-Items
- `<mblock:item/>`: Formular-Inhalt des einzelnen Items  
- `<mblock:settings/>`: MBlock-Einstellungen als HTML-Attribute
- `{{count}}`: Aktuelle Item-Nummer
- `{{mblock_*}}`: Übersetzungskeys aus Sprachdateien

### Template-Migration

#### Von v3.x Templates
Bestehende Templates aus dem `data/templates/` Ordner funktionieren weiterhin:

```php
// Alt (v3.x und v4.x kompatibel)
redaxo/data/addons/mblock/templates/my_theme/

// Neue Struktur (v4.x Default Templates)
redaxo/src/addons/mblock/templates/default_theme/
```

#### Template-Debugging
```php
// Template-Pfade anzeigen
$paths = MBlockTemplateFileProvider::getTemplatePaths();
dump($paths);

// Aktuell geladene Templates prüfen
$wrapperContent = MBlockTemplateFileProvider::loadTemplate('wrapper');
$elementContent = MBlockTemplateFileProvider::loadTemplate('element');
```

### Template-Dateien

#### `mblock_wrapper.ini`
Container für alle MBlock-Items:
```html
<div class="mblock_wrapper"<mblock:settings/>>
    <div class="mblock-copy-paste-toolbar">
        <div class="btn-group btn-group-xs">
            <button type="button" class="btn btn-default mblock-clear-clipboard" 
                    title="{{mblock_clear_clipboard}}">
                <i class="rex-icon rex-icon-delete"></i> {{mblock_clear_clipboard}}
            </button>
        </div>
    </div>
    <mblock:output/>
</div>
```

#### `mblock_element.ini`
Template für einzelne MBlock-Items:
```html
<div class="sortitem">
    <div class="mblock-item-toolbar">
        <button class="btn btn-xs btn-default mblock-copy-btn" title="{{mblock_copy_element}}">
            <i class="rex-icon rex-icon-copy"></i>
        </button>
        <button class="btn btn-xs btn-default mblock-paste-btn" title="{{mblock_paste_element}}">
            <i class="rex-icon rex-icon-paste"></i>
        </button>
        <button class="btn btn-xs btn-success mblock-online-toggle btn-online" 
                title="Set offline">
            <i class="rex-icon rex-icon-online"></i>
        </button>
    </div>
    
    <mblock:item/>
    
    <div class="mblock-controls">
        <button class="btn btn-xs btn-primary addme" title="Add">
            <i class="rex-icon rex-icon-add"></i>
        </button>
        <button class="btn btn-xs btn-danger removeme" title="Remove">
            <i class="rex-icon rex-icon-delete"></i>
        </button>
    </div>
</div>
```

---

## Erweiterte Features

### Copy & Paste Funktionalität
MBlock v4.0 bietet Copy & Paste zwischen verschiedenen MBlock-Instanzen:

**Features:**
- Session/Local Storage Persistenz
- Modultyp-Validierung
- Komplexe Formularelemente (CKEditor, Media, Links)
- Cross-Tab Unterstützung

**JavaScript API:**
```javascript
// Kopieren
MBlockClipboard.copy(element, item);

// Einfügen
MBlockClipboard.paste(element, afterItem);

// Zwischenablage leeren
MBlockClipboard.clear();

// Debug-Informationen
MBlockClipboard.debug();
```

### Online/Offline Toggle
Items können als Online/Offline markiert werden:

**Template-Integration:**
```php
// Hidden Input für Offline-Status
echo '<input type="hidden" name="REX_INPUT_VALUE[1][0][mblock_offline]" value="0" />';
```

**Datenabfrage:**
```php
// Nur Online-Items für Frontend
$onlineItems = MBlock::getOnlineDataArray("REX_VALUE[1]");
foreach($onlineItems as $item) {
    // Nur veröffentlichte Items anzeigen
}
```

---

## Helper-Klassen

### MBlockValueHandler
Verarbeitet REX_VALUE und POST-Daten.

### MBlockTemplateFileProvider  
Lädt Template-Dateien aus verschiedenen Quellen.

### MBlockThemeHelper
Verwaltet Theme-Einstellungen und -Pfade.

### MBlockSessionHelper
Session-Management für MBlock.

### MBlockJsonHelper
JSON-Verarbeitung und -Validierung.

---

## Replacer-System

### Verfügbare Replacer
- `MBlockValueReplacer`: Ersetzt Formular-Werte
- `MBlockCountReplacer`: Fügt Zähler hinzu  
- `MBlockCheckboxReplacer`: Verarbeitet Checkboxen
- `MBlockSystemButtonReplacer`: System-Buttons (Add/Remove)
- `MBlockBootstrapReplacer`: Bootstrap-spezifische Replacements

### Eigene Replacer erstellen
```php
class CustomMBlockReplacer extends MBlockReplacer
{
    public static function replace($output, $item, $count)
    {
        // Custom replacement logic
        return str_replace('{{custom_placeholder}}', $customValue, $output);
    }
}
```

---

## Integration mit anderen AddOns

### MForm Integration
```php
use FriendsOfRedaxo\MForm;

$mform = MForm::factory()
    ->addTextField("1.0.title", ['label' => 'Titel'])
    ->addTextAreaField("1.0.content", ['label' => 'Inhalt']);

echo MBlock::show(1, $mform->show(), ['max' => 5]);
```

### YForm Integration  
```php
$yform = new rex_yform();
$yform->setObjectparams('form_name', 'mblock_form');
// ... YForm configuration

echo MBlock::show('yform::form_name', $yform);
```

---

## Konfiguration

### Addon-Einstellungen
```php
// Theme setzen
rex_config::set('mblock', 'theme', 'custom_theme');

// Smooth Scroll aktivieren
rex_config::set('mblock', 'smooth_scroll', '1');

// Copy & Paste aktivieren
rex_config::set('mblock', 'copy_paste', '1');

// Offline Toggle aktivieren  
rex_config::set('mblock', 'offline_toggle', '1');
```

### Frontend-Ausgabe optimieren
```php
// Performance: Nur Online-Items laden
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Caching für große Datenmengen
$cacheKey = 'mblock_' . md5("REX_VALUE[1]");
$items = rex_cache::get($cacheKey, function() {
    return MBlock::getOnlineDataArray("REX_VALUE[1]");
});
```

---

## Migration & Kompatibilität

### Von v3.x auf v4.x
```php
// Alt (v3.x)
$items = rex_var::toArray("REX_VALUE[1]");

// Neu (v4.x) - empfohlen
$items = MBlock::getDataArray("REX_VALUE[1]");

// Legacy-Support weiterhin verfügbar
$data = rex_var::toArray("REX_VALUE[1]");
$onlineItems = MBlock::getOnlineItems($data);
```

---

## Best Practices

### Performance
- Verwende `getOnlineDataArray()` für Frontend-Ausgabe
- Cache große MBlock-Datenmengen
- Limitiere max. Anzahl Items bei großen Datensets

### Sicherheit
- Validiere Benutzereingaben in MBlock-Items
- Escape HTML-Ausgabe: `rex_escape($item['title'])`
- Prüfe Berechtigungen vor dem Anzeigen sensibler Daten

### Wartbarkeit
- Verwende aussagekräftige Feldnamen: `title`, `content`, nicht `field1`, `field2`
- Dokumentiere Custom-Templates und -Replacer
- Nutze die neuen v4.x API-Methoden für Zukunftssicherheit

---

## Debugging

### Debug-Ausgabe
```php
// Alle Daten anzeigen
dump(MBlock::getDataArray("REX_VALUE[1]"));

// Clipboard-Status (JavaScript Console)
MBlockClipboard.debug();

// Template-Pfade prüfen
dump(MBlockThemeHelper::getTemplatePaths());
```

### Häufige Probleme
1. **Leere Items**: Prüfe Template-Syntax und Replacer
2. **Sortierung funktioniert nicht**: Bloecks-Addon aktiviert?
3. **Copy & Paste**: Modultyp-Kompatibilität prüfen
4. **Offline-Toggle**: Hidden Input im Template vorhanden?

---

## Weitere Ressourcen
- [README](index.php?page=mblock/readme) - Installation und Grundlagen
- [Demo & Beispiele](index.php?page=mblock/overview) - Praktische Beispiele
- [GitHub Repository](https://github.com/FriendsOfREDAXO/mblock) - Source Code
