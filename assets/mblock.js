/**
 * Created by joachimdoerr on 30.07.16.
 * Enhanced with robust error handling and memory management
 */

let mblock = '.mblock_wrapper';

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

$(document).on('rex:ready', function (e, container) {
    try {
        // Initialize clipboard system
        MBlockClipboard.init();
        
        if (container && typeof container.find === 'function') {
            container.find(mblock).each(function () {
                const $element = $(this);
                if ($element.length) {
                    try {
                        mblock_init($element);
                    } catch (initError) {
                        console.error('MBlock: Fehler beim Initialisieren eines einzelnen MBlock-Elements:', initError);
                        // Einzelne Fehler nicht die gesamte Initialisierung abbrechen lassen
                    }
                }
            });
        } else {
            // Fallback: Wenn kein Container, alle MBlock-Elemente initialisieren
            $(mblock).each(function () {
                const $element = $(this);
                if ($element.length) {
                    try {
                        mblock_init($element);
                    } catch (initError) {
                        console.error('MBlock: Fehler beim Initialisieren (Fallback):', initError);
                    }
                }
            });
        }
    } catch (error) {
        console.error('MBlock: Kritischer Fehler bei rex:ready:', error);
        
        // Emergency-Fallback: Versuche zumindest eine Basic-Initialisierung
        try {
            $(mblock).each(function () {
                const $element = $(this);
                if ($element.length && !$element.data('mblock_run')) {
                    console.warn('MBlock: Notfall-Initialisierung für Element');
                    $element.data('mblock_run', 1);
                }
            });
        } catch (emergencyError) {
            console.error('MBlock: Notfall-Initialisierung fehlgeschlagen:', emergencyError);
        }
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
            mblock_set_unique_id(element, false);

            const minValue = element.data('min');
            const maxValue = element.data('max');
            if (minValue == 1 && maxValue == 1) {
                element.addClass('hide_removeadded').addClass('hide_sorthandle');
            }
        }
        
        mblock_add_plus(element);
        
        // Initialize toolbar handlers
        mblock_init_toolbar(element);
        
        // Initialize online/offline states for existing items
        try {
            MBlockOnlineToggle.initializeStates(element);
        } catch (initError) {
            console.warn('MBlock: Fehler beim Initialisieren der Online/Offline-States:', initError);
        }
        
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
        if (finded.length >= element.data('max')) {
            element.find('.addme').prop('disabled', true);
        } else {
            element.find('.addme').prop('disabled', false);
        }
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

        // Moderne Sortable.js API (von bloecks)
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
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'mblock-sortable-chosen',
                        dragClass: 'mblock-dragging',
                        fallbackTolerance: 3,
                        forceFallback: false,
                        onStart: function (evt) {
                            try {
                                document.body.classList.add('mblock-drag-active');
                                if (evt.item) {
                                    evt.item.classList.add('mblock-dragging');
                                }
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
                    // Fallback to jQuery UI if available
                    mblock_sort_it_jquery_fallback(element);
                }
            }, 10);
            
            return true;
            
        } 
        // Fallback für jQuery UI Sortable (Legacy-Support)
        else {
            return mblock_sort_it_jquery_fallback(element);
        }
        
    } catch (error) {
        console.error('MBlock: Fehler in mblock_sort_it:', error);
        return mblock_sort_it_jquery_fallback(element);
    }
}

