REDAXO AddOn :: MBlock
======

Mit MBlock ist es möglich, innerhalb eines Moduls beliebig viele Datenblöcke zu erzeugen. Diese können dann einfach per But# Online/Offline Check
foreach ($data as $item) {
    if (MBlock::isOnline($item)) {
        // Item ist online
        echo rex_escape($item['title']);
    }
}r Drag & Drop sortiert werden. Die erweiterte Version bietet Copy & Paste Funktionalität und einen Offline/Online Toggle für einzelne Blöcke.

_English:_ MBlock lets you create an unlimited number of data blocks within a single module. These data blocks can be sorted per click or drag & drop. The enhanced version provides copy & paste functionality and an offline/online toggle for individual blocks.

> Please note: The examples are valid for MForm version 8 and higher. MBlock now requires the bloecks addon (^5.2.0) for modern drag & drop functionality.

### 🚨 Hinweis für markitup- und ckeditor-Nutzer 

Copy & Paste funktioniert leider nicht! 

Es sollte in den betreffenden Modulen deaktiviert werden. 

Beispiel 
```
echo MBlock::show(1, $form, [
    'min' => 1,              // Minimale Anzahl Items
    'max' => 10,             // Maximale Anzahl Items  
    'template' => 'modern',  // Template-Name
    'copy_paste' => false,    // Copy & Paste aktivieren
    'online_offline' => true // Online/Offline Toggle , hidden field muss angelegt sein. 
]);
```

## Features / Funktionen

### Grundfunktionen / Core Features

- [x] **Beliebig viele Datenblöcke** pro Modul erstellen
- [x] **Drag & Drop Sortierung** mit bloecks addon (^5.2.0)
- [x] **Minimale/Maximale Anzahl** von Blöcken definierbar
- [x] **MForm Integration** für professionelle Formulare
- [x] **Template System** mit Prioritätsladung
- [x] **Mehrsprachigkeit** (DE/EN)

### Erweiterte Funktionen / Advanced Features (MBlock 4.0)

- [x] **Online/Offline Toggle** - Blöcke aktivieren/deaktivieren ohne löschen
- [x] **Copy & Paste** - Komfortable Duplizierung von Inhalten
- [x] **Frontend API Methoden** - `filterByField()`, `sortByField()`, `groupByField()`
- [x] **Schema.org Support** - SEO-optimierte JSON-LD Generierung
- [x] **Erweiterte Datenabfrage** - Online/Offline/All Modi
- [x] **Template-Priorität** - Custom templates überschreiben defaults
- [x] **Media-ID Konflikt-Schutz** - Bessere Warnung bei ID-Überschneidungen

## Namespace Migration

**MBlock führt Namespaces ein!** Für neue Projekte wird die Verwendung des Namespace empfohlen:

```php
<?php
// Empfohlen: Neue Namespace-Syntax
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getDataArray("REX_VALUE[1]");
echo MBlock::show(1, $mform->show());
```

**Vollständig rückwärtskompatibel!** Bestehende Module funktionieren weiterhin ohne Änderungen:

```php
<?php
// Weiterhin unterstützt: Legacy-Syntax (für Bestandscode)
$items = MBlock::getDataArray("REX_VALUE[1]");
echo MBlock::show(1, $mform->show());
```

### Migration Guide
- **Neue Module**: Verwenden die `use FriendsOfRedaxo\MBlock\MBlock;` Syntax
- **Bestehende Module**: Funktionieren ohne Änderungen weiter
- **Deprecated-Warnung**: Alte Syntax wird in Version 5.0 entfernt

## Installation

MBlock erfordert:

```bash
# Via REDAXO Installer
# 1. Gehe zu System > Installer
# 2. Suche nach "mblock"
# 3. Installiere mblock zusammen mit bloecks
```

## API & Datenabfrage

### Zentrale getDataArray() Methode

**Mit Namespace (empfohlen für neue Projekte):**
```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

// Alle MBlock-Daten abrufen
$allItems = MBlock::getDataArray("REX_VALUE[1]");

// Nur Online-Blöcke (für Frontend) - EMPFOHLEN
$onlineItems = MBlock::getDataArray("REX_VALUE[1]", 'online');

// Nur Offline-Blöcke (für Backend-Previews)
$offlineItems = MBlock::getDataArray("REX_VALUE[1]", 'offline');

// Convenience-Methoden
$onlineItems = MBlock::getOnlineDataArray("REX_VALUE[1]");
$offlineItems = MBlock::getOfflineDataArray("REX_VALUE[1]");
```

