#!/bin/bash

# MBlock Build Script
# This script builds the MBlock addon for different environments

set -e

echo "🚀 MBlock Build System"
echo "====================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    log_error "npm is not installed. Please install Node.js and npm first."
    exit 1
fi

# Check if dependencies are installed
if [ ! -d "node_modules" ]; then
    log_info "Installing dependencies..."
    npm install
fi

# Parse command line arguments
MODE=${1:-"development"}

case $MODE in
    "production" | "prod")
        log_info "Building for PRODUCTION..."
        export NODE_ENV=production
        npm run build
        
        # Verify production build
        if [ -f "assets/dist/mblock.min.js" ]; then
            SIZE=$(wc -c < "assets/dist/mblock.min.js" | tr -d ' ')
            log_success "Production build completed! Size: ${SIZE} bytes"
            
            # Check if console.log was removed
            if grep -q "console.log" "assets/dist/mblock.min.js"; then
                log_warning "Console.log statements found in production build!"
            else
                log_success "All debug statements removed from production build"
            fi
        else
            log_error "Production build failed!"
            exit 1
        fi
        ;;
        
    "development" | "dev")
        log_info "Building for DEVELOPMENT..."
        export NODE_ENV=development
        npm run build
        
        if [ -f "assets/dist/mblock.min.js" ]; then
            SIZE=$(wc -c < "assets/dist/mblock.min.js" | tr -d ' ')
            log_success "Development build completed! Size: ${SIZE} bytes"
        else
            log_error "Development build failed!"
            exit 1
        fi
        ;;
        
    "watch")
        log_info "Starting watch mode for development..."
        npm run dev
        ;;
        
    "clean")
        log_info "Cleaning build artifacts..."
        npm run clean
        log_success "Clean completed!"
        ;;
        
    *)
        echo "Usage: $0 [production|development|watch|clean]"
        echo ""
        echo "Modes:"
        echo "  production   - Build minified version without debug logs"
        echo "  development  - Build unminified version with source maps"
        echo "  watch        - Start watch mode for development"
        echo "  clean        - Remove all build artifacts"
        echo ""
        echo "Default: development"
        exit 1
        ;;
esac

log_success "Build process completed! 🎉"
