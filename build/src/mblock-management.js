

// ================== MBLOCK MANAGEMENT MODULE ================
/**
 * MBlock Management - DOM manipulation and sortable handling
 * 
 * Contains:
 * - Sortable management (MBlockSortable)
 * - Item manipulation (add, remove, move)
 * - Reindexing and form element handling
 * - REX field handling
 * - Toolbar functionality
 * 
 * Depends on: mblock-core.js
 * 
 * @author joachim doerr
 * @version 2.0
 */

// 🔧 Reusable Sortable Management
const MBlockSortable = {
    /**
     * Safely destroy existing sortable instance
     */
    destroy(element) {
        try {
            const domElement = element?.get ? element.get(0) : element;
            if (domElement && domElement._sortable) {
                if (typeof domElement._sortable.destroy === 'function') {
                    domElement._sortable.destroy();
                }
                domElement._sortable = null;
                return true;
            }
        } catch (error) {
            console.warn('MBlock: Sortable destroy error:', error);
            if (element?.get) {
                const domElement = element.get(0);
                if (domElement) domElement._sortable = null;
            }
        }
        return false;
    },

    /**
     * Create new sortable instance with unified configuration
     */
    create(element) {
        try {
            if (!element?.length || !element.get) return false;
            const domElement = element.get(0);
            if (!document.contains(domElement) || typeof Sortable === 'undefined') {
                return false;
            }

            const sortableInstance = Sortable.create(domElement, {
                handle: '.sorthandle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'mblock-sortable-chosen',
                dragClass: 'mblock-dragging',
                onStart: (evt) => this._handleStart(evt),
                onEnd: (evt) => this._handleEnd(evt, element),
                onError: (evt) => console.error('MBlock: Sortable Fehler:', evt)
            });
            domElement._sortable = sortableInstance;
            return true;

        } catch (error) {
            console.error('MBlock: Fehler beim Erstellen der Sortable-Instanz:', error);
            return false;
        }
    },

    /**
     * Reinitialize sortable (destroy + create with safety delay)
     */
    reinitialize(element) {
        this.destroy(element);
        setTimeout(() => this.create(element), 10);
    },

    // Private event handlers
    _handleStart(evt) {
        try {
            document.body.classList.add('mblock-drag-active');
            if (evt.item) {
                evt.item.classList.add('mblock-dragging');
            }
        } catch (error) {
            console.error('MBlock: Fehler in sortable onStart:', error);
        }
    },
    _handleEnd(evt, element) {
        try {
            document.body.classList.remove('mblock-drag-active');
            if (evt.item) {
                evt.item.classList.remove('mblock-dragging');
                MBlockUtils.animation.flashEffect($(evt.item));
            }
            
            mblock_reindex(element);
            mblock_remove(element);
            const iClone = $(evt.item);
            if (iClone.length) {
                iClone.trigger('mblock:change', [iClone]);
            }
        } catch (error) {
            console.error('MBlock: Fehler in sortable onEnd:', error);
        }
    }
};

