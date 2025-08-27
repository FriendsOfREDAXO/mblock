#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Terser für Minification
let terser;
try {
    terser = require('terser');
} catch (e) {
    console.log('📦 Installiere Terser...');
    require('child_process').execSync('npm install terser --no-save', { stdio: 'inherit' });
    terser = require('terser');
}

const sourceFile = path.join(__dirname, 'assets', 'mblock.js');
const targetFile = path.join(__dirname, 'assets', 'mblock.min.js');

console.log('🔧 MBlock Build Script gestartet...');
console.log('📄 Source:', sourceFile);
console.log('📄 Target:', targetFile);

if (!fs.existsSync(sourceFile)) {
    console.error('❌ Source-Datei nicht gefunden:', sourceFile);
    process.exit(1);
}

const sourceCode = fs.readFileSync(sourceFile, 'utf8');
console.log('✅ Source-Code gelesen:', sourceCode.length, 'Zeichen');

const options = {
    compress: {
        dead_code: true,
        drop_console: false,
        passes: 2
    },
    mangle: {
        reserved: ['$', 'jQuery', 'MBlockClipboard', 'openLinkMap', 'deleteREXLink']
    },
    format: {
        comments: false
    },
    sourceMap: true
};

console.log('⚙️ Minifiziere Code...');

terser.minify(sourceCode, options).then(result => {
    if (result.error) {
        console.error('❌ Minification Fehler:', result.error);
        process.exit(1);
    }
    
    fs.writeFileSync(targetFile, result.code);
    console.log('✅ Minifizierte Datei erstellt:', targetFile);
    
    if (result.map) {
        fs.writeFileSync(targetFile + '.map', result.map);
        console.log('🗺️ Source Map erstellt');
    }
    
    const originalSize = sourceCode.length;
    const minifiedSize = result.code.length;
    const savings = ((originalSize - minifiedSize) / originalSize * 100).toFixed(1);
    
    console.log('\n📈 Statistiken:');
    console.log('   Original:', originalSize, 'Zeichen');
    console.log('   Minifiziert:', minifiedSize, 'Zeichen');
    console.log('   Ersparnis:', savings + '%');
    console.log('\n🎉 Build erfolgreich!');
    
}).catch(error => {
    console.error('❌ Build Fehler:', error);
    process.exit(1);
});
