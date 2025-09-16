/**
 * MBlock Core - Base functionality and utilities
 * 
 * Contains:
 * - Core utilities (MBlockUtils)
 * - Basic validation and helper functions
 * - Message handling
 * - Translation functions
 * - Element validation
 * 
 * @author joachim doerr
 * @version 2.0
 */

let mblock = '.mblock_wrapper';

// 🔧 Central Utility Object - Reduces redundancy and provides reusable functions
const MBlockUtils = {
    // Cached selectors for better performance
    selectors: {
        wrapper: '.mblock_wrapper',
        sortitem: '> div.sortitem',
        addme: '.addme',
        removeme: '.removeme',
        moveup: '.moveup',
        movedown: '.movedown',
        copyBtn: '.mblock-copy-btn',
        pasteBtn: '.mblock-paste-btn',
        onlineToggle: '.mblock-online-toggle',
        autoToggle: '.mblock-offline-toggle-btn'
    },

    // DOM manipulation helpers
    dom: {
        findElement(container, selector) {
            return container?.find ? container.find(selector) : $(selector);
        },

        safeRemove(element) {
            if (element?.length) {
                element.find('*').off('.mblock');
                element.off('.mblock');
                element.remove();
                return true;
            }
            return false;
        },

        createFromHTML(html) {
            return $($.parseHTML(html));
        }
    },

    // Event handling utilities
    events: {
        bindSafe(element, event, handler, namespace = '.mblock') {
            if (element?.length) {
                element.off(event + namespace).on(event + namespace, handler);
            }
        },

        cleanup(element, namespace = '.mblock') {
            if (mblock_validate_element(element) && element.jquery) {
                element.find('*').off(namespace);
                element.off(namespace);
            }
        }
    },

    // State management
    state: {
        isDisabled(element) {
            return element.prop('disabled') || element.hasClass('disabled');
        },

        toggleDisabled(element, disabled) {
            element.prop('disabled', disabled);
            element.toggleClass('disabled', disabled);
        }
    },

    // Nested MBlock utilities for GridBlock compatibility
    nested: {
        /**
         * Clean up duplicate elements in nested MBlocks
         * @param {jQuery} container - Container to clean
         */
        cleanupDuplicates(container) {
            try {
                if (!container || !container.length) return;
                
                container.find('.mblock_wrapper').each(function() {
                    const $nestedWrapper = $(this);
                    // Remove duplicate initialization
                    if ($nestedWrapper.data('mblock_initialized')) {
                        $nestedWrapper.removeData('mblock_initialized');
                    }
                });
            } catch (error) {
                console.error('MBlock: Error cleaning up nested duplicates:', error);
            }
        },

        /**
         * Initialize nested MBlocks safely
         * @param {jQuery} container - Container with nested MBlocks
         */
        initializeNested(container) {
            try {
                if (!container || !container.length) return;
                
                container.find('.mblock_wrapper').each(function() {
                    const $nestedWrapper = $(this);
                    if (!$nestedWrapper.data('mblock_initialized')) {
                        mblock_init($nestedWrapper);
                        $nestedWrapper.data('mblock_initialized', true);
                    }
                });
            } catch (error) {
                console.error('MBlock: Error initializing nested MBlocks:', error);
            }
        }
    },

    // Animation utilities
    animation: {
        addGlowEffect(element, className = 'mblock-copy-glow', duration = 1000) {
            if (element?.length) {
                element.addClass(className);
                setTimeout(() => element.removeClass(className), duration);
            }
        },

        flashEffect(element, className = 'mblock-dropped-flash', duration = 600) {
            if (element?.length) {
                element.addClass(className);
                setTimeout(() => element.removeClass(className), duration);
            }
        }
    },

    // Type checking utilities
    is: {
        validElement(element) {
            return mblock_validate_element(element);
        },

        rexField(id, type) {
            return id && id.indexOf(`REX_${type}_`) >= 0;
        },

        hiddenInput(element) {
            return element.attr('type') === 'hidden';
        }
    }
};

