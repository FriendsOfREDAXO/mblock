# MBlock Build System - Modulare Architektur

Automatisierte Kombination und Minification für MBlock JavaScript Assets

## 🚀 Quick Start

```bash
cd build/
./build.sh
```

## 🏗️ Modulare Architektur (MBlock 5.0)

Das Build-System kombiniert **3 modulare JavaScript-Dateien** zu einer optimierten Version:

### Module Structure
```
../assets/
├── mblock-core.js        # 384 Zeilen - Base Utilities & Validation
├── mblock-management.js  # 1008 Zeilen - DOM Manipulation & Sortable  
├── mblock-features.js    # 815 Zeilen - Copy/Paste, Online/Offline Toggle
│
│ Build Ergebnis:
├── mblock-combined.js    # ~105 KB - Kombinierte Datei (Zwischenresultat)
├── mblock.js             # ~105 KB - Development Version (readable)
├── mblock.min.js         # ~36 KB - Production Version (minifiziert)
└── mblock.min.js.map     # ~40 KB - Source Map für Debugging
```

### Module Dependencies
```
mblock-core.js
    ↓ (depends on)
mblock-management.js  
    ↓ (depends on)
mblock-features.js
```

## 📋 Voraussetzungen

- **Node.js** >= 14.0.0
- **npm** (kommt mit Node.js)

## 🛠️ Installation

1. **Dependencies installieren:**
   ```bash
   cd build/
   npm install
   ```

2. **Build ausführen:**
   ```bash
   # Mit Shell-Script (empfohlen)
   ./build.sh
   
   # Oder direkt mit Node.js
   node minify.js
   
   # Oder mit npm
   npm run build
   ```

## 📊 Build-Ergebnis

### Performance Stats
- **Input:** 3 modulare Dateien (~105 KB kombiniert)
- **Output:** `../assets/mblock.min.js` (~36 KB)
- **Ersparnis:** ~65% kleinere Dateigröße
- **Source Map:** `../assets/mblock.min.js.map` (~40 KB)
- **Build Zeit:** ~200-300ms

### Code Improvements
✅ **~200 Zeilen Redundanz eliminiert**  
✅ **Reusable Functions** (`MBlockUtils`, `MBlockClipboard`, etc.)  
✅ **Unified Event Handling** mit Namespace-Management  
✅ **Better Error Handling** mit konsistenten Patterns  
✅ **Enhanced REX_LINK/REX_MEDIA Support** für Copy/Paste  

## ⚙️ Build-Prozess

### 1. Module Combination
```bash
🔗 Erstelle kombinierte Datei aus modularen Komponenten...
   mblock-core.js (384 lines)
   + mblock-management.js (1008 lines)  
   + mblock-features.js (815 lines)
   = mblock-combined.js (105.23 KB)
```

### 2. Development Sync
```bash
🔗 Aktualisiere mblock.js für Entwicklungsmodus...
   mblock-combined.js → mblock.js
```

### 3. Production Minification
```bash
⚙️ Starte Minification der kombinierten Datei...
   mblock-combined.js → mblock.min.js (36.37 KB, 65.43% Ersparnis)
   + Source Map erstellt (mblock.min.js.map)
```

## 🎯 Asset Loading Modi

### boot.php Konfiguration
```php
// Asset-Modus in boot.php
$assetMode = 'auto'; // Optionen: 'auto', 'modular', 'combined', 'prod'
```

**Modi:**
- **`'auto'`** (Standard) - Automatische Erkennung
  - **Development:** `mblock.js` (kombiniert)
  - **Production:** `mblock.min.js` (minifiziert)
  
- **`'modular'`** - Lädt 3 separate Module (erweiterte Debugging)
  - `mblock-core.js` → `mblock-management.js` → `mblock-features.js`
  
- **`'combined'`** - Immer `mblock.js` (kombiniert, unminifiziert)
- **`'prod'`** - Immer `mblock.min.js` (minifiziert)

### Asset Loading Logic
```php
if (isset($useModular) && $useModular) {
    // 📦 Load modular files for advanced debugging
    rex_view::addJsFile($this->getAssetsUrl('mblock-core.js'));
    rex_view::addJsFile($this->getAssetsUrl('mblock-management.js'));
    rex_view::addJsFile($this->getAssetsUrl('mblock-features.js'));
} else {
    // 📦 Load combined/minified file (standard approach)
    $jsFile = $useMinified ? 'mblock.min.js' : 'mblock.js';
    rex_view::addJsFile($this->getAssetsUrl($jsFile));
}
```

