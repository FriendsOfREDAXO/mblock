#!/bin/bash

# MBlock Deployment Script
# Erstellt eine bereinigte Version für die Veröffentlichung

set -e

echo "📦 MBlock Deployment Preparation"
echo "================================"

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# 1. Clean previous builds
log_info "Cleaning previous builds..."
npm run clean

# 2. Install dependencies (if needed)
if [ ! -d "node_modules" ]; then
    log_info "Installing dependencies..."
    npm install
fi

# 3. Build production version
log_info "Building production version..."
./build.sh production

# 4. Verify build
log_info "Verifying build quality..."

if [ ! -f "assets/dist/mblock.min.js" ]; then
    echo "❌ Build failed: mblock.min.js not found"
    exit 1
fi

# Check file size
SIZE=$(wc -c < "assets/dist/mblock.min.js" | tr -d ' ')
if [ "$SIZE" -lt 5000 ]; then
    log_warning "Build seems unusually small: ${SIZE} bytes"
fi

# Check for console.log
if grep -q "console.log" "assets/dist/mblock.min.js"; then
    echo "❌ Console.log statements found in production build!"
    exit 1
fi

# 5. Create deployment info
log_info "Creating deployment info..."
cat > "assets/dist/BUILD_INFO.txt" << EOF
MBlock Build Information
========================

Build Date: $(date)
Build Type: Production
File Size: ${SIZE} bytes
Git Hash: $(git rev-parse --short HEAD 2>/dev/null || echo "N/A")
Node Version: $(node --version)

Features:
- Minified JavaScript
- Debug statements removed  
- Optimized for production
- Source maps disabled

Files:
- mblock.min.js (Main bundle)
- mblock.css (Styles)
- mblock_smooth_scroll.js (Smooth scrolling)
- mblock_sortable.js (Sorting functionality)
EOF

# 6. Show summary
log_success "Deployment build completed!"
echo ""
echo "📊 Build Summary:"
echo "  • File size: ${SIZE} bytes"
echo "  • Debug logs: Removed"
echo "  • Minification: Enabled"
echo "  • Ready for deployment: ✅"
echo ""
echo "📁 Deployment files are in: assets/dist/"
echo ""
echo "🚀 You can now:"
echo "  • Upload assets/dist/ to your server"
echo "  • Include mblock.min.js instead of mblock.js"
echo "  • Deploy with confidence!"

log_success "Ready for deployment! 🎉"
