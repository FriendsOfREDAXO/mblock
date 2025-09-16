# MBlock - REDAXO Addon Development Guide

MBlock is a REDAXO PHP addon that enables creation of unlimited repeatable content blocks with drag-and-drop functionality, copy/paste features, and online/offline toggles. The codebase combines PHP backend logic with JavaScript frontend components.

Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

### Environment Requirements
- **PHP**: Version 8.3.6+ (validate with `php --version`)
- **Node.js**: Version 20.19.5+ for JavaScript builds (validate with `node --version`)
- **REDAXO**: Version ^5.18.0 required for addon functionality

### Development Workflow
1. **Edit PHP files**: Main logic in `lib/MBlock/` directory
2. **Edit JavaScript**: Source file is `assets/mblock.js` (never edit `assets/mblock.min.js` directly)
3. **After JavaScript changes**: Run `cd build/ && ./build.sh` to create minified version
4. **Test PHP syntax**: Use `php -l filename.php` for validation

## Validation

### Testing Infrastructure
- **No PHPUnit**: No PHP unit testing framework present
- **No Composer**: No dependency management for PHP packages
- **No JavaScript testing**: No Jest, Mocha, or similar frameworks
- **No automated linting tools**: No ESLint, PHPStan, or PHP_CodeSniffer

### Manual Validation
Since this is a REDAXO addon, validation requires **real REDAXO installation testing**:

1. **Install in REDAXO**: Place addon in REDAXO's `src/addons/mblock/` directory
2. **Activate addon**: Enable through REDAXO backend
3. **Test functionality**: Create modules with MBlock, test drag-and-drop, copy/paste, online/offline toggles

### Syntax Validation
```bash
# Test PHP syntax for key files
php -l boot.php
php -l lib/MBlock/MBlock.php
```

## Repository Structure

### Key Directories
```
/home/runner/work/mblock/mblock/
├── .github/                    # GitHub workflows and config
├── assets/                     # Frontend assets
│   ├── mblock.js              # JavaScript source (EDIT HERE)
│   ├── mblock.min.js          # Minified version (AUTO-GENERATED)
│   ├── mblock.css             # Stylesheet
│   └── sortable.min.js        # Drag-and-drop library
├── lib/                        # PHP backend logic
│   └── MBlock/                # Main addon classes
│       ├── MBlock.php         # Core functionality
│       ├── Handler/           # Request handlers
│       ├── Parser/            # Content parsers
│       └── Utils/             # Utility classes
├── pages/                      # REDAXO backend pages
│   ├── examples/              # Code examples
│   └── settings.php           # Addon configuration
├── data/                       # Data templates and configs
├── lang/                       # Translation files (DE/EN)
├── boot.php                    # Addon bootstrap file
├── package.yml                # REDAXO addon manifest
├── install.php                # Installation script
└── update.php                  # Update script
```

### Important Files
- **`boot.php`**: Main addon bootstrap - loads assets and registers extensions
- **`package.yml`**: REDAXO addon configuration and requirements
- **`lib/MBlock/MBlock.php`**: Core PHP class with main API methods
- **`assets/mblock.js`**: JavaScript source file (edit this, not .min.js)

## Common Tasks

### Adding New PHP Features
1. **Edit classes** in `lib/MBlock/` directory
2. **Test syntax**: `php -l lib/MBlock/YourClass.php`
3. **Update bootstrap** if needed: `boot.php`
4. **Document changes**: Update relevant files in `pages/examples/`

### Template Development
Templates are located in `data/templates/` and managed via:
- **Selection**: Backend settings page (`pages/settings.php`)
- **CSS Management**: `lib/MBlock/Utils/TemplateManager.php`

### Debugging
- **PHP errors**: Check REDAXO error logs
- **JavaScript errors**: Browser developer console
- **Asset loading**: Verify in browser network tab

## REDAXO Integration

### Core Requirements
- **REDAXO**: Version ^5.18.0 required
- **Optional dependencies**: `bloecks` addon ^5.2.0, `mform` addon

### API Usage Examples
The addon provides both legacy and modern namespace-based APIs:

**Modern (Recommended)**:
```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");
echo MBlock::show(1, $form, ['min' => 1, 'max' => 5]);
```

**Legacy (Still Supported)**:
```php
<?php
$items = MBlock::getDataArray("REX_VALUE[1]");
echo MBlock::show(1, $form);
```

## Critical Reminders

1. **NEVER edit `mblock.min.js` directly** - Always edit `mblock.js` and rebuild
2. **Always rebuild** after JavaScript changes using `cd build/ && ./build.sh`
3. **Test PHP syntax** after changes using `php -l filename.php`
4. **Manual testing required** - No automated test suite available
5. **REDAXO installation needed** for full functionality validation