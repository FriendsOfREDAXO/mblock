# MBlock Custom Templates

Dieses Verzeichnis enthält benutzerdefinierte Templates für das MBlock AddOn.

## Template-System

Das MBlock AddOn verwendet ein zweistufiges Template-System:

- **Default Theme**: Wird aus dem Haupt-`templates/` Verzeichnis geladen
- **Custom Templates**: Werden aus diesem `data/templates/` Verzeichnis geladen

```
templates/default_theme/        (Standard-Template, Teil des AddOns)
├── mblock_element.ini
├── mblock_wrapper.ini
└── theme.css

data/templates/                 (Benutzerdefinierte Templates)
├── redaxo_blue/
│   ├── mblock_element.ini
│   ├── mblock_wrapper.ini
│   └── redaxo_blue.css
├── redaxo_akg/
│   ├── mblock_element.ini
│   ├── mblock_wrapper.ini
│   └── redaxo_akg.css
└── my_custom_theme/
    ├── mblock_element.ini
    ├── mblock_wrapper.ini
    └── my_custom_theme.css (Optional)
```

## Template-Dateien

### mblock_element.ini
Template für einzelne MBlock-Elemente. Verfügbare Platzhalter:
- `{content}` - Der Inhalt des Elements
- `{move_up_button}` - Button zum Nach-oben-Verschieben
- `{move_down_button}` - Button zum Nach-unten-Verschieben
- `{copy_button}` - Button zum Kopieren
- `{paste_button}` - Button zum Einfügen
- `{delete_button}` - Button zum Löschen

### mblock_wrapper.ini
Template für den MBlock-Container. Verfügbare Platzhalter:
- `{name}` - Name des MBlock-Felds
- `{elements}` - Alle MBlock-Elemente
- `{add_button}` - Button zum Hinzufügen neuer Elemente

## CSS-Dateien

Jedes Template kann eine eigene CSS-Datei enthalten. Diese muss den gleichen Namen wie der Template-Ordner haben (z.B. `modern_theme.css` für das `modern_theme` Template).

### CSS-Regeln:
1. Die CSS-Datei muss den gleichen Namen wie der Template-Ordner haben
2. Sie wird automatisch nach `assets/` kopiert, wenn das Template ausgewählt wird
3. Sie wird nach der Standard-CSS geladen, sodass Defaults überschrieben werden können
4. Wichtige CSS-Klassen:
   - `.mblock_wrapper` - Container für alle MBlock-Elemente
   - `.mblock_item` - Einzelnes MBlock-Element
   - `.mblock_add` - Container für den "Hinzufügen"-Button

## Template auswählen

1. Gehen Sie zu "AddOns" → "MBlock" → "Einstellungen"
2. Wählen Sie Ihr gewünschtes Template aus der Dropdown-Liste
3. Klicken Sie auf "Einstellungen speichern"
4. Das CSS wird automatisch kopiert und aktiviert

## Beispiel-Template

Das `modern_theme` Template zeigt, wie ein benutzerdefiniertes Template strukturiert sein kann:
- Moderne, abgerundete Boxen
- Farbverlauf-Buttons
- Hover-Effekte
- Responsive Layout

Kopieren Sie dieses Template als Ausgangspunkt für Ihre eigenen Anpassungen.
