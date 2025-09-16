#!/usr/bin/env node

/**
 * MBlock Production Preprocessor
 *
 * Removes console.log statements and other development code from source files
 * before building the production bundle
 *
 * Usage: node build/preprocess.js
 *
 * @author MBlock Development Team
 * @version 1.0.0
 */

const fs = require('fs');
const path = require('path');

// Configuration
const sourceDir = path.join(__dirname, 'src');
const modules = [
    'mblock-core.js',
    'mblock-management.js',
    'mblock-features.js'
];

/**
 * Remove console.log statements from code
 */
function removeConsoleLogs(code) {
    // Remove console.log, console.debug, console.info but keep console.error, console.warn
    const patterns = [
        /\s*console\.log\([^;]*\);\s*/g,
        /\s*console\.debug\([^;]*\);\s*/g,
        /\s*console\.info\([^;]*\);\s*/g,
        /\s*console\.trace\([^;]*\);\s*/g
    ];

    let processedCode = code;
    patterns.forEach(pattern => {
        processedCode = processedCode.replace(pattern, '');
    });

    return processedCode;
}

/**
 * Remove empty lines that were left after console.log removal
 */
function cleanEmptyLines(code) {
    return code
        .split('\n')
        .filter((line, index, arr) => {
            // Remove lines that are empty or only contain whitespace
            if (line.trim() === '') {
                // Keep empty lines if they separate logical blocks (after comments, braces, etc.)
                const prevLine = arr[index - 1] || '';
                const nextLine = arr[index + 1] || '';

                // Keep if previous line ends with comment, brace, or is empty
                if (prevLine.trim().endsWith('*/') ||
                    prevLine.trim().endsWith('{') ||
                    prevLine.trim().endsWith('}') ||
                    prevLine.trim() === '') {
                    return true;
                }

                // Keep if next line starts with comment or brace
                if (nextLine.trim().startsWith('//') ||
                    nextLine.trim().startsWith('/*') ||
                    nextLine.trim().startsWith('{') ||
                    nextLine.trim().startsWith('}')) {
                    return true;
                }

                return false;
            }
            return true;
        })
        .join('\n');
}

/**
 * Process a single module file
 */
function processModule(modulePath) {
    try {
        console.log(`🔧 Processing: ${path.basename(modulePath)}`);

        const originalCode = fs.readFileSync(modulePath, 'utf8');
        const originalSize = Buffer.byteLength(originalCode, 'utf8');

        // Remove console statements
        let processedCode = removeConsoleLogs(originalCode);

        // Clean up empty lines
        processedCode = cleanEmptyLines(processedCode);

        const processedSize = Buffer.byteLength(processedCode, 'utf8');
        const savings = originalSize - processedSize;
        const savingsPercent = ((savings / originalSize) * 100).toFixed(2);

        // Write back the processed file
        fs.writeFileSync(modulePath, processedCode, 'utf8');

        console.log(`   📏 Size: ${formatBytes(originalSize)} → ${formatBytes(processedSize)} (${savingsPercent}% reduction)`);

        return { originalSize, processedSize, savings };

    } catch (error) {
        console.error(`❌ Error processing ${modulePath}:`, error.message);
        return null;
    }
}

/**
 * Format bytes to human readable format
 */
function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Main preprocessing function
 */
function preprocess() {
    try {
        console.log('🔧 MBlock Production Preprocessing gestartet...\n');

        let totalOriginalSize = 0;
        let totalProcessedSize = 0;
        let totalSavings = 0;

        for (const module of modules) {
            const modulePath = path.join(sourceDir, module);

            if (!fs.existsSync(modulePath)) {
                console.warn(`⚠️  Module nicht gefunden: ${module}`);
                continue;
            }

            const result = processModule(modulePath);
            if (result) {
                totalOriginalSize += result.originalSize;
                totalProcessedSize += result.processedSize;
                totalSavings += result.savings;
            }
        }

        const totalSavingsPercent = ((totalSavings / totalOriginalSize) * 100).toFixed(2);

        console.log('\n📊 Preprocessing Zusammenfassung:');
        console.log('─'.repeat(50));
        console.log(`📏 Gesamt Original:     ${formatBytes(totalOriginalSize)}`);
        console.log(`🗜️  Gesamt Processed:   ${formatBytes(totalProcessedSize)}`);
        console.log(`💾 Ersparnis:           ${formatBytes(totalSavings)} (${totalSavingsPercent}%)`);
        console.log('─'.repeat(50));

        console.log('\n✅ Preprocessing erfolgreich abgeschlossen!');
        console.log('💡 Die Quelldateien wurden für die Produktion optimiert');

    } catch (error) {
        console.error('❌ Fehler beim Preprocessing:', error.message);
        process.exit(1);
    }
}

// Run preprocessing if called directly
if (require.main === module) {
    preprocess();
}

module.exports = { preprocess, removeConsoleLogs, cleanEmptyLines };
