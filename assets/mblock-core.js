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
        // Fallback to console
        if (type === 'error' || type === 'danger') {
            console.error('MBlock:', message);
        } else {
            console.warn('MBlock:', message);
        }
    }
}

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
                return (copyPasteAttr === '1' || copyPasteAttr === 'true' || copyPasteAttr === true);
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
            element.scrollIntoView({
                behavior: config.behavior,
                block: config.block,
                inline: config.inline
            });
        }
    } catch (error) {
        // Ultimate fallback
        try {
            element.scrollIntoView();
        } catch (fallbackError) {
            console.warn('MBlock: Smooth scroll nicht verfügbar:', fallbackError);
        }
    }
}

// Export for module systems (if used)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { MBlockUtils, mblock_show_message, mblock_get_text, mblock_validate_element };
}
