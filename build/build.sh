#!/bin/bash

#
# MBlock Modular Build Script
#
# Combines modular source files and minifies for production
# Uses new modular build system with separate core, management, and features modules
#
# Usage: ./build.sh
#
# @author MBlock Development Team
# @version 2.0.0
#

set -e  # Exit on any error

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "🚀 MBlock Modular Build Process gestartet"
echo "════════════════════════════════════════════"

# Check if Node.js is available
if ! command -v node &> /dev/null; then
    echo "❌ Node.js ist nicht installiert!"
    echo "💡 Installiere Node.js von: https://nodejs.org/"
    exit 1
fi

echo "✅ Node.js gefunden: $(node --version)"

# Check if package.json exists
if [ ! -f "package.json" ]; then
    echo "❌ package.json nicht gefunden!"
    exit 1
fi

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "📦 Installiere Dependencies..."
    npm install
    echo "✅ Dependencies installiert"
else
    echo "✅ Dependencies bereits vorhanden"
fi

# Check if source modules exist
MODULES=("src/mblock-core.js" "src/mblock-management.js" "src/mblock-features.js")
for module in "${MODULES[@]}"; do
    if [ ! -f "$module" ]; then
        echo "❌ Modul nicht gefunden: $module"
        exit 1
    fi
done

echo "✅ Alle Module gefunden"

# Get source file sizes for statistics
echo "📊 Modul-Größen:"
for module in "${MODULES[@]}"; do
    if [ -f "$module" ]; then
        SIZE=$(stat -f%z "$module" 2>/dev/null || stat -c%s "$module" 2>/dev/null || echo "unknown")
        if [ "$SIZE" != "unknown" ]; then
            echo "   $module: $((SIZE / 1024)) KB"
        fi
    fi
done

echo ""
echo "🔧 Kombiniere Module..."
# Use the new modular build system
node build-modules.js

# Verify output
OUTPUT_FILE="../assets/mblock.js"
if [ -f "$OUTPUT_FILE" ]; then
    echo "✅ Kombination erfolgreich abgeschlossen!"
    ls -la "$OUTPUT_FILE"
else
    echo "❌ Kombination fehlgeschlagen - Output Datei nicht erstellt"
    exit 1
fi

echo ""
echo "🎯 Nächste Schritte:"
echo "   1. Teste mblock.js in deiner REDAXO-Installation"
echo "   2. Führe 'npm run minify' aus für Produktions-Build"
echo "   3. Oder verwende 'npm run full-build' für alles zusammen"
echo ""
echo "💡 Entwicklungs-Tipps:"
echo "   • Bearbeite die Module in src/ (nicht die kombinierte Datei)"
echo "   • Verwende 'npm run build:watch' für automatischen Rebuild"
echo "   • Core-Änderungen erfordern meist Neustart des Browsers"

echo ""
echo "🎉 MBlock Modular Build Process abgeschlossen!"
