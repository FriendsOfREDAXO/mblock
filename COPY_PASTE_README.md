# 📋 Copy & Paste Funktionalität für MBlock

## 🎯 **Übersicht**

Diese neue Funktionalität ermöglicht es, MBlock-Blöcke innerhalb derselben MBlock-Instanz zu kopieren und einzufügen. Die Implementierung erfüllt alle Anforderungen aus [Issue #172](https://github.com/FriendsOfREDAXO/mblock/issues/172).

## ✨ **Features**

### 🔄 **Copy & Paste innerhalb einer MBlock-Instanz**
- Blöcke können innerhalb derselben MBlock-Instanz kopiert werden
- **Keine Übertragung zwischen verschiedenen MBlocks** (isoliertes Clipboard pro Instanz)
- Alle Formularfelder und deren Werte werden korrekt kopiert

### 🎛️ **Benutzeroberfläche**
- **Copy & Paste Buttons in eigener Toolbar** (links oben in jedem Block)
- Visuelles Feedback beim Kopieren
- Paste-Button wird nur aktiviert wenn Clipboard-Inhalt vorhanden

### 🔧 **Technische Robustheit**
- **Automatische ID-Regenerierung** für alle kopierten Elemente
- **Widget-Reinitialisierung** (REX_MEDIA, REX_LINK, etc.)
- **Event-Neubindung** für alle JavaScript-Funktionalitäten
- **Unique-Field-Handling** (Felder mit `data-unique` werden geleert)

## 🚀 **Verwendung**

### 1. **Block kopieren**
- Klick auf "Kopieren" Button in der Toolbar eines Blocks
- Visuelles Feedback: Button wird kurz grün mit "Kopiert!" Text
- Paste-Buttons in allen anderen Blöcken werden aktiviert

### 2. **Block einfügen**
- Klick auf "Einfügen" Button in einem anderen Block
- Neuer Block wird **nach** dem aktuellen Block eingefügt
- Alle Formulardaten werden übernommen (außer unique Felder)

## 🔒 **Sicherheit & Validierung**

### **Beschränkungen respektiert**
- Maximale Anzahl (`max`) wird überprüft
- Minimale Anzahl (`min`) wird respektiert beim Löschen
- Warnung bei Erreichen der maximalen Blockanzahl

### **Datenintegrität**
- Unique IDs werden automatisch neu generiert
- REX_MEDIA und REX_LINK Widgets werden korrekt reinitialisiert
- Checkbox/Radio-Status wird korrekt übertragen

## 📝 **Implementierung**

### **JavaScript Funktionen**
```javascript
// Hauptfunktionen
mblock_init_copy_paste(element)      // Initialisierung
mblock_copy_item(element, item)      // Item kopieren
mblock_paste_item(element, item)     // Item einfügen

// Hilfsfunktionen  
mblock_extract_form_data(item)       // Formulardaten extrahieren
mblock_restore_form_data(item, data) // Formulardaten wiederherstellen
mblock_add_copy_paste_buttons(element) // Buttons hinzufügen
```

### **CSS Klassen**
```css
.mblock-copy-paste-toolbar    // Toolbar Container
.mblock-copy-btn             // Copy Button
.mblock-paste-btn            // Paste Button
```

## 🎨 **Styling**

### **Helles Theme**
- Toolbar: Hellgrauer Hintergrund (`#f8f9fa`)
- Buttons: Bootstrap-Standard mit Hover-Effekten

### **Dark Mode**
- Toolbar: Dunkler Hintergrund (`#2d3436`) 
- Buttons: Angepasste Farben für bessere Lesbarkeit

## 🔄 **Kompatibilität**

### **REDAXO Widgets**
✅ REX_MEDIA Widgets  
✅ REX_LINK Widgets  
✅ REX_MEDIALIST Widgets  
✅ REX_LINKLIST Widgets  
✅ Custom Links (MForm)  
✅ Checkboxes & Radio Buttons  

### **MBlock Features**
✅ Sortierung  
✅ Min/Max Beschränkungen  
✅ Unique ID Generierung  
✅ Event System (`rex:ready`, `mblock:change`)  
✅ Bestehende Button-Funktionalitäten  

## 🧪 **Testing**

### **Manuelle Tests durchführen:**
1. MBlock mit verschiedenen Feldtypen erstellen
2. Blöcke kopieren und einfügen
3. Widgets testen (Media, Link Buttons)
4. Min/Max Limits testen
5. Sortierung nach Copy/Paste testen

### **Edge Cases:**
- Kopieren bei erreichter max-Grenze
- Einfügen ohne Clipboard-Inhalt
- Mehrfaches Kopieren/Einfügen
- Widgets nach Copy/Paste

## 📈 **Performance**

- **Minimaler Overhead**: Clipboard wird nur bei Bedarf verwendet
- **Effiziente DOM-Manipulation**: Nur notwendige Elemente werden geklont
- **Event-Optimierung**: Events werden nur einmal pro Block gebunden

## 🐛 **Bekannte Limitationen**

1. **Keine Cross-MBlock Funktionalität** (by Design - wie gefordert)
2. **Unique Felder werden geleert** (by Design - verhindert Duplikate)
3. **Clipboard ist nicht persistent** (geht bei Seitenreload verloren)

## 🔮 **Mögliche Erweiterungen**

- Keyboard Shortcuts (Ctrl+C, Ctrl+V)
- Bulk Copy/Paste (mehrere Blöcke gleichzeitig)
- Clipboard-History (mehrere Items im Clipboard)
- Visual Clipboard Indicator

---

**✅ Issue #172 komplett implementiert und getestet!**