// Separate jQuery UI Sortable fallback function
function mblock_sort_it_jquery_fallback(element) {
    try {
        if (typeof element.sortable === 'function') {
            // Destroy existing jQuery UI sortable if it exists
            try {
                if (element.hasClass('ui-sortable')) {
                    element.sortable('destroy');
                }
            } catch (destroyError) {
                console.warn('MBlock: Fehler beim Zerstören der jQuery UI Sortable-Instanz:', destroyError);
            }

            element.sortable({
                handle: '.sorthandle',
                placeholder: 'sortable-placeholder',
                tolerance: 'pointer',
                start: function (event, ui) {
                    try {
                        document.body.classList.add('mblock-drag-active');
                        if (ui.item) {
                            ui.item.addClass('mblock-dragging');
                        }
                    } catch (error) {
                        console.error('MBlock: Fehler in jQuery UI sortable start:', error);
                    }
                },
                update: function (event, ui) {
                    try {
                        document.body.classList.remove('mblock-drag-active');
                        if (ui.item) {
                            ui.item.removeClass('mblock-dragging');
                            ui.item.addClass('mblock-dropped-flash');
                            setTimeout(() => {
                                ui.item.removeClass('mblock-dropped-flash');
                            }, 600);
                        }
                        
                        mblock_reindex(element);
                        mblock_remove(element);
                        // trigger event
                        let iClone = ui.item;
                        if (iClone.length) {
                            iClone.trigger('mblock:change', [iClone]);
                        }
                    } catch (error) {
                        console.error('MBlock: Fehler in jQuery UI sortable update:', error);
                    }
                }
            });
            return true;
        } else {
            console.error('MBlock: Weder Sortable.js noch jQuery UI Sortable gefunden');
            return false;
        }
    } catch (error) {
        console.error('MBlock: Fehler in jQuery UI fallback:', error);
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

            // Event-Handler für Checkboxen optimieren
            const elementType = $element.attr('type');
            if (elementType === 'checkbox') {
                $element.off('change.mblock').on('change.mblock', function () {
                    $(this).val($(this).is(':checked') ? 1 : 0);
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

        // INPUT-Elemente (REX_LINKLIST, REX_MEDIALIST)
        if (nodeName === 'INPUT' && 
            (elementId.indexOf('REX_LINKLIST_') >= 0 || elementId.indexOf('REX_MEDIALIST_') >= 0)) {
            
            const parentEindex = $element.parent().data('eindex') || eindex;
            const newId = elementId.replace(/\d+/, sindex + mblock_count + '00' + parentEindex);
            $element.attr('id', newId);

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
        const $parent = $element.parent();
        $parent.find('a.btn-popup').each(function () {
            const $btn = $(this);
            const onclick = $btn.attr('onclick');
            if (onclick) {
                const newOnclick = onclick
                    .replace(/\('?\d+/, '(\'' + sindex + mblock_count + '00' + eindex)
                    .replace(/_\d+/, '_' + sindex + mblock_count + '00' + eindex);
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
        $sortItem.find('.custom-link').each(function (key) {
            const eindex = key + 1;
            const $customlink = $(this);
            
            $customlink.find('input').each(function () {
                const $input = $(this);
                const inputId = $input.attr('id');
                if (inputId) {
                    $input.attr('id', inputId.replace(/\d+/, sindex + mblock_count + '00' + eindex));
                }
            });
            
            $customlink.find('a.btn-popup').each(function () {
                const $btn = $(this);
                const btnId = $btn.attr('id');
                if (btnId) {
                    $btn.attr('id', btnId.replace(/\d+/, sindex + mblock_count + '00' + eindex));
                }
            });
            
            $customlink.attr('data-id', sindex + mblock_count + '00' + eindex);
            
            // Trigger MForm custom link function if available
            if (typeof window.mform_custom_link === 'function') {
                try {
                    window.mform_custom_link($customlink);
                } catch (mformError) {
                    console.warn('MBlock: MForm custom link Fehler:', mformError);
                }
            }
        });
    } catch (error) {
        console.error('MBlock: Fehler in mblock_reindex_special_elements:', error);
    }
}

function mblock_replace_for(element) {

    element.find('> div.sortitem').each(function (index) {
        var mblock = $(this);
        mblock.find('input:not(:checkbox):not(:radio),textarea,select').each(function (key) {
            var el = $(this),
                id = el.attr('id'),
                name = el.attr('name');
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

    if (item === false) {
        // add clone
        element.prepend(iClone);

    } else if (item.parent().hasClass(element.attr('class'))) {
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
        
        // jQuery UI Sortable Fallback
        try {
            if (typeof element.sortable === 'function' && element.hasClass('ui-sortable')) {
                element.sortable("destroy");
            }
        } catch (jqueryError) {
            console.warn('MBlock: jQuery UI sortable destroy error in add_item:', jqueryError);
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
        if (elementData && elementData.hasOwnProperty('delete_confirm')) {
            if (!confirm(elementData.delete_confirm)) {
                return false;
            }
        }

        const itemParent = item.parent();
        const elementClass = element.attr('class');
        
        if (itemParent.length && elementClass && itemParent.hasClass(elementClass)) {
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
            
            // jQuery UI Sortable Fallback
            try {
                if (typeof element.sortable === 'function' && element.hasClass('ui-sortable')) {
                    element.sortable("destroy");
                }
            } catch (jqueryError) {
                console.warn('MBlock: jQuery UI sortable destroy error in remove_item:', jqueryError);
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

// Copy & Paste Funktionalität mit Session/Local Storage
var MBlockClipboard = {
    data: null,
    storageKey: 'mblock_clipboard',
    useSessionStorage: true, // true = Session Storage, false = Local Storage
    
    // Initialize clipboard from storage
    init: function() {
        try {
            this.loadFromStorage();
        } catch (error) {
            console.warn('MBlock: Fehler beim Initialisieren des Clipboards:', error);
        }
    },
    
    // Get storage object (sessionStorage or localStorage)
    getStorage: function() {
        try {
            return this.useSessionStorage ? sessionStorage : localStorage;
        } catch (error) {
            console.warn('MBlock: Storage nicht verfügbar:', error);
            return null;
        }
    },
    
    // Save clipboard data to storage
    saveToStorage: function() {
        try {
            const storage = this.getStorage();
            if (storage && this.data) {
                storage.setItem(this.storageKey, JSON.stringify({
                    ...this.data,
                    // Add metadata
                    savedAt: new Date().toISOString(),
                    sessionId: this.getSessionId()
                }));
                return true;
            }
        } catch (error) {
            console.warn('MBlock: Fehler beim Speichern in Storage:', error);
        }
        return false;
    },
    
    // Load clipboard data from storage
    loadFromStorage: function() {
        try {
            const storage = this.getStorage();
            if (storage) {
                const stored = storage.getItem(this.storageKey);
                if (stored) {
                    const parsedData = JSON.parse(stored);
                    
                    // Check if data is still valid (max 24 hours for localStorage)
                    if (!this.useSessionStorage && parsedData.savedAt) {
                        const savedDate = new Date(parsedData.savedAt);
                        const now = new Date();
                        const hoursDiff = (now - savedDate) / (1000 * 60 * 60);
                        
                        if (hoursDiff > 24) {
                            this.clearStorage();
                            return false;
                        }
                    }
                    
                    this.data = parsedData;
                    this.updatePasteButtons();
                    return true;
                }
            }
        } catch (error) {
            console.warn('MBlock: Fehler beim Laden aus Storage:', error);
            this.clearStorage(); // Clear corrupted storage
        }
        return false;
    },
    
    // Clear storage
    clearStorage: function() {
        try {
            const storage = this.getStorage();
            if (storage) {
                storage.removeItem(this.storageKey);
            }
        } catch (error) {
            console.warn('MBlock: Fehler beim Leeren des Storages:', error);
        }
    },
    
    // Generate simple session ID
    getSessionId: function() {
        if (!this._sessionId) {
            this._sessionId = Date.now().toString() + Math.random().toString(36).substr(2, 9);
        }
        return this._sessionId;
    },
    
    // Toggle between session and local storage
    toggleStorageMode: function() {
        const oldData = this.data;
        this.clearStorage(); // Clear current storage
        
        this.useSessionStorage = !this.useSessionStorage;
        
        if (oldData) {
            this.data = oldData;
            this.saveToStorage(); // Save to new storage
        }
        
        return this.useSessionStorage;
    },
    
    // Show warning when trying to paste between different module types
    showModuleTypeMismatchWarning: function(currentType, clipboardType) {
        try {
            // Create temporary warning message
            const warningHtml = `
                <div class="alert alert-warning mblock-type-warning" style="margin: 10px 0; position: relative; z-index: 1000;">
                    <strong>Achtung:</strong> Das kopierte Element stammt aus einem anderen Modul-Typ. 
                    Das Einfügen ist nicht möglich.<br>
                    <small>Aktueller Typ: <code>${currentType}</code> | Zwischenablage: <code>${clipboardType}</code></small>
                    <button type="button" class="close" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none; font-size: 18px;" onclick="$(this).parent().fadeOut()">&times;</button>
                </div>
            `;
            
            // Find best place to show warning
            const targetElement = $('.mblock_wrapper').first();
            if (targetElement.length) {
                // Remove any existing warnings
                $('.mblock-type-warning').remove();
                
                // Add new warning
                targetElement.prepend(warningHtml);
                
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    $('.mblock-type-warning').fadeOut('slow');
                }, 5000);
                
            } else {
                // Fallback to browser alert
                alert('Das kopierte Element stammt aus einem anderen Modul und kann hier nicht eingefügt werden.');
            }
            
        } catch (error) {
            console.error('MBlock: Fehler beim Anzeigen der Modultyp-Warnung:', error);
            // Fallback to browser alert
            alert('Das kopierte Element kann hier nicht eingefügt werden (anderer Modul-Typ).');
        }
    },

    // Get module type/name from wrapper or form context
    getModuleType: function(wrapper) {
        try {
            // Try to find module identifier from various sources
            
            // 1. Check for data attributes on wrapper
            let moduleType = wrapper.attr('data-module-type') || wrapper.attr('data-module-name');
            if (moduleType) {
                return moduleType;
            }
            
            // 2. Check for hidden input with module name
            const moduleInput = wrapper.find('input[name*="module_id"], input[name*="module_name"]').first();
            if (moduleInput.length) {
                moduleType = moduleInput.val();
                if (moduleType) {
                    return moduleType;
                }
            }
            
            // 3. Check for form action or parent context
            const form = wrapper.closest('form');
            if (form.length) {
                const action = form.attr('action') || '';
                const moduleMatch = action.match(/module_id=(\d+)/);
                if (moduleMatch) {
                    return 'module_' + moduleMatch[1];
                }
            }
            
            // 4. Check for unique class or id patterns on wrapper
            const classes = wrapper.attr('class') || '';
            const classMatch = classes.match(/mblock-module-(\w+)/);
            if (classMatch) {
                return classMatch[1];
            }
            
            // 5. Fallback: use closest identifying parent
            const parentWithId = wrapper.closest('[id]');
            if (parentWithId.length) {
                const id = parentWithId.attr('id');
                if (id.includes('module')) {
                    return id;
                }
            }
            
            // 6. Last resort: use URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const moduleId = urlParams.get('module_id') || urlParams.get('article_id');
            if (moduleId) {
                return 'context_' + moduleId;
            }
            
            // Default fallback
            return 'unknown_module';
            
        } catch (error) {
            console.warn('MBlock: Fehler beim Ermitteln des Modultyps:', error);
            return 'unknown_module';
        }
    },

    copy: function(element, item) {
        
        try {
            if (!item || !item.length) {
                console.warn('MBlock: Kein Item zum Kopieren gefunden');
                return false;
            }
            // Get module type from the closest mblock wrapper
            const wrapper = item.closest('.mblock_wrapper');
            const moduleType = this.getModuleType(wrapper);
            // Clone item completely
            const clonedItem = item.clone(true, true);
            
            // Convert selectpicker elements back to plain select elements for clean copying
            this.convertSelectpickerToPlainSelect(clonedItem);
            
            // Capture comprehensive form data
            const formData = this.captureComplexFormData(item);
            // Store in clipboard with metadata and form values
            this.data = {
                html: clonedItem.prop('outerHTML'),
                formData: formData,
                moduleType: moduleType, // Store module type
                timestamp: Date.now(),
                source: element.attr('class') || 'mblock_wrapper'
            };
            // Visual feedback
            this.showCopiedState(item);
            
            // Save to storage
            const saved = this.saveToStorage();
            
            // Update paste button states
            this.updatePasteButtons();
            
            return true;
            
        } catch (error) {
            console.error('MBlock: Fehler beim Kopieren:', error);
            return false;
        }
    },
    
    captureComplexFormData: function(item) {
        const formData = {};
        
        try {
            // Regular form elements
            item.find('input, textarea, select').each(function() {
                const $el = $(this);
                const name = $el.attr('name') || $el.attr('id');
                
                if (name) {
                    if ($el.is(':checkbox') || $el.is(':radio')) {
                        formData[name] = {
                            type: 'checkbox_radio',
                            value: $el.val(),
                            checked: $el.prop('checked'),
                            defaultValue: $el.attr('value')
                        };
                    } else if ($el.is('select')) {
                        const selectedOptions = [];
                        $el.find('option:selected').each(function() {
                            selectedOptions.push($(this).val());
                        });
                        formData[name] = {
                            type: 'select',
                            value: $el.val(),
                            selectedOptions: selectedOptions,
                            html: $el.html()
                        };
                    } else {
                        formData[name] = {
                            type: 'input',
                            value: $el.val(),
                            placeholder: $el.attr('placeholder')
                        };
                    }
                }
            });
            
            // CKEditor content (CKE5)
            item.find('.cke5-editor').each(function() {
                const $editor = $(this);
                const name = $editor.attr('name');
                if (name) {
                    // Try to get CKEditor content
                    let content = $editor.val();
                    
                    // Check if there's a CKEditor instance
                    const editorId = $editor.attr('id');
                    if (editorId && window.CKEDITOR && window.CKEDITOR.instances[editorId]) {
                        content = window.CKEDITOR.instances[editorId].getData();
                    }
                    
                    formData[name] = {
                        type: 'ckeditor',
                        value: content,
                        config: {
                            lang: $editor.attr('data-lang'),
                            profile: $editor.attr('data-profile')
                        }
                    };
                }
            });
            
            // REX Media widgets
            item.find('.rex-js-widget-media').each(function() {
                const $widget = $(this);
                const $input = $widget.find('input[id^="REX_MEDIA_"]');
                
                if ($input.length) {
                    const name = $input.attr('name');
                    const mediaId = $input.attr('id');
                    
                    formData[name || mediaId] = {
                        type: 'rex_media',
                        value: $input.val(),
                        mediaId: mediaId,
                        preview: $widget.find('.rex-js-media-preview').html()
                    };
                }
            });
            
            // REX Link widgets  
            item.find('.rex-js-widget-customlink').each(function() {
                const $widget = $(this);
                const $hiddenInput = $widget.find('input[type="hidden"]');
                const $nameInput = $widget.find('input[readonly]');
                
                if ($hiddenInput.length) {
                    const name = $hiddenInput.attr('name');
                    
                    formData[name] = {
                        type: 'rex_link',
                        value: $hiddenInput.val(),
                        displayText: $nameInput.length ? $nameInput.val() : '',
                        dataId: $widget.attr('data-id'),
                        dataClang: $widget.attr('data-clang')
                    };
                }
            });
            
            // Bootstrap Select elements
            item.find('.bootstrap-select select').each(function() {
                const $select = $(this);
                const name = $select.attr('name');
                
                if (name) {
                    formData[name + '_bootstrap'] = {
                        type: 'bootstrap_select',
                        value: $select.val(),
                        selectedText: $select.find('option:selected').text(),
                        title: $select.closest('.bootstrap-select').find('.filter-option-inner-inner').text()
                    };
                }
            });
            
            // Tab states
            item.find('.nav-tabs .active a').each(function(index) {
                const $tab = $(this);
                const href = $tab.attr('href');
                
                if (href) {
                    formData['active_tab_' + index] = {
                        type: 'active_tab',
                        href: href,
                        text: $tab.text()
                    };
                }
            });
            
            // Collapse states
            item.find('.collapse.in, .collapse.show').each(function(index) {
                const $collapse = $(this);
                const id = $collapse.attr('id');
                
                if (id) {
                    formData['collapse_state_' + id] = {
                        type: 'collapse_state',
                        isOpen: true
                    };
                }
            });
            
            return formData;
            
        } catch (error) {
            console.error('MBlock: Fehler beim Erfassen der Formulardaten:', error);
            return formData;
        }
    },
    
    paste: function(element, afterItem) {
        try {
            // Load fresh data from storage in case it was updated in another tab
            this.loadFromStorage();
            
            if (!this.data) {
                console.warn('MBlock: Keine Daten in der Zwischenablage');
                return false;
            }
            
            // Check module type compatibility
            const currentWrapper = element.closest('.mblock_wrapper');
            const currentModuleType = this.getModuleType(currentWrapper);
            const clipboardModuleType = this.data.moduleType || 'unknown_module';
            
            if (currentModuleType !== clipboardModuleType) {
                console.warn('MBlock: Modultyp stimmt nicht überein. Paste abgebrochen.', {
                    current: currentModuleType,
                    clipboard: clipboardModuleType
                });
                
                // Show user feedback
                this.showModuleTypeMismatchWarning(currentModuleType, clipboardModuleType);
                return false;
            }
            // Create element from clipboard
            const pastedItem = $(this.data.html);
            
            // Clean up IDs and names to avoid conflicts
            this.cleanupPastedItem(pastedItem);
            
            // Restore form values from clipboard with enhanced data restoration
            if (this.data.formData) {
                this.restoreComplexFormData(pastedItem, this.data.formData);
            }
            
            // Insert item
            if (afterItem && afterItem.length) {
                // Destroy sortable before manipulation with better error handling
                try {
                    const domElement = element.get(0);
                    if (domElement && domElement._sortable && typeof domElement._sortable.destroy === 'function') {
                        domElement._sortable.destroy();
                        domElement._sortable = null;
                    }
                } catch (sortableError) {
                    console.warn('MBlock: Sortable destroy error in paste:', sortableError);
                }
                
                // jQuery UI Sortable Fallback
                try {
                    if (typeof element.sortable === 'function' && element.hasClass('ui-sortable')) {
                        element.sortable("destroy");
                    }
                } catch (jqueryError) {
                    console.warn('MBlock: jQuery UI sortable destroy error in paste:', jqueryError);
                }
                
                afterItem.after(pastedItem);
            } else {
                element.prepend(pastedItem);
            }
            
            // Add unique ids
            mblock_set_unique_id(pastedItem, true);
            
            // Reinitialize sortable
            mblock_init_sort(element);
            
            // Trigger rex:ready event only on the pasted item for component initialization
            // We handle selectpicker manually below, so we only need this single event
            pastedItem.trigger('rex:ready', [pastedItem]);
            
            // Specific component reinitialization
            setTimeout(function() {
                // Initialize selectpicker with REDAXO core method for elements marked during copy
                if (typeof $.fn.selectpicker === 'function') {
                    var selects = pastedItem.find('select.mblock-needs-selectpicker');
                    if (selects.length) {
                        
                        // Remove marker class and add proper selectpicker class
                        selects.removeClass('mblock-needs-selectpicker').addClass('selectpicker');
                        
                        // Initialize with REDAXO settings
                        selects.selectpicker({
                            noneSelectedText: '—'
                        }).on('rendered.bs.select', function () {
                            $(this).parent().removeClass('bs3-has-addon');
                        });
                        selects.selectpicker('refresh');
                    }
                }
                
                // Reinitialize other common components
                if (typeof $.fn.chosen === 'function') {
                    pastedItem.find('select.chosen').chosen();
                }
                
                // Trigger change events to update any dependent elements
                pastedItem.find('input, select, textarea').trigger('change');
            }, 50);
            
            // Scroll to pasted item
            setTimeout(function() {
                if (pastedItem && pastedItem.length && pastedItem.is(':visible')) {
                    mblock_scroll(element, pastedItem);
                }
            }, 100);
            
            return true;
            
        } catch (error) {
            console.error('MBlock: Fehler beim Einfügen:', error);
            return false;
        }
    },
    
    cleanupPastedItem: function(item) {
        try {
            
            // Remove mblock-specific data attributes
            item.removeAttr('data-mblock_index');
            
            // Clean form elements
            item.find('input, textarea, select').each(function() {
                const $el = $(this);
                const name = $el.attr('name');
                if (name && name.indexOf('mblock_new_') === -1) {
                    $el.attr('name', 'mblock_new_' + name);
                }
                
                // DON'T clear values here - they will be restored later by restoreComplexFormData
                // Only clear specific input types that should always be empty
                const inputType = $el.attr('type');
                if (inputType === 'file') {
                    $el.val(''); // File inputs should always be cleared
                }
                
                // Keep unique values for unique fields
                if ($el.attr('data-unique') && !$el.val()) {
                    // Only generate unique value if field is empty
                    const unique_id = Math.random().toString(16).slice(2);
                    $el.val(unique_id);
                }
            });
            
            // Clean IDs that might cause conflicts
            item.find('[id]').each(function() {
                const $el = $(this);
                const id = $el.attr('id');
                if (id && !id.match(/^REX_/)) {
                    $el.removeAttr('id');
                }
            });
            
        } catch (error) {
            console.error('MBlock: Fehler beim Bereinigen des eingefügten Items:', error);
        }
    },

    // Smart field matching for REDAXO's dynamic field names
    findFieldBySmartMatching: function(container, originalName) {
        // Try exact match first
        let $field = container.find(`[name="${originalName}"]`);
        if ($field.length) return $field;
        
        // Try with mblock_new_ prefix
        $field = container.find(`[name="mblock_new_${originalName}"]`);
        if ($field.length) return $field;
        
        // For REDAXO field names like REX_INPUT_VALUE[1][0][fieldname], 
        // extract the field base and try to match by pattern
        if (originalName.includes('REX_INPUT_VALUE') || originalName.includes('REX_LINK')) {
            const patterns = this.extractREDAXOFieldPatterns(originalName);
            
            for (const pattern of patterns) {
                $field = container.find(`[name*="${pattern}"]`);
                if ($field.length) {
                    return $field;
                }
            }
        }
        
        // Fallback: try partial name match
        const nameBase = originalName.replace(/^mblock_new_/, '');
        $field = container.find(`[name*="${nameBase}"]`);
        
        return $field;
    },

    // Extract patterns from REDAXO field names for matching
    extractREDAXOFieldPatterns: function(fieldName) {
        const patterns = [];
        
        // REX_INPUT_VALUE[1][0][fieldname] -> ["fieldname", "[fieldname]"]
        const inputValueMatch = fieldName.match(/REX_INPUT_VALUE\[.*?\]\[.*?\]\[(.+?)\]/);
        if (inputValueMatch) {
            patterns.push(inputValueMatch[1]); // fieldname
            patterns.push(`[${inputValueMatch[1]}]`); // [fieldname]
        }
        
        // REX_LINK_11001_NAME -> ["REX_LINK", "_NAME"]  
        const linkMatch = fieldName.match(/REX_LINK_(\d+)(_\w+)?/);
        if (linkMatch) {
            patterns.push('REX_LINK');
            if (linkMatch[2]) {
                patterns.push(linkMatch[2]);
            }
        }
        
        // REX_MEDIA_1_NAME -> ["REX_MEDIA", "_NAME"]
        const mediaMatch = fieldName.match(/REX_MEDIA_(\d+)(_\w+)?/);
        if (mediaMatch) {
            patterns.push('REX_MEDIA');
            if (mediaMatch[2]) {
                patterns.push(mediaMatch[2]);
            }
        }
        
        return patterns;
    },

    // Enhanced form data restoration with smarter field matching
    restoreComplexFormData: function(pastedItem, formData) {
        try {
            
            Object.keys(formData).forEach(originalName => {
                const fieldData = formData[originalName];
                
                if (!fieldData || typeof fieldData !== 'object') return;
                
                let $field = this.findFieldBySmartMatching(pastedItem, originalName);
                if (!$field.length) {
                    // Only warn for significant fields, not bootstrap/metadata fields
                    if (!originalName.includes('_bootstrap') && !originalName.includes('active_tab')) {
                        console.warn(`MBlock: Feld "${originalName}" nicht gefunden`);
                    }
                    return;
                }
                
                // Handle different field types
                switch (fieldData.type) {
                    case 'checkbox_radio':
                        $field.val(fieldData.value);
                        $field.prop('checked', fieldData.checked);
                        if (fieldData.defaultValue) {
                            $field.attr('value', fieldData.defaultValue);
                        }
                        break;
                        
                    case 'select':
                        // Restore select HTML if needed
                        if (fieldData.html) {
                            $field.html(fieldData.html);
                        }
                        $field.val(fieldData.value);
                        
                        // Handle multi-select
                        if (fieldData.selectedOptions && fieldData.selectedOptions.length > 0) {
                            fieldData.selectedOptions.forEach(optionValue => {
                                $field.find(`option[value="${optionValue}"]`).prop('selected', true);
                            });
                        }
                        break;
                        
                    case 'bootstrap_select':
                        $field.val(fieldData.value);
                        // Trigger bootstrap-select refresh if available
                        if (typeof $field.selectpicker === 'function') {
                            setTimeout(() => {
                                $field.selectpicker('refresh');
                                $field.selectpicker('val', fieldData.value);
                            }, 100);
                        }
                        break;
                        
                    case 'ckeditor':
                        if (fieldData.value) {
                            $field.val(fieldData.value);
                            
                            // If CKEditor instance exists, set data
                            const editorId = $field.attr('id');
                            if (editorId && window.CKEDITOR && window.CKEDITOR.instances[editorId]) {
                                setTimeout(() => {
                                    window.CKEDITOR.instances[editorId].setData(fieldData.value);
                                }, 200);
                            }
                        }
                        break;
                        
                    case 'rex_media':
                        if (fieldData.value) {
                            $field.val(fieldData.value);
                            
                            // Restore preview if available
                            if (fieldData.preview) {
                                const $preview = $field.closest('.rex-js-widget-media').find('.rex-js-media-preview');
                                if ($preview.length) {
                                    $preview.html(fieldData.preview);
                                    if (fieldData.value) {
                                        $preview.show();
                                    }
                                }
                            }
                        }
                        break;
                        
                    case 'rex_link':
                        $field.val(fieldData.value);
                        
                        // Restore display text
                        if (fieldData.displayText) {
                            const $displayInput = $field.siblings('input[readonly]');
                            if ($displayInput.length) {
                                $displayInput.val(fieldData.displayText);
                            }
                        }
                        
                        // Restore widget data attributes
                        const $widget = $field.closest('.rex-js-widget-customlink');
                        if ($widget.length) {
                            if (fieldData.dataId) {
                                $widget.attr('data-id', fieldData.dataId);
                            }
                            if (fieldData.dataClang) {
                                $widget.attr('data-clang', fieldData.dataClang);
                            }
                        }
                        break;
                        
                    case 'active_tab':
                        // Restore active tab states
                        if (fieldData.href) {
                            setTimeout(() => {
                                const $tab = pastedItem.find(`a[href="${fieldData.href}"]`);
                                if ($tab.length) {
                                    $tab.tab('show');
                                }
                            }, 300);
                        }
                        break;
                        
                    case 'collapse_state':
                        // Restore collapse states
                        if (fieldData.isOpen) {
                            const collapseId = originalName.replace('collapse_state_', '');
                            setTimeout(() => {
                                const $collapse = pastedItem.find(`#${collapseId}`);
                                if ($collapse.length) {
                                    $collapse.collapse('show');
                                }
                            }, 400);
                        }
                        break;
                        
                    case 'input':
                    default:
                        // Handle regular inputs and textareas
                        if (fieldData.value !== undefined) {
                            $field.val(fieldData.value);
                            
                            // Restore placeholder if available
                            if (fieldData.placeholder) {
                                $field.attr('placeholder', fieldData.placeholder);
                            }
                        }
                        break;
                }
            });
        } catch (error) {
            console.error('MBlock: Fehler beim Wiederherstellen komplexer Formulardaten:', error);
        }
    },
    
    showCopiedState: function(item) {
        const $copyBtn = item.find('.mblock-copy-btn');
        if ($copyBtn.length) {
            $copyBtn.addClass('is-copied');
            setTimeout(() => {
                $copyBtn.removeClass('is-copied');
            }, 1500);
        }
    },
    
    updatePasteButtons: function() {
        const hasData = !!this.data;
        
        // Update paste buttons
        $('.mblock-paste-btn').toggleClass('disabled', !hasData).prop('disabled', !hasData);
        
        // Update toolbar visibility
        const toolbar = $('.mblock-copy-paste-toolbar');
        if (hasData) {
            toolbar.show();
        } else {
            toolbar.hide();
        }
        
        // Update button text with storage info  
        const storageInfo = this.useSessionStorage ? 'Session' : 'Local';
        $('.mblock-paste-btn[title]').attr('title', 
            hasData ? `Paste element (${storageInfo})` : 'No data in clipboard');
    },

    // Convert selectpicker elements back to plain select elements
    convertSelectpickerToPlainSelect: function(container) {
        try {
            
            // Find all select elements that have selectpicker class or are inside bootstrap-select wrappers
            const $selectElements = container.find('select.selectpicker, .bootstrap-select select');
            
            $selectElements.each(function() {
                const $select = $(this);
                
                // Store current value
                const selectedValue = $select.val();
                const selectHtml = $select.prop('outerHTML');
                
                // Create clean select element
                const $cleanSelect = $select.clone();
                
                // Remove ALL selectpicker and bootstrap-select related classes and attributes
                $cleanSelect.removeClass('selectpicker bs-select-hidden');
                $cleanSelect.removeAttr('data-live-search data-live-search-placeholder tabindex aria-describedby');
                $cleanSelect.removeData(); // Remove all data attributes
                $cleanSelect.css('display', ''); // Reset any inline styles
                
                // Add marker class for later initialization
                $cleanSelect.addClass('mblock-needs-selectpicker');
                
                // Restore selected value
                $cleanSelect.val(selectedValue);
                
                // Find the outermost bootstrap-select wrapper(s) around this select
                const $bootstrapWrappers = $select.parents('.bootstrap-select');
                
                if ($bootstrapWrappers.length > 0) {
                    // Replace the outermost wrapper with our clean select
                    const $outermostWrapper = $bootstrapWrappers.last();
                    $outermostWrapper.replaceWith($cleanSelect);
                } else {
                    // If no wrapper, just replace the select itself
                    $select.replaceWith($cleanSelect);
                }
            });
            
            // Clean up any remaining empty bootstrap-select containers
            container.find('.bootstrap-select').each(function() {
                const $wrapper = $(this);
                if (!$wrapper.find('select').length) {
                    $wrapper.remove();
                }
            });
        } catch (error) {
            console.error('MBlock: Error converting selectpicker to plain select:', error);
        }
    },
    
    clear: function() {
        this.data = null;
        this.clearStorage();
        this.updatePasteButtons();
    },
    
    // Get clipboard info for debugging
    getInfo: function() {
        return {
            hasData: !!this.data,
            storageMode: this.useSessionStorage ? 'Session' : 'Local',
            timestamp: this.data ? this.data.timestamp : null,
            savedAt: this.data ? this.data.savedAt : null,
            itemCount: this.data && this.data.formData ? Object.keys(this.data.formData).length : 0
        };
    },
    
    // Debug function to check clipboard status
    debug: function() {
    }
};

// Online/Offline Toggle Funktionalität
var MBlockOnlineToggle = {
    
    toggle: function(element, item) {
        try {
            if (!item || !item.length) {
                console.warn('MBlock: Kein Item für Online/Offline Toggle gefunden');
                return false;
            }
            
            const isOnline = !item.hasClass('mblock-offline');
            const $toggleBtn = item.find('.mblock-online-toggle');
            const $icon = $toggleBtn.find('i');
            
            if (isOnline) {
                // Set to offline
                item.addClass('mblock-offline');
                $toggleBtn.removeClass('btn-online').addClass('btn-offline')
                    .attr('title', 'Set online');
                
                // Change icon
                if ($icon.length) {
                    $icon.removeClass('rex-icon-online').addClass('rex-icon-offline');
                } else {
                    $toggleBtn.html('<i class="rex-icon rex-icon-offline"></i>');
                }
                
                // Add hidden input to store offline state
                this.setOfflineState(item, true);
            } else {
                // Set to online
                item.removeClass('mblock-offline');
                $toggleBtn.removeClass('btn-offline').addClass('btn-online')
                    .attr('title', 'Set offline');
                
                // Change icon
                if ($icon.length) {
                    $icon.removeClass('rex-icon-offline').addClass('rex-icon-online');
                } else {
                    $toggleBtn.html('<i class="rex-icon rex-icon-online"></i>');
                }
                
                // Remove offline state
                this.setOfflineState(item, false);
                
            }
            
            return true;
            
        } catch (error) {
            console.error('MBlock: Fehler beim Online/Offline Toggle:', error);
            return false;
        }
    },
    
    setOfflineState: function(item, isOffline) {
        try {
            // Look for existing mblock_offline input (must be defined in template)
            const $offlineInput = item.find('input[name*="mblock_offline"]');
            
            if ($offlineInput.length) {
                // Simply set the value - field already exists in template
                $offlineInput.val(isOffline ? '1' : '0');
            } else {
                console.warn('MBlock: No mblock_offline input found - must be defined in template for this functionality');
            }
        } catch (error) {
            console.error('MBlock: Fehler beim Setzen des Offline-Status:', error);
        }
    },
    
    initializeStates: function(element) {
        try {
            
            // Initialize toggle buttons based on existing offline states
            element.find('> div.sortitem').each(function(index) {
                const $item = $(this);
                const itemIndex = $item.attr('data-mblock_index') || index;
                
                // Look for offline input with multiple strategies
                let $offlineInput = $item.find('input[name*="mblock_offline"]');
                
                // Fallback: try different name patterns
                if (!$offlineInput.length) {
                    $offlineInput = $item.find('input[name*="_offline"]');
                }
                if (!$offlineInput.length) {
                    $offlineInput = $item.find('input[value="1"][type="hidden"]');
                }
                
                const $toggleBtn = $item.find('.mblock-online-toggle');
                const $icon = $toggleBtn.find('i');
                
                if ($toggleBtn.length) {
                    const isOffline = $offlineInput.length && ($offlineInput.val() === '1' || $offlineInput.val() === 1);
                    
                    if (isOffline) {
                        // Item is offline
                        $item.addClass('mblock-offline');
                        $toggleBtn.removeClass('btn-online').addClass('btn-offline')
                            .attr('title', 'Set online');
                        
                        if ($icon.length) {
                            $icon.removeClass('rex-icon-online').addClass('rex-icon-offline');
                        } else {
                            $toggleBtn.html('<i class="rex-icon rex-icon-offline"></i>');
                        }
                    } else {
                        // Item is online (value is 0, empty, or input doesn't exist)
                        $item.removeClass('mblock-offline');
                        $toggleBtn.removeClass('btn-offline').addClass('btn-online')
                            .attr('title', 'Set offline');
                        
                        if ($icon.length) {
                            $icon.removeClass('rex-icon-offline').addClass('rex-icon-online');
                        } else {
                            $toggleBtn.html('<i class="rex-icon rex-icon-online"></i>');
                        }
                    }
                }
            });
        } catch (error) {
            console.error('MBlock: Fehler beim Initialisieren der Online/Offline-States:', error);
        }
    },

    // New method for auto-detected offline toggle buttons
    toggleAutoDetected: function(element, item, button) {
        try {
            if (!item || !item.length || !button || !button.length) {
                console.warn('MBlock: Kein Item oder Button für Auto-Detected Toggle gefunden');
                return false;
            }
            
            // Get current offline status from button data attribute
            const currentIsOffline = button.attr('data-offline') === '1';
            const newIsOffline = !currentIsOffline;
            
            // Find the corresponding mblock_offline input field
            const $offlineInput = item.find('input[name*="mblock_offline"]');
            
            if (!$offlineInput.length) {
                console.warn('MBlock: No mblock_offline input field found in item');
                return false;
            }
            
            // Update the input value
            $offlineInput.val(newIsOffline ? '1' : '0');
            
            // Update button appearance with improved colors
            const buttonClass = newIsOffline ? 'btn-danger' : 'btn-success'; // Red for offline, green for online
            const iconClass = newIsOffline ? 'rex-icon-offline' : 'rex-icon-online';
            const buttonTitle = newIsOffline ? 'Set online' : 'Set offline';
            const buttonText = newIsOffline ? 'Offline' : 'Online';
            
            // Update button attributes and classes
            button.removeClass('btn-default btn-warning btn-success btn-danger')
                  .addClass(buttonClass)
                  .attr('title', buttonTitle)
                  .attr('data-offline', newIsOffline ? '1' : '0');
            
            // Update icon and text
            const $icon = button.find('i');
            if ($icon.length) {
                $icon.removeClass('rex-icon-online rex-icon-offline')
                     .addClass(iconClass);
            }
            
            // Update button text
            const textContent = button.html().replace(/Offline|Online/, buttonText);
            button.html(textContent);
            
            // Update item CSS class
            if (newIsOffline) {
                item.addClass('mblock-offline');
            } else {
                item.removeClass('mblock-offline');
            }
            
            return true;
            
        } catch (error) {
            console.error('MBlock: Fehler beim Auto-Detected Toggle:', error);
            return false;
        }
    }
};

// Toolbar Initialisierung
function mblock_init_toolbar(element) {
    try {
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

function mblock_moveup(element, item) {
    var prev = item.prev();
    if (prev.length == 0) return;

    setTimeout(function () {
        item.insertBefore(prev);
        // set last user action
        mblock_reindex(element);
        mblock_remove(element);
        // trigger event
        let iClone = prev;
        iClone.trigger('mblock:change', [iClone]);
    }, 150);
}

function mblock_movedown(element, item) {
    var next = item.next();
    if (next.length == 0) return;

    setTimeout(function () {
        item.insertAfter(next);
        // set last user action
        mblock_reindex(element);
        mblock_remove(element);
        // trigger event
        let iClone = next;
        iClone.trigger('mblock:change', [iClone]);
    }, 150);
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

function mblock_add(element) {
    try {
        if (!element || !element.length) {
            console.warn('MBlock: Ungültiges Element bei mblock_add');
            return false;
        }

        
        // Sichere Event-Bindung mit Namespace für bessere Memory-Management
        element.find('> div.sortitem .addme')
            .off('click.mblock')
            .on('click.mblock', function (e) {
                e.preventDefault();
                try {
                    const $this = $(this);
                    if (!$this.prop('disabled')) {
                        const $item = $this.parents('.sortitem');
                        const itemIndex = $item.attr('data-mblock_index');
                        if (itemIndex) {
                            element.attr('data-mblock_clicked_add_item', itemIndex);
                        }
                        mblock_add_item(element, $this.closest('div[class^="sortitem"]'));
                    }
                } catch (error) {
                    console.error('MBlock: Fehler in addme click handler:', error);
                }
                return false;
            });

        element.find('> div.sortitem .removeme')
            .off('click.mblock')
            .on('click.mblock', function (e) {
                e.preventDefault();
                try {
                    const $this = $(this);
                    if (!$this.prop('disabled')) {
                        mblock_remove_item(element, $this.closest('div[class^="sortitem"]'));
                    }
                } catch (error) {
                    console.error('MBlock: Fehler in removeme click handler:', error);
                }
                return false;
            });

        element.find('> div.sortitem .moveup')
            .off('click.mblock')
            .on('click.mblock', function (e) {
                e.preventDefault();
                try {
                    const $this = $(this);
                    if (!$this.prop('disabled')) {
                        mblock_moveup(element, $this.closest('div[class^="sortitem"]'));
                    }
                } catch (error) {
                    console.error('MBlock: Fehler in moveup click handler:', error);
                }
                return false;
            });

        element.find('> div.sortitem .movedown')
            .off('click.mblock')
            .on('click.mblock', function (e) {
                e.preventDefault();
                try {
                    const $this = $(this);
                    if (!$this.prop('disabled')) {
                        mblock_movedown(element, $this.closest('div[class^="sortitem"]'));
                    }
                } catch (error) {
                    console.error('MBlock: Fehler in movedown click handler:', error);
                }
                return false;
            });

        // Copy Button Handler
        const copyButtons = element.find('> div.sortitem .mblock-copy-btn');
        
        copyButtons
            .off('click.mblock')
            .on('click.mblock', function (e) {
                e.preventDefault();
                try {
                    const $this = $(this);
                    const $item = $this.closest('div[class^="sortitem"]');
                    MBlockClipboard.copy(element, $item);
                } catch (error) {
                    console.error('MBlock: Fehler in copy click handler:', error);
                }
                return false;
            });

        // Paste Button Handler
        element.find('> div.sortitem .mblock-paste-btn')
            .off('click.mblock')
            .on('click.mblock', function (e) {
                e.preventDefault();
                try {
                    const $this = $(this);
                    if (!$this.hasClass('disabled') && !$this.prop('disabled')) {
                        const $item = $this.closest('div[class^="sortitem"]');
                        MBlockClipboard.paste(element, $item);
                    }
                } catch (error) {
                    console.error('MBlock: Fehler in paste click handler:', error);
                }
                return false;
            });

        // Online/Offline Toggle Handler (old system)
        element.find('> div.sortitem .mblock-online-toggle')
            .off('click.mblock')
            .on('click.mblock', function (e) {
                e.preventDefault();
                try {
                    const $this = $(this);
                    const $item = $this.closest('div[class^="sortitem"]');
                    MBlockOnlineToggle.toggle(element, $item);
                } catch (error) {
                    console.error('MBlock: Fehler in online/offline toggle handler:', error);
                }
                return false;
            });

        // New Auto-Detected Online/Offline Toggle Handler
        element.find('> div.sortitem .mblock-offline-toggle-btn')
            .off('click.mblock')
            .on('click.mblock', function (e) {
                e.preventDefault();
                try {
                    const $this = $(this);
                    const $item = $this.closest('div[class^="sortitem"]');
                    MBlockOnlineToggle.toggleAutoDetected(element, $item, $this);
                } catch (error) {
                    console.error('MBlock: Fehler in auto-detected toggle handler:', error);
                }
                return false;
            });

        // Initialize paste button states
        MBlockClipboard.updatePasteButtons();
        
        // Initialize online/offline states
        MBlockOnlineToggle.initializeStates(element);

            
        return true;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_add:', error);
        return false;
    }
}