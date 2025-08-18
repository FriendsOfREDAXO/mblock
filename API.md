# MBlock API-Dokumentation

## Hauptklassen

### MBlock
- Initialisiert einen MBlock-Container
- Wichtige Methoden:
  - `__construct($type, $value)`
  - `parse()`

### MBlockNested
- Unterstützt verschachtelte Blöcke
- Methoden:
  - `parseNested()`

### MBlockElement
- Datenobjekt für einzelne Elemente

### MBlockItem
- Datenobjekt für einzelne Items

## Wichtige Methoden

```php
$mblock = new MBlock('mform', $value);
$html = $mblock->parse();
```

## Erweiterungen
- Eigene Themes: Ablage unter `data/templates/`
- Eigene Replacer: Siehe `lib/MBlock/Replacer/`

## Weitere Infos
- [README](index.php?page=mblock/readme)
- [Demo & Beispiele](index.php?page=mblock/overview)
