# MBlock v5.0 - Mehrfachblöcke für REDAXO Module

Mit **MBlock** können Sie innerhalb eines REDAXO-Moduls beliebig viele gleichartige Datenblöcke erstellen, die der Redakteur einfach hinzufügen, löschen, sortieren und ein-/ausklappen kann.

![MBlock Demo](https://raw.githubusercontent.com/FriendsOfREDAXO/mblock/assets/mblock.png)

## 🚀 Was kann MBlock?

- **Mehrfachblöcke erstellen**: Beliebig viele Wiederholungen eines Formularbereichs
- **Drag & Drop Sortierung**: Blöcke einfach per Maus neu anordnen
- **Toggle-Funktionalität**: Blöcke ein- und ausklappen (NEU in v3.5)
- **Min/Max Limits**: Mindest- und Höchstanzahl von Blöcken definieren
- **Flexible Integration**: Funktioniert mit MForm und reinem HTML
- **Moderne API**: Vereinfachte Datenausgabe mit `getBlocks()` und `getAllBlocks()`

## 📋 Voraussetzungen

- REDAXO 5.12+
- PHP 8.1+
- **Empfohlen**: [MForm Addon](https://github.com/FriendsOfREDAXO/mform) für komfortable Formularerstellung

## 🎯 MForm vs. HTML - Unsere Empfehlung

**MForm wird empfohlen** für die Verwendung mit MBlock:

### ✅ Vorteile MForm
- Automatische Widget-Integration (Media, Link, etc.)
- Saubere Datenstruktur
- Weniger Code erforderlich
- Bessere Wartbarkeit
- Integrierte Validierung

### ⚠️ Reines HTML
- Mehr Code erforderlich
- Manuelle Widget-Integration nötig
- Nur für spezielle Anwendungsfälle empfohlen

## 🔧 Grundlegende Verwendung

### Mit MForm (empfohlen)

```php
<?php
// Modul-Eingabe
use FriendsOfRedaxo\MForm;

$id = 1;
$mform = new MForm();

$mform->addFieldset('Teammitglied');
$mform->addTextField("$id.0.name", ['label' => 'Name']);
$mform->addMediaField(1, ['label' => 'Avatar']);  // Richtige Widget-Methode!

echo MBlock::show($id, $mform->show(), [
    'min' => 1,
    'max' => 5,
    'collapsed' => true  // NEU: Blöcke eingeklappt starten
]);
```

```php
<?php
// Modul-Ausgabe
$blocks = MBlock::getBlocks(1);  // NEU: Vereinfachte API

foreach ($blocks as $block) {
    echo '<div class="team-member">';
    echo '<h3>' . rex_escape($block['name']) . '</h3>';
    
    if ($block['REX_MEDIA_1']) {
        echo '<img src="' . rex_url::media($block['REX_MEDIA_1']) . '">';
    }
    
    echo '</div>';
}
```

### Mit HTML

```php
<?php
// Modul-Eingabe
$id = 1;
$form = '
<fieldset>
    <legend>Teammitglied</legend>
    <input type="text" name="REX_INPUT_VALUE[' . $id . '][0][name]" placeholder="Name">
    REX_MEDIA[id="1" widget="1"]
</fieldset>';

echo MBlock::show($id, $form);
```

## 🆕 Migration von älteren Versionen (< 4.0)

### API-Änderungen

**ALT** (deprecated):
```php
$data = rex_var::toArray("REX_VALUE[1]");
```

**NEU** (empfohlen):
```php
$blocks = MBlock::getBlocks(1);        // Für einen spezifischen Block-Typ
$allBlocks = MBlock::getAllBlocks();   // Für alle Block-Typen des Artikels
```

### Toggle-Funktionalität aktivieren

Für die neue Toggle-Funktionalität (Ein-/Ausklappen) müssen Sie:

1. **CSS aktualisieren**: Neue MBlock-Styles einbinden
2. **JavaScript aktualisieren**: Neue Event-Handler verwenden  
3. **Optional**: `collapsed: true` Parameter bei `MBlock::show()` verwenden

## 🎨 REDAXO Widgets richtig verwenden

### ⚠️ Wichtig für MForm: Richtige Widget-Methoden verwenden!

```php
// ✅ RICHTIG - Spezifische Widget-Methoden verwenden
$mform->addMediaField(1, ['label' => 'Bild']);          // REX_MEDIA[1]
$mform->addLinkField(2, ['label' => 'Link']);           // REX_LINK[2]
$mform->addLinklistField(3, ['label' => 'Linkleiste']); // REX_LINKLIST[3]
$mform->addMedialistField(4, ['label' => 'Medialeiste']); // REX_MEDIALIST[4]

// ❌ FALSCH - String-Felder funktionieren nicht mit Widgets
$mform->addMediaField("1.0.media", ['label' => 'Bild']);
$mform->addMediaField("1", ['label' => 'Bild']);  // Auch falsch - String statt INT!
```

### Widget-Ausgabe

```php
$blocks = MBlock::getBlocks(1);

foreach ($blocks as $block) {
    // Media Widget
    if (!empty($block['REX_MEDIA_1'])) {
        echo '<img src="' . rex_url::media($block['REX_MEDIA_1']) . '">';
    }
    
    // Link Widget  
    if (!empty($block['REX_LINK_2'])) {
        echo '<a href="' . rex_getUrl($block['REX_LINK_2']) . '">Link</a>';
    }
    
    // Medialist Widget
    if (!empty($block['REX_MEDIALIST_4'])) {
        $mediaIds = explode(',', $block['REX_MEDIALIST_4']);
        foreach ($mediaIds as $mediaId) {
            echo '<img src="' . rex_url::media($mediaId) . '">';
        }
    }
}
```

## 📚 MBlock Klassen-Referenz

### MBlock Hauptklasse

```php
MBlock::show($id, $form, $options = [])
```

**Parameter:**
- `$id` (int): Eindeutige Block-ID
- `$form` (string): HTML-Formular oder MForm-Output
- `$options` (array): Konfigurationsoptionen

**Optionen:**
```php
[
    'min' => 1,              // Mindestanzahl Blöcke
    'max' => 10,             // Maximalanzahl Blöcke
    'collapsed' => false,    // Blöcke eingeklappt starten
    'template' => 'default', // CSS-Theme
    'sortable' => true       // Drag & Drop aktiviert
]
```

### Neue API-Methoden (v3.5)

```php
// Blöcke eines bestimmten Typs abrufen
MBlock::getBlocks('REX_VALUE[1]')

// Alle Blöcke des aktuellen Artikels abrufen
MBlock::getAllBlocks('REX_VALUE[1]')

// Block-Daten prüfen
MBlock::hasBlocks('REX_VALUE[1]')

// Anzahl Blöcke ermitteln
MBlock::getBlockCount('REX_VALUE[1]')
```

### MBlock_I18n Klasse

```php
// Übersetzungen für MBlock-Interface
MBlock_I18n::msg($key, $fallback = '')
```

## 🎪 JavaScript Events

### Event-Listener registrieren

```javascript
// Nach Block hinzufügen
$(document).on('mblock:add', function(event, blockId, element) {
    console.log('Block hinzugefügt:', blockId);
});

// Nach Block löschen  
$(document).on('mblock:delete', function(event, blockId, element) {
    console.log('Block gelöscht:', blockId);
});

// Nach Block sortieren
$(document).on('mblock:sort', function(event, blockId, element) {
    console.log('Block sortiert:', blockId);
});

// Nach Block toggle (ein-/ausklappen)
$(document).on('mblock:toggle', function(event, blockId, element, isCollapsed) {
    console.log('Block toggle:', blockId, 'eingeklappt:', isCollapsed);
});

// Nach MBlock initialisierung
$(document).on('mblock:ready', function(event, blockId) {
    console.log('MBlock bereit:', blockId);
});
```

### Custom JavaScript ausführen

```javascript
// Warten bis MBlock vollständig geladen
$(document).on('rex:ready', function() {
    // Ihre MBlock-Initialisierung hier
    $('.my-mblock-field').each(function() {
        // Custom Logic
    });
});
```

## 🔍 Debugging & Troubleshooting

### Debug-Ausgaben

```php
// Alle Block-Daten ausgeben
$blocks = MBlock::getBlocks(1);
dump($blocks);

// Einzelnen Block analysieren
$blocks = MBlock::getAllBlocks();
foreach ($blocks as $blockType => $blockData) {
    echo "Block-Typ: $blockType<br>";
    dump($blockData);
}
```

### Häufige Probleme

1. **Widgets funktionieren nicht**
   - Lösung: Integer-Felder in MForm verwenden

2. **JavaScript-Fehler**
   - Lösung: `rex:ready` Event abwarten

3. **Daten werden nicht gespeichert**
   - Lösung: Korrekte `name`-Attribute prüfen

4. **Toggle funktioniert nicht**
   - Lösung: Auf v4.0 migrieren und neue Assets einbinden

## 🎯 Best Practices

1. **Verwenden Sie MForm** für bessere Wartbarkeit
2. **Begrenzen Sie die Anzahl** der Blöcke mit `min`/`max`
3. **Verwenden Sie aussagekräftige Block-IDs** (1, 2, 3...)
4. **Testen Sie mit ein-/ausgeklappten Blöcken**
5. **Validieren Sie Eingaben** in der Ausgabe
6. **Nutzen Sie die neuen API-Methoden** für sauberen Code

## � Entwicklung & Build-System

### Build-System Setup

MBlock verwendet ein modernes Build-System basierend auf **Rollup** für optimierte JavaScript-Bundles:

```bash
# Dependencies installieren
npm install

# Development-Build (mit Debug-Logs)
npm run build

# Production-Build (minifiziert, ohne Logs)
npm run prod

# Watch-Modus für Entwicklung
npm run dev

# Build-Artefakte löschen
npm run clean
```

### Shell-Scripts

Für erweiterte Build-Optionen stehen Shell-Scripts zur Verfügung:

```bash
# Produktionsbuild mit Validierung
./build.sh production

# Entwicklungsbuild
./build.sh development

# Watch-Modus
./build.sh watch

# Cleanup
./build.sh clean

# Deployment-Vorbereitung
./deploy.sh
```

### Build-Output

Das Build-System erstellt optimierte Dateien in `assets/dist/`:

- **mblock.min.js** (16.8KB) - Haupt-Bundle, minifiziert
- **mblock.css** - CSS-Styles
- **BUILD_INFO.txt** - Build-Informationen

### Performance-Optimierungen

- ✅ **Console-Logs entfernt** in Production-Builds
- ✅ **Dead Code Elimination** durch Rollup
- ✅ **Minifizierung** durch Terser (62% Größenreduktion)
- ✅ **ES5-Kompatibilität** für maximale Browser-Unterstützung

### Development vs. Production

| Modus | Dateigröße | Console-Logs | Source Maps | Debug-Mode | Verwendung |
|-------|------------|--------------|-------------|------------|------------|
| Production | ~17KB | ❌ Nein | ❌ Nein | ❌ Aus | Live-Server |
| Debug | ~45KB | ✅ Ja | ✅ Ja | ✅ Ein | Lokale Entwicklung |

**Automatische Asset-Ladung:**
- **Debug-Modus ein** (`rex::isDebugMode()` = true): Separate Dateien mit Console-Logs
- **Debug-Modus aus** + Bundle vorhanden: Minifiziertes Bundle ohne Logs
- **Fallback**: Separate Dateien falls Bundle nicht verfügbar

### Git-Workflow

Das Build-System ist Git-freundlich konfiguriert:
- `node_modules/` wird ignoriert
- Nur Source-Code wird versioniert
- Build-Artefakte werden lokal generiert

## �📞 Support & Community

- **GitHub**: [FriendsOfREDAXO/mblock](https://github.com/FriendsOfREDAXO/mblock)
- **REDAXO Slack**: #addons Channel
- **Dokumentation**: REDAXO Backend → MBlock → Hilfe

---

**MBlock v5.0** - Entwickelt von [Friends Of REDAXO](https://friendsofredaxo.github.io/)
