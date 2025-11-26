#!/bin/bash

#
# MBlock JavaScript Build Script
# 
# Automatisierte Minification für mblock.js
# Erstellt mblock.min.js für Produktionseinsatz
#
# Usage: ./build.sh
#
# @author MBlock Development Team
# @version 1.0.0
#

set -e  # Exit on any error

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "🚀 MBlock Build Process gestartet"
echo "═══════════════════════════════════"

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

# Check if source file exists
SOURCE_FILE="../assets/mblock.js"
if [ ! -f "$SOURCE_FILE" ]; then
    echo "❌ Quelldatei nicht gefunden: $SOURCE_FILE"
    exit 1
fi

echo "📖 Quelldatei gefunden: $SOURCE_FILE"

# Get file size for statistics
SOURCE_SIZE=$(stat -f%z "$SOURCE_FILE" 2>/dev/null || stat -c%s "$SOURCE_FILE" 2>/dev/null || echo "unknown")
echo "📏 Quelldatei Größe: $([ "$SOURCE_SIZE" != "unknown" ] && echo "$((SOURCE_SIZE / 1024)) KB" || echo "unbekannt")"

echo "⚙️  Starte Minification von mblock.js..."
# Use the existing mblock.js as source
node minify.js

# Verify output
OUTPUT_FILE="../assets/mblock.min.js"
if [ -f "$OUTPUT_FILE" ]; then
    echo "✅ Build erfolgreich abgeschlossen!"
    echo ""
    echo "📁 Output Dateien:"
    ls -la "$OUTPUT_FILE"* 2>/dev/null || true
    echo ""
    echo "🎯 Nächste Schritte:"
    echo "   1. Teste mblock.min.js in deiner REDAXO-Installation"
    echo "   2. boot.php lädt automatisch mblock.min.js"
    echo "   3. Deploye in die Produktion"
else
    echo "❌ Build fehlgeschlagen - Output Datei nicht erstellt"
    exit 1
fi

echo ""
echo "🎉 MBlock Build Process abgeschlossen!"
