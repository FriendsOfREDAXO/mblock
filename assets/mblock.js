/**
 * Created by joachimdoerr on 30.07.16.
 * Enhanced with robust error handling and memory management
 * Integrated with bloecks ^5.2.0 for enhanced functionality
 */

let mblock = '.mblock_wrapper';

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
    const shortKey = key.replace(/^mblock_(toast_)?/, '');
    if (typeof rex !== 'undefined' && rex.mblock_i18n && rex.mblock_i18n[shortKey]) {
        return rex.mblock_i18n[shortKey];
    }
    
    // Secondary: Try rex_i18n if available
    if (typeof rex !== 'undefined' && rex.i18n && typeof rex.i18n.msg === 'function') {
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

/**
 * MBlock-only Bridges fuer die klassischen REDAXO-Widgets (Linklist, Medialist, Media, Link).
 * Faengt Klicks auf die Widget-Buttons in der Capture-Phase ab und ruft die Kernfunktion
 * mit der tatsaechlich vorhandenen Feld-Id im selben Widget auf: Nach dem Klonen eines Blocks
 * stimmt die Id im inline onclick sonst nicht mehr mit dem Hidden-Input ueberein.
 *
 * Eine Tabelle je Widget: Flag (einmalige Installation), Widget-Klasse, Id-Praefix des
 * Hidden-Inputs (optional mit Select), Kernfunktionen je Aktion. Die Reihenfolge ist wichtig:
 * "openREXMedia" steckt auch in "openREXMedialist", die Medialist-Bridge muss zuerst greifen.
 */
const MBLOCK_POPUP_BRIDGES = [
    { flag: 'mblockLinklistPopupBridgeInstalled', widget: '.rex-js-widget-linklist', prefix: 'REX_LINKLIST_', hasSelect: true,
        fns: { open: 'openREXLinklist', move: 'moveREXLinklist', delete: 'deleteREXLinklist' } },
    { flag: 'mblockMedialistPopupBridgeInstalled', widget: '.rex-js-widget-medialist', prefix: 'REX_MEDIALIST_', hasSelect: true,
        fns: { open: 'openREXMedialist', view: 'viewREXMedialist', move: 'moveREXMedialist', delete: 'deleteREXMedialist' } },
    { flag: 'mblockMediaPopupBridgeInstalled', widget: '.rex-js-widget-media', prefix: 'REX_MEDIA_',
        fns: { open: 'openREXMedia', view: 'viewREXMedia', add: 'addREXMedia', delete: 'deleteREXMedia' } },
    { flag: 'mblockLinkPopupBridgeInstalled', widget: '.rex-js-widget-link', prefix: 'REX_LINK_', idExclude: '_NAME', openWithPrefix: true,
        fns: { open: 'openLinkMap', delete: 'deleteREXLink' } }
];

/**
 * Argumente eines Funktionsaufrufs aus einem inline onclick lesen: fn('1', '&args') -> ['1', '&args'].
 */
function mblock_onclick_args(onclick, fnName) {
    const start = onclick.indexOf(fnName + '(');
    if (start === -1) return [];
    const args = [];
    let current = '', quote = '', depth = 0;
    for (let i = start + fnName.length + 1; i < onclick.length; i++) {
        const ch = onclick[i];
        if (quote) {
            if (ch === '\\' && i + 1 < onclick.length) { current += onclick[++i]; continue; }
            if (ch === quote) { quote = ''; continue; }
            current += ch;
            continue;
        }
        if (ch === '"' || ch === "'") { quote = ch; continue; }
        if (ch === '(') { depth++; current += ch; continue; }
        if (ch === ')') {
            if (depth === 0) { args.push(current.trim()); break; }
            depth--; current += ch; continue;
        }
        if (ch === ',' && depth === 0) { args.push(current.trim()); current = ''; continue; }
        current += ch;
    }
    return args;
}

function mblock_install_popup_bridge(bridge) {
    if (window[bridge.flag]) return;
    window[bridge.flag] = true;
    const actions = Object.keys(bridge.fns);
    const selector = actions.map(function (a) { return '[onclick*="' + bridge.fns[a] + '"]'; }).join(', ');

    document.addEventListener('click', function (event) {
        const target = event.target && typeof event.target.closest === 'function' ? event.target.closest(selector) : null;
        if (!target || !target.closest('.mblock_wrapper')) return;

        const onclick = target.getAttribute('onclick') || '';
        // spezifischere Aktionen zuerst (delete/move/view/add), "open" zuletzt
        const action = actions.filter(function (a) { return a !== 'open'; }).find(function (a) { return onclick.includes(bridge.fns[a]); }) || 'open';
        const fnName = bridge.fns[action];
        if (typeof window[fnName] !== 'function') return;

        // Feld-Id aus dem Widget lesen, das den Button enthaelt
        const findSourceId = function (scope) {
            if (!scope || typeof scope.querySelector !== 'function') return '';
            const select = bridge.hasSelect ? scope.querySelector('select[id^="' + bridge.prefix + 'SELECT_"]') : null;
            const hidden = scope.querySelector('input[id^="' + bridge.prefix + '"]' + (bridge.idExclude ? ':not([id$="' + bridge.idExclude + '"])' : ''));
            return (select && select.id) || (hidden && hidden.id) || '';
        };
        const scopes = [target.closest(bridge.widget), target.closest('.rex-js-widget'), target.closest('.input-group'), target.closest('.form-group'), target.parentElement];
        let sourceId = '';
        for (let i = 0; i < scopes.length && !sourceId; i++) sourceId = findSourceId(scopes[i]);

        const args = mblock_onclick_args(onclick, fnName);
        if (!sourceId && args.length) {
            const rawId = String(args[0]).replace(bridge.prefix, '');
            if (document.getElementById(bridge.prefix + rawId)) sourceId = bridge.prefix + rawId;
            else if (bridge.hasSelect && document.getElementById(bridge.prefix + 'SELECT_' + rawId)) sourceId = bridge.prefix + 'SELECT_' + rawId;
        }
        const idMatch = sourceId.match(new RegExp('^' + bridge.prefix + '(?:SELECT_)?(.+)$'));
        if (!idMatch) {
            console.warn('MBlock: Keine gueltige Widget-Id gefunden (' + bridge.prefix + ').');
            return;
        }

        // Erst nach erfolgreicher Id-Ermittlung das native Inline-Handling unterdruecken.
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        const id = idMatch[1];
        if (action === 'delete') {
            window[fnName](id);
            return;
        }
        window[fnName](bridge.openWithPrefix && action === 'open' ? bridge.prefix + id : id, args.length > 1 ? args[1] : '');
    }, true);
}

function mblock_install_popup_bridges() {
    MBLOCK_POPUP_BRIDGES.forEach(function (bridge) {
        try {
            mblock_install_popup_bridge(bridge);
        } catch (error) {
            console.warn('MBlock: Fehler beim Installieren der Popup-Bridge ' + bridge.prefix + ':', error);
        }
    });
}

$(document).on('rex:ready', function (e, container) {
    try {
        mblock_install_popup_bridges();

        // Initialize clipboard system only if copy/paste is enabled
        const isCopyPasteEnabled = checkCopyPasteEnabled();
        if (isCopyPasteEnabled) {
            MBlockClipboard.init();
        }
        
        if (container && typeof container.find === 'function') {
            container.find(mblock).each(function () {
                const $element = $(this);
                if ($element.length) {
                    try {
                        // GRIDBLOCK FIX: Reset mblock_run flag to force reinitialization after copy/paste
                        // Gridblock copies slices including the mblock_run=1 flag, which prevents reinitialization
                        $element.removeData('mblock_run');
                        
                        mblock_init($element);
                    } catch (initError) {
                        console.error('MBlock: Fehler beim Initialisieren eines einzelnen MBlock-Elements:', initError);
                        // Einzelne Fehler nicht die gesamte Initialisierung abbrechen lassen
                    }
                }
            });
        } else {
            // Initialize all MBlock elements
            $(mblock).each(function () {
                const $element = $(this);
                if ($element.length) {
                    // GRIDBLOCK FIX: Reset mblock_run flag
                    $element.removeData('mblock_run');
                    
                    mblock_init($element);
                }
            });
        }
    } catch (error) {
        console.error('MBlock: Fehler bei rex:ready:', error);
    }
});

function mblock_init(element) {
    try {
        if (!element || !element.length || typeof element.data !== 'function') {
            console.warn('MBlock: Ungültiges Element bei mblock_init');
            return false;
        }

        if (!element.data('mblock_run')) {
            element.data('mblock_run', 1);
            mblock_sort(element);
            // Initial render can contain pre-cloned/min blocks with duplicate [0] names.
            // Reindex once during init so each block posts with its own array index.
            mblock_reindex(element);
            mblock_set_unique_id(element, false);

            const minValue = element.data('min');
            const maxValue = element.data('max');
            if (minValue == 1 && maxValue == 1) {
                element.addClass('hide_removeadded').addClass('hide_sorthandle');
            }
        }
        
        mblock_add_plus(element);
        mblock_init_toolbar(element);
        
        return true;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_init:', error);
        return false;
    }
}

// List with handle
function mblock_init_sort(element) {
    try {
        if (!element || !element.length) {
            return false;
        }
        // reindex
        mblock_reindex(element);
        // init
        mblock_sort(element);
        return true;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_init_sort:', error);
        return false;
    }
}

function mblock_sort(element) {
    try {
        if (!element || !element.length) {
            return false;
        }
        // add linking
        mblock_add(element);
        // remove mblock_remove
        mblock_remove(element);
        // init sortable
        mblock_sort_it(element);
        return true;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_sort:', error);
        return false;
    }
}

/**
 * Ensure Sortable.js is available at runtime — dynamically load local fallback if needed.
 * Calls callback once Sortable is ready (or immediately if already available).
 * Uses `rex.mblock_sortable_local_url` if set by server boot.php, otherwise falls back to known asset path.
 */
function mblock_ensure_sortable(callback) {
    try {
        if (typeof Sortable !== 'undefined' && Sortable.create) {
            if (typeof callback === 'function') callback();
            return true;
        }

        // Avoid loading twice
        var existingScript = document.querySelector('script[data-mblock-sortable]');
        if (existingScript) {
            if (typeof callback === 'function') {
                existingScript.addEventListener('load', function() {
                    setTimeout(function() { callback(); }, 5);
                });
            }
            return true;
        }

        var scriptUrl = null;
        if (typeof rex !== 'undefined' && rex.mblock_sortable_local_url) {
            scriptUrl = rex.mblock_sortable_local_url;
        } else {
            // fallback relative path inside addon assets (best-effort)
            scriptUrl = window.location.origin + '/redaxo/src/addons/mblock/assets/sortable.min.js';
        }

        var script = document.createElement('script');
        script.setAttribute('src', scriptUrl);
        script.setAttribute('data-mblock-sortable', '1');
        script.async = true;
        script.onload = function() {
            if (typeof callback === 'function') callback();
        };
        script.onerror = function(e) {
            console.error('MBlock: Fehler beim Laden von Sortable.js:', e);
            if (typeof callback === 'function') callback();
        };
        (document.head || document.documentElement).appendChild(script);
        return true;
    } catch (e) {
        console.error('MBlock: mblock_ensure_sortable failed', e);
        if (typeof callback === 'function') callback();
        return false;
    }
}

function mblock_add_plus(element) {
    if (!element.find('> div.sortitem').length) {

        element.prepend($($.parseHTML(element.data('mblock-single-add'))));

        element.find('> div.mblock-single-add .addme').unbind().bind('click', function () {
            mblock_add_item(element, false);
            $(this).parents('.mblock-single-add').remove();
        });
    }
}

function mblock_remove(element) {
    var finded = element.find('> div.sortitem');

    if (finded.length == 1) {
        finded.find('.removeme').prop('disabled', true);
        finded.find('.removeme').attr('data-disabled', true);
    } else {
        finded.find('.removeme').prop('disabled', false);
        finded.find('.removeme').attr('data-disabled', false);
    }

    // has data?
    if (element.data().hasOwnProperty('max')) {
        const maxReached = finded.length >= element.data('max');
        element.find('.addme, > .mblock-add-bar .mblock-add-last').prop('disabled', maxReached);
    }

    if (element.data().hasOwnProperty('min')) {
        if (finded.length <= element.data('min')) {
            element.find('.removeme').prop('disabled', true);
        } else {
            element.find('.removeme').prop('disabled', false);
        }
    }

    finded.each(function (index) {
        // min removeme hide
        if ((index + 1) == element.data('min') && finded.length == element.data('min')) {
            $(this).find('.removeme').prop('disabled', true);
        }
        if (index == 0) {
            $(this).find('.moveup').prop('disabled', true);
        } else {
            $(this).find('.moveup').prop('disabled', false);
        }
        if ((index + 1) == finded.length) { // if max count?
            $(this).find('.movedown').prop('disabled', true);
        } else {
            $(this).find('.movedown').prop('disabled', false);
        }
    });
}

function mblock_sort_it(element) {
    try {
        if (!element || !element.length || !element.get || !element.get(0)) {
            console.warn('MBlock: Ungültiges Element für mblock_sort_it');
            return false;
        }

        const domElement = element.get(0);
        
        // Check if element is still in the DOM
        if (!document.contains(domElement)) {
            console.warn('MBlock: Element nicht mehr im DOM');
            return false;
        }

        // Sortable.js API (from bloecks addon or dynamically loaded fallback)
        if (typeof Sortable !== 'undefined' && Sortable.create) {
            // Destroy existing sortable if it exists - with better error handling
            try {
                if (domElement._sortable) {
                    if (typeof domElement._sortable.destroy === 'function') {
                        domElement._sortable.destroy();
                    }
                    domElement._sortable = null;
                }
            } catch (destroyError) {
                console.warn('MBlock: Fehler beim Zerstören der vorhandenen Sortable-Instanz:', destroyError);
                domElement._sortable = null;
            }
            
            // Add safety delay before creating new instance
            setTimeout(() => {
                try {
                    const sortableInstance = Sortable.create(domElement, {
                        handle: '.sorthandle',
                        animation: 150,
                        chosenClass: 'mblock-sortable-chosen',
                        dragClass: 'mblock-dragging',
                        onStart: function (evt) {
                            try {
                                document.body.classList.add('mblock-drag-active');
                                if (evt.item) {
                                    evt.item.classList.add('mblock-dragging');
                                }
                                
                                // Destroy TinyMCE instances in the list to prevent ID conflicts during reindex
                                element.find('.tiny-editor').each(function() {
                                    var editorId = $(this).attr('id');
                                    if (editorId && typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                                        try {
                                            tinymce.get(editorId).save(); // Save content to textarea first
                                            tinymce.get(editorId).remove();
                                        } catch(e) {
                                            console.warn('MBlock: TinyMCE remove error:', e);
                                        }
                                    }
                                });
                            } catch (error) {
                                console.error('MBlock: Fehler in sortable onStart:', error);
                            }
                        },
                        onEnd: function (evt) {
                            try {
                                document.body.classList.remove('mblock-drag-active');
                                if (evt.item) {
                                    evt.item.classList.remove('mblock-dragging');
                                    // Add flash effect
                                    evt.item.classList.add('mblock-dropped-flash');
                                    setTimeout(() => {
                                        evt.item.classList.remove('mblock-dropped-flash');
                                    }, 600);
                                }
                                
                                // Reindex and update
                                mblock_reindex(element);
                                mblock_remove(element);
                                
                                // Trigger event
                                let iClone = $(evt.item);
                                if (iClone.length) {
                                    iClone.trigger('mblock:change', [iClone]);
                                }
                                
                                // Re-initialize widgets for ALL items since IDs have changed
                                element.find('> div.sortitem').each(function() {
                                    $(this).trigger('rex:ready', [$(this)]);
                                });
                                
                            } catch (error) {
                                console.error('MBlock: Fehler in sortable onEnd:', error);
                            }
                        },
                        onError: function (evt) {
                            console.error('MBlock: Sortable Fehler:', evt);
                        }
                    });
                    
                    // Store sortable instance for later destruction
                    domElement._sortable = sortableInstance;
                    
                } catch (createError) {
                    console.error('MBlock: Fehler beim Erstellen der Sortable-Instanz:', createError);
                    return false;
                }
            }, 10);
            
            return true;
            
        } else {
            // Try runtime fallback: load local sortable.min.js and retry
            try {
                mblock_ensure_sortable(function() {
                    if (typeof Sortable !== 'undefined' && Sortable.create) {
                        // Retry initialization once Sortable is available
                        mblock_sort_it(element);
                    } else {
                        console.error('MBlock: Sortable.js ist auch nach dem dynamischen Laden nicht verfügbar');
                    }
                });
            } catch (e) {
                console.error('MBlock: Fehler beim Versuch, Sortable runtime zu laden:', e);
            }

            // We attempted a dynamic load — return true to indicate an async retry is ongoing
            return true;
        }
        
    } catch (error) {
        console.error('MBlock: Fehler in mblock_sort_it:', error);
        return false;
    }
}

function mblock_reindex(element) {
    try {
        if (!mblock_validate_element(element)) {
            console.warn('MBlock: Ungültiges Element bei mblock_reindex');
            return false;
        }

        const mblock_count = element.data('mblock_count') || 0;
        const sortItems = element.find('> div.sortitem');
        
        if (!sortItems.length) {
            return true;
        }

        // Performance-Optimierung: Batch DOM-Updates
        sortItems.each(function (index) {
            const $sortItem = $(this);
            const sindex = index + 1;
            
            // Set index attribute
            $sortItem.attr('data-mblock_index', sindex);
            
            // Optimierte Element-Behandlung
            mblock_reindex_form_elements($sortItem, index, sindex, mblock_count);
            mblock_reindex_special_elements($sortItem, index, sindex, mblock_count);
        });

        // Nach Reindexierung: for-Attribute korrigieren
        mblock_replace_for(element);
        
        return true;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_reindex:', error);
        return false;
    }
}

/**
 * Optimierte Behandlung von Formularelementen beim Reindexing
 */
function mblock_reindex_form_elements($sortItem, index, sindex, mblock_count) {
    try {
        $sortItem.find('input,textarea,select,button').each(function (key) {
            const $element = $(this);
            const eindex = key + 1;
            const attr = $element.attr('name');
            
            // Name-Attribut aktualisieren
            if (attr && typeof attr !== 'undefined') {
                const nameMatches = attr.match(/\]\[\d+\]\[/g);
                if (nameMatches) {
                    const newValue = attr.replace(nameMatches, '][' + index + '][').replace('mblock_new_', '');
                    $element.attr('name', newValue);
                }
            }

            // Event-Handler fuer Checkboxen rueckwaertskompatibel halten.
            // Custom-Values (z.B. "checked") duerfen nicht auf 1/0 ueberschrieben werden.
            const elementType = $element.attr('type');
            if (elementType === 'checkbox') {
                const currentValue = $element.val();
                if (!$element.attr('data-value')) {
                    $element.attr('data-value', currentValue);
                }

                $element.off('change.mblock').on('change.mblock', function () {
                    const $checkbox = $(this);
                    const baseValue = String($checkbox.attr('data-value') ?? $checkbox.val() ?? '1');
                    const normalizedBaseValue = baseValue.toLowerCase();
                    const isBooleanStyleValue = normalizedBaseValue === ''
                        || normalizedBaseValue === '1'
                        || normalizedBaseValue === '0'
                        || normalizedBaseValue === 'on'
                        || normalizedBaseValue === 'true'
                        || normalizedBaseValue === 'false';

                    if ($checkbox.is(':checked')) {
                        $checkbox.val(baseValue);
                        return;
                    }

                    if (isBooleanStyleValue) {
                        $checkbox.val('0');
                        return;
                    }

                    $checkbox.val(baseValue);
                });
            }

            // Radio-Button Werte wiederherstellen
            if (elementType === 'radio') {
                const dataValue = $element.attr('data-value');
                if (dataValue) {
                    $element.val(dataValue);
                }
            }

            // REX-spezifische IDs aktualisieren
            mblock_update_rex_ids($element, sindex, mblock_count, eindex);
        });
    } catch (error) {
        console.error('MBlock: Fehler in mblock_reindex_form_elements:', error);
    }
}

/**
 * REX-System-IDs aktualisieren (SELECT/INPUT)
 */
function mblock_update_rex_ids($element, sindex, mblock_count, eindex) {
    try {
        const elementId = $element.attr('id');
        const nodeName = $element.prop('nodeName');
        
        if (!elementId) return;

        // SELECT-Elemente (REX_MEDIALIST_SELECT, REX_LINKLIST_SELECT)
        if (nodeName === 'SELECT' && 
            (elementId.indexOf('REX_MEDIALIST_SELECT_') >= 0 || elementId.indexOf('REX_LINKLIST_SELECT_') >= 0)) {
            
            $element.parent().data('eindex', eindex);
            const newId = elementId.replace(/_\d+/, '_' + sindex + mblock_count + '00' + eindex);
            $element.attr('id', newId);
            
            const nameAttr = $element.attr('name');
            if (nameAttr) {
                $element.attr('name', nameAttr.replace(/_\d+/, '_' + sindex + mblock_count + '00' + eindex));
            }
        }

        // INPUT-Elemente (REX_MEDIA, REX_LINKLIST, REX_MEDIALIST)
        if (nodeName === 'INPUT' && 
            (elementId.indexOf('REX_MEDIA_') >= 0 || 
             elementId.indexOf('REX_LINKLIST_') >= 0 || 
             elementId.indexOf('REX_MEDIALIST_') >= 0)) {
            
            const parentEindex = $element.parent().data('eindex') || eindex;
            const newId = elementId.replace(/\d+/, '' + sindex + mblock_count + '00' + parentEindex);
            $element.attr('id', newId);

            // Button-Updates für Popup-Funktionen
            mblock_update_rex_buttons($element, sindex, mblock_count, parentEindex);
        }

        // INPUT-Elemente REX_LINK_: hidden und _NAME müssen dieselbe ID-Basis bekommen.
        // _NAME-Inputs werden übersprungen – sie werden gemeinsam mit dem hidden-Input aktualisiert.
        if (nodeName === 'INPUT' && elementId.indexOf('REX_LINK_') >= 0) {
            // _NAME-Input überspringen, wird weiter unten zusammen mit hidden-Input gesetzt
            if (elementId.indexOf('_NAME') >= 0) {
                return;
            }

            const parentEindex = $element.parent().data('eindex') || eindex;
            const newId = elementId.replace(/\d+/, '' + sindex + mblock_count + '00' + parentEindex);
            $element.attr('id', newId);

            // Zugehöriges _NAME-Input anhand der alten ID im selben sortitem finden und synchron aktualisieren
            const $sortItem = $element.closest('.sortitem');
            const $nameInput = $sortItem.find('input[id="' + elementId + '_NAME"]');
            if ($nameInput.length) {
                $nameInput.attr('id', newId + '_NAME');
            }

            // Button-Updates für Popup-Funktionen
            mblock_update_rex_buttons($element, sindex, mblock_count, parentEindex);
        }
    } catch (error) {
        console.error('MBlock: Fehler in mblock_update_rex_ids:', error);
    }
}

/**
 * REX-Popup-Buttons aktualisieren
 */
function mblock_update_rex_buttons($element, sindex, mblock_count, eindex) {
    try {
        // Id-Teil aus der tatsaechlichen Input-Id ableiten, damit onclick und Input auch bei
        // Gridblock-Ids mit Buchstaben (REX_MEDIA_1GBS...) zusammenpassen
        const inputId = $element.attr('id') || '';
        const idSuffix = inputId.replace(/^REX_(MEDIALIST_SELECT|LINKLIST_SELECT|MEDIALIST|LINKLIST|MEDIA|LINK)_/, '').replace(/_NAME$/, '');
        const newIdPart = idSuffix !== '' && idSuffix !== inputId ? idSuffix : '' + sindex + mblock_count + '00' + eindex;
        // Suche Buttons im nächsten Widget-Container oder im Parent als Fallback
        const $container = $element.closest('.rex-js-widget-link, .rex-js-widget-media, .rex-js-widget-medialist, .rex-js-widget-linklist, .rex-js-widget-customlink, .input-group');
        const $scope = $container.length ? $container : $element.parent();
        $scope.find('a.btn-popup').each(function () {
            const $btn = $(this);
            const onclick = $btn.attr('onclick');
            if (onclick) {
                let newOnclick = onclick;
                // openLinkMap('REX_LINK_X', ...) -> _X ersetzen
                newOnclick = newOnclick.replace(/(openLinkMap\('REX_LINK_)[^'\"]+/, '$1' + newIdPart);
                // deleteREXLink('X') oder deleteREXLink(X) -> korrekt mit Quotes
                newOnclick = newOnclick.replace(/deleteREXLink\([^)]+\)/, "deleteREXLink('" + newIdPart + "')");
                // openREXMedia('X', ...) und ähnliche
                newOnclick = newOnclick.replace(/(_)[^'\",)]+([',)])/, '$1' + newIdPart + '$2');
                // Fallback: erste Ziffernfolge nach ( ersetzen (für unbekannte Patterns)
                if (newOnclick === onclick) {
                    newOnclick = newOnclick
                        .replace(/\('?[^'",)]+'?/, "('" + newIdPart + "'")
                        .replace(/_[^'\",)]+/, '_' + newIdPart);
                }
                $btn.attr('onclick', newOnclick);
            }
        });
    } catch (error) {
        console.error('MBlock: Fehler in mblock_update_rex_buttons:', error);
    }
}

/**
 * Behandlung spezieller Elemente beim Reindexing (Bootstrap-Tabs, Accordions, etc.)
 */
function mblock_reindex_special_elements($sortItem, index, sindex, mblock_count) {
    try {
        // Bootstrap Tabs
        $sortItem.find('a[data-toggle="tab"]').each(function (key) {
            const eindex = key + 1;
            const $tab = $(this);
            const href = $tab.attr('href');
            
            if (href) {
                const newHref = href.replace(/_\d+/, '_' + sindex + mblock_count + '00' + eindex);
                $tab.attr('href', newHref);
                
                // Update corresponding tab content
                const $container = $tab.parent().parent().parent().find('.tab-content ' + href);
                if ($container.length) {
                    $container.attr('id', newHref.replace('#', ''));
                }

                // LocalStorage tab handling mit Error-Handling
                $tab.off('shown.bs.tab.mblock').on('shown.bs.tab.mblock', function (e) {
                    try {
                        const id = $(e.target).attr('href');
                        if (id && typeof localStorage !== 'undefined') {
                            localStorage.setItem('selectedTab', id);
                        }
                    } catch (storageError) {
                        console.warn('MBlock: LocalStorage nicht verfügbar:', storageError);
                    }
                });
            }
        });

        // Bootstrap Collapse/Accordion
        $sortItem.find('a[data-toggle="collapse"]').each(function (key) {
            const eindex = key + 1;
            const $collapse = $(this);
            
            if (!$collapse.attr('data-ignore-mblock')) {
                const href = $collapse.attr('data-target');
                if (href) {
                    const newHref = href.replace(/_\d+/, '_' + sindex + mblock_count + '00' + eindex);
                    $collapse.attr('data-target', newHref);
                    
                    // Update collapse content
                    const $container = $collapse.parent().find(href);
                    if ($container.length) {
                        $container.attr('id', newHref.replace('#', ''));
                    }
                    
                    // Update group parent if exists
                    const $group = $collapse.parent().parent().parent().find('.panel-group');
                    if ($group.length) {
                        const parentId = 'accgr_' + sindex + mblock_count + '00';
                        $group.attr('id', parentId);
                        $collapse.attr('data-parent', '#' + parentId);
                    }
                }
            }
        });

        // Custom Links (MForm)
        // Pro Widget genau einmal reindizieren.
        // `.custom-link` existiert sowohl am Outer-Wrapper als auch an der inneren input-group.
        // Deshalb direkt über `.rex-js-widget-customlink` gehen, sonst werden IDs doppelt umgeschrieben.
        $sortItem.find('.rex-js-widget-customlink').each(function (key) {
            const eindex = key + 1;
            const newId = '' + sindex + mblock_count + '00' + eindex;
            const $widget = $(this);
            const $customlink = $widget.find('.input-group.custom-link').first();

            $widget.attr('data-widget-id', newId);
            $customlink.attr('data-id', newId);
            $customlink.find('ul.dropdown-menu').attr('id', 'mform_ylink_' + newId);

            $customlink.find('input').each(function () {
                const $input = $(this);
                const inputId = $input.attr('id');
                if (inputId) {
                    $input.attr('id', inputId.replace(/\d+/, newId));
                }
            });

            $customlink.find('a.btn-popup').each(function () {
                const $btn = $(this);
                const btnId = $btn.attr('id');
                if (btnId) {
                    $btn.attr('id', btnId.replace(/\d+/, newId));
                }
            });

            // Neu initialisieren, damit customlink.js die aktuelle Widget-ID wieder sauber bindet.
            $widget.trigger('rex:ready', [$widget]);
        });

        // MForm9 list-widgets (custom_medialist / custom_linklist)
        // Diese Widgets verwenden eigene data-widget-id und mform-list-select/mform-list-value IDs,
        // die beim MBlock-Reindex nicht automatisch aktualisiert werden.
        // Wir setzen die IDs neu und triggern rex:ready, damit der Popup-Callback
        // auf die richtige Widget-Instanz zeigt.
        $sortItem.find('.mform-list-widget').each(function (key) {
            try {
                const $widget = $(this);
                const newId = '' + sindex + mblock_count + '00' + (key + 1);

                // data-widget-id auf neuen Wert setzen
                $widget.attr('data-widget-id', newId);

                // IDs von Select und Hidden-Input aktualisieren
                $widget.find('select.mform-list-select').attr('id', function (_, id) {
                    return id ? id.replace(/\d+$/, newId) : id;
                });
                $widget.find('input.mform-list-value').attr('id', function (_, id) {
                    return id ? id.replace(/\d+$/, newId) : id;
                });

                // Popup-Buttons (openREXMedialist / openREXLinklist) aktualisieren
                $widget.find('[onclick]').each(function () {
                    const $btn = $(this);
                    const onclick = $btn.attr('onclick');
                    if (onclick) {
                        $btn.attr('onclick', onclick.replace(/\b\d{4,}\b/, newId));
                    }
                });
                $widget.find('[data-params]').each(function () {
                    const $el = $(this);
                    const params = $el.attr('data-params');
                    if (params) {
                        $el.attr('data-params', params.replace(/\b\d{4,}\b/, newId));
                    }
                });

                // Widget neu initialisieren: rex:ready auf dem Widget-Element feuern
                // damit list-widget.js den Popup-Callback neu registriert
                $widget.trigger('rex:ready', [$widget]);
            } catch (widgetError) {
                console.warn('MBlock: Fehler beim Reindex eines MForm9-List-Widgets:', widgetError);
            }
        });

        // MForm9 Custom-Link-Widgets (.rex-js-widget-customlink)
        // Sicherstellen dass customlink.js nach Reindex neu initialisiert wird
        $sortItem.find('.rex-js-widget-customlink').each(function () {
            try {
                $(this).trigger('rex:ready', [$(this)]);
            } catch (clError) {
                console.warn('MBlock: Fehler beim Reindex eines MForm9-Customlink-Widgets:', clError);
            }
        });
    } catch (error) {
        console.error('MBlock: Fehler in mblock_reindex_special_elements:', error);
    }
}

let mblock_editor_id_seq = 0;

/**
 * Generates a client-side unique id for rich-text editor fields (TinyMCE, CKEditor5).
 * Never derived from position/name - see mblock_replace_for() for why.
 */
function mblock_generate_unique_editor_id() {
    var id;
    do {
        mblock_editor_id_seq += 1;
        id = 'mblock_editor_' + Date.now().toString(36) + '_' + mblock_editor_id_seq;
    } while (document.getElementById(id));
    return id;
}

function mblock_replace_for(element) {

    // Rich-text editor fields (TinyMCE via .tiny-editor, CKEditor5 via .cke5-editor)
    // need a STABLE id across reindexing. Their JS-side EditorManager tracks a live
    // editor instance by an internal id captured once at init and never updated
    // afterwards. The id rewrite below derives ids from the (position-based,
    // reindexed) name, so an insert/remove/reorder can hand a block's id to a
    // DIFFERENT physical block. A later tinymce.get(id) lookup then resolves to
    // the stale editor instance instead of the current one, and its content gets
    // saved into the wrong block. This is the exact same class of bug the
    // "redactor"/"markitup" exclusion below already guards against for older
    // WYSIWYG editors - editor fields just keep whatever id they already have,
    // and only get a fresh one if it's missing or duplicated (e.g. a freshly
    // cloned block, which starts out sharing the static template's id).
    var seenEditorIds = {};

    element.find('> div.sortitem').each(function (index) {
        var mblock = $(this);
        mblock.find('input:not(:checkbox):not(:radio),textarea,select').each(function (key) {
            var el = $(this),
                id = el.attr('id'),
                name = el.attr('name');

            if (el.hasClass('tiny-editor') || el.hasClass('cke5-editor')) {
                if (!id || seenEditorIds[id]) {
                    id = mblock_generate_unique_editor_id();
                    el.attr('id', id);
                }
                seenEditorIds[id] = true;
                return;
            }

            if ((typeof id !== typeof undefined && id !== false) && (typeof name !== typeof undefined && name !== false)) {
                if (!(id.indexOf("REX_MEDIA") >= 0 ||
                    id.indexOf("REX_LINK") >= 0 ||
                    id.indexOf("redactor") >= 0 ||
                    id.indexOf("markitup") >= 0)
                ) {
                    var label = mblock.find('label[for="' + id + '"]');
                    name = name.replace(/(\[|\])/gm, '');
                    el.attr('id', name);
                    label.attr('for', name);
                }
            }
        });
    });
}

function mblock_add_item(element, item) {
    // create iclone
    var iClone = $($.parseHTML(element.data('mblock-plain-sortitem')));

    // fix for checkbox and radio bug
    iClone.find('input:radio, input:checkbox').each(function () {
        $(this).parent().removeAttr('for');
    });

    // fix radio bug
    iClone.find('input:radio, input:checkbox').each(function () {
        // fix lost checked from parent item
        $(this).attr('name', 'mblock_new_' + $(this).attr('name'));
        // fix lost value
        $(this).attr('data-value', $(this).val());
    });

    // Neue Bloecke sollen mit leeren MForm-Widgets starten.
    // Seit Link- und Media-Felder als custom_link-Wrapper gerendert werden,
    // muessen sichtbarer Textwert, Hidden-Value und Widget-Zustand explizit
    // zurueckgesetzt werden, sonst uebernimmt der Clone den alten Inhalt.
    iClone.find('.rex-js-widget-customlink').each(function () {
        const $widget = $(this);
        const $customLink = $widget.find('.input-group.custom-link').first();

        $widget.find('input[type="hidden"], input[type="text"]').val('');
        $widget.find('a.btn-popup').removeClass('active');
        $widget.find('a.media_preview_link').addClass('hidden disabled').attr('aria-disabled', 'true');
        $customLink.addClass('is-empty');
    });

    // Neue Bloecke sollen auch bei MForm-List-Widgets leer starten.
    // Das betrifft custom_medialist, custom_linklist und imglist,
    // die sonst Select-Optionen, Hidden-Values und gerenderte Listen aus dem
    // Quellblock mitnehmen wuerden.
    iClone.find('.mform-list-widget').each(function () {
        const $widget = $(this);

        $widget.find('input.mform-list-value').val('');
        $widget.find('select.mform-list-select').each(function () {
            $(this).find('option').prop('selected', false).remove();
            $(this).val('');
        });
        $widget.find('ul.mform-list-items, ul.thumbnail-list').empty();
        $widget.find('.mform-list-btn, .btn-popup').removeClass('active disabled').attr('aria-disabled', 'false');
        $widget.addClass('is-empty').removeClass('is-grid-view is-gallery-view is-list-view');
    });

    // Neue Bloecke sollen mit leeren Checkbox-Group-Widgets starten.
    // Active-Klassen und Hidden-Value zuruecksetzen, damit kein Zustand vom Quellblock vererbt wird.
    iClone.find('.mform-checkbox-group').each(function () {
        $(this).find('.mform-cbg-option').removeClass('active');
        $(this).find('.mform-cbg-value').val('');
    });

    if (item === false) {
        // add clone
        element.prepend(iClone);

    } else if (item.parent().is(element)) {
        // Destroy sortable before manipulation with better error handling
        try {
            const domElement = element.get(0);
            if (domElement && domElement._sortable && typeof domElement._sortable.destroy === 'function') {
                domElement._sortable.destroy();
                domElement._sortable = null;
            }
        } catch (sortableError) {
            console.warn('MBlock: Sortable destroy error in add_item:', sortableError);
        }
        
        // add clone
                            item.after(iClone);
        // set count
        mblock_set_count(element, item);
    }

    // add unique id
    mblock_set_unique_id(iClone, true);
    // reinit first
    mblock_init_sort(element);
    
    // trigger rex:ready event only on the new item for component initialization
    // We handle selectpicker manually below, so we only need this single event
    iClone.trigger('rex:ready', [iClone]);
    
    // specific component reinitialization
    setTimeout(function() {
        // Initialize selectpicker with REDAXO core method for new items
        if (typeof $.fn.selectpicker === 'function') {
            var selects = iClone.find('select.selectpicker');
            if (selects.length) {
                selects.selectpicker({
                    noneSelectedText: '—'
                }).on('rendered.bs.select', function () {
                    $(this).parent().removeClass('bs3-has-addon');
                });
                selects.selectpicker('refresh');
            }
        }
        
        // reinitialize other common components
        if (typeof $.fn.chosen === 'function') {
            iClone.find('select.chosen').chosen();
        }
        
        // trigger change events to update any dependent elements
        iClone.find('input, select, textarea').trigger('change');
    }, 50);
    
    // scroll to item with slight delay to ensure DOM is ready
    setTimeout(function() {
        if (iClone && iClone.length && iClone.is(':visible')) {
            mblock_scroll(element, iClone);
            
            // ✨ Add glow effect to new item
            iClone.addClass('mblock-add-glow');
            setTimeout(function() {
                iClone.removeClass('mblock-add-glow');
            }, 1200);
        }
    }, 100);
}

function mblock_set_unique_id(item, input_delete) {
    try {
        if (!item || !item.length || typeof item.find !== 'function') {
            console.warn('MBlock: Ungültiges Item bei mblock_set_unique_id');
            return false;
        }

        item.find('input').each(function () {
            try {
                const $input = $(this);
                const isUniqueInt = $input.attr('data-unique-int') == 1;
                const isUnique = $input.attr('data-unique') == 1 || isUniqueInt;
                
                if (isUnique) {
                    let unique_id;
                    if (isUniqueInt) {
                        unique_id = Math.floor(Math.random() * 1000000000000);
                    } else {
                        unique_id = Math.random().toString(16).slice(2);
                    }

                    if (input_delete === true) {
                        $input.val('');
                    }
                    if ($input.val() === '' || $input.val() === null) {
                        $input.val(unique_id);
                    }
                }
            } catch (error) {
                console.error('MBlock: Fehler bei unique_id Generierung:', error);
            }
        });
        return true;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_set_unique_id:', error);
        return false;
    }
}

function mblock_set_count(element, item) {
    var countItem = item.next().find('span.mb_count'),
        count = element.find('> div.sortitem').length;

    if (element.data('latest')) {
        count = element.data('latest') + 1;
    }

    countItem.text(count);
    element.data('latest', count);
}

function mblock_remove_item(element, item) {
    try {
        if (!element || !element.length || !item || !item.length) {
            console.warn('MBlock: Ungültige Parameter bei mblock_remove_item');
            return false;
        }

        const elementData = element.data();
        // Use non-blocking custom confirm UI if a confirmation message is configured
        // We keep the old confirm() fallback for environments where dialogs are fine,
        // but Safari sometimes blocks native alerts/confirms — avoid using them.
        if (elementData && elementData.hasOwnProperty('delete_confirm')) {
            // If a custom modal is present, removal should have been handled by
            // the click handler's confirmation flow. If we get here synchronously
            // (e.g. called directly), fall back to browser confirm.
            // Return false to indicate caller should open the confirmation.
            if (!item.data('__mblock_confirmed')) {
                return false;
            }
            // clear the flag for subsequent operations
            item.removeData('__mblock_confirmed');
        }

        const itemParent = item.parent();
        const elementClass = element.attr('class');
        
            // Ensure the item actually belongs to the given wrapper element.
            // Older code relied on hasClass(elementClass) which fails when the
            // wrapper has multiple classes (space-separated). Be robust: check
            // direct parent equality or whether the item is a descendant of
            // the provided element.
            if (itemParent.length && element && element.length && (
                itemParent[0] === element[0] || // direct parent === wrapper
                item.parents().filter(function() { return this === element.get(0); }).length > 0
            )) {
            // Sichere Sortable-Deaktivierung (für beide Sortable-Typen)
            try {
                const domElement = element.get(0);
                if (domElement && domElement._sortable && typeof domElement._sortable.destroy === 'function') {
                    domElement._sortable.destroy();
                    domElement._sortable = null;
                }
            } catch (sortableError) {
                console.warn('MBlock: Sortable destroy error in remove_item:', sortableError);
            }

            // set prev item
            let prevItem = item.prev();
            // is prev exist?
            if (!prevItem.length || !prevItem.hasClass('sortitem')) {
                prevItem = item.next(); // go to next
            }

            // Sichere Element-Entfernung mit Event-Cleanup
            try {
                // Event-Listeners entfernen um Memory Leaks zu verhindern
                item.find('*').off('.mblock');
                item.off('.mblock');
                item.remove();
            } catch (removeError) {
                console.error('MBlock: Fehler beim Entfernen des Items:', removeError);
                return false;
            }
            
            // reinit
            mblock_init_sort(element);
            // scroll to item (falls ein vorheriges Element existiert)
            if (prevItem && prevItem.length) {
                mblock_scroll(element, prevItem);
            }
            // add add button
            mblock_add_plus(element);

            return true;
        }
        
        return false;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_remove_item:', error);
        return false;
    }
}

/**
 * Nicht blockierender Bestaetigungsdialog fuer das Loeschen (Bootstrap-Modal, sonst confirm()).
 * @returns {Promise<boolean>}
 */
function mblock_show_confirm(element, item, message) {
    return new Promise(function (resolve) {
        const text = message || 'Sind Sie sicher?';
        if (typeof $().modal !== 'function') {
            resolve(confirm(text));
            return;
        }
        const i18n = mblock_get_text;
        let $modal = $('#mblock-confirm-modal');
        if (!$modal.length) {
            $('body').append(
                '<div id="mblock-confirm-modal" class="modal fade" tabindex="-1" role="dialog"><div class="modal-dialog" role="document"><div class="modal-content">'
                + '<div class="modal-header"><h5 class="modal-title">' + i18n('mblock_confirm_title', 'Bestätigen') + '</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
                + '<div class="modal-body"><p class="mblock-confirm-text"></p></div>'
                + '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">' + i18n('mblock_confirm_cancel', 'Abbrechen') + '</button><button type="button" class="btn btn-danger mblock-confirm-ok">' + i18n('mblock_confirm_ok', 'Löschen') + '</button></div>'
                + '</div></div></div>');
            $modal = $('#mblock-confirm-modal');
        }
        let confirmed = false;
        $modal.find('.mblock-confirm-text').text(text);
        $modal.off('.mblock_confirm')
            .on('click.mblock_confirm', '.mblock-confirm-ok', function () { confirmed = true; $modal.modal('hide'); })
            .on('hidden.bs.modal.mblock_confirm', function () { resolve(confirmed); });
        $modal.modal('show');
    });
}

// Kopieren & Einfuegen: der Block wird als HTML mit eingefrorenen Werten in der sessionStorage
// abgelegt und beim Einfuegen als neuer Block eingehaengt; mblock_reindex() vergibt Namen und Ids.
var MBlockClipboard = {
    data: null,
    storageKey: 'mblock_clipboard',

    init: function () {
        this.loadFromStorage();
    },

    getStorage: function () {
        try { return sessionStorage; } catch (error) { return null; }
    },

    saveToStorage: function () {
        const storage = this.getStorage();
        if (!storage || !this.data) return false;
        try {
            storage.setItem(this.storageKey, JSON.stringify(this.data));
            return true;
        } catch (error) {
            console.warn('MBlock: Fehler beim Speichern in Storage:', error);
            return false;
        }
    },

    loadFromStorage: function () {
        const storage = this.getStorage();
        if (!storage) return false;
        try {
            const parsed = JSON.parse(storage.getItem(this.storageKey) || 'null');
            // nur das aktuelle Format (HTML mit eingefrorenen Werten) uebernehmen
            if (parsed && typeof parsed.html === 'string' && !parsed.formData) {
                this.data = parsed;
                this.updatePasteButtons();
                return true;
            }
        } catch (error) {
            console.warn('MBlock: Fehler beim Laden aus Storage:', error);
        }
        this.clearStorage();
        return false;
    },

    clearStorage: function () {
        const storage = this.getStorage();
        if (storage) storage.removeItem(this.storageKey);
    },

    // Modultyp des Wrappers: module_id aus dem Slice-Formular, sonst Formular-Action, sonst URL
    getModuleType: function (wrapper) {
        const form = wrapper.closest('form');
        const moduleId = form.find('input[name="module_id"]').first().val() || wrapper.find('input[name="module_id"]').first().val();
        if (moduleId) return 'module_' + moduleId;
        const actionMatch = ((form.attr('action') || '')).match(/module_id=(\d+)/);
        if (actionMatch) return 'module_' + actionMatch[1];
        const params = new URLSearchParams(window.location.search);
        const contextId = params.get('module_id') || params.get('article_id');
        return contextId ? 'context_' + contextId : 'unknown_module';
    },

    showModuleTypeMismatchWarning: function (currentType, clipboardType) {
        const $target = $('.mblock_wrapper').first();
        if (!$target.length) {
            alert('Das kopierte Element stammt aus einem anderen Modul und kann hier nicht eingefügt werden.');
            return;
        }
        $('.mblock-type-warning').remove();
        $target.prepend(
            '<div class="alert alert-warning mblock-type-warning" style="margin: 10px 0; position: relative; z-index: 1000;">'
            + '<strong>Achtung:</strong> Das kopierte Element stammt aus einem anderen Modul-Typ. Das Einfügen ist nicht möglich.<br>'
            + '<small>Aktueller Typ: <code>' + currentType + '</code> | Zwischenablage: <code>' + clipboardType + '</code></small>'
            + '<button type="button" class="close" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none; font-size: 18px;" onclick="$(this).parent().fadeOut()">&times;</button></div>');
        setTimeout(function () { $('.mblock-type-warning').fadeOut('slow'); }, 5000);
    },

    /**
     * Aktuelle Werte des Quellblocks als Attribute in den Klon schreiben, damit outerHTML sie enthaelt.
     * Quelle und Klon sind DOM-gleich, die Felder werden ueber ihre Position zugeordnet.
     */
    freezeValues: function ($source, $clone) {
        const sourceFields = $source.find('input, textarea, select').toArray();
        const cloneFields = $clone.find('input, textarea, select').toArray();
        sourceFields.forEach(function (field, index) {
            const target = cloneFields[index];
            if (!target || target.tagName !== field.tagName) return;
            const tag = field.tagName.toLowerCase();
            if (tag === 'textarea') {
                target.textContent = field.value;
            } else if (tag === 'select') {
                const options = target.options;
                for (let i = 0; i < options.length; i++) {
                    if (field.options[i] && field.options[i].selected) options[i].setAttribute('selected', 'selected');
                    else options[i].removeAttribute('selected');
                }
            } else if (field.type === 'checkbox' || field.type === 'radio') {
                if (field.checked) target.setAttribute('checked', 'checked'); else target.removeAttribute('checked');
            } else if (field.type !== 'file') {
                target.setAttribute('value', field.value);
            }
        });
    },

    // Editor-Oberflaechen (TinyMCE, CKEditor 5) aus einem Klon entfernen, Textareas zuruecksetzen
    stripEditors: function ($item) {
        $item.find('.cke5-editor').each(function () {
            const $editor = $(this);
            let $next = $editor.next();
            while ($next.length && ($next.hasClass('ck-editor') || $next.hasClass('ck'))) {
                const $current = $next;
                $next = $current.next();
                $current.remove();
            }
            $editor.removeAttr('style').removeAttr('data-cke5-init-state').removeAttr('id');
        });
        $item.find('.tiny-editor').each(function () {
            const $editor = $(this);
            $editor.next('.tox-tinymce').remove();
            $editor.removeAttr('style').removeClass('mce-initialized');
        });
    },

    copy: function (element, item) {
        if (!item || !item.length) return false;
        // Editor-Inhalte in die Textareas schreiben, damit der Klon sie mitnimmt
        if (typeof tinymce !== 'undefined' && tinymce.triggerSave) tinymce.triggerSave();
        mblock_sync_all_cke5_to_textareas(item);

        const $clone = item.clone(false, false);
        this.freezeValues(item, $clone);
        this.stripEditors($clone);
        this.convertSelectpickerToPlainSelect($clone);
        $clone.removeClass('mblock-copy-glow mblock-paste-glow mblock-add-glow mblock-sortable-chosen mblock-dragging');

        this.data = {
            html: $clone.prop('outerHTML'),
            moduleType: this.getModuleType(item.closest('.mblock_wrapper')),
            timestamp: Date.now()
        };
        this.showCopiedState(item);
        this.saveToStorage();
        this.updatePasteButtons();
        return true;
    },

    paste: function (element, afterItem) {
        this.loadFromStorage();
        if (!this.data) {
            mblock_show_message('❌ ' + mblock_get_text('mblock_toast_clipboard_empty', 'Keine Daten in der Zwischenablage'), 'warning', 3000);
            return false;
        }
        const currentModuleType = this.getModuleType(element.closest('.mblock_wrapper'));
        const clipboardModuleType = this.data.moduleType || 'unknown_module';
        if (currentModuleType !== clipboardModuleType) {
            mblock_show_message('⚠️ ' + mblock_get_text('mblock_toast_module_type_mismatch', 'Modultyp stimmt nicht überein') + ': ' + clipboardModuleType + ' ≠ ' + currentModuleType, 'error', 4000);
            this.showModuleTypeMismatchWarning(currentModuleType, clipboardModuleType);
            return false;
        }

        const pastedItem = $(this.data.html);
        this.cleanupPastedItem(pastedItem);

        if (afterItem && afterItem.length) {
            const domElement = element.get(0);
            if (domElement && domElement._sortable && typeof domElement._sortable.destroy === 'function') {
                domElement._sortable.destroy();
                domElement._sortable = null;
            }
            afterItem.after(pastedItem);
        } else {
            element.prepend(pastedItem);
        }

        mblock_set_unique_id(pastedItem, true);
        mblock_init_sort(element);
        pastedItem.trigger('rex:ready', [pastedItem]);

        setTimeout(function () {
            if (typeof $.fn.selectpicker === 'function') {
                const selects = pastedItem.find('select.mblock-needs-selectpicker');
                if (selects.length) {
                    selects.removeClass('mblock-needs-selectpicker').addClass('selectpicker');
                    selects.selectpicker({ noneSelectedText: '—' }).on('rendered.bs.select', function () {
                        $(this).parent().removeClass('bs3-has-addon');
                    });
                    selects.selectpicker('refresh');
                }
            }
            if (typeof $.fn.chosen === 'function') {
                pastedItem.find('select.chosen').chosen();
            }
            pastedItem.find('input, select, textarea').trigger('change');
        }, 50);

        setTimeout(function () {
            if (!pastedItem.is(':visible')) return;
            mblock_smooth_scroll_to_element(pastedItem[0]);
            pastedItem.addClass('mblock-paste-glow');
            mblock_show_message('✅ ' + mblock_get_text('mblock_toast_paste_success', 'Block erfolgreich eingefügt!'), 'success', 4000);
            setTimeout(function () { pastedItem.removeClass('mblock-paste-glow'); }, 1200);
        }, 100);
        return true;
    },

    cleanupPastedItem: function (item) {
        item.removeAttr('data-mblock_index');
        this.stripEditors(item);

        item.find('input, textarea, select').each(function () {
            const $el = $(this);
            const name = $el.attr('name');
            // Praefix bis zum Reindex, damit Radio-Gruppen nicht mit bestehenden Bloecken kollidieren
            if (name && name.indexOf('mblock_new_') === -1) {
                $el.attr('name', 'mblock_new_' + name);
            }
            if ($el.attr('type') === 'file') {
                $el.val('');
            }
        });

        // Ids entfernen, ausser REX_*-Widget-Ids und Ziele von Tabs/Collapse innerhalb des Blocks (#230)
        const referencedIds = {};
        item.find('[href^="#"], [data-target^="#"], [aria-controls]').each(function () {
            const $ref = $(this);
            ['href', 'data-target'].forEach(function (attr) {
                const value = $ref.attr(attr);
                if (value && value.length > 1) referencedIds[value.slice(1)] = true;
            });
            (($ref.attr('aria-controls') || '').split(/\s+/)).forEach(function (id) { if (id) referencedIds[id] = true; });
        });
        item.find('[id]').each(function () {
            const id = $(this).attr('id');
            if (id && !/^REX_/.test(id) && !referencedIds[id]) $(this).removeAttr('id');
        });
    },

    showCopiedState: function (item) {
        item.addClass('mblock-copy-glow');
        setTimeout(function () { item.removeClass('mblock-copy-glow'); }, 1000);
        mblock_show_message('📋 ' + mblock_get_text('mblock_toast_copy_success', 'Block erfolgreich kopiert!'), 'success', 3000);
        const $copyBtn = item.find('.mblock-copy-btn');
        $copyBtn.addClass('is-copied');
        setTimeout(function () { $copyBtn.removeClass('is-copied'); }, 1000);
    },

    updatePasteButtons: function () {
        const self = this;
        if (this.data) {
            $('.mblock_wrapper').each(function () {
                const $wrapper = $(this);
                const compatible = self.getModuleType($wrapper) === (self.data.moduleType || 'unknown_module');
                $wrapper.find('.mblock-paste-btn').toggleClass('disabled', !compatible).prop('disabled', !compatible)
                    .attr('title', compatible ? 'Paste element (Module kompatibel)' : 'Cannot paste: Different module type');
            });
        } else {
            $('.mblock-paste-btn').addClass('disabled').prop('disabled', true).attr('title', 'No data in clipboard');
        }
        $('.mblock-copy-paste-toolbar').toggle(!!this.data);
    },

    // Selectpicker-Selects im Klon auf normale Selects zurueckfuehren; nach dem Einfuegen werden sie neu initialisiert
    convertSelectpickerToPlainSelect: function (container) {
        container.find('select.selectpicker, .bootstrap-select select').each(function () {
            const $select = $(this);
            const $clean = $select.clone();
            $clean.removeClass('selectpicker bs-select-hidden').addClass('mblock-needs-selectpicker')
                .removeAttr('data-live-search data-live-search-placeholder tabindex aria-describedby style');
            const $wrapper = $select.parents('.bootstrap-select').last();
            if ($wrapper.length) $wrapper.replaceWith($clean); else $select.replaceWith($clean);
        });
        container.find('.bootstrap-select').filter(function () { return !$(this).find('select').length; }).remove();
    },

    clear: function () {
        this.data = null;
        this.clearStorage();
        this.updatePasteButtons();
    }
};

// Online/Offline je Block: Button .mblock-offline-toggle-btn (vom Server mit data-offline gerendert)
// und das Hidden-Feld mblock_offline im Block.
var MBlockOnlineToggle = {
    toggle: function (element, item) {
        const $button = item.find('.mblock-offline-toggle-btn').first();
        return $button.length ? this.toggleAutoDetected(element, item, $button) : false;
    },

    setOfflineState: function (item, isOffline) {
        const $offlineInput = item.find('input[name*="mblock_offline"]');
        if (!$offlineInput.length) {
            console.warn('MBlock: No mblock_offline input found - must be defined in template for this functionality');
            return false;
        }
        $offlineInput.val(isOffline ? '1' : '0');
        item.toggleClass('mblock-offline', isOffline);
        return true;
    },

    toggleAutoDetected: function (element, item, button) {
        if (!item || !item.length || !button || !button.length) return false;
        const isOffline = button.attr('data-offline') !== '1';
        if (!this.setOfflineState(item, isOffline)) return false;
        button.removeClass('btn-default btn-warning btn-success btn-danger')
            .addClass(isOffline ? 'btn-danger' : 'btn-success')
            .attr('title', isOffline ? 'Set online' : 'Set offline')
            .attr('data-offline', isOffline ? '1' : '0');
        button.find('i').removeClass('fa-toggle-on fa-toggle-off').addClass('fa-solid').addClass(isOffline ? 'fa-toggle-off' : 'fa-toggle-on');
        button.html(button.html().replace(/Offline|Online/, isOffline ? 'Offline' : 'Online'));
        return true;
    }
};

// Toolbar Initialisierung
function mblock_init_toolbar(element) {
    try {
        // Nur initialisieren wenn Copy/Paste aktiviert ist
        if (!checkCopyPasteEnabled()) {
            return;
        }
        
        // Paste Button in Toolbar
        element.find('.mblock-copy-paste-toolbar .mblock-paste-btn')
            .off('click.mblock')
            .on('click.mblock', function (e) {
                e.preventDefault();
                try {
                    const $this = $(this);
                    if (!$this.hasClass('disabled') && !$this.prop('disabled')) {
                        MBlockClipboard.paste(element, false); // false = am Anfang einfügen
                    }
                } catch (error) {
                    console.error('MBlock: Fehler in toolbar paste click handler:', error);
                }
                return false;
            });

        // Clear Clipboard Button
        element.find('.mblock-copy-paste-toolbar .mblock-clear-clipboard')
            .off('click.mblock')
            .on('click.mblock', function (e) {
                e.preventDefault();
                try {
                    MBlockClipboard.clear();
                } catch (error) {
                    console.error('MBlock: Fehler in clear clipboard click handler:', error);
                }
                return false;
            });
            
    } catch (error) {
        console.error('MBlock: Fehler in mblock_init_toolbar:', error);
    }
}

// Synchronisiere CKEditor5 Inhalte zurück in versteckte Textareas bevor ein Formular abgesendet wird
function mblock_sync_all_cke5_to_textareas(context) {
    try {
        const scope = context && context.length ? context : $(document);

        // Find all cke5-editor textarea placeholders inside scope
        scope.find('.cke5-editor').each(function () {
            try {
                const $textarea = $(this);
                const editorId = $textarea.attr('id');

                // Prefer CKEDITOR global API if available (setData/getData pair)
                if (editorId && typeof window.CKEDITOR !== 'undefined' && window.CKEDITOR.instances && window.CKEDITOR.instances[editorId]) {
                    try {
                        const data = window.CKEDITOR.instances[editorId].getData();
                        $textarea.val(data);
                        return; // next
                    } catch (e) {
                        // continue to DOM fallback
                    }
                }

                // DOM fallback: try to find the editable area that CKEditor5 renders
                // and copy its innerHTML back to the hidden textarea
                let content = $textarea.val() || '';
                // look for nearby ck-editor editable content
                let $editable = $textarea.siblings('.ck, .ck-editor__main').find('.ck-editor__editable, .ck-content').first();
                if (!$editable.length) {
                    // try a wider search within the same parent
                    const $container = $textarea.closest('.mform, .mblock_wrapper, form, body');
                    if ($container.length) {
                        $editable = $container.find('.ck-editor__editable, .ck-content').first();
                    }
                }
                if ($editable && $editable.length) {
                    content = $editable.html();
                }

                // write value into textarea (keep HTML encoded/unencoded exactly as editor provides)
                $textarea.val(content);

            } catch (err) {
                // keep best-effort sync, don't break submit flow
                console.warn('MBlock: Fehler beim Synchronisieren eines CKE5-Editors in Textarea:', err);
            }
        });

        return true;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_sync_all_cke5_to_textareas:', error);
        return false;
    }
}

// Hook into all form submits to ensure ckeditor content is persisted to textareas before saving
$(document).on('submit', 'form', function (e) {
    try {
        mblock_sync_all_cke5_to_textareas($(this));
    } catch (err) {
        // fall back silently
    }
});

/**
 * Block nach oben oder unten verschieben; TinyMCE-Instanzen vorher sichern und entfernen,
 * nach dem Umhaengen werden alle Bloecke per rex:ready neu initialisiert.
 */
function mblock_move(element, item, direction) {
    const neighbour = direction === 'up' ? item.prev() : item.next();
    if (!neighbour.length) return;

    element.find('.tiny-editor').each(function () {
        const editorId = $(this).attr('id');
        if (editorId && typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
            try {
                tinymce.get(editorId).save();
                tinymce.get(editorId).remove();
            } catch (e) { console.warn(e); }
        }
    });

    setTimeout(function () {
        if (direction === 'up') item.insertBefore(neighbour); else item.insertAfter(neighbour);
        mblock_reindex(element);
        mblock_remove(element);
        neighbour.trigger('mblock:change', [neighbour]);
        element.find('> div.sortitem').each(function () {
            $(this).trigger('rex:ready', [$(this)]);
        });
    }, 150);
}

function mblock_moveup(element, item) {
    mblock_move(element, item, 'up');
}

function mblock_movedown(element, item) {
    mblock_move(element, item, 'down');
}

function mblock_scroll(element, item) {
    try {
        if (!element || !element.length || !item || !item.length) {
            return false;
        }

        const elementData = element.data();
        
        // Wenn smooth_scroll aktiviert ist, verwende die smooth scroll Funktion
        if (elementData && elementData.hasOwnProperty('smooth_scroll') && elementData.smooth_scroll === true) {
            if (typeof $.mblockSmoothScroll === 'function') {
                $.mblockSmoothScroll({
                    scrollTarget: item,
                    speed: 500
                });
                return true;
            }
        }
        
        // Fallback: Standard-Browser-Scrolling zu dem Element
        if (item.length && item.offset()) {
            const itemOffset = item.offset().top;
            const windowHeight = $(window).height();
            const scrollTop = $(window).scrollTop();
            
            // Nur scrollen wenn Element nicht bereits sichtbar ist
            if (itemOffset < scrollTop || itemOffset > (scrollTop + windowHeight - 200)) {
                $('html, body').animate({
                    scrollTop: itemOffset - 100 // 100px Abstand vom oberen Rand
                }, 300);
            }
        }
        
        return true;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_scroll:', error);
        return false;
    }
}

/**
 * Buttons eines Wrappers binden: hinzufuegen, loeschen, verschieben, kopieren/einfuegen, online/offline.
 */
function mblock_add(element) {
    if (!element || !element.length) return false;
    const items = element.find('> div.sortitem');
    const bind = function (selector, handler) {
        items.find(selector).off('click.mblock').on('click.mblock', function (e) {
            e.preventDefault();
            const $this = $(this);
            if ($this.prop('disabled') || $this.hasClass('disabled')) return false;
            handler($this, $this.closest('div.sortitem'));
            return false;
        });
    };

    bind('.addme', function ($btn, $item) {
        const itemIndex = $item.attr('data-mblock_index');
        if (itemIndex) element.attr('data-mblock_clicked_add_item', itemIndex);
        mblock_add_item(element, $item);
    });

    bind('.removeme', function ($btn, $item) {
        const elementData = element.data() || {};
        if (elementData.delete_confirm) {
            mblock_show_confirm(element, $item, elementData.delete_confirm).then(function (confirmed) {
                if (!confirmed) return;
                $item.data('__mblock_confirmed', true);
                mblock_remove_item(element, $item);
            });
        } else {
            mblock_remove_item(element, $item);
        }
    });

    bind('.moveup', function ($btn, $item) { mblock_move(element, $item, 'up'); });
    bind('.movedown', function ($btn, $item) { mblock_move(element, $item, 'down'); });

    if (checkCopyPasteEnabled()) {
        bind('.mblock-copy-btn', function ($btn, $item) { MBlockClipboard.copy(element, $item); });
        bind('.mblock-paste-btn', function ($btn, $item) { MBlockClipboard.paste(element, $item); });
        MBlockClipboard.updatePasteButtons();
    }

    bind('.mblock-offline-toggle-btn', function ($btn, $item) { MBlockOnlineToggle.toggleAutoDetected(element, $item, $btn); });

    // Optionaler Streifen am Ende des Wrappers (Template-Tag <div class="mblock-add-bar"><button class="mblock-add-last">)
    element.find('> .mblock-add-bar .mblock-add-last').off('click.mblock').on('click.mblock', function (e) {
        e.preventDefault();
        if ($(this).prop('disabled')) return false;
        const last = element.find('> div.sortitem').last();
        mblock_add_item(element, last.length ? last : false);
        return false;
    });

    return true;
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

// ==================== CKEditor5 & TinyMCE Data Cleanup ====================
/**
 * Clean editor artifacts before form submission
 * - CKEditor5: Removes ck-list-bogus-paragraph spans and fixes internal links
 * - TinyMCE: Ensures content is properly saved to textarea
 * This runs on every form submit to ensure clean data storage
 */
$(document).on('submit', 'form', function(e) {
    try {
        // ========== TinyMCE Handling ==========
        // Ensure all TinyMCE editors save their content to textareas
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
        
        // ========== CKEditor5 Handling ==========
        // Store original internal links before CKEditor5 processes them
        const internalLinksMap = new Map();
        
        // Get CKEditor5 editors via the global function
        if (typeof window.cke5_get_editors === 'function') {
            const editors = window.cke5_get_editors();
            
            if (editors && typeof editors === 'object') {
                Object.keys(editors).forEach(function(editorId) {
                    const editor = editors[editorId];
                    
                    if (editor && editor.sourceElement) {
                        try {
                            // Get current data from editor
                            let data = editor.getData();
                            
                            if (data && typeof data === 'string') {
                                // Step 0: Remove CKEditor filler content
                                // This fixes the issue where empty blocks save <p><br data-cke-filler="true"></p>
                                data = data.replace(/<p><br data-cke-filler="true" ?\/?><\/p>/gi, '');
                                data = data.replace(/<br data-cke-filler="true" ?\/?>/gi, '');
                                data = data.replace(/<p class="ck-placeholder" data-placeholder="[^"]+"><\/p>/gi, '');
                                
                                // Step 0.1: Remove empty paragraphs that might result from the above
                                data = data.replace(/<p>&nbsp;<\/p>/gi, '');
                                data = data.replace(/^(\s*<p>\s*<br\s*\/?>\s*<\/p>\s*)*$/i, '');
                                
                                // Step 1: Remove ck-list-bogus-paragraph spans
                                data = data.replace(/<span class="ck-list-bogus-paragraph">(.*?)<\/span>/gi, '$1');
                                
                                // Step 2: Try to restore internal links
                                // Check if we have the model data with proper link attributes
                                if (editor.model && editor.model.document) {
                                    const root = editor.model.document.getRoot();
                                    const range = editor.model.createRangeIn(root);
                                    
                                    // Collect all links with their attributes from the model
                                    for (const value of range.getWalker({ ignoreElementEnd: true })) {
                                        if (value.item.is('element', 'link')) {
                                            const linkHref = value.item.getAttribute('linkHref');
                                            
                                            // If we find a redaxo:// link in the model, store it
                                            if (linkHref && linkHref.startsWith('redaxo://')) {
                                                // Get the text content
                                                const textContent = Array.from(value.item.getChildren())
                                                    .map(child => child.data || '')
                                                    .join('');
                                                
                                                if (textContent) {
                                                    internalLinksMap.set(textContent, linkHref);
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                // Step 3: Replace # links that should be internal links
                                // This fixes the RexLink plugin issue where internal links become #
                                if (internalLinksMap.size > 0) {
                                    internalLinksMap.forEach((href, text) => {
                                        // Find <a href="#">text</a> and replace with proper redaxo:// link
                                        const escapedText = text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                                        const pattern = new RegExp(`<a href="#">([^<]*${escapedText}[^<]*)</a>`, 'gi');
                                        data = data.replace(pattern, `<a href="${href}">$1</a>`);
                                    });
                                }
                                
                                // Fallback: If no model data available, try to detect patterns
                                // Look for data-link-id attributes that might be in the DOM
                                const $temp = $('<div>').html(data);
                                let hasChanges = false;
                                
                                $temp.find('a[href="#"]').each(function() {
                                    const $link = $(this);
                                    const linkId = $link.attr('data-link-id') || $link.attr('data-rex-link');
                                    
                                    if (linkId) {
                                        // Restore internal link
                                        $link.attr('href', 'redaxo://' + linkId);
                                        hasChanges = true;
                                    }
                                });
                                
                                if (hasChanges) {
                                    data = $temp.html();
                                }
                                
                                // Update the textarea with cleaned data
                                const originalValue = editor.sourceElement.value;
                                if (data !== originalValue) {
                                    editor.sourceElement.value = data;
                                }
                            }
                        } catch (error) {
                            console.warn('MBlock: Error cleaning CKEditor5 data for', editorId, error);
                        }
                    }
                });
            }
        }
        
        // Also handle textareas directly (backup method)
        $(this).find('textarea.cke5-editor').each(function() {
            try {
                const $textarea = $(this);
                let value = $textarea.val();
                
                if (value && typeof value === 'string') {
                    // Step 0: Remove CKEditor filler content
                    value = value.replace(/<p><br data-cke-filler="true" ?\/?><\/p>/gi, '');
                    value = value.replace(/<br data-cke-filler="true" ?\/?>/gi, '');
                    value = value.replace(/<p class="ck-placeholder" data-placeholder="[^"]+"><\/p>/gi, '');
                    
                    // Step 0.1: Remove empty paragraphs that might result from the above
                    value = value.replace(/<p>&nbsp;<\/p>/gi, '');
                    value = value.replace(/^(\s*<p>\s*<br\s*\/?>\s*<\/p>\s*)*$/i, '');
                    
                    // Remove ck-list-bogus-paragraph spans
                    value = value.replace(/<span class="ck-list-bogus-paragraph">(.*?)<\/span>/gi, '$1');
                    
                    // Try to fix # links by looking for data attributes
                    const $temp = $('<div>').html(value);
                    let hasChanges = false;
                    
                    $temp.find('a[href="#"]').each(function() {
                        const $link = $(this);
                        const linkId = $link.attr('data-link-id') || $link.attr('data-rex-link');
                        
                        if (linkId) {
                            $link.attr('href', 'redaxo://' + linkId);
                            hasChanges = true;
                        }
                    });
                    
                    if (hasChanges) {
                        value = $temp.html();
                    }
                    
                    if (value !== $textarea.val()) {
                        $textarea.val(value);
                    }
                }
            } catch (error) {
                console.warn('MBlock: Error cleaning textarea CKE5 data:', error);
            }
        });
        
    } catch (error) {
        console.error('MBlock: Error in editor cleanup:', error);
        // Don't prevent form submission even if cleanup fails
    }
});