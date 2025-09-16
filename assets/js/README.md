# MBlock JavaScript Modular Architecture

This document describes the new modular architecture for MBlock JavaScript files.

## Overview

The MBlock JavaScript has been refactored from a single monolithic file (3,472 lines) into 5 focused, maintainable modules:

## Module Structure

### 1. `mblock-core.js` - Core Functionality
**Dependencies:** None  
**Size:** ~13,7KB

Contains:
- Core utilities (`MBlockUtils`)
- Basic validation and helper functions
- Message handling (`mblock_show_message`, `MBLOCK_TOAST`)
- Translation functions (`mblock_get_text`)
- Element validation (`mblock_validate_element`)
- Smooth scrolling utilities

### 2. `widgets.js` - Widget-Specific Code  
**Dependencies:** mblock-core.js  
**Size:** ~35,7KB

Contains:
- REDAXO Media widget handling (`MBlockWidgets.media`)
- REDAXO Link widget handling (`MBlockWidgets.link`)
- Selectpicker utilities (`MBlockWidgets.selectpicker`)
- Widget reinitialization for new blocks
- REX field processing and AJAX functions
- REX_LINK article name fetching

### 3. `addonfixes.js` - Addon Compatibility Fixes
**Dependencies:** mblock-core.js  
**Size:** ~14,2KB  

Contains:
- **GridBlock compatibility utilities**
- **CKEditor5 specific handling**
- Nested MBlock initialization fixes
- CKEditor content capture and restoration
- GridBlock widget reinitialization

### 4. `mblock-management.js` - DOM Operations & Sortable  
**Dependencies:** mblock-core.js, widgets.js, addonfixes.js  
**Size:** ~38,2KB

Contains:
- Sortable management (`MBlockSortable`)
- Item manipulation (add, remove, move)
- Reindexing and form element handling
- REX field handling during reindex
- Bootstrap tab/accordion reindexing
- Event handler binding

### 5. `mblock-features.js` - Advanced Features
**Dependencies:** All previous modules  
**Size:** ~31,1KB

Contains:
- Copy/Paste functionality (`MBlockClipboard`)
- Online/Offline toggle (`MBlockOnlineToggle`) 
- Advanced features and toolbar functionality
- Storage management (Session/Local Storage)
- Module type compatibility checking

## Build System

### Development Workflow

1. **Edit individual modules** in `assets/js/` directory
2. **Run build command** to generate combined files:
   ```bash
   node build.js
   ```
3. **Generated files:**
   - `assets/mblock.js` - Combined development version
   - `assets/mblock.min.js` - Minified production version
   - `assets/mblock.min.js.map` - Source map

### Build Script Features

- **Automatic dependency resolution** - modules loaded in correct order
- **Module combination** - all modules merged into single file
- **Minification** - production-ready compressed version
- **Source maps** - for debugging minified code
- **Build statistics** - shows compression rates and module info

### Build Output Example
```
🔧 MBlock Modular Build Script gestartet...
📄 Source modules: 5
✅ Lade Modul 1/5: mblock-core.js (13747 Zeichen)
✅ Lade Modul 2/5: widgets.js (35734 Zeichen)  
✅ Lade Modul 3/5: addonfixes.js (14182 Zeichen)
✅ Lade Modul 4/5: mblock-management.js (38160 Zeichen)
✅ Lade Modul 5/5: mblock-features.js (31052 Zeichen)
✅ Module kombiniert: 133551 Zeichen
✅ Kombinierte Datei erstellt
✅ Minifizierte Datei erstellt
📈 Statistiken:
   Module: 5
   Kombiniert: 133551 Zeichen
   Minifiziert: 48737 Zeichen
   Ersparnis: 63.5%
🎉 Modular Build erfolgreich!
```

## Key Benefits

### 1. Better Maintainability
- **Separation of concerns** - each module has a specific purpose
- **Easier debugging** - issues can be isolated to specific modules
- **Focused development** - work on specific functionality without distractions

### 2. Improved Organization
- **GridBlock fixes** isolated in `addonfixes.js`
- **Widget code** centralized in `widgets.js`
- **Core functionality** separated from features
- **Clear dependencies** between modules

### 3. Development Benefits
- **Individual testing** - modules can be tested separately
- **Selective loading** - modules can be loaded conditionally if needed
- **Team collaboration** - different developers can work on different modules
- **Code reuse** - modules can be shared between projects

## File Locations

```
assets/
├── js/                          # Modular source files (edit these)
│   ├── mblock-core.js          # Core functionality
│   ├── widgets.js              # Widget handling
│   ├── addonfixes.js           # GridBlock & CKE5 fixes  
│   ├── mblock-management.js    # DOM operations
│   └── mblock-features.js      # Advanced features
├── mblock.js                   # Generated combined file
├── mblock.min.js              # Generated minified file
└── mblock.min.js.map          # Generated source map
```

## Development Guidelines

### DO ✅
- Edit files in `assets/js/` directory only
- Run `node build.js` after making changes
- Test functionality after each build
- Keep module dependencies minimal and clear
- Document any new functions or significant changes

### DON'T ❌ 
- Edit `assets/mblock.js` directly (auto-generated)
- Edit `assets/mblock.min.js` directly (auto-generated)
- Add circular dependencies between modules
- Mix unrelated functionality in the same module

## Module Dependencies

```
mblock-core.js
    ↓
widgets.js ← addonfixes.js
    ↓           ↓
mblock-management.js
    ↓
mblock-features.js
```

## Backward Compatibility

The modular architecture maintains **100% backward compatibility**:
- All existing function names preserved
- Same API surface area
- Generated combined file identical in functionality
- No breaking changes for existing implementations

## Future Enhancements

This modular structure enables:
- **Conditional loading** - load only needed modules
- **Plugin architecture** - easier to add new modules
- **Testing improvements** - module-specific unit tests
- **Documentation generation** - per-module documentation
- **Performance optimization** - tree-shaking unused code