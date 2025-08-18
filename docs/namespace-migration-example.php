<?php
/**
 * MBlock 4.0 - Namespace Migration Example
 * 
 * This example demonstrates how to use both the old compatibility syntax
 * and the new namespace syntax for MBlock 4.0
 * 
 * @author https://github.com/FriendsOfREDAXO
 * @package redaxo5
 * @license MIT
 */

// =============================================================================
// OLD SYNTAX (still works for backward compatibility)
// =============================================================================

// This will still work thanks to the compatibility class
$oldStyleMBlock = MBlock::show(1, $mform->show(), [
    'min' => 1,
    'max' => 10
]);

// Old static method calls still work
$oldData = MBlock::getOnlineDataArray("REX_MBLOCK[1]");

// =============================================================================
// NEW NAMESPACE SYNTAX (recommended for new projects)
// =============================================================================

// Import the namespaced class
use FriendsOfRedaxo\MBlock\MBlock;

// Use the new namespace syntax  
$newStyleMBlock = MBlock::show(1, $mform->show(), [
    'min' => 1,
    'max' => 10
]);

// New static method calls with namespace
$newData = MBlock::getOnlineDataArray("REX_MBLOCK[1]");

// =============================================================================
// UTILITY CLASSES (only available with namespace)
// =============================================================================

use FriendsOfRedaxo\MBlock\Utils\MBlockJsonHelper;
use FriendsOfRedaxo\MBlock\Utils\MBlockSessionHelper;
use FriendsOfRedaxo\MBlock\Utils\MBlockSettingsHelper;

// JSON operations
$jsonData = MBlockJsonHelper::decodeMBlockData($jsonString);
$encodedJson = MBlockJsonHelper::encodeMBlockData($dataArray);

// Session management  
MBlockSessionHelper::initializeSession();
$currentCount = MBlockSessionHelper::getCurrentCount();

// Settings management
$settings = MBlockSettingsHelper::getSettings($configArray);

// =============================================================================
// COMPLETE MIGRATION EXAMPLE
// =============================================================================

/**
 * Before (MBlock 3.x):
 */
// $items = MBlock::show(1, $mform->show());
// $data = rex_var::toArray("REX_MBLOCK[1]");

/**
 * After (MBlock 4.0 - Recommended):
 */
use FriendsOfRedaxo\MBlock\MBlock;

// Enhanced MBlock with new features
$items = MBlock::show(1, $mform->show(), [
    'min' => 1,
    'max' => 20,
    'settings' => [
        'mediapool_token_check' => false
    ]
]);

// Enhanced data retrieval with filtering
$onlineData = MBlock::getOnlineDataArray("REX_MBLOCK[1]");
$filteredData = MBlock::filterByField($onlineData, 'status', 'active');
$sortedData = MBlock::sortByField($filteredData, 'date', 'desc');

// =============================================================================
// BENEFITS OF NEW NAMESPACE SYSTEM
// =============================================================================

/*
✅ ADVANTAGES:

1. **Backward Compatibility**: Old code continues to work
2. **Modern Structure**: Clean namespace organization
3. **Enhanced Features**: New utility classes and methods
4. **Better IDE Support**: Improved autocompletion and type hints
5. **Future-Proof**: Prepared for REDAXO 6.x and PHP 8.x+
6. **Conflict Prevention**: No more global class name conflicts

🚀 MIGRATION STRATEGY:

1. **Phase 1**: Update existing projects gradually
2. **Phase 2**: Use new namespace for new features
3. **Phase 3**: Full migration when convenient
4. **Phase 4**: Remove compatibility layer in v5.0

📚 NEW CLASSES AVAILABLE:

- FriendsOfRedaxo\MBlock\MBlock (main class)
- FriendsOfRedaxo\MBlock\Utils\MBlockJsonHelper (JSON operations)
- FriendsOfRedaxo\MBlock\Utils\MBlockSessionHelper (session management)
- FriendsOfRedaxo\MBlock\Utils\MBlockSettingsHelper (configuration)
- FriendsOfRedaxo\MBlock\Handler\MBlockValueHandler (value processing)
- FriendsOfRedaxo\MBlock\Parser\MBlockParser (template parsing)
- And many more...
*/

// =============================================================================
// RECOMMENDED MIGRATION PATH
// =============================================================================

/**
 * Step 1: Add namespace import to existing files
 */
// Add to top of file:
// use FriendsOfRedaxo\MBlock\MBlock;

/**
 * Step 2: Update MBlock::show() calls with new options
 */
// Old:
// echo MBlock::show(1, $mform->show());

// New:
// echo MBlock::show(1, $mform->show(), [
//     'min' => 1,
//     'max' => 10,
//     'settings' => ['mediapool_token_check' => false]
// ]);

/**
 * Step 3: Replace manual data processing with new methods
 */
// Old:
// $data = rex_var::toArray("REX_MBLOCK[1]");
// $filtered = array_filter($data, function($item) {
//     return $item['mblock_offline'] != '1';
// });

// New:
// $filtered = MBlock::getOnlineDataArray("REX_MBLOCK[1]");

/**
 * Step 4: Use new utility classes for advanced features
 */
// Enhanced JSON operations
// $json = MBlockJsonHelper::encodeMBlockData($data);
// $decoded = MBlockJsonHelper::decodeMBlockData($json);

// Schema.org generation
// $schema = MBlock::generateSchema($items, 'Article', [
//     'headline' => 'title',
//     'description' => 'content'
// ]);

?>
