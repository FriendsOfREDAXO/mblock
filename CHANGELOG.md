# MBlock - REDAXO Addon für Modul-Input-Blöcke

### Version 4.0.0 - Complete Modernization & Backend Integration 🚀

**Release Date:** 20. Juni 2025  
**Major Release:** Vollständige Modernisierung mit Backend-Integration, robustem Toggle-System und verbesserter Entwicklererfahrung

---

## 🎯 **Hauptfeatures**

### ✨ **Neue Backend-Integration**
* **NEW:** Hilfe-Seite im Backend mit integrierter README-Dokumentation
* **NEW:** Moderne Demo-Seiten mit Tab-Navigation (MForm & HTML)
* **NEW:** Scrollbare Code-Beispiele mit verbesserter Lesbarkeit
* **NEW:** Admin-Debug-Seite mit erweiterten Debugging-Optionen
* **NEW:** Vollständige Backend-Navigation mit übersichtlicher Struktur

### 🔧 **Block Toggle System**
* **NEW:** Block Toggle-Funktionalität - Blöcke ein-/ausschalten ohne Löschung
* **NEW:** Vereinfachte API mit `getBlocks()` (nur aktive) und `getAllBlocks()` (alle Blöcke)
* **NEW:** Optionale `collapsed: true` Konfiguration für eingeklappte Blöcke
* **NEW:** Visuelle Darstellung aktiver/inaktiver Blöcke

### 🎨 **Moderne Benutzeroberfläche**
* **NEW:** Einheitliche Button-Gruppe mit 6 Steuerelementen: Toggle, Hoch/Runter, Drag Handle, Hinzufügen, Löschen
* **NEW:** Internationalisierte Tooltips (de, en, es, nl, pt, sv)
* **NEW:** Dark Mode Unterstützung für alle Komponenten
* **NEW:** Responsive Design mit verbesserter Zugänglichkeit
* **NEW:** Moderne CSS mit CSS-Variablen für einfache Anpassung

---

## 🔧 **Technische Verbesserungen**

### 📚 **API & Entwicklererfahrung**
* **NEW:** `MBlock::getBlocks($id)` - Nur aktive Blöcke abrufen
* **NEW:** `MBlock::getAllBlocks()` - Alle Blöcke des Artikels abrufen
* **NEW:** `MBlock::hasBlocks($id)` - Prüfen ob Blöcke vorhanden
* **NEW:** `MBlock::getBlockCount($id)` - Anzahl Blöcke ermitteln
* **NEW:** `MBlock_I18n::msg($key, $fallback)` - Übersetzungen abrufen

### 🎪 **JavaScript Event System**
* **IMPROVED:** Vollständige Event-System-Überarbeitung
* **NEW:** `mblock:toggle` Event für Block-Toggle-Aktionen
* **NEW:** `mblock:ready` Event für Initialisierung
* **IMPROVED:** Bestehende Events: `mblock:add`, `mblock:delete`, `mblock:sort`
* **IMPROVED:** Reindex-sichere Event-Handler ohne doppelte Bindungen

### 🛠️ **Backend & Dokumentation**
* **NEW:** Vollständig neue deutsche README mit umfassender Dokumentation
* **NEW:** Widget-Integration-Anleitung (MForm-spezifisch)
* **NEW:** Migration Guide für < 3.5 Versionen
* **NEW:** JavaScript Event-Referenz für Entwickler
* **NEW:** Troubleshooting-Sektion mit häufigen Problemen
* **NEW:** Best Practices Richtlinien

---

## 🐛 **Fehlerbehebungen**

### 🔄 **Stabilität & Performance**
* **FIXED:** Mehrfache Event-Handler-Bindung behoben
* **FIXED:** Fehlerhafte Block-Indizierung (Array-Index -1 Fehler)
* **FIXED:** Toggle-Felder nur bei Bedarf erstellen
* **FIXED:** Button-Status-Inkonsistenzen nach Drag & Drop
* **FIXED:** Legacy CSS-Klassenkonlikte zwischen alten und neuen Buttons
* **FIXED:** Server-Template mit Legacy-Buttons beim Hinzufügen neuer Blöcke

### 📝 **Datenintegration**
* **FIXED:** REX_MBLOCK_VALUE/REX_MBLOCK_ID Fehler behoben (existieren nicht)
* **FIXED:** Korrekte Verwendung von REX_INPUT_VALUE, REX_VALUE etc.
* **FIXED:** Widget-Integration mit korrekten MForm-Methoden
* **FIXED:** String- vs. Integer-ID Probleme bei Widgets

---

## 📋 **Demo & Beispiele**

### 🎭 **Neue Demo-Seiten**
* **NEW:** MForm-Demo mit 4 praktischen Beispielen
* **NEW:** HTML-Demo mit 4 praktischen Beispielen
* **NEW:** Tabbed Code-Darstellung (Input/Output)
* **NEW:** Echte MBlock-Buttons und -Features in allen Demos
* **NEW:** Scrollbare Code-Blöcke mit verbesserter Schriftgröße
* **NEW:** Responsive Design für alle Bildschirmgrößen

### 📖 **Dokumentation**
* **NEW:** Umfassende deutsche README (298 Zeilen)
* **NEW:** MForm vs. HTML Empfehlungen mit Vor-/Nachteilen
* **NEW:** Vollständige Klassen-Referenz
* **NEW:** Widget-Integrations-Anleitung mit korrekten Beispielen
* **NEW:** Debugging-Sektion mit praktischen Tipps

---

## 🌍 **Internationalisierung**

