# MBlock - REDAXO Addon Development Guide

MBlock is a REDAXO PHP addon that enables creation of unlimited repeatable content blocks with drag-and-drop functionality, copy/paste features, and online/offline toggles. The codebase combines PHP backend logic with JavaScript frontend components.

Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

### Bootstrap Environment
- **PHP**: Version 8.3.6+ available (validate with `php --version`)
- **Node.js**: Version 20.19.5+ available (validate with `node --version`)
- **npm**: Version 10.8.2+ available (validate with `npm --version`)

### Build JavaScript Assets
The addon requires JavaScript build process for production-ready assets:

1. **Install build dependencies** (takes ~2 seconds):
   ```bash
   cd build/
   npm install
   ```

2. **Build JavaScript assets** (takes <1 second):
   ```bash
   # Option 1: Shell script (recommended)
   cd build/
   ./build.sh
   
   # Option 2: npm command
   cd build/
   npm run build
   
   # Option 3: Direct Node.js
   cd build/
   node minify.js
   ```

3. **Build output**:
   - Input: `assets/mblock.js` (~142 KB)
   - Output: `assets/mblock.min.js` (~45 KB, 68% compression)
   - Source Map: `assets/mblock.min.js.map` (~50 KB)

**TIMING**: Build process completes in under 1 second. No special timeout needed.

### Validate PHP Syntax
```bash
# Test key PHP files for syntax errors
php -l boot.php
php -l install.php
php -l update.php
php -l lib/MBlock/MBlock.php
```

### Development Workflow
1. **Edit PHP files**: Main logic in `lib/MBlock/` directory
2. **Edit JavaScript**: Source file is `assets/mblock.js`
3. **Build after JS changes**: Run build process (see above)
4. **Test syntax**: Use `php -l` for PHP files

## Validation

### CRITICAL: No Traditional Testing Infrastructure
- **No PHPUnit**: No PHP unit testing framework present
- **No Composer**: No dependency management for PHP packages
- **No JavaScript testing**: No Jest, Mocha, or similar frameworks
- **No linting tools**: No ESLint, PHPStan, or PHP_CodeSniffer

### Manual Validation Requirements
Since this is a REDAXO addon, validation must be done through **real REDAXO installation testing**:

1. **Install in REDAXO**: Place addon in REDAXO's `src/addons/mblock/` directory
2. **Activate addon**: Enable through REDAXO backend
3. **Test core scenarios**:
   - Create a module with MBlock functionality
   - Add/remove content blocks via drag-and-drop
   - Test copy/paste functionality between blocks  
   - Test online/offline toggle functionality
   - Verify JavaScript assets load correctly

### Build Validation
Always validate builds work correctly:
```bash
cd build/
./build.sh
# Verify output files exist and have reasonable sizes:
ls -la ../assets/mblock.min.js ../assets/mblock.min.js.map
```

### JavaScript Asset Loading
The system automatically loads optimized assets based on environment:
- **Production**: `mblock.min.js` (minified)
- **Development**: `mblock.js` (source) 
- **Asset management**: Handled by `boot.php`

## Repository Structure

### Key Directories
```
/home/runner/work/mblock/mblock/
├── .github/                    # GitHub workflows and config
├── assets/                     # Frontend assets
│   ├── mblock.js              # JavaScript source (EDIT HERE)
│   ├── mblock.min.js          # Minified production version (AUTO-GENERATED)
│   ├── mblock.min.js.map      # Source map (AUTO-GENERATED)
│   ├── mblock.css             # Stylesheet
│   └── sortable.min.js        # Drag-and-drop library
├── build/                      # Build system
│   ├── build.sh               # Shell script for building
│   ├── minify.js              # Node.js minification script
│   ├── package.json           # npm dependencies
│   └── README.md              # Build documentation
├── lib/                        # PHP backend logic
│   └── MBlock/                # Main addon classes
│       ├── MBlock.php         # Core functionality
│       ├── Handler/           # Request handlers
│       ├── Parser/            # Content parsers
│       ├── Utils/             # Utility classes
│       └── ...
├── pages/                      # REDAXO backend pages
│   ├── examples/              # Code examples for documentation
│   ├── demo.demo_mform.php    # MForm integration examples
│   ├── settings.php           # Addon configuration
│   └── ...
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
- **`assets/mblock.js`**: JavaScript source file (EDIT THIS, NOT .min.js)
- **`build/build.sh`**: Primary build script for JavaScript minification

## Common Tasks

### After Making JavaScript Changes
```bash
cd build/
./build.sh
# Verify build completed successfully
ls -la ../assets/mblock.min.js*
```

### Adding New PHP Features
1. **Edit classes** in `lib/MBlock/` directory
2. **Test syntax**: `php -l lib/MBlock/YourClass.php`  
3. **Update bootstrap** if needed: `boot.php`
4. **Document changes**: Update relevant files in `pages/examples/`

### Template Development
Templates are located in `data/templates/` and managed via:
- **Selection**: Backend settings page (`pages/settings.php`)
- **CSS Management**: `lib/MBlock/Utils/TemplateManager.php`
- **Available templates**: Automatically discovered from data directory

### Debugging
- **PHP errors**: Check REDAXO error logs
- **JavaScript errors**: Browser developer console
- **Source maps**: Available for debugging minified JavaScript
- **Asset loading**: Verify in browser network tab

## REDAXO Integration

### Core Requirements
- **REDAXO**: Version ^5.18.0 required
- **Optional dependencies**: 
  - `bloecks` addon ^5.2.0 (for enhanced drag-and-drop)
  - `mform` addon (for advanced form building)

### Installation Process
1. **Copy addon**: Place in REDAXO's `src/addons/mblock/` directory
2. **Install**: Execute `install.php` (sets default configurations)
3. **Activate**: Enable through REDAXO backend
4. **Configure**: Access settings via `pages/settings.php`

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

## Known Limitations

### Testing Constraints
- **No automated tests**: Manual testing in REDAXO required
- **No PHP testing framework**: Cannot run PHPUnit tests  
- **No JavaScript tests**: No Jest or similar frameworks
- **REDAXO dependency**: Cannot test addon functionality outside REDAXO environment

### Build System Notes
- **Single file output**: JavaScript combines into one minified file
- **No hot reloading**: Must rebuild after JavaScript changes
- **Node.js dependency**: Build process requires Node.js 14+

### Development Environment
- **REDAXO required**: Full functionality testing requires REDAXO installation
- **Backend only**: Frontend testing requires REDAXO module integration
- **Manual validation**: No automated validation of addon functionality

## Critical Reminders

1. **NEVER edit `mblock.min.js` directly** - Always edit `mblock.js` and rebuild
2. **Always rebuild** after JavaScript changes using `cd build/ && ./build.sh`  
3. **Test PHP syntax** after changes using `php -l filename.php`
4. **Manual testing required** - No automated test suite available
5. **REDAXO installation needed** for full functionality validation
6. **Build dependencies excluded** - `build/node_modules/` is in .gitignore