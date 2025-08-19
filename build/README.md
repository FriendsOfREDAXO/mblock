# MBlock Build System

Automatisierte Minification für MBlock JavaScript Assets

## 🚀 Quick Start

```bash
cd build/
./build.sh
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

- **Input:** `../assets/mblock.js` (~84 KB)
- **Output:** `../assets/mblock.min.js` (~30 KB)
- **Ersparnis:** ~64% kleinere Dateigröße
- **Source Map:** `../assets/mblock.min.js.map`

## ⚙️ Konfiguration

### Preserved Function Names
Diese Funktionen werden **nicht** umbenannt:
- `mblock_init`
- `mblock_init_sort` 
- `mblock_sort`
- `mblock_add`
- `MBlockClipboard`
- `MBlockOnlineToggle`
- `mblock_smooth_scroll_to_element`

### Entfernte Debug-Codes
- `console.log()` Statements werden entfernt
- `console.error()` und `console.warn()` bleiben erhalten
- Alle Kommentare werden entfernt
- Debugger-Statements werden entfernt

## 📁 Datei-Struktur

```
build/
├── build.sh           # Shell-Script für automatisierten Build
├── minify.js          # Node.js Minification-Script  
├── package.json       # NPM Dependencies
├── node_modules/      # Installierte Packages
└── README.md          # Diese Datei

../assets/
├── mblock.js          # Original Source (Development)
├── mblock.min.js      # Minified Version (Production) ✨
├── mblock.min.js.map  # Source Map für Debugging
└── mblock.css         # Stylesheet
```

## 🎯 NPM Scripts

```bash
npm run build     # Minification ausführen
npm run minify    # Alias für build
npm run clean     # Minified Dateien löschen
```

## 🔧 Development vs Production

### Asset-Modus in boot.php

Die `boot.php` unterstützt intelligentes Asset-Management:

```php
// In boot.php - Asset Management Konfiguration
$assetMode = 'auto'; // Optionen: 'auto', 'dev', 'prod'
```

**Modi:**
- **`'auto'`** (empfohlen) - Automatische Erkennung
  - **Production:** `mblock.min.js` wenn Debug-Modus deaktiviert
  - **Development:** `mblock.js` wenn Debug-Modus aktiv
- **`'dev'`** - Immer `mblock.js` (Development/Debugging)
- **`'prod'`** - Immer `mblock.min.js` (Production/Performance)

### Lokale Entwicklung
1. Setze `$assetMode = 'dev';` in boot.php
2. Verwende `mblock.js` für besseres Debugging
3. Console-Logs und Source-Code sind verfügbar

### Produktion
1. Setze `$assetMode = 'prod';` oder lasse `'auto'` 
2. Verwende `mblock.min.js` für bessere Performance
3. Führe nach Änderungen `./build.sh` aus

### Auto-Detection (Empfohlen)
Lasse `$assetMode = 'auto';` für automatische Erkennung:
- **REDAXO Debug-Modus AN:** Verwendet `mblock.js`
- **REDAXO Debug-Modus AUS:** Verwendet `mblock.min.js`

## 🚨 Wichtige Hinweise

1. **Nach jeder Änderung** an `mblock.js` muss das Build-Script ausgeführt werden
2. **boot.php** ist bereits konfiguriert für `mblock.min.js`
3. **Source Maps** helfen beim Debugging der Production-Version
4. **Preserved Functions** können weiterhin extern aufgerufen werden

## 🔄 Workflow

1. Entwickle in `mblock.js`
2. Führe `./build.sh` aus
3. Teste `mblock.min.js` in REDAXO
4. Deploye in die Produktion

---

**💡 Tipp:** Nutze ein Watch-System für automatische Builds bei Dateiänderungen!
