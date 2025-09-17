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
            if (element && element.length && element.data('sortable')) {
                element.sortable('destroy');
                element.removeData('sortable');
                return true;
            }
        } catch (error) {
            console.warn('MBlock: Error destroying sortable:', error);
        }
        return false;
    },

    /**
     * Create new sortable instance with unified configuration
     */
    create(element) {
        try {
            if (!element || !element.length) return false;

            // Destroy existing instance first
            this.destroy(element);
            const sortableOptions = {
                handle: '.mblock-handle',
                placeholder: 'mblock-sortable-placeholder',
                tolerance: 'pointer',
                cursor: 'move',
                axis: 'y',
                containment: 'parent',
                start: this._handleStart,
                stop: (evt, ui) => this._handleEnd(evt, element),
                update: (evt, ui) => this._handleUpdate(evt, element)
            };
            element.sortable(sortableOptions);
            element.data('sortable', true);
            return true;
        } catch (error) {
            console.error('MBlock: Error creating sortable:', error);
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
            const $item = $(evt.target);
            $item.addClass('mblock-dragging');
        } catch (error) {
            console.warn('MBlock: Error in sort start handler:', error);
        }
    },
    _handleEnd(evt, element) {
        try {
            const $item = $(evt.target);
            $item.removeClass('mblock-dragging');
            mblock_reindex(element);
        } catch (error) {
            console.warn('MBlock: Error in sort end handler:', error);
        }
    },
    _handleUpdate(evt, element) {
        try {
            mblock_reindex(element);
            mblock_remove(element);
        } catch (error) {
            console.warn('MBlock: Error in sort update handler:', error);
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
            container.find('.mblock_wrapper').each(function() {
                mblock_init($(this));
            });
        } else {
            $('.mblock_wrapper').each(function() {
                mblock_init($(this));
            });
        }
    } catch (error) {
        console.error('MBlock: Fehler bei rex:ready:', error);
    }
});
function mblock_init(element) {
    try {
        if (!element || !element.length || typeof element.data !== 'function') {
            console.warn('MBlock: Invalid element passed to mblock_init');
            return false;
        }

        // Check if element is already initialized
        const isAlreadyInitialized = element.data('mblock_run');
        if (!isAlreadyInitialized) {
            element.data('mblock_run', true);
            mblock_init_sort(element);
        } else {return true;
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
            console.warn('MBlock: Invalid element passed to mblock_init_sort');
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
            console.warn('MBlock: Invalid element passed to mblock_sort');
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
            const $wrapper = $(this).closest('.mblock_wrapper');
            mblock_add_item($wrapper, false);
        });
    } else if (hasSortItems && hasAddButton) {
        element.find('> div.mblock-single-add').remove();
    }
}

function mblock_remove(element) {
    var found = element.find('> div.sortitem');
    if (found.length == 1) {
        found.find('.removeme').prop('disabled', true);
        found.find('.removeme').attr('data-disabled', true);
    } else {
        found.find('.removeme').prop('disabled', false);
        found.find('.removeme').attr('data-disabled', false);
    }

    // has data?
    if (element.data().hasOwnProperty('max')) {
        if (found.length >= element.data('max')) {
            element.find('.addme').prop('disabled', true);
            element.find('.addme').attr('data-disabled', true);
        } else {
            element.find('.addme').prop('disabled', false);
            element.find('.addme').attr('data-disabled', false);
        }
    }

    if (element.data().hasOwnProperty('min')) {
        if (found.length <= element.data('min')) {
            found.find('.removeme').prop('disabled', true);
            found.find('.removeme').attr('data-disabled', true);
        } else {
            found.find('.removeme').prop('disabled', false);
            found.find('.removeme').attr('data-disabled', false);
        }
    }

    found.each(function (index) {
        // min removeme hide
        if ((index + 1) == element.data('min') && found.length == element.data('min')) {
            $(this).find('.removeme').hide();
        }
        if (index == 0) {
            $(this).find('.moveup').prop('disabled', true);
        } else {
            $(this).find('.moveup').prop('disabled', false);
        }
        if ((index + 1) == found.length) {
            $(this).find('.movedown').prop('disabled', true);
        } else {
            $(this).find('.movedown').prop('disabled', false);
        }
    });
}