## 🔧 Preserved Function Names

Diese **kritischen Funktionen** werden nicht umbenannt:

```javascript
// Core Functions
'mblock_init',
'mblock_init_sort', 
'mblock_sort',
'mblock_add',

// Clipboard & Toggle Functions  
'MBlockClipboard',
'MBlockOnlineToggle',

// Utility Functions
'mblock_smooth_scroll_to_element',
'mblock_reinitialize_redaxo_widgets',
'mblock_fetch_article_name'
```

## 📁 Datei-Struktur

```
build/
├── build.sh           # 🔧 Shell-Script für automatisierten Build
├── minify.js          # ⚙️ Node.js Minification-Script  
├── package.json       # 📦 NPM Dependencies (Terser)
├── node_modules/      # 🗂️ Installierte Packages
└── README.md          # 📖 Diese Datei

../assets/
├── mblock-core.js        # 🧩 Modul 1: Base Utilities
├── mblock-management.js  # 🧩 Modul 2: DOM Management  
├── mblock-features.js    # 🧩 Modul 3: Advanced Features
├── mblock-combined.js    # 🔗 Kombinierte Datei (intermediate)
├── mblock.js            # 🛠️ Development Version
├── mblock.min.js        # 🚀 Production Version ✨
├── mblock.min.js.map    # 🗺️ Source Map für Debugging
└── mblock.css           # 🎨 Stylesheet
```

## 🎯 NPM Scripts

```bash
npm run build     # Modulare Kombination + Minification
npm run minify    # Alias für build
npm run clean     # Minified Dateien löschen
```

## � Development Workflow

### Für MBlock-Entwicklung:

1. **Bearbeite modulare Dateien:**
   ```bash
   assets/mblock-core.js        # Base utilities
   assets/mblock-management.js  # DOM manipulation
   assets/mblock-features.js    # Copy/Paste & widgets
   ```

2. **Build nach Änderungen:**
   ```bash
   cd build && ./build.sh
   ```

3. **Testing:**
   - Debug-Modus: Nutzt automatisch `mblock.js` (readable)
   - Production: Nutzt `mblock.min.js` (optimiert)

### Modulare Entwicklung:

Für **erweiterte Debugging-Möglichkeiten** setze in `boot.php`:
```php
$assetMode = 'modular'; // Lädt 3 separate JavaScript-Dateien
```

## 🚨 Wichtige Hinweise

1. **⚠️ Bearbeite NIE `mblock.js` oder `mblock.min.js` direkt!**
   - Ändere nur die modularen Dateien (`mblock-*.js`)
   - Build-System überschreibt kombinierte Dateien

2. **🔄 Nach jeder Änderung** an modularen Dateien muss Build ausgeführt werden

3. **📍 Source Maps** helfen beim Debugging der Production-Version

4. **🔗 Preserved Functions** können weiterhin extern aufgerufen werden

5. **🧩 Module Dependencies** werden automatisch in korrekter Reihenfolge geladen

## � Troubleshooting

### Build-Fehler beheben:
```bash
# Node.js Version prüfen  
node --version  # sollte >= 14.0.0 sein

# Dependencies neu installieren
rm -rf node_modules
npm install

# Modulare Dateien validieren
ls -la ../assets/mblock-*.js  
# Sollte alle 3 modularen Dateien zeigen

# Manuelle Terser Installation
npm install terser
```

### Syntax-Fehler in modularen Dateien:
```bash
# JavaScript-Syntax prüfen
node -c ../assets/mblock-core.js
node -c ../assets/mblock-management.js  
node -c ../assets/mblock-features.js
```

### Asset-Loading-Probleme:
```bash
# boot.php Debug-Info aktivieren
rex::isDebugMode() // sollte true sein für Development

# Asset-Modus prüfen in Browser-Konsole
console.log(rex.mblock_asset_mode);
```

---

**💡 Tip:** Nutze `$assetMode = 'modular'` für line-genaues Debugging der JavaScript-Module!
