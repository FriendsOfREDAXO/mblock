#!/usr/bin/env node

/**
 * MBlock JavaScript Minification Script
 * 
 * Minifies mblock.js using Terser for production use
 * Creates mblock.min.js with source maps
 * 
 * Usage: node minify.js
 * 
 * @author MBlock Development Team
 * @version 1.0.0
 */

const fs = require('fs');
const path = require('path');
const { minify } = require('terser');

// Configuration - Support environment variable for dynamic source file
const sourceFile = process.env.MBLOCK_SOURCE_FILE || '../assets/mblock.js';
const outputFile = '../assets/mblock.min.js';
const sourceMapFile = '../assets/mblock.min.js.map';

// Terser options for optimal minification
const terserOptions = {
    compress: {
        // Remove console.log statements but keep console.error/warn
        pure_funcs: ['console.log'],
        drop_console: false,
        drop_debugger: true,
        passes: 2,
        unsafe_arrows: true,
        unsafe_methods: true,
        hoist_funs: true,
        hoist_vars: true,
        if_return: true,
        join_vars: true,
        collapse_vars: true,
        reduce_vars: true,
        warnings: false,
        negate_iife: true,
        pure_getters: true,
        keep_fargs: false,
        keep_fnames: false,
        keep_infinity: true
    },
    mangle: {
        // Preserve specific function names that might be called externally
        reserved: [
            'mblock_init',
            'mblock_init_sort', 
            'mblock_sort',
            'mblock_add',
            'MBlockClipboard',
            'MBlockOnlineToggle',
            'mblock_smooth_scroll_to_element'
        ],
        toplevel: true,
        eval: true,
        keep_fnames: false,
        safari10: true
    },
    format: {
        comments: false, // Remove all comments
        semicolons: true,
        beautify: false,
        ascii_only: true
    },
    sourceMap: {
        filename: path.basename(outputFile),
        url: path.basename(sourceMapFile)
    },
    toplevel: true,
    ie8: false,
    safari10: true,
    keep_classnames: false,
    keep_fnames: false
};

/**
 * Main minification function
 */
async function minifyMBlock() {
    try {
        console.log('🔄 MBlock JavaScript Minification gestartet...\n');
        
        // Read source file
        const sourceFilePath = path.resolve(__dirname, sourceFile);
        console.log(`📖 Lese Quelldatei: ${sourceFilePath}`);
        
        if (!fs.existsSync(sourceFilePath)) {
            throw new Error(`Quelldatei nicht gefunden: ${sourceFilePath}`);
        }
        
        const sourceCode = fs.readFileSync(sourceFilePath, 'utf8');
        const originalSize = Buffer.byteLength(sourceCode, 'utf8');
        console.log(`📏 Originalgröße: ${formatBytes(originalSize)}`);
        
        // Perform minification
        console.log('⚙️  Minification wird durchgeführt...');
        const startTime = Date.now();
        
        const result = await minify(sourceCode, terserOptions);
        
        if (result.error) {
            throw new Error(`Minification Fehler: ${result.error}`);
        }
        
        const endTime = Date.now();
        const minifiedSize = Buffer.byteLength(result.code, 'utf8');
        
        // Write minified file
        const outputFilePath = path.resolve(__dirname, outputFile);
        fs.writeFileSync(outputFilePath, result.code, 'utf8');
        console.log(`💾 Minified Datei erstellt: ${outputFilePath}`);
        
        // Write source map
        if (result.map) {
            const sourceMapFilePath = path.resolve(__dirname, sourceMapFile);
            fs.writeFileSync(sourceMapFilePath, result.map, 'utf8');
            console.log(`🗺️  Source Map erstellt: ${sourceMapFilePath}`);
        }
        
        // Statistics
        const compressionRatio = ((originalSize - minifiedSize) / originalSize * 100);
        
        console.log('\n📊 Minification Statistiken:');
        console.log('─'.repeat(40));
        console.log(`📏 Originalgröße:     ${formatBytes(originalSize)}`);
        console.log(`🗜️  Minified Größe:   ${formatBytes(minifiedSize)}`);
        console.log(`💾 Ersparnis:         ${formatBytes(originalSize - minifiedSize)} (${compressionRatio.toFixed(2)}%)`);
        console.log(`⏱️  Verarbeitungszeit: ${endTime - startTime}ms`);
        console.log('─'.repeat(40));
        
        console.log('\n✅ MBlock JavaScript erfolgreich minified!');
        console.log(`\n💡 Tipp: Verwende 'mblock.min.js' in der Produktion für bessere Performance`);
        
    } catch (error) {
        console.error('❌ Fehler bei der Minification:');
        console.error(error.message);
        process.exit(1);
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
 * Check if terser is available
 */
function checkDependencies() {
    try {
        require('terser');
        return true;
    } catch (error) {
        console.error('❌ Terser ist nicht installiert!');
        console.log('💡 Installiere es mit: npm install terser');
        return false;
    }
}

// Run minification if called directly
if (require.main === module) {
    if (checkDependencies()) {
        minifyMBlock();
    } else {
        process.exit(1);
    }
}

module.exports = { minifyMBlock, sourceFile, outputFile, sourceMapFile, terserOptions };