function mblock_sort_it(element) {
    try {
        if (!MBlockUtils.is.validElement(element) || !element.length || !element.get) {
            console.warn('MBlock: Invalid element passed to mblock_sort_it');
            return false;
        }

        const domElement = element.get(0);
        if (!document.contains(domElement)) {
            console.warn('MBlock: Element not in DOM, skipping sortable initialization');
            return false;
        }

        // Use centralized sortable management
        if (typeof Sortable !== 'undefined' && Sortable.create) {
            // Use SortableJS library if available
            const sortableInstance = Sortable.create(domElement, {
                handle: '.mblock-handle',
                animation: 150,
                ghostClass: 'mblock-sortable-ghost',
                chosenClass: 'mblock-sortable-chosen',
                dragClass: 'mblock-sortable-drag',
                onStart: function(evt) {
                    MBlockSortable._handleStart(evt);
                },
                onEnd: function(evt) {
                    MBlockSortable._handleEnd(evt, element);
                }
            });
            element.data('sortable-instance', sortableInstance);
        } else {
            // Fallback to jQuery UI sortable
            MBlockSortable.create(element);
        }
        
    } catch (error) {
        console.error('MBlock: Fehler in mblock_sort_it:', error);
        return false;
    }
}

function mblock_reindex(element) {
    try {
        if (!mblock_validate_element(element)) {
            console.warn('MBlock: Invalid element passed to mblock_reindex');
            return false;
        }

        const mblock_count = element.data('mblock_count') || 0;
        const sortItems = element.find('> div.sortitem');
        if (!sortItems.length) {return true;
        }

        // Performance-Optimierung: Batch DOM-Updates
        sortItems.each(function (index) {
            const $sortItem = $(this);
            const sindex = index + 1;
            
            // Update data attributes
            $sortItem.attr('data-mblock_index', sindex);
            $sortItem.data('mblock_index', sindex);
            
            // Reindex form elements
            mblock_reindex_form_elements($sortItem, index, sindex, mblock_count);
            
            // Reindex special elements
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
            const elementId = $element.attr('id');
            const elementName = $element.attr('name');
            if (!elementId && !elementName) return;
            
            // Update IDs and names with new index
            if (elementId) {
                const newId = elementId.replace(/_\d+/, '_' + sindex);
                $element.attr('id', newId);
            }
            
            if (elementName) {
                // Remove mblock_new_ prefix and update index
                let newName = elementName.replace(/^mblock_new_/, '');
                newName = newName.replace(/\[(\d+)\]/, '[' + sindex + ']');
                $element.attr('name', newName);
            }
            
            // Handle REX fields
            mblock_update_rex_ids($element, sindex, mblock_count, key);
            mblock_update_rex_buttons($element, sindex, mblock_count, key);
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
                    $element.attr('id', newId);
                    if (nameAttr) $element.attr('name', nameAttr);
                }
            },
            {
                type: 'INPUT',
                patterns: ['REX_MEDIA_', 'REX_LINKLIST_', 'REX_MEDIALIST_'],
                handler: (newId) => {
                    $element.attr('id', newId);
                }
            }
        ];

        // Find matching configuration and apply handler
        const config = rexConfigs.find(cfg => 
            cfg.type === nodeName && 
            cfg.patterns.some(pattern => elementId.indexOf(pattern) >= 0)
        );
        if (config) {
            const newId = elementId.replace(/_\d+/, '_' + sindex);
            const nameAttr = $element.attr('name')?.replace(/\[(\d+)\]/, '[' + sindex + ']');
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
            const $button = $(this);
            const onclick = $button.attr('onclick');
            if (onclick) {
                const newOnclick = onclick.replace(/_\d+/, '_' + sindex);
                $button.attr('onclick', newOnclick);
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
            const $tab = $(this);
            const href = $tab.attr('href');
            if (href) {
                const newHref = href.replace(/_\d+/, '_' + sindex);
                $tab.attr('href', newHref);
            }
        });

        // Bootstrap Collapse/Accordion
        $sortItem.find('a[data-toggle="collapse"]').each(function (key) {
            const $collapse = $(this);
            const target = $collapse.attr('href') || $collapse.attr('data-target');
            if (target) {
                const newTarget = target.replace(/_\d+/, '_' + sindex);
                $collapse.attr('href', newTarget);
                $collapse.attr('data-target', newTarget);
            }
        });

        // Custom Links (MForm)
        $sortItem.find('.custom-link').each(function (key) {
            const $link = $(this);
            const href = $link.attr('href');
            if (href) {
                const newHref = href.replace(/_\d+/, '_' + sindex);
                $link.attr('href', newHref);
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
            var forAttr = $(this).attr('id');
            if (forAttr) {
                var label = mblock.find('label[for="' + forAttr + '"]');
                if (label.length > 0) {
                    label.attr('for', forAttr);
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
        item.after(iClone);
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
            iClone.find('select.selectpicker').selectpicker('refresh');
        }
        
        // reinitialize other common components
        if (typeof $.fn.chosen === 'function') {
            iClone.find('select').chosen('destroy').chosen();
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
            mblock_smooth_scroll_to_element(iClone.get(0));
        }
    }, 100);

    // ✨ Add glow effect to new item using utility (same as paste effect)
    setTimeout(() => {
        MBlockUtils.animation.flashEffect(iClone);
        mblock_show_message(mblock_get_text('mblock_toast_add_success', 'Block erfolgreich hinzugefügt!'), 'success', 3000);
    }, 150);
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
            console.warn('MBlock: Invalid parameters passed to mblock_remove_item');
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
            // Remove item safely
            MBlockUtils.dom.safeRemove(item);
            mblock_reindex(element);
            mblock_remove(element);
            return true;
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
            console.warn('MBlock: Invalid parameters passed to mblock_scroll');
            return false;
        }

        const elementData = element.data();
        
        // Wenn smooth_scroll aktiviert ist, verwende die smooth scroll Funktion
        if (elementData && elementData.hasOwnProperty('smooth_scroll') && elementData.smooth_scroll === true) {
            mblock_smooth_scroll_to_element(item.get(0));
        }
        
        // Fallback: Standard-Browser-Scrolling zu dem Element
        if (item.length && item.offset()) {
            const offset = item.offset().top - 100; // 100px offset from top
            $('html, body').animate({ scrollTop: offset }, 500);
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
            console.warn('MBlock: Invalid element passed to mblock_add');
            return false;
        }

        // Centralized event handler configuration
        const eventHandlers = [
            {
                selector: MBlockUtils.selectors.addme,
                event: 'click',
                handler: function (e) {
                    e.preventDefault();
                    const $wrapper = $(this).closest('.mblock_wrapper');
                    mblock_add_item($wrapper, $(this).closest('.sortitem'));
                }
            },
            {
                selector: MBlockUtils.selectors.removeme,
                event: 'click',
                handler: function (e) {
                    e.preventDefault();
                    const $wrapper = $(this).closest('.mblock_wrapper');
                    const $item = $(this).closest('.sortitem');
                    mblock_remove_item($wrapper, $item);
                }
            },
            {
                selector: MBlockUtils.selectors.moveup,
                event: 'click',
                handler: function (e) {
                    e.preventDefault();
                    const $wrapper = $(this).closest('.mblock_wrapper');
                    const $item = $(this).closest('.sortitem');
                    mblock_moveup($wrapper, $item);
                }
            },
            {
                selector: MBlockUtils.selectors.movedown,
                event: 'click',
                handler: function (e) {
                    e.preventDefault();
                    const $wrapper = $(this).closest('.mblock_wrapper');
                    const $item = $(this).closest('.sortitem');
                    mblock_movedown($wrapper, $item);
                }
            }
        ];

        // Bind all basic event handlers
        eventHandlers.forEach(({selector, event, handler}) => {
            MBlockUtils.events.bindSafe(MBlockUtils.dom.findElement(element, selector), event, handler);
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
            const $wrapper = $(this).closest('.mblock_wrapper');
            const $item = $(this).closest('.sortitem');
            if (typeof MBlockClipboard !== 'undefined') {
                MBlockClipboard.copy($wrapper, $item);
            }
        });
    }

    // Paste Button Handler
    const pasteButtons = MBlockUtils.dom.findElement(element, `${MBlockUtils.selectors.sortitem} ${MBlockUtils.selectors.pasteBtn}`);
    if (pasteButtons.length > 0) {
        MBlockUtils.events.bindSafe(pasteButtons, 'click', function (e) {
            e.preventDefault();
            const $wrapper = $(this).closest('.mblock_wrapper');
            const $item = $(this).closest('.sortitem');
            if (typeof MBlockClipboard !== 'undefined') {
                MBlockClipboard.paste($wrapper, $item);
            }
        });
    }
};
mblock_add._bindToggleHandlers = function(element) {
    // Online/Offline Toggle Handler (old system)
    const toggleButtons = MBlockUtils.dom.findElement(element, `${MBlockUtils.selectors.sortitem} ${MBlockUtils.selectors.onlineToggle}`);
    MBlockUtils.events.bindSafe(toggleButtons, 'click', function (e) {
        e.preventDefault();
        try {
            const $wrapper = $(this).closest('.mblock_wrapper');
            const $item = $(this).closest('.sortitem');
            if (typeof MBlockOnlineToggle !== 'undefined') {
                MBlockOnlineToggle.toggle($wrapper, $item);
            }
        } catch (error) {
            console.error('MBlock: Error in toggle handler:', error);
        }
        return false;
    });

    // New Auto-Detected Toggle Handler
    const autoToggleButtons = MBlockUtils.dom.findElement(element, `${MBlockUtils.selectors.sortitem} ${MBlockUtils.selectors.autoToggle}`);
    MBlockUtils.events.bindSafe(autoToggleButtons, 'click', function (e) {
        e.preventDefault();
        try {
            const $wrapper = $(this).closest('.mblock_wrapper');
            const $item = $(this).closest('.sortitem');
            const $button = $(this);
            if (typeof MBlockOnlineToggle !== 'undefined') {
                MBlockOnlineToggle.toggleAutoDetected($wrapper, $item, $button);
            }
        } catch (error) {
            console.error('MBlock: Error in auto toggle handler:', error);
        }
        return false;
    });
};