**Legacy-Syntax (weiterhin unterstützt):**
```php
<?php
// Alle MBlock-Daten abrufen
$allItems = MBlock::getDataArray("REX_VALUE[1]");

// Nur Online-Blöcke (für Frontend) - EMPFOHLEN
$onlineItems = MBlock::getDataArray("REX_VALUE[1]", 'online');

// Nur Offline-Blöcke (für Backend-Previews)
$offlineItems = MBlock::getDataArray("REX_VALUE[1]", 'offline');

// Convenience-Methoden
$onlineItems = MBlock::getOnlineDataArray("REX_VALUE[1]");
$offlineItems = MBlock::getOfflineDataArray("REX_VALUE[1]");
``` 

### Frontend API - Datenverarbeitung

**Mit Namespace (empfohlen):**
```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Nach Feld filtern
$newsItems = MBlock::filterByField($items, 'category', 'news');
$activeItems = MBlock::filterByField($items, 'status', 'active');

// Nach Feld sortieren
$sortedByName = MBlock::sortByField($items, 'name', 'ASC');
$sortedByDate = MBlock::sortByField($items, 'date', 'DESC', 'date');
$sortedByPrice = MBlock::sortByField($items, 'price', 'DESC', 'numeric');

// Nach Feld gruppieren
$grouped = MBlock::groupByField($items, 'category');
foreach ($grouped as $category => $categoryItems) {
    echo "<h2>" . rex_escape($category) . "</h2>";
    foreach ($categoryItems as $item) {
        echo "<p>" . rex_escape($item['title']) . "</p>";
    }
}

// Anzahl begrenzen (Pagination)
$topItems = MBlock::limitItems($items, 5);
$nextItems = MBlock::limitItems($items, 5, 5);

// SEO Schema.org JSON-LD generieren
$schema = MBlock::generateSchema($items, 'Person', [
    'name' => 'name',
    'jobTitle' => 'position',
    'image' => 'photo',
    'email' => 'email'
]);
echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
```

### Legacy Array-Filterung (falls Array schon vorhanden)

```php
$data = rex_var::toArray("REX_VALUE[1]");

// Online/Offline Check (MBlock 4.0)
foreach ($data as $item) {
    if (MBlock::isOnline($item)) {
        // Item ist online
        echo rex_escape($item['title']);
    }
}

// Legacy-Methoden (deprecated, bitte getDataArray() verwenden)
$onlineItems = MBlock::getOnlineItems($data);
$offlineItems = MBlock::getOfflineItems($data);
```

## Templates & Theming

### Template-System mit Dropdown-Auswahl
MBlock bietet ein modernes Template-System mit grafischer Auswahl über ein Dropdown-Menü in den AddOn-Einstellungen. Das System kopiert automatisch die CSS-Dateien in den `assets/` Ordner und sorgt für optimale Performance.

### Template-Auswahl
Die Template-Auswahl erfolgt über die **MBlock-Einstellungen**:

1. **Gehe zu** `Addons > MBlock > Einstellungen`
2. **Wähle ein Template** aus der Dropdown-Liste
3. **Klicke "Speichern"** - Das CSS wird automatisch kopiert
4. **Das Template ist sofort aktiv**


### Dark Mode Support
**Die mitgelieferten Templates** unterstützen Dark Mode:

- **REDAXO Theme Detection** (`body.rex-theme-dark`)
- **Browser Preference** (`@media (prefers-color-scheme: dark)`)
- **Bootstrap 5 Dark Mode** (`[data-bs-theme="dark"]`)

### Custom Templates erstellen
Eigene Templates können im `data/` Ordner erstellt werden:

```bash
redaxo/data/addons/mblock/templates/
├── my_custom_theme/
│   ├── template.ini           # Template-Konfiguration
│   ├── mblock_wrapper.ini     # HTML-Wrapper für alle Items
│   ├── mblock_element.ini     # HTML-Template für einzelne Items
│   └── my_custom_theme.css    # Template-Styling (gleicher Name wie Ordner!)
```

**Wichtig:** Die CSS-Datei muss den **gleichen Namen wie der Template-Ordner** haben!


## Modulbeispiele / Module examples

