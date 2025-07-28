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
        if (!element || !element.length || typeof element.mblock_sortable !== 'function') {
            console.warn('MBlock: Sortable nicht verfügbar für Element');
            return false;
        }

        element.mblock_sortable({
            handle: '.sorthandle',
            animation: 150,
            onEnd: function (event) {
                try {
                    if (event && event.item) {
                        mblock_reindex(element);
                        mblock_remove(element);
                        // trigger event
                        let iClone = $(event.item);
                        if (iClone.length) {
                            iClone.trigger('mblock:change', [iClone]);
                        }
                    }
                } catch (error) {
                    console.error('MBlock: Fehler in sortable onEnd:', error);
                }
            }
        });
        return true;
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
        // unset sortable
        element.mblock_sortable("destroy");
        // add clone
        item.after(iClone);
        // set count
        mblock_set_count(element, item);
    }

    // add unique id
    mblock_set_unique_id(iClone, true);
    // reinit
    mblock_init_sort(element);
    // scroll to item
    mblock_scroll(element, iClone);
    // trigger rex ready
    iClone.trigger('rex:ready', [iClone]);
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
            // Sichere Sortable-Deaktivierung
            try {
                if (typeof element.mblock_sortable === 'function') {
                    element.mblock_sortable("destroy"); // Fixed typo: "destory" -> "destroy"
                }
            } catch (sortableError) {
                console.warn('MBlock: Sortable destroy fehlgeschlagen:', sortableError);
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
        item.remove();
            // reinit
            mblock_init_sort(element);
            // scroll to item
            mblock_scroll(element, prevItem);
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
        if (elementData && elementData.hasOwnProperty('smooth_scroll') && elementData.smooth_scroll === true) {
            if (typeof $.mblockSmoothScroll === 'function') {
                $.mblockSmoothScroll({
                    scrollTarget: item,
                    speed: 500
                });
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

        return true;
    } catch (error) {
        console.error('MBlock: Fehler in mblock_add:', error);
        return false;
    }
}
