# 🚀 MBlock Multi-Template Konzept

## Vision: Verschiedene Block-Typen in einem MBlock

### Problem-Statement
Aktuell: Ein MBlock = Ein Formular-Typ (wiederholt)
```php
// Aktuell: Nur Team Members
echo MBlock::show(1, $teamMemberForm->show());
```

Vision: Ein MBlock = Verschiedene Block-Typen wählbar
```php
// Vision: Wählbare Block-Typen
echo MBlock::show(1, [
    'team_member' => $teamMemberForm->show(),
    'image_gallery' => $galleryForm->show(), 
    'text_block' => $textForm->show(),
    'video_block' => $videoForm->show()
]);
```

## 🎯 User Experience Konzept

### Schritt 1: Block-Typ Auswahl
```
┌─────────────────────────────────┐
│ Neuen Block hinzufügen:         │
│                                 │
│ [📝 Text Block]                 │
│ [👥 Team Member]                │  
│ [🖼️ Bildergalerie]              │
│ [🎥 Video Block]                │
│ [📊 Daten-Tabelle]              │
│                                 │
│ [Abbrechen]                     │
└─────────────────────────────────┘
```

### Schritt 2: Formular für gewählten Typ
```
┌─────────────────────────────────┐
│ 👥 Team Member Block #1         │
│ ┌─────────────────────────────┐ │
│ │ Name: [________________]    │ │
│ │ Position: [_____________]   │ │  
│ │ E-Mail: [_______________]   │ │
│ │ Avatar: [📁 Wählen...]      │ │
│ └─────────────────────────────┘ │
│ [+ Gleicher Typ] [🗑️] [📋] [📄] │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ 🖼️ Bildergalerie Block #2       │
│ ┌─────────────────────────────┐ │
│ │ Titel: [________________]   │ │
│ │ Bilder: [📁📁📁 Wählen...]   │ │
│ │ Layout: [▼ Raster]          │ │
│ └─────────────────────────────┘ │
│ [+ Gleicher Typ] [🗑️] [📋] [📄] │
└─────────────────────────────────┘

[+ Neuen Block-Typ hinzufügen]
```

## 🔧 Technische Implementierung

### Variante A: Template-basiert (JavaScript)
```javascript
// Multi-Template System
const MBlockMulti = {
    templates: {
        'team_member': {
            form: '<!-- Team Member Form HTML -->',
            label: '👥 Team Member',
            icon: 'fa-user'
        },
        'gallery': {
            form: '<!-- Gallery Form HTML -->',
            label: '🖼️ Bildergalerie', 
            icon: 'fa-images'
        },
        'text': {
            form: '<!-- Text Block Form HTML -->',
            label: '📝 Text Block',
            icon: 'fa-align-left'
        }
    },
    
    showTypeSelector: function(container) {
        // Zeige Block-Typ Auswahl Modal
    },
    
    addBlock: function(type, afterElement) {
        // Füge Block vom gewählten Typ hinzu
    }
};
```

### Variante B: PHP Array-basiert
```php
<?php
use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MBlock\MBlock;

$id = 1;

// Verschiedene Block-Templates definieren
$blockTypes = [
    'team_member' => [
        'label' => '👥 Team Member',
        'form' => MForm::factory()
            ->addTextField("$id.0.name", ['label' => 'Name'])
            ->addTextField("$id.0.position", ['label' => 'Position'])  
            ->addMediaField(1, ['label' => 'Avatar'])
            ->addHiddenField("$id.0.block_type", 'team_member')
            ->addHiddenField("$id.0.mblock_offline", '0')
    ],
    
    'gallery' => [
        'label' => '🖼️ Bildergalerie',
        'form' => MForm::factory()
            ->addTextField("$id.0.title", ['label' => 'Titel'])
            ->addMedialistField(2, ['label' => 'Bilder'])
            ->addSelectField("$id.0.layout", [
                'grid' => 'Raster',
                'carousel' => 'Karussell',
                'masonry' => 'Masonry'
            ], ['label' => 'Layout'])
            ->addHiddenField("$id.0.block_type", 'gallery')
            ->addHiddenField("$id.0.mblock_offline", '0')
    ],
    
    'text' => [
        'label' => '📝 Text Block', 
        'form' => MForm::factory()
            ->addTextField("$id.0.headline", ['label' => 'Überschrift'])
            ->addTextAreaField("$id.0.content", ['label' => 'Inhalt'])
            ->addHiddenField("$id.0.block_type", 'text')
            ->addHiddenField("$id.0.mblock_offline", '0')
    ]
];

// Multi-Template MBlock anzeigen
echo MBlock::showMultiTemplate($id, $blockTypes, [
    'max' => 20,
    'allow_type_mixing' => true
]);
?>
```