MBlock enthält einige Modulbeispiele. Diese finden sich auf der MBlock-Seite im REDAXO-Backend. An dieser Stelle werden nur zwei Beispiele aufgelistet — mit Unterstützung durch [MForm](https://github.com/FriendsOfREDAXO/mform) und ohne —, um zu zeigen, wie MBlock funktioniert.

_English:_ MBlock contains several module examples. You’ll find them on the MBlock page within the REDAXO backend. At this point, we want to show two examples only — one with [MForm](https://github.com/FriendsOfREDAXO/mform) support and another one without — to demonstrate how MBlock works.

### Example 1: team members (requires [MForm](https://github.com/FriendsOfREDAXO/mform) addon)

__Input:__

```php
<?php
// Modernisiertes Beispiel mit MForm

use FriendsOfRedaxo\MForm;

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
    'max' => 4
));
```

__Output:__

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;
// Verbesserte Ausgabe
$items = MBlock::getOnlineDataArray("REX_VALUE[1]"); // Nur Online-Items

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
// echo '<pre>';
// dump(MBlock::getDataArray("REX_VALUE[1]")); // Alle Items inkl. Offline
// echo '</pre>';
```

### Example 2: team members (without [MForm](https://github.com/FriendsOfREDAXO/mform))

__Input:__

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;
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

// MBlock mit Features
echo MBlock::show($id, $form, array(
    'online_offline' => true,
    'copy_paste' => true
));
```

__Output:__

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

## Development & Build

### Modulare JavaScript-Architektur

MBlock verwendet eine **modulare JavaScript-Architektur**, die den Code in drei logische Module aufteilt:

- **`mblock-core.js`** - Base utilities, Validierung, Übersetzungen (384 Zeilen)
- **`mblock-management.js`** - DOM-Manipulation, Sortable-Handling (1008 Zeilen)  
- **`mblock-features.js`** - Copy/Paste, Online/Offline Toggle, REDAXO Widgets (815 Zeilen)

Dies verbessert die **Wartbarkeit**, reduziert **Code-Redundanz** und ermöglicht besseres **Debugging**.

### Build-System

Das Build-System kombiniert automatisch die modularen Dateien und erstellt optimierte Versionen:

#### Voraussetzungen

- **Node.js** (Version 14 oder höher)  
- **npm** (wird normalerweise mit Node.js installiert)

#### Build-Prozess ausführen

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

#### Build-Ausgabe
```
� MBlock Build Process gestartet
═══════════════════════════════════
✅ Node.js gefunden: v23.9.0
✅ Dependencies bereits vorhanden
� Quelldatei gefunden: ../assets/mblock.js
🔗 Erstelle kombinierte Datei aus modularen Komponenten...
✅ Kombinierte Datei erstellt: mblock-combined.js
🔗 Aktualisiere mblock.js für Entwicklungsmodus...
✅ mblock.js aktualisiert
⚙️  Starte Minification der kombinierten Datei...

� Minification Statistiken:
────────────────────────────────────────
📏 Originalgröße:     105.23 KB
🗜️  Minified Größe:   36.37 KB  
💾 Ersparnis:         68.85 KB (65.43%)
⏱️  Verarbeitungszeit: 238ms
────────────────────────────────────────

✅ MBlock JavaScript erfolgreich minified!
🎉 MBlock Build Process abgeschlossen!
```

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

#### Code-Verbesserungen

Die modulare Architektur bringt folgende **Verbesserungen**:

✅ **Code-Reduktion**: ~200 Zeilen Redundanz eliminiert  
✅ **Reusable Functions**: `MBlockUtils`, `MBlockClipboard`, `MBlockOnlineToggle`  
✅ **Unified Event Handling**: Zentralisierte Event-Konfiguration  
✅ **Better Error Handling**: Konsistente Fehlerbehandlung  
✅ **Memory Management**: Automatisches Event-Cleanup  
✅ **Performance**: Cached Selectors und optimierte Algorithmen  

#### Enhanced REX_LINK/REX_MEDIA Support

Das neue System bietet **verbesserte REDAXO Widget-Unterstützung**:

- ✅ **REX_LINK Copy/Paste** - Artikel-IDs und Namen werden korrekt kopiert
- ✅ **REX_MEDIA Copy/Paste** - Media-Dateien mit Metadaten  
- ✅ **Widget-Reinitialization** - Onclick-Handler werden automatisch aktualisiert
- ✅ **Auto Name Fetching** - Artikel-Namen werden automatisch per AJAX geholt

#### Build-System Features

- **🔗 Smart Combining** - Intelligente Kombination der modularen Dateien
- **⚙️  Advanced Minification** - Terser mit optimierten Settings (2 Compression-Passes)
- **🗺️  Source Maps** - Für einfaches Debugging der minifizierten Datei
- **📊 Performance Stats** - Detaillierte Größen- und Kompressions-Statistiken
- **🔧 Error Handling** - Robuste Fehlerbehandlung und Validierung
- **♻️  Auto-Update** - Synchronisation zwischen Development- und Production-Dateien

#### Troubleshooting

Falls Probleme auftreten:

1. **Node.js Version prüfen**:
   ```bash
   node --version  # sollte >= 14.0.0 sein
   ```

2. **Dependencies neu installieren**:
   ```bash
   cd redaxo/src/addons/mblock/build
   rm -rf node_modules
   npm install
   ```

3. **Modulare Dateien prüfen**:
   ```bash
   ls -la ../assets/mblock-*.js
   # Sollte alle 3 modularen Dateien anzeigen
   ```

4. **Manuelle Terser Installation**:
   ```bash
   npm install terser
   ```

#### Development Workflow

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

## Author

**Friends Of REDAXO**

* [REDAXO](http://www.redaxo.org)
* [FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)


## Credits

**Project Leads**

* [Joachim Dörr](https://github.com/joachimdoerr)  
* [Thomas Skerbis](https://github.com/skerbis)  





