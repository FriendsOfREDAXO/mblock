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
├── akg_skin/
│   ├── mblock_element.ini
│   ├── mblock_wrapper.ini
│   └── akg_skin.css
└── my_custom_theme/
    ├── mblock_element.ini
    ├── mblock_wrapper.ini
    └── my_custom_theme.css (Optional)
```

## Template-Dateien

### mblock_element.ini
Template für einzelne MBlock-Elemente. Verfügbare Platzhalter:
- `<mblock:form/>` - Der Formular-Inhalt des Elements
- `<mblock:index/>` - Index/Position des Elements
- `<mblock:offline_class/>` - CSS-Klasse für Offline-Status
- `<mblock:offline_button/>` - Button für Offline-Schaltung
- `<mblock:copy_paste_buttons/>` - Copy/Paste Buttons
- Einzelne Buttons:
  - `<button type="button" class="btn btn-default addme">` - Hinzufügen-Button
  - `<button type="button" class="btn btn-move moveup">` - Nach-oben-Button
  - `<button type="button" class="btn btn-move movedown">` - Nach-unten-Button
  - `<button type="button" class="btn btn-delete removeme">` - Löschen-Button

### mblock_wrapper.ini
Template für den MBlock-Container. Verfügbare Platzhalter:
- `<mblock:settings/>` - Data-Attribute und Einstellungen
- `<mblock:copy_paste_toolbar/>` - Copy/Paste Toolbar
- `<mblock:output/>` - Alle MBlock-Elemente
- Der Add-Button wird automatisch per JavaScript eingefügt

## Sprachlabels

In Templates können Sprachlabels mit doppelten geschweiften Klammern verwendet werden:
- `{{mblock_add_element}}` - "Element hinzufügen"
- `{{mblock_delete_confirm}}` - Löschen-Bestätigung
- etc.

## CSS-Dateien

Jedes Template kann eine eigene CSS-Datei enthalten. Diese muss den gleichen Namen wie der Template-Ordner haben (z.B. `akg_skin.css` für das `akg_skin` Template).

### CSS-Regeln:
1. Die CSS-Datei muss den gleichen Namen wie der Template-Ordner haben
2. Sie wird automatisch nach `assets/` kopiert, wenn das Template ausgewählt wird
3. Sie wird nach der Standard-CSS geladen, sodass Defaults überschrieben werden können
4. Wichtige CSS-Klassen:
   - `.mblock_wrapper` - Container für alle MBlock-Elemente
   - `.sortitem` - Einzelnes MBlock-Element
   - `.sorthandle` - Drag-Handle für Sortierung
   - `.removeadded` - Button-Container

## Template auswählen

1. Gehen Sie zu "AddOns" → "MBlock" → "Einstellungen"
2. Wählen Sie Ihr gewünschtes Template aus der Dropdown-Liste
3. Klicken Sie auf "Einstellungen speichern"
4. Das CSS wird automatisch kopiert und aktiviert

## Beispiel-Template

Das `akg_skin` Template zeigt, wie ein benutzerdefiniertes Template strukturiert sein kann:
- Bootstrap-Grid-Layout mit `row` und `col-md-*`
- Vertikale Button-Gruppe
- Angepasste CSS-Klassen für spezifisches Styling

Kopieren Sie dieses Template als Ausgangspunkt für Ihre eigenen Anpassungen.

## Migration von alten Templates

Alte Templates mit `{content}`, `{elements}`, `{add_button}` etc. müssen auf das neue System migriert werden:

**Alt:** `{elements}` → **Neu:** `<mblock:output/>`
**Alt:** `{add_button}` → **Neu:** Wird automatisch eingefügt
**Alt:** `{content}` → **Neu:** `<mblock:form/>`
**Alt:** `{move_up_button}` → **Neu:** `<button class="btn btn-move moveup">`
