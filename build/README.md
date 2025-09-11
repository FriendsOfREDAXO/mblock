# MBlock Build System - Simplified Minification

Automatisierte Minification für MBlock JavaScript mit optimaler Performance

## 🚀 Quick Start

```bash
cd build/
./build.sh
```

## 🏗️ Vereinfachte Build-Architektur

Das Build-System erstellt aus der bestehenden `mblock.js` eine optimierte minifizierte Version:

### Build Structure
```
../assets/
├── mblock.js         # ~142 KB - Source (Development & Editing)
├── mblock.min.js     # ~45 KB - Production (minifiziert) ✨
├── mblock.min.js.map # ~50 KB - Source Map für Debugging
└── mblock.css        # 🎨 Stylesheet
```

### Performance Stats
- **Input:** `mblock.js` (~142 KB)
- **Output:** `mblock.min.js` (~45 KB)  
- **Ersparnis:** ~68% kleinere Dateigröße
- **Build Zeit:** ~200-300ms

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

## ⚙️ Build-Prozess

### 1. Source Validation
```bash
📖 Quelldatei gefunden: ../assets/mblock.js
📏 Quelldatei Größe: 141 KB
```

### 2. Minification
```bash
⚙️ Starte Minification von mblock.js...
🗜️ Minified Größe: 45.21 KB
💾 Ersparnis: 96.67 KB (68.13%)
⏱️ Verarbeitungszeit: 231ms
```

### 3. Output
```bash
💾 Minified Datei erstellt: mblock.min.js
🗺️ Source Map erstellt: mblock.min.js.map
```

## 🎯 Asset Loading

Die `boot.php` lädt automatisch die optimierte Version:

```php
// Automatisches Laden der minimizierten Version
rex_view::addJsFile($this->getAssetsUrl('mblock.min.js'));
rex_view::addCssFile($this->getAssetsUrl('mblock.css'));
```

**Vorteile:**
- ✅ **68% kleinere Dateigröße** für bessere Performance
- ✅ **Source Maps** für einfaches Debugging  
- ✅ **Automatische Compression** von Console-Ausgaben
- ✅ **Preserved Function Names** für externe API-Calls

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
'mblock_smooth_scroll_to_element'
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
├── mblock.js            # 🛠️ Source (bearbeiten hier)
├── mblock.min.js        # 🚀 Production Version ✨ (automatisch generiert)
├── mblock.min.js.map    # 🗺️ Source Map für Debugging
└── mblock.css           # 🎨 Stylesheet
```

## 🎯 NPM Scripts

```bash
npm run build     # Minification ausführen
npm run minify    # Alias für build
npm run clean     # Minified Dateien löschen
```

## 🛠️ Development Workflow

### Für MBlock-Entwicklung:

1. **Bearbeite Source-Datei:**
   ```bash
   assets/mblock.js        # Hauptdatei bearbeiten
   ```

2. **Build nach Änderungen:**
   ```bash
   cd build && ./build.sh
   ```

3. **Testing:**
   - Production nutzt automatisch `mblock.min.js` (optimiert)
   - Source Maps ermöglichen Debugging der Original-Zeilen

## 🚨 Wichtige Hinweise

1. **⚠️ Bearbeite NIE `mblock.min.js` direkt!**
   - Ändere nur `mblock.js`
   - Build-System überschreibt minifizierte Datei

2. **🔄 Nach jeder Änderung** an `mblock.js` muss Build ausgeführt werden

3. **📍 Source Maps** helfen beim Debugging der Production-Version

4. **🔗 Preserved Functions** können weiterhin extern aufgerufen werden

## 🚨 Troubleshooting

### Build-Fehler beheben:
```bash
# Node.js Version prüfen  
node --version  # sollte >= 14.0.0 sein

# Dependencies neu installieren
rm -rf node_modules
npm install

# Source-Datei validieren
ls -la ../assets/mblock.js  
# Sollte mblock.js zeigen

# Manuelle Terser Installation
npm install terser
```

### Syntax-Fehler in Source-Datei:
```bash
# JavaScript-Syntax prüfen
node -c ../assets/mblock.js
```

### Asset-Loading-Probleme:
```bash
# Prüfe ob mblock.min.js existiert
ls -la ../assets/mblock.min.js

# Browser-Konsole für Debugging
# Source Maps zeigen Original-Zeilen bei Fehlern
```

---

**💡 Tip:** Die Source Maps ermöglichen es, auch in der minimizierten Production-Version die Original-Zeilennummern zu sehen!
