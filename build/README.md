# MBlock Modular Build System

Das neue modulare Build-System für MBlock ermöglicht eine bessere Organisation, Wartbarkeit und Performance des JavaScript-Codes.

## 📁 Neue Struktur

```
build/
├── src/
│   ├── mblock-core.js          # Basis-Funktionen und Utilities
│   ├── mblock-management.js    # DOM-Manipulation und Sortierung
│   └── mblock-features.js      # Erweiterte Features (Copy/Paste, etc.)
├── build-modules.js            # Kombiniert die Module
├── minify.js                   # Minifiziert die kombinierte Datei
├── build.sh                    # Haupt-Build-Script
├── package.json               # Build-Konfiguration
└── README.md                  # Diese Dokumentation
```

## 🚀 Schnellstart

```bash
cd build/
npm run full-build
```

Das erstellt automatisch:
- `../assets/mblock.js` (kombinierte Module)
- `../assets/mblock.min.js` (minifizierte Produktionsversion)
- `../assets/mblock.min.js.map` (Source Maps für Debugging)

## 📋 Voraussetzungen

- **Node.js** >= 14.0.0
- **npm** (wird mit Node.js installiert)

## 🛠️ Installation & Setup

1. **Dependencies installieren:**
   ```bash
   cd build/
   npm install
   ```

2. **Ersten Build ausführen:**
   ```bash
   npm run full-build
   ```

## 🎯 Verwendung

### Vollständiger Build (Empfohlen)
```bash
npm run full-build
```

### Einzelne Schritte

#### 1. Module kombinieren
```bash
npm run build
# oder
node build-modules.js
```

#### 2. Minifizieren
```bash
npm run minify
# oder
node minify.js
```

### Entwicklungsmodi

#### Watch-Modus (automatischer Rebuild bei Änderungen)
```bash
npm run build:watch
```

#### Aufräumen
```bash
npm run clean
```

## 📝 Entwicklung

### Module bearbeiten

**Wichtig**: Bearbeite niemals die kombinierte `mblock.js` direkt! Änderungen gehören in die entsprechenden Modul-Dateien.

#### mblock-core.js (24 KB)
- Basis-Funktionen und Utilities
- Element-Validierung
- Translation-Funktionen
- Toast-System
- Event-Handling

#### mblock-management.js (38 KB)
- DOM-Manipulation
- Sortable-Funktionalität
- Add/Remove/Move Operationen
- Form-Element Reindexing
- REX-Field Handling

#### mblock-features.js (22 KB)
- Copy/Paste Funktionalität
- Online/Offline Toggle
- REDAXO Widget Reinitialisierung
- AJAX-Funktionen
- CKEditor5 Integration

### Neue Features hinzufügen

1. **Entscheide das richtige Modul:**
   - Core: Basis-Funktionen, Utilities
   - Management: DOM-Manipulation, UI-Interaktionen
   - Features: Erweiterte Funktionen, Integrationen

2. **Implementiere das Feature:**
   ```javascript
   // Beispiel: Neues Feature in mblock-features.js
   function myNewFeature() {
       // Implementierung hier
   }
   ```

3. **Build ausführen:**
   ```bash
   npm run build
   ```

4. **Testen:**
   - Teste in REDAXO-Umgebung
   - Prüfe Browser-Konsole auf Fehler
   - Teste alle betroffenen Funktionen

## 📊 Performance & Statistiken

### Build-Ergebnisse
- **Input:** 3 Module (84 KB gesamt)
- **Output:** `mblock.min.js` (32 KB)
- **Ersparnis:** 62% kleinere Dateigröße
- **Build-Zeit:** ~200ms

### Modul-Aufteilung
```
mblock-core.js:        24 KB (29%)
mblock-management.js:  38 KB (45%)
mblock-features.js:    22 KB (26%)
```

### Vorteile des modularen Systems
- ✅ **62% kleinere Dateigröße** für bessere Performance
- ✅ **Modulare Entwicklung** ohne Konflikte
- ✅ **Bessere Wartbarkeit** durch klare Trennung
- ✅ **Automatischer Rebuild** im Watch-Modus
- ✅ **Source Maps** für einfaches Debugging
- ✅ **Production Preprocessing** entfernt console.log automatisch

## 🔧 Technische Details

### Build-Prozess

1. **Modul-Validierung:**
   - Prüft Existenz aller Module
   - Validiert JavaScript-Syntax

2. **Kombination:**
   - Fügt Header-Kommentare hinzu
   - Kombiniert alle Module in richtiger Reihenfolge
   - Erstellt `mblock.js`

3. **Minifizierung:**
   - Terser-Optimierung
   - Preserved Function Names für API-Kompatibilität
   - Source Map Generierung

### Preserved Function Names

Diese kritischen Funktionen werden nicht umbenannt:

```javascript
// Core API
'mblock_init',
'mblock_init_sort',
'mblock_sort',
'mblock_add',

// Feature APIs
'MBlockClipboard',
'MBlockOnlineToggle',
'mblock_smooth_scroll_to_element',

// Utilities
'mblock_validate_element',
'mblock_show_message',
'mblock_get_text'
```