// Toolbar Initialisierung
function mblock_init_toolbar(element) {
    try {
        // Nur initialisieren wenn Copy/Paste aktiviert ist
        if (!checkCopyPasteEnabled()) {return;
        }
        
        // Centralized toolbar event configuration
        const toolbarEvents = [
            {
                selector: '.mblock-copy-paste-toolbar .mblock-paste-btn',
                handler: function (e) {
                    e.preventDefault();
                    const $wrapper = $(this).closest('.mblock_wrapper');
                    if (typeof MBlockClipboard !== 'undefined') {
                        MBlockClipboard.paste($wrapper, false);
                    }
                }
            },
            {
                selector: '.mblock-copy-paste-toolbar .mblock-clear-clipboard',
                handler: function (e) {
                    e.preventDefault();
                    if (typeof MBlockClipboard !== 'undefined') {
                        MBlockClipboard.clear();
                        mblock_show_message('Zwischenablage geleert', 'info', 2000);
                    }
                }
            }
        ];

        // Bind all toolbar events
        toolbarEvents.forEach(({selector, handler}) => {
            MBlockUtils.events.bindSafe(MBlockUtils.dom.findElement(element, selector), 'click', handler);
        });
            
    } catch (error) {
        console.error('MBlock: Fehler in mblock_init_toolbar:', error);
    }
}

// Export for module systems (if used)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { MBlockSortable };
}