// 🔧 Helper function for improved error/warning feedback using bloecks
function mblock_show_message(message, type = 'warning', duration = 5000) {
    // Try to use bloecks toast system first with specific mblock method
    if (typeof BLOECKS !== 'undefined' && BLOECKS.fireMBlockToast) {
        BLOECKS.fireMBlockToast(message, type, duration);
    } else if (typeof BLOECKS !== 'undefined' && BLOECKS.showToast) {
        // Fallback to general showToast method
        BLOECKS.showToast(message, type, duration);
    } else {
        // Use internal toast fallback if available
        if (typeof MBLOCK_TOAST !== 'undefined' && MBLOCK_TOAST.show) {
            MBLOCK_TOAST.show(message, type, duration);
        }

        // Fallback to console
        if (type === 'error' || type === 'danger') {
            console.error('MBlock:', message);
        } else {
            console.warn('MBlock:', message);
        }
    }
}

// Simple namespaced toast fallback used when BLOECKS toast isn't available
const MBLOCK_TOAST = (function () {
    let counter = 0;
    let container = null;

    function createContainer() {
        if (container) return container;
        container = document.createElement('div');
        container.className = 'mblock-toast-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = 99999;
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '8px';
        document.body.appendChild(container);
        return container;
    }

    function show(message, type = 'info', duration = 4000) {
        try {
            const cont = createContainer();
            const id = 'mblock-toast-' + (++counter);
            const toast = document.createElement('div');
            toast.id = id;
            toast.className = 'mblock-toast mblock-toast-' + type;
            toast.style.minWidth = '180px';
            toast.style.maxWidth = '420px';
            toast.style.padding = '10px 14px';
            toast.style.borderRadius = '4px';
            toast.style.boxShadow = '0 2px 8px rgba(0,0,0,0.12)';
            toast.style.background = type === 'error' || type === 'danger' ? '#f8d7da' : (type === 'success' ? '#d4edda' : '#fff3cd');
            toast.style.color = '#222';
            toast.style.border = '1px solid rgba(0,0,0,0.06)';
            toast.style.fontSize = '13px';
            toast.innerText = message;
            cont.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                }
            }, Math.max(50, duration - 250));

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, duration + 100);
            return id;
        } catch (e) {
            console.warn('MBlock: Toast fallback failed', e);
            return false;
        }
    }

    return { show };
})();

// 🌍 Helper function to get translated text for toast messages
function mblock_get_text(key, fallback = '') {
    // Primary: Use server-provided translations (via boot.php)
    if (typeof rex !== 'undefined' && rex.mblock_i18n && rex.mblock_i18n[key.replace('mblock_toast_', '')]) {
        return rex.mblock_i18n[key.replace('mblock_toast_', '')];
    }
    
    // Secondary: Try rex_i18n if available
    if (typeof rex !== 'undefined' && rex.i18n) {
        const text = rex.i18n.msg(key);
        return text !== key ? text : fallback; // Return fallback if key not found
    }
    
    // Fallback to simple translations if rex is not available
    const translations = {
        'mblock_toast_copy_success': {
            'de': 'Block erfolgreich kopiert!',
            'en': 'Block copied successfully!',
            'es': '¡Bloque copiado con éxito!',
            'pt': 'Bloco copiado com sucesso!',
            'sv': 'Block kopierat framgångsrikt!',
            'nl': 'Blok succesvol gekopieerd!'
        },
        'mblock_toast_paste_success': {
            'de': 'Block erfolgreich eingefügt!',
            'en': 'Block pasted successfully!',
            'es': '¡Bloque pegado con éxito!',
            'pt': 'Bloco colado com sucesso!',
            'sv': 'Block inklistrat framgångsrikt!',
            'nl': 'Blok succesvol geplakt!'
        },
        'mblock_toast_clipboard_empty': {
            'de': 'Keine Daten in der Zwischenablage',
            'en': 'No data in clipboard',
            'es': 'No hay datos en el portapapeles',
            'pt': 'Nenhum dado na área de transferência',
            'sv': 'Inga data i urklipp',
            'nl': 'Geen gegevens in klembord'
        },
        'mblock_toast_module_type_mismatch': {
            'de': 'Modultyp stimmt nicht überein',
            'en': 'Module type mismatch',
            'es': 'No coincide el tipo de módulo',
            'pt': 'Tipo de módulo não corresponde',
            'sv': 'Modultyp matchar inte',
            'nl': 'Moduletype komt niet overeen'
        }
    };
    
    // Get browser language or default to German
    const lang = (navigator.language || 'de').substring(0, 2);
    const langData = translations[key];
    
    if (langData && langData[lang]) {
        return langData[lang];
    } else if (langData && langData['de']) {
        return langData['de']; // Fallback to German
    }
    
    return fallback;
}