// Core MBlock functions
$(document).on('rex:ready', function (e, container) {
    try {
        // Initialize clipboard system only if copy/paste is enabled
        const isCopyPasteEnabled = checkCopyPasteEnabled();
        if (isCopyPasteEnabled && typeof MBlockClipboard !== 'undefined') {
            MBlockClipboard.init();
        }
        
        if (container && typeof container.find === 'function') {
            container.find(mblock).each(function () {
                const $element = $(this);
                if ($element.length) {
                    try {
                        // Check if this is a nested initialization to prevent conflicts
                        const isNestedContext = container.closest('.mblock_wrapper').length > 0;
                        if (isNestedContext) {return;
                        }
                        
                        mblock_init($element);
                    } catch (initError) {
                        console.error('MBlock: Fehler beim Initialisieren eines einzelnen MBlock-Elements:', initError);
                        // Einzelne Fehler nicht die gesamte Initialisierung abbrechen lassen
                    }
                }
            });
        } else {
            // Initialize all MBlock elements (global initialization)
            $(mblock).each(function () {
                const $element = $(this);
                if ($element.length) {
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

        // Check if element is already initialized
        const isAlreadyInitialized = element.data('mblock_run');
        if (!isAlreadyInitialized) {element.data('mblock_run', 1);
            mblock_sort(element);
            mblock_set_unique_id(element, false);
            const minValue = element.data('min');
            const maxValue = element.data('max');
            if (minValue == 1 && maxValue == 1) {
                element.addClass('hide_removeadded').addClass('hide_sorthandle');
            }
        } else {// Remove any duplicate mblock-single-add elements before reinitializing
            element.find('.mblock-single-add').remove();
        }
        
        mblock_add_plus(element);
        mblock_init_toolbar(element);
        
        // Initialize online/offline states if MBlockOnlineToggle is available
        if (typeof MBlockOnlineToggle !== 'undefined') {
            MBlockOnlineToggle.initializeStates(element);
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
    // Only add the "add" button if there are no sortitems AND no existing add button
    const hasSortItems = element.find('> div.sortitem').length > 0;
    const hasAddButton = element.find('> div.mblock-single-add').length > 0;
    if (!hasSortItems && !hasAddButton) {element.prepend($($.parseHTML(element.data('mblock-single-add'))));
        element.find('> div.mblock-single-add .addme').unbind().bind('click', function () {
            mblock_add_item(element, false);
            $(this).parents('.mblock-single-add').remove();
        });
    } else if (hasSortItems && hasAddButton) {
        // Remove add button if there are now sortitems (should not happen but safety check)element.find('> div.mblock-single-add').remove();
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
        if (!MBlockUtils.is.validElement(element) || !element.length || !element.get) {
            console.warn('MBlock: Ungültiges Element für mblock_sort_it');
            return false;
        }

        const domElement = element.get(0);
        if (!document.contains(domElement)) {
            console.warn('MBlock: Element nicht mehr im DOM');
            return false;
        }

        // Use centralized sortable management
        if (typeof Sortable !== 'undefined' && Sortable.create) {
            MBlockSortable.reinitialize(element);
            return true;
        } else {
            console.error('MBlock: Sortable.js ist nicht verfügbar');
            return false;
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
 * Centralized REX-System-ID updater with unified logic
 */
function mblock_update_rex_ids($element, sindex, mblock_count, eindex) {
    try {
        const elementId = $element.attr('id');
        const nodeName = $element.prop('nodeName');
        if (!elementId) return;

        // Configuration for different REX field types
        const rexConfigs = [
            {
                type: 'SELECT',
                patterns: ['REX_MEDIALIST_SELECT_', 'REX_LINKLIST_SELECT_'],
                handler: (newId, nameAttr) => {
                    $element.parent().data('eindex', eindex);
                    $element.attr('id', newId);
                    if (nameAttr) {
                        $element.attr('name', nameAttr.replace(/_\d+/, '_' + sindex + mblock_count + '00' + eindex));
                    }
                }
            },
            {
                type: 'INPUT',
                patterns: ['REX_MEDIA_', 'REX_LINKLIST_', 'REX_MEDIALIST_'],
                handler: (newId) => {
                    const parentEindex = $element.parent().data('eindex') || eindex;
                    const actualNewId = elementId.replace(/\d+/, sindex + mblock_count + '00' + parentEindex);
                    $element.attr('id', actualNewId);
                    mblock_update_rex_buttons($element, sindex, mblock_count, parentEindex);
                }
            }
        ];

        // Find matching configuration and apply handler
        const config = rexConfigs.find(cfg => 
            cfg.type === nodeName && 
            cfg.patterns.some(pattern => elementId.indexOf(pattern) >= 0)
        );
        if (config) {
            const newId = elementId.replace(/_\d+/, '_' + sindex + mblock_count + '00' + eindex);
            const nameAttr = $element.attr('name');
            config.handler(newId, nameAttr);
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
    const iClone = MBlockUtils.dom.createFromHTML(element.data('mblock-plain-sortitem'));

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
        // Destroy sortable before manipulation
        MBlockSortable.destroy(element);
        
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
    iClone.trigger('rex:ready', [iClone]);
    
    // CRITICAL: Initialize nested MBlocks BEFORE other components to prevent duplicate initialization
    setTimeout(function() {
        // Use utility function for safe nested MBlock initialization
        MBlockUtils.nested.initializeNested(iClone);
        
        // specific component reinitialization
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
        
        // CRITICAL FIX: Reinitialize REDAXO Media and Link functionality for new blocks
        if (typeof mblock_reinitialize_redaxo_widgets === 'function') {
            mblock_reinitialize_redaxo_widgets(iClone);
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
        if (!MBlockUtils.is.validElement(element) || !element.length || !item || !item.length) {
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
            // Destroy sortable before manipulation
            MBlockSortable.destroy(element);

            // set prev item
            let prevItem = item.prev();
            // is prev exist?
            if (!prevItem.length || !prevItem.hasClass('sortitem')) {
                prevItem = item.next(); // go to next
            }

            // Safe element removal with event cleanup using utilities
            if (MBlockUtils.dom.safeRemove(item)) {
                // reinit
                mblock_init_sort(element);
                // scroll to item (falls ein vorheriges Element existiert)
                if (prevItem && prevItem.length) {
                    mblock_scroll(element, prevItem);
                }
                // add add button
                mblock_add_plus(element);
                return true;
            } else {
                console.error('MBlock: Fehler beim Entfernen des Items');
                return false;
            }
        }
        
        return false;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_remove_item:', error);
        return false;
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
        if (!MBlockUtils.is.validElement(element) || !element.length) {
            console.warn('MBlock: Ungültiges Element bei mblock_add');
            return false;
        }

        // Centralized event handler configuration
        const eventHandlers = [
            {
                selector: MBlockUtils.selectors.addme,
                event: 'click',
                handler: function (e) {
                    e.preventDefault();
                    try {
                        const $this = $(this);
                        if (!MBlockUtils.state.isDisabled($this)) {
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
                }
            },
            {
                selector: MBlockUtils.selectors.removeme,
                event: 'click',
                handler: function (e) {
                    e.preventDefault();
                    try {
                        const $this = $(this);
                        if (!MBlockUtils.state.isDisabled($this)) {
                            mblock_remove_item(element, $this.closest('div[class^="sortitem"]'));
                        }
                    } catch (error) {
                        console.error('MBlock: Fehler in removeme click handler:', error);
                    }
                    return false;
                }
            },
            {
                selector: MBlockUtils.selectors.moveup,
                event: 'click',
                handler: function (e) {
                    e.preventDefault();
                    try {
                        const $this = $(this);
                        if (!MBlockUtils.state.isDisabled($this)) {
                            mblock_moveup(element, $this.closest('div[class^="sortitem"]'));
                        }
                    } catch (error) {
                        console.error('MBlock: Fehler in moveup click handler:', error);
                    }
                    return false;
                }
            },
            {
                selector: MBlockUtils.selectors.movedown,
                event: 'click',
                handler: function (e) {
                    e.preventDefault();
                    try {
                        const $this = $(this);
                        if (!MBlockUtils.state.isDisabled($this)) {
                            mblock_movedown(element, $this.closest('div[class^="sortitem"]'));
                        }
                    } catch (error) {
                        console.error('MBlock: Fehler in movedown click handler:', error);
                    }
                    return false;
                }
            }
        ];

        // Bind all basic event handlers
        eventHandlers.forEach(({selector, event, handler}) => {
            const elements = MBlockUtils.dom.findElement(element, `${MBlockUtils.selectors.sortitem} ${selector}`);
            MBlockUtils.events.bindSafe(elements, event, handler);
        });

        // Handle copy/paste buttons only if enabled
        if (checkCopyPasteEnabled()) {
            mblock_add._bindCopyPasteHandlers(element);
        }

        // Handle online/offline toggle buttons
        mblock_add._bindToggleHandlers(element);

        // Initialize states
        if (checkCopyPasteEnabled() && typeof MBlockClipboard !== 'undefined') {
            MBlockClipboard.updatePasteButtons();
        }
        if (typeof MBlockOnlineToggle !== 'undefined') {
            MBlockOnlineToggle.initializeStates(element);
        }

        return true;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_add:', error);
        return false;
    }
}

// Private helper methods for mblock_add
mblock_add._bindCopyPasteHandlers = function(element) {
    // Copy Button Handler
    const copyButtons = MBlockUtils.dom.findElement(element, `${MBlockUtils.selectors.sortitem} ${MBlockUtils.selectors.copyBtn}`);
    if (copyButtons.length > 0) {
        MBlockUtils.events.bindSafe(copyButtons, 'click', function (e) {
            e.preventDefault();
            try {
                const $this = $(this);
                const $item = $this.closest('div[class^="sortitem"]');
                if (typeof MBlockClipboard !== 'undefined') {
                    MBlockClipboard.copy(element, $item);
                }
            } catch (error) {
                console.error('MBlock: Fehler in copy click handler:', error);
            }
            return false;
        });
    }

    // Paste Button Handler
    const pasteButtons = MBlockUtils.dom.findElement(element, `${MBlockUtils.selectors.sortitem} ${MBlockUtils.selectors.pasteBtn}`);
    if (pasteButtons.length > 0) {
        MBlockUtils.events.bindSafe(pasteButtons, 'click', function (e) {
            e.preventDefault();
            try {
                const $this = $(this);
                if (!MBlockUtils.state.isDisabled($this)) {
                    const $item = $this.closest('div[class^="sortitem"]');
                    if (typeof MBlockClipboard !== 'undefined') {
                        MBlockClipboard.paste(element, $item);
                    }
                }
            } catch (error) {
                console.error('MBlock: Fehler in paste click handler:', error);
            }
            return false;
        });
    }
};
mblock_add._bindToggleHandlers = function(element) {
    // Online/Offline Toggle Handler (old system)
    const toggleButtons = MBlockUtils.dom.findElement(element, `${MBlockUtils.selectors.sortitem} ${MBlockUtils.selectors.onlineToggle}`);
    MBlockUtils.events.bindSafe(toggleButtons, 'click', function (e) {
        e.preventDefault();
        try {
            const $this = $(this);
            const $item = $this.closest('div[class^="sortitem"]');
            if (typeof MBlockOnlineToggle !== 'undefined') {
                MBlockOnlineToggle.toggle(element, $item);
            }
        } catch (error) {
            console.error('MBlock: Fehler in online/offline toggle handler:', error);
        }
        return false;
    });

    // New Auto-Detected Toggle Handler
    const autoToggleButtons = MBlockUtils.dom.findElement(element, `${MBlockUtils.selectors.sortitem} ${MBlockUtils.selectors.autoToggle}`);
    MBlockUtils.events.bindSafe(autoToggleButtons, 'click', function (e) {
        e.preventDefault();
        try {
            const $this = $(this);
            const $item = $this.closest('div[class^="sortitem"]');
            if (typeof MBlockOnlineToggle !== 'undefined') {
                MBlockOnlineToggle.toggleAutoDetected(element, $item, $this);
            }
        } catch (error) {
            console.error('MBlock: Fehler in auto-detected toggle handler:', error);
        }
        return false;
    });
};

// Toolbar Initialisierung
function mblock_init_toolbar(element) {
    try {
        // Nur initialisieren wenn Copy/Paste aktiviert ist
        if (!checkCopyPasteEnabled()) {
            return;
        }
        
        // Centralized toolbar event configuration
        const toolbarEvents = [
            {
                selector: '.mblock-copy-paste-toolbar .mblock-paste-btn',
                handler: function (e) {
                    e.preventDefault();
                    try {
                        const $this = $(this);
                        if (!MBlockUtils.state.isDisabled($this)) {
                            if (typeof MBlockClipboard !== 'undefined') {
                                MBlockClipboard.paste(element, false); // false = am Anfang einfügen
                            }
                        }
                    } catch (error) {
                        console.error('MBlock: Fehler in toolbar paste click handler:', error);
                    }
                    return false;
                }
            },
            {
                selector: '.mblock-copy-paste-toolbar .mblock-clear-clipboard',
                handler: function (e) {
                    e.preventDefault();
                    try {
                        if (typeof MBlockClipboard !== 'undefined') {
                            MBlockClipboard.clear();
                        }
                    } catch (error) {
                        console.error('MBlock: Fehler in clear clipboard click handler:', error);
                    }
                    return false;
                }
            }
        ];

        // Bind all toolbar events
        toolbarEvents.forEach(({selector, handler}) => {
            const elements = MBlockUtils.dom.findElement(element, selector);
            MBlockUtils.events.bindSafe(elements, 'click', handler);
        });
            
    } catch (error) {
        console.error('MBlock: Fehler in mblock_init_toolbar:', error);
    }
}

// Export for module systems (if used)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { MBlockSortable };
}