### 🗣️ **Mehrsprachigkeit**
* **NEW:** Vollständige Übersetzungen für 6 Sprachen
* **NEW:** Tooltips passen sich automatisch an Backend-Sprache an
* **IMPROVED:** Konsistente Übersetzungsschlüssel
* **NEW:** Einfache Erweiterung um weitere Sprachen

### 🔤 **Unterstützte Sprachen**
* Deutsch (de_de) - Vollständig
* English (en_gb) - Vollständig
* Español (es_es) - Vollständig
* Nederlands (nl_nl) - Vollständig
* Português (pt_br) - Vollständig
* Svenska (sv_se) - Vollständig

---

## 🧹 **Projekt-Bereinigung**

### 🗑️ **Entfernte Dateien**
* **REMOVED:** Alte Example-Dateien (16 .ini-Dateien)
* **REMOVED:** Temporäre Test- und Demo-Dateien
* **REMOVED:** Veraltete Dokumentation und Konzept-Dateien
* **REMOVED:** Backup-Dateien und Legacy-Templates
* **REMOVED:** Überflüssige Info-Seiten (durch README ersetzt)

### � **Optimierte Struktur**
* **IMPROVED:** Klare Trennung zwischen Kern-Funktionalität und Demos
* **IMPROVED:** Reduzierte Dateienanzahl für bessere Performance
* **IMPROVED:** Fokus auf moderne Demo-Seiten statt alter Beispiele
* **IMPROVED:** Aufgeräumte Backend-Navigation

---

## ⚡ **Performance & Stabilität**

### 🚀 **Optimierungen**
* **IMPROVED:** Event-Delegation für bessere Performance
* **IMPROVED:** Reduzierte DOM-Manipulationen
* **IMPROVED:** Effizientere CSS-Selektoren
* **IMPROVED:** Optimierte JavaScript-Ausführung
* **IMPROVED:** Weniger Dateien, schnelleres Laden

### 🔒 **Stabilität**
* **IMPROVED:** Robuste Button-Status-Verwaltung
* **IMPROVED:** Sichere Block-Indizierung
* **IMPROVED:** Fehlerbehandlung bei Widget-Integration
* **IMPROVED:** Konsistente Datenstruktur

---

## 🔄 **Migration & Kompatibilität**

### ✅ **Vollständige Rückwärtskompatibilität**
* **MAINTAINED:** Alle bestehenden MBlock-Module funktionieren unverändert
* **MAINTAINED:** Bestehende API-Methoden bleiben verfügbar
* **ENHANCED:** Bestehende Blöcke erhalten automatisch neue v3.5-Funktionen
* **OPTIONAL:** Toggle-Funktionalität ist opt-in (Blöcke standardmäßig aktiv)

### 🔄 **Migration von < 3.5**
* **GUIDE:** Detaillierte Anleitung für API-Änderungen
* **GUIDE:** Toggle-Funktionalität aktivieren
* **GUIDE:** Neue Event-Handler implementieren
* **GUIDE:** CSS-Anpassungen für moderne UI

---

## 📊 **Verbesserungen im Detail**

### 📈 **Metriken**
* **Code-Qualität:** Vollständige Überarbeitung der Kern-Klassen
* **Dokumentation:** 298 Zeilen umfassende README
* **Tests:** Erweiterte Debug-Funktionen
* **Benutzerfreundlichkeit:** Moderne UI mit verbesserter Zugänglichkeit
* **Performance:** Optimierte Event-Handler und CSS

### 🎯 **Entwicklererfahrung**
* **NEW:** Vollständige API-Dokumentation
* **NEW:** Praktische Code-Beispiele
* **NEW:** Debugging-Tools und -Anleitungen
* **NEW:** Best Practices Richtlinien
* **NEW:** Troubleshooting-Sektion

---

## 🏆 **Besondere Erwähnungen**

### 👥 **Community**
* **IMPROVED:** Fokus auf deutsche REDAXO-Community
* **NEW:** Umfassende deutsche Dokumentation
* **NEW:** Praxisnahe Beispiele und Demos
* **NEW:** Einfache Erweiterbarkeit für Entwickler

### 🔧 **Technologie**
* **MODERN:** CSS-Variablen für einfache Anpassung
* **MODERN:** Event-Delegation für bessere Performance
* **MODERN:** Responsive Design für alle Geräte
* **MODERN:** Zugänglichkeit nach aktuellen Standards

---

## ⚠️ **Breaking Changes**
* **NONE** - Vollständige Rückwärtskompatibilität gewährleistet

---

## 🙏 **Credits**
* Modernisierung basierend auf REDAXO-Design-Philosophie
* Event-System-Optimierung durch Community-Feedback
* Internationalisierung für globale REDAXO-Community
* Deutsche Dokumentation für lokale Entwickler-Community

---

#### 📝 **API Examples**
```php
// New simplified API - get only active blocks
$activeBlocks = MBlock::getBlocks('REX_VALUE[1]');

// Get all blocks (including inactive ones)
$allBlocks = MBlock::getAllBlocks('REX_VALUE[1]');

// Check if specific block is active
foreach ($allBlocks as $block) {
    $isActive = !isset($block['mblock_active']) || $block['mblock_active'] !== '0';
    if ($isActive) {
        // Process active block
    }
}
```

#### 🌍 **Internationalization**
* Tooltips automatically adapt to REDAXO backend language
* Supported languages: German, English, Spanish, Dutch, Portuguese, Swedish
* Easy to extend with additional languages

#### ⚠️ **Breaking Changes**
* None - Full backwards compatibility maintained

#### 🙏 **Credits**
* Modern UI design inspired by REDAXO's design philosophy
* Event system optimization based on community feedback
* Internationalization support for global REDAXO community

---

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