/**
 * Utility-Funktion zur sicheren jQuery-Element-Validierung
 * @param {jQuery|HTMLElement|string} element - Element zum Validieren
 * @returns {boolean} Ob Element gültig ist
 */
function mblock_validate_element(element) {
    try {
        if (!element) return false;
        
        // jQuery-Objekt prüfen
        if (element.jquery) {
            return element.length > 0 && typeof element.data === 'function';
        }
        
        // DOM-Element prüfen
        if (element.nodeType) {
            return true;
        }
        
        // String-Selector prüfen
        if (typeof element === 'string') {
            return element.length > 0;
        }
        
        return false;
    } catch (error) {
        console.error('MBlock: Fehler bei Element-Validierung:', error);
        return false;
    }
}

/**
 * Sichere Event-Cleanup-Funktion für besseres Memory-Management
 * @param {jQuery} element - Element dessen Events bereinigt werden sollen
 * @param {string} namespace - Event-Namespace (optional)
 */
function mblock_cleanup_events(element, namespace = '.mblock') {
    try {
        if (mblock_validate_element(element) && element.jquery) {
            // Alle Event-Listener mit Namespace entfernen
            element.find('*').off(namespace);
            element.off(namespace);
        }
    } catch (error) {
        console.error('MBlock: Fehler bei Event-Cleanup:', error);
    }
}

/**
 * Prüft ob Copy/Paste in der Konfiguration aktiviert ist
 * @returns {boolean} True wenn aktiviert
 */
function checkCopyPasteEnabled() {
    try {
        // Method 1: Check data attribute on any mblock_wrapper
        const $wrapper = $(mblock).first();
        if ($wrapper.length) {
            const copyPasteAttr = $wrapper.attr('data-copy_paste');
            if (copyPasteAttr !== undefined) {
                return copyPasteAttr === 'true' || copyPasteAttr === '1';
            }
        }
        
        // Method 2: Check for presence of copy/paste buttons in DOM
        const hasCopyButtons = $('.mblock-copy-btn').length > 0;
        const hasToolbar = $('.mblock-copy-paste-toolbar').length > 0;
        
        return hasCopyButtons || hasToolbar;
        
    } catch (error) {
        console.warn('MBlock: Fehler beim Prüfen der Copy/Paste-Konfiguration:', error);
        return true; // Default: aktiviert bei Fehlern
    }
}

// ✨ Modern Smooth Scroll - Use bloecks if available, fallback to vanilla
function mblock_smooth_scroll_to_element(element, options = {}) {
    if (!element) return;
    
    // Try to use bloecks smooth scroll system first
    if (typeof BLOECKS !== 'undefined' && typeof BLOECKS.scrollToSlice === 'function') {
        try {
            BLOECKS.scrollToSlice(element);
            return;
        } catch (error) {
            console.warn('MBlock: Bloecks scroll failed, using fallback:', error);
        }
    }
    
    const config = {
        behavior: 'smooth',
        block: 'center',
        inline: 'nearest',
        offset: -20, // Extra offset from top
        ...options
    };
    
    try {
        // Modern approach with scrollIntoView
        if ('scrollIntoView' in element) {
            // Calculate position with offset
            const elementRect = element.getBoundingClientRect();
            const absoluteElementTop = elementRect.top + window.pageYOffset;
            const scrollToPosition = absoluteElementTop + config.offset;
            
            // Smooth scroll to calculated position
            window.scrollTo({
                top: Math.max(0, scrollToPosition),
                behavior: config.behavior
            });
        } else {
            // Fallback for very old browsers
            element.scrollIntoView(config);
        }
    } catch (error) {
        // Ultimate fallback
        try {
            element.scrollIntoView();
        } catch (fallbackError) {
            console.warn('MBlock: Scroll fallback failed:', fallbackError);
        }
    }
}

// Export for module systems (if used)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { MBlockUtils, mblock_show_message, mblock_get_text, mblock_validate_element };
}