## 🎯 Asset Loading

Die `boot.php` lädt automatisch die optimierte Version:

```php
// Automatisches Laden der minifizierten Version
rex_view::addJsFile($this->getAssetsUrl('mblock.min.js'));
rex_view::addCssFile($this->getAssetsUrl('mblock.css'));
```

## 🐛 Fehlerbehebung

### Häufige Probleme

#### "Modul nicht gefunden"
```bash
# Prüfe Modul-Dateien
ls -la src/

# Fehlende Module neu erstellen
touch src/mblock-core.js
```

#### Build-Fehler
```bash
# Node.js Version prüfen
node --version  # sollte >= 14.0.0 sein

# Dependencies neu installieren
rm -rf node_modules
npm install

# Cache leeren
npm cache clean --force
```

#### Syntax-Fehler
```bash
# JavaScript-Syntax prüfen
node -c src/mblock-core.js
node -c src/mblock-management.js
node -c src/mblock-features.js
```

#### Performance-Probleme
- Verwende immer `mblock.min.js` in Produktion
- Aktiviere Gzip-Kompression auf dem Server
- Setze Cache-Header für statische Assets

### Debug-Modus

Für Entwicklung mit voller Debug-Information:

```bash
# Source Maps aktivieren in Browser DevTools
# Console zeigt Original-Zeilennummern
npm run build  # Erstelle unminifizierte Version
```

## 📈 Erweiterte Features

### Watch-Modus für Entwicklung

```bash
npm run build:watch
```

- Überwacht Änderungen an Modulen
- Automatischer Rebuild bei Dateiänderungen
- Ideal für aktive Entwicklung

### Custom Build-Konfiguration

Bearbeite `build-modules.js` für:

- Zusätzliche Module
- Andere Kombinationsreihenfolge
- Custom Header/Footer

### CI/CD Integration

Für automatische Builds in CI/CD:

```yaml
# Beispiel GitHub Actions
- name: Build MBlock
  run: |
    cd build
    npm install
    npm run full-build
```

## 🎯 Migration Guide

### Von alter Struktur migrieren

1. **Backup sichern:**
   ```bash
   cp ../assets/mblock.js ../assets/mblock.js.backup
   ```

2. **Neues System verwenden:**
   ```bash
   cd build
   npm run full-build
   ```

3. **Testen:**
   - REDAXO-Installation testen
   - Alle MBlock-Funktionen prüfen
   - Browser-Konsole auf Fehler überwachen

4. **Deploy:**
   - Neue `mblock.min.js` in Produktion verwenden
   - Alte `mblock.js` kann entfernt werden

## 📚 API-Referenz

### Core Functions
```javascript
mblock_init(element)           // Initialisiert MBlock
mblock_validate_element(el)    // Validiert jQuery-Element
mblock_show_message(msg, type) // Zeigt Toast-Nachricht
```

### Management Functions
```javascript
mblock_add_item(wrapper, item) // Fügt neuen Block hinzu
mblock_remove_item(wrapper, item) // Entfernt Block
mblock_reindex(wrapper)        // Reindexiert alle Blöcke
```

### Feature Functions
```javascript
MBlockClipboard.copy(wrapper, item)    // Kopiert Block
MBlockClipboard.paste(wrapper, item)   // Fügt Block ein
MBlockOnlineToggle.toggle(wrapper, item) // Toggle Online/Offline
```

## 🔮 Roadmap

### Geplante Features
- [ ] Unit-Tests für einzelne Module
- [ ] Code-Splitting für noch kleinere Bundles
- [ ] Lazy-Loading für Features
- [ ] TypeScript-Migration
- [ ] Bundle-Analyzer Integration

### Performance-Optimierungen
- [ ] Tree Shaking für ungenutzte Features
- [ ] Dynamic Imports für bedingte Features
- [ ] Webpack-Bundle-Analyzer Integration

## 🤝 Beitrag leisten

### Code beitragen
1. Fork das Repository
2. Erstelle einen Feature-Branch
3. Implementiere Änderungen in entsprechenden Modulen
4. Teste mit `npm run full-build`
5. Erstelle Pull Request

### Dokumentation verbessern
- Issues für Dokumentationslücken melden
- Verbesserungsvorschläge sind willkommen
- Beispiele und Tutorials erweitern

## 📞 Support

Bei Problemen oder Fragen:

1. **Dokumentation prüfen** - Viele Antworten hier
2. **Issues durchsuchen** - Bekannte Probleme
3. **Neues Issue erstellen** - Bei unbekannten Problemen
4. **Community-Forum** - Für allgemeine Fragen

## 📄 Lizenz

MIT License - siehe LICENSE-Datei für Details.

---

**💡 Tipp:** Verwende `npm run build:watch` während der Entwicklung für automatische Builds bei Dateiänderungen!