### Variante C: Conditional Forms (Aktuelle MBlock-Kompatibilität)
```php
<?php
// Kompromiss-Lösung: Ein Formular mit bedingten Feldern
$mform = MForm::factory();

// Block-Typ Auswahl
$mform->addSelectField("$id.0.block_type", [
    'team_member' => '👥 Team Member',
    'gallery' => '🖼️ Bildergalerie', 
    'text' => '📝 Text Block'
], ['label' => 'Block-Typ']);

// Team Member Felder (conditional via CSS/JS)
$mform->addHTML('<div class="block-type-fields" data-type="team_member" style="display:none;">');
$mform->addTextField("$id.0.name", ['label' => 'Name']);
$mform->addTextField("$id.0.position", ['label' => 'Position']);
$mform->addMediaField(1, ['label' => 'Avatar']);
$mform->addHTML('</div>');

// Gallery Felder
$mform->addHTML('<div class="block-type-fields" data-type="gallery" style="display:none;">');
$mform->addTextField("$id.0.title", ['label' => 'Titel']);
$mform->addMedialistField(2, ['label' => 'Bilder']);
$mform->addHTML('</div>');

// Text Block Felder  
$mform->addHTML('<div class="block-type-fields" data-type="text" style="display:none;">');
$mform->addTextField("$id.0.headline", ['label' => 'Überschrift']);
$mform->addTextAreaField("$id.0.content", ['label' => 'Inhalt']);
$mform->addHTML('</div>');

$mform->addHiddenField("$id.0.mblock_offline", '0');

echo MBlock::show($id, $mform->show());
?>
```

## 📊 Vor- und Nachteile

### ✅ Vorteile
- **Flexibilität:** Verschiedene Inhaltstypen in einem Block
- **Benutzerfreundlichkeit:** Intuitive Auswahl
- **Wiederverwendbarkeit:** Templates können geteilt werden
- **Skalierbarkeit:** Einfach neue Block-Typen hinzufügen

### ⚠️ Herausforderungen
- **Komplexität:** Deutlich komplexere Implementierung
- **Datenstruktur:** JSON wird komplexer (block_type field nötig)
- **Template-System:** Neue Template-Engine nötig
- **Rückwärtskompatibilität:** Muss gewährleistet bleiben
- **UI/UX:** Neue Interface-Komponenten nötig

### 🔧 Frontend-Verarbeitung
```php
<?php
// Ausgabe verschiedener Block-Typen
$blocks = MBlock::getOnlineDataArray("REX_VALUE[1]");

foreach ($blocks as $block) {
    $type = $block['block_type'] ?? 'default';
    
    switch ($type) {
        case 'team_member':
            echo renderTeamMember($block);
            break;
            
        case 'gallery':
            echo renderGallery($block);
            break;
            
        case 'text':
            echo renderTextBlock($block);
            break;
            
        default:
            echo renderDefault($block);
    }
}

function renderTeamMember($data) {
    return "
    <div class='team-member'>
        <h3>{$data['name']}</h3>
        <p>{$data['position']}</p>
        <img src='".rex_media_manager::getUrl('profile', $data['REX_MEDIA_1'])."'>
    </div>";
}

function renderGallery($data) {
    $images = explode(',', $data['REX_MEDIALIST_2']);
    $html = "<div class='gallery {$data['layout']}'>";
    foreach ($images as $img) {
        $html .= "<img src='".rex_media_manager::getUrl('gallery', $img)."'>";
    }
    $html .= "</div>";
    return $html;
}
?>
```

## 🎯 Machbarkeitsanalyse

### Stufe 1: Proof of Concept ⭐⭐⭐
- Conditional Fields mit JavaScript (einfachste Lösung)
- Ein Formular, verschiedene Bereiche je nach Auswahl
- Schnell implementierbar

### Stufe 2: Template System ⭐⭐⭐⭐  
- Neue MBlock-Funktion: `showMultiTemplate()`
- Templates als Array übergeben
- Fortgeschrittene JavaScript UI nötig

### Stufe 3: Visual Block Builder ⭐⭐⭐⭐⭐
- Drag & Drop Block-Auswahl
- Live-Preview verschiedener Block-Typen
- Komplette UI-Neugestaltung

## 💡 Empfehlung

**Start mit Stufe 1 (Conditional Fields):**
1. Sofort umsetzbar mit aktuellem MBlock
2. Block-Typ als Select-Field 
3. JavaScript zeigt/versteckt entsprechende Felder
4. Proof of Concept für User-Testing

**Falls erfolgreich → Stufe 2:**
- Erweitere MBlock um Multi-Template Funktionalität
- Bessere UX mit modalen Block-Typ-Auswahlen
- Template-System ausbauen

## 🔗 Integration mit bloecks

Das Multi-Template System würde perfekt mit bloecks harmonieren:
- **Toast Notifications:** "Neuer Text-Block hinzugefügt!"
- **Smooth Scrolling:** Zu neuem Block scrollen
- **Drag & Drop:** Verschiedene Block-Typen sortieren
- **UI Components:** Modale für Block-Typ-Auswahl

---

**Fazit:** Sehr innovatives Konzept! Würde MBlock zu einem echten "Page Builder" machen. 🚀
