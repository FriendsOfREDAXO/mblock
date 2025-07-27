/**
 * Created by joachimdoerr on 30.07.16.
 */

let mblock = '.mblock_wrapper';

$(document).on('rex:ready', function (e, container) {
    container.find(mblock).each(function () {
        mblock_init($(this));
    });
});

function mblock_init(element) {
    if (!element.data('mblock_run')) {
        element.data('mblock_run', 1);
        mblock_sort(element);
        mblock_set_unique_id(element, false);
        // Copy & Paste Funktionalität initialisieren
        mblock_init_copy_paste(element);

        if (element.data('min') == 1 && element.data('max') == 1) {
            element.addClass('hide_removeadded').addClass('hide_sorthandle');
        }
    }
    mblock_add_plus(element);
}

/**
 * Initialisiert Copy & Paste Funktionalität für MBlock
 * @param {jQuery} element - MBlock Container Element
 */
function mblock_init_copy_paste(element) {
    // Clipboard für dieses MBlock (nur innerhalb einer Instanz)
    if (!element.data('mblock_clipboard')) {
        element.data('mblock_clipboard', null);
    }
    
    // Copy & Paste Buttons zu bestehenden Blöcken hinzufügen
    mblock_add_copy_paste_buttons(element);
}

// List with handle
function mblock_init_sort(element) {
    // reindex
    mblock_reindex(element);
    // init
    mblock_sort(element);
}

function mblock_sort(element) {
    // add linking
    mblock_add(element);
    // remove mblock_remove
    mblock_remove(element);
    // init sortable
    mblock_sort_it(element);
    // Copy & Paste Buttons aktualisieren
    mblock_add_copy_paste_buttons(element);
}

/**
 * Fügt Copy & Paste Buttons zu allen MBlock Items hinzu
 * @param {jQuery} element - MBlock Container Element
 */
function mblock_add_copy_paste_buttons(element) {
    element.find('> div.sortitem').each(function() {
        var item = $(this);
        
        // Prüfen ob bereits Copy/Paste Buttons vorhanden sind
        if (item.find('.mblock-copy-paste-toolbar').length === 0) {
            // Toolbar für Copy & Paste Buttons erstellen
            var toolbar = $('<div class="mblock-copy-paste-toolbar" style="margin-bottom: 10px; padding: 5px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;"></div>');
            
            // Copy Button
            var copyBtn = $('<button type="button" class="btn btn-sm btn-default mblock-copy-btn" title="Block kopieren" style="margin-right: 5px;"><i class="rex-icon fa-copy"></i> Kopieren</button>');
            
            // Paste Button (anfangs deaktiviert wenn kein Clipboard-Inhalt)
            var pasteBtn = $('<button type="button" class="btn btn-sm btn-default mblock-paste-btn" title="Block einfügen"><i class="rex-icon fa-paste"></i> Einfügen</button>');
            
            // Paste Button Status aktualisieren
            if (!element.data('mblock_clipboard')) {
                pasteBtn.prop('disabled', true).addClass('disabled');
            }
            
            toolbar.append(copyBtn).append(pasteBtn);
            item.prepend(toolbar);
            
            // Event Handlers
            mblock_bind_copy_paste_events(element, item, copyBtn, pasteBtn);
        }
    });
}

/**
 * Bindet Events für Copy & Paste Buttons
 * @param {jQuery} element - MBlock Container
 * @param {jQuery} item - MBlock Item
 * @param {jQuery} copyBtn - Copy Button
 * @param {jQuery} pasteBtn - Paste Button
 */
function mblock_bind_copy_paste_events(element, item, copyBtn, pasteBtn) {
    // Copy Event
    copyBtn.off('click').on('click', function(e) {
        e.preventDefault();
        mblock_copy_item(element, item);
        
        // Alle Paste Buttons in dieser MBlock-Instanz aktivieren
        element.find('.mblock-paste-btn').prop('disabled', false).removeClass('disabled');
        
        // Visuelles Feedback
        var originalText = copyBtn.html();
        copyBtn.html('<i class="rex-icon fa-check"></i> Kopiert!').addClass('btn-success');
        setTimeout(function() {
            copyBtn.html(originalText).removeClass('btn-success');
        }, 1500);
    });
    
    // Paste Event
    pasteBtn.off('click').on('click', function(e) {
        e.preventDefault();
        if (element.data('mblock_clipboard')) {
            mblock_paste_item(element, item);
        }
    });
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
    element.mblock_sortable({
        handle: '.sorthandle',
        animation: 150,
        onEnd: function (event) {
            mblock_reindex(element);
            mblock_remove(element);
            // trigger event
            let iClone = $(event.item);
            iClone.trigger('mblock:change', [iClone]);
        }
    });
}

function mblock_reindex(element) {

    var mblock_count = element.data('mblock_count');

    element.find('> div.sortitem').each(function (index) {
        // find input elements
        $(this).attr('data-mblock_index', (index + 1));
        $(this).find('input,textarea,select,button').each(function (key) {
            var attr = $(this).attr('name');
            eindex = key + 1;
            sindex = index + 1;
            // For some browsers, `attr` is undefined; for others,
            // `attr` is false. Check for both.
            if (typeof attr !== typeof undefined && attr !== false) {
                var value = attr.replace($(this).attr('name').match(/\]\[\d+\]\[/g), '][' + index + '][').replace('mblock_new_', '');
                $(this).attr('name', value);
            }

            // checkbox problem fix
            if ($(this).attr('type') == 'checkbox') {
                $(this).unbind().bind('change', function () {
                    if ($(this).is(':checked')) {
                        $(this).val(1);
                    } else {
                        $(this).val(0);
                    }
                });
            }

            // radio problem fix
            if ($(this).attr('type') == 'radio' && $(this).attr('data-value')) {
                $(this).val($(this).attr('data-value'));
            }

            // select rex button
            if ($(this).prop("nodeName") == 'SELECT' && $(this).attr('id') && (
                $(this).attr('id').indexOf("REX_MEDIALIST_SELECT_") >= 0 ||
                $(this).attr('id').indexOf("REX_LINKLIST_SELECT_") >= 0
            )) {
                $(this).parent().data('eindex', eindex);
                $(this).attr('id', $(this).attr('id').replace(/_\d+/, '_' + sindex + '' + mblock_count + '00' + eindex));
                if ($(this).attr('name') != undefined) {
                    $(this).attr('name', $(this).attr('name').replace(/_\d+/, '_' + sindex + '' + mblock_count + '00' + eindex));
                }
            }

            // input rex button
            if ($(this).prop("nodeName") == 'INPUT' && $(this).attr('id') && (
                $(this).attr('id').indexOf("REX_LINKLIST_") >= 0 ||
                $(this).attr('id').indexOf("REX_MEDIALIST_") >= 0
            )) {
                if ($(this).parent().data('eindex')) {
                    eindex = $(this).parent().data('eindex');
                }
                $(this).attr('id', $(this).attr('id').replace(/\d+/, sindex + '' + mblock_count + '00' + eindex));

                // button
                $(this).parent().find('a.btn-popup').each(function () {
                    $(this).attr('onclick', $(this).attr('onclick').replace(/\('?\d+/, '(\'' + sindex + '' + mblock_count + '00' + eindex));
                    $(this).attr('onclick', $(this).attr('onclick').replace(/_\d+/, '_' + sindex + '' + mblock_count + '00' + eindex));
                });
            }

            // input rex button
            if ($(this).prop("nodeName") == 'INPUT' && $(this).attr('id') && (
                $(this).attr('id').indexOf("REX_LINK_") >= 0 ||
                $(this).attr('id').indexOf("REX_MEDIA_") >= 0
            )) {
                if ($(this).attr('type') != 'hidden') {
                    if ($(this).parent().data('eindex')) {
                        eindex = $(this).parent().data('eindex');
                    }
                    $(this).attr('id', $(this).attr('id').replace(/\d+/, sindex + '' + mblock_count + '00' + eindex));

                    if ($(this).next().attr('type') == 'hidden') {
                        $(this).next().attr('id', $(this).next().attr('id').replace(/\d+/, sindex + '' + mblock_count + '00' + eindex));
                    }
                }
            }

            // input rex link button
            if ($(this).prop("nodeName") == 'INPUT' && $(this).attr('id') && (
                $(this).attr('id').indexOf("REX_LINK_") >= 0
            )) {
                if ($(this).attr('type') != 'hidden') {
                    // button
                    $(this).parent().find('a.btn-popup').each(function () {
                        if ($(this).attr('onclick')) {
                            $(this).attr('onclick', $(this).attr('onclick').replace(/\('?\d+/, '(\'' + sindex + '' + mblock_count + '00' + eindex));
                            $(this).attr('onclick', $(this).attr('onclick').replace(/_\d+/, '_' + sindex + '' + mblock_count + '00' + eindex));
                        }
                    });
                }
            }

            // input rex media button
            if ($(this).prop("nodeName") == 'INPUT' && $(this).attr('id') && (
                $(this).attr('id').indexOf("REX_MEDIA_") >= 0
            )) {
                if ($(this).attr('type') != 'hidden') {
                    // button
                    $(this).parent().find('a.btn-popup').each(function () {
                        if ($(this).attr('onclick')) {
                            $(this).attr('onclick', $(this).attr('onclick').replace(/\('?\d+/, '(\'' + sindex + '' + mblock_count + '00' + eindex));
                            $(this).attr('onclick', $(this).attr('onclick').replace(/_\d+/, '_' + sindex + '' + mblock_count + '00' + eindex));
                        }
                    });
                }
            }
        });

        $(this).find('a[data-toggle="collapse"]').each(function (key) {
            eindex = key + 1;
            sindex = index + 1;
            togglecollase = $(this);
            if (!$(this).attr('data-ignore-mblock')) {
                href = $(this).attr('data-target');
                container = togglecollase.parent().find(href);
                if (container.length) {
                    group = togglecollase.parent().parent().parent().find('.panel-group');
                    nexit = container.attr('id').replace(/_\d+/, '_' + sindex + '' + mblock_count + '00' + eindex);

                    container.attr('id', nexit);
                    togglecollase.attr('data-target', '#' + nexit);

                    if (group.length) {
                        parentit = group.attr('id').replace(/_\d+/, '_' + sindex + '' + mblock_count + '00');
                        group.attr('id', parentit);
                        togglecollase.attr('data-parent', '#' + parentit);
                    }
                }
            }
        });

        $(this).find('a[data-toggle="tab"]').each(function (key) {
            eindex = key + 1;
            sindex = index + 1;
            toggletab = $(this);
            href = $(this).attr('href');
            container = toggletab.parent().parent().parent().find('.tab-content ' + href);
            if (container.length) {
                nexit = container.attr('id').replace(/_\d+/, '_' + sindex + '' + mblock_count + '00' + eindex);

                container.attr('id', nexit);
                toggletab.attr('href', '#' + nexit);

                toggletab.unbind().bind("shown.bs.tab", function (e) {
                    var id = $(e.target).attr("href");
                    localStorage.setItem('selectedTab', id)
                });

                var selectedTab = localStorage.getItem('selectedTab');
                if (selectedTab != null) {
                    $('a[data-toggle="tab"][href="' + selectedTab + '"]').tab('show');
                }
            }
        });

        $(this).find('.custom-link').each(function (key) {
            eindex = key + 1;
            sindex = index + 1;
            customlink = $(this);
            $(this).find('input').each(function () {
                if ($(this).attr('id')) {
                    $(this).attr('id', $(this).attr('id').replace(/\d+/, sindex + '' + mblock_count + '00' + eindex));
                }
            });
            $(this).find('a.btn-popup').each(function () {
                if ($(this).attr('id')) {
                    $(this).attr('id', $(this).attr('id').replace(/\d+/, sindex + '' + mblock_count + '00' + eindex));
                }
            });
            customlink.attr('data-id', sindex + '' + mblock_count + '00' + eindex);
            if (typeof mform_custom_link === 'function') mform_custom_link(customlink);
        });
    });

    mblock_replace_for(element);
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
    item.find('input').each(function () {
        var unique_id = Math.random().toString(16).slice(2),
            unique_int = parseInt(Math.random() * 1000000000000);

        if ($(this).attr('data-unique-int') == 1) {
            unique_id = unique_int;
        }
        if ($(this).attr('data-unique') == 1 || $(this).attr('data-unique-int') == 1) {
            if (input_delete == true) {
                $(this).val('');
            }
            if ($(this).val() == '') {
                $(this).val(unique_id);
            }
        }
    });
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
    if (element.data().hasOwnProperty('delete_confirm')) {
        if (!confirm(element.data('delete_confirm'))) {
            return false;
        }
    }

    if (item.parent().hasClass(element.attr('class'))) {
        // unset sortable
        element.mblock_sortable("destory");
        // set prev item
        var prevItem = item.prev();
        // is prev exist?
        if (!prevItem.hasClass('sortitem')) {
            prevItem = item.next(); // go to next
        }
        // remove element
        item.remove();
        // reinit
        mblock_init_sort(element);
        // scroll to item
        mblock_scroll(element, prevItem);
        // add add button
        mblock_add_plus(element);
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
    if (element.data().hasOwnProperty('smooth_scroll')) {
        if (element.data('smooth_scroll') == true) {
            $.mblockSmoothScroll({
                scrollTarget: item,
                speed: 500
            });
        }
    }
}

function mblock_add(element) {
    element.find('> div.sortitem .addme').unbind().bind('click', function () {
        if (!$(this).prop('disabled')) {
            $item = $(this).parents('.sortitem');
            element.attr('data-mblock_clicked_add_item', $item.attr('data-mblock_index'));
            mblock_add_item(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
    element.find('> div.sortitem .removeme').unbind().bind('click', function () {
        if (!$(this).prop('disabled')) {
            mblock_remove_item(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
    element.find('> div.sortitem .moveup').unbind().bind('click', function () {
        if (!$(this).prop('disabled')) {
            mblock_moveup(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
    element.find('> div.sortitem .movedown').unbind().bind('click', function () {
        if (!$(this).prop('disabled')) {
            mblock_movedown(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
}

/**
 * Entfernt alle IDs aus einem Item vor dem Paste um Duplikate zu vermeiden
 * @param {jQuery} item - MBlock Item das eingefügt werden soll
 */
function mblock_clear_ids_before_paste(item) {
    // Alle Elemente mit ID-Attribut finden und IDs entfernen
    item.find('[id]').each(function() {
        $(this).removeAttr('id');
    });
    
    // Alle data-target und href Attribute auf Tabs/Collapse entfernen
    item.find('a[data-toggle="tab"]').each(function() {
        $(this).removeAttr('href');
    });
    
    item.find('a[data-toggle="collapse"]').each(function() {
        $(this).removeAttr('data-target').removeAttr('data-parent');
    });
    
    // Alle for-Attribute von Labels entfernen
    item.find('label[for]').each(function() {
        $(this).removeAttr('for');
    });
}

/**
 * Kopiert ein MBlock Item in das Clipboard dieser MBlock-Instanz
 * @param {jQuery} element - MBlock Container Element
 * @param {jQuery} item - Zu kopierendes MBlock Item
 */
function mblock_copy_item(element, item) {
    // Item klonen (mit allen Daten und Events)
    var clonedItem = item.clone(true, true);
    
    // Copy & Paste Toolbar aus dem Klon entfernen (wird beim Einfügen neu erstellt)
    clonedItem.find('.mblock-copy-paste-toolbar').remove();
    
    // In Clipboard dieser MBlock-Instanz speichern
    element.data('mblock_clipboard', {
        html: clonedItem.prop('outerHTML'),
        data: mblock_extract_form_data(item),
        timestamp: Date.now()
    });
    
    console.log('MBlock Item copied to clipboard:', element.data('mblock_clipboard'));
}

/**
 * Fügt ein Item aus dem Clipboard nach dem aktuellen Item ein
 * @param {jQuery} element - MBlock Container Element  
 * @param {jQuery} currentItem - Item nach dem eingefügt werden soll
 */
function mblock_paste_item(element, currentItem) {
    var clipboard = element.data('mblock_clipboard');
    if (!clipboard) {
        console.warn('Kein Item im Clipboard verfügbar');
        return;
    }
    
    // Prüfen ob maximale Anzahl erreicht
    if (element.data('max') && element.find('> div.sortitem').length >= element.data('max')) {
        alert('Maximale Anzahl von Blöcken erreicht');
        return;
    }
    
    // Sortable deaktivieren
    element.mblock_sortable("destroy");
    
    // Item aus Clipboard erstellen
    var newItem = $(clipboard.html);
    
    // WICHTIG: Alle IDs temporär entfernen um Duplikate zu vermeiden
    mblock_clear_ids_before_paste(newItem);
    
    // Nach dem aktuellen Item einfügen
    currentItem.after(newItem);
    
    // Form-Daten wiederherstellen
    mblock_restore_form_data(newItem, clipboard.data);
    
    // Unique IDs generieren (leert auch Felder mit data-unique Attribut)
    mblock_set_unique_id(newItem, true);
    
    // VOLLSTÄNDIGE Reindexierung aller Items (behebt Tab/Collapse/Field IDs)
    mblock_reindex(element);
    
    // Alles neu initialisieren
    mblock_init_sort(element);
    
    // Scroll zum neuen Item
    mblock_scroll(element, newItem);
    
    // Rex Ready Event triggern für Widget-Reinitialisierung
    newItem.trigger('rex:ready', [newItem]);
    
    console.log('MBlock Item pasted successfully');
}

/**
 * Extrahiert Formulardaten aus einem MBlock Item
 * @param {jQuery} item - MBlock Item
 * @returns {Object} Formulardaten
 */
function mblock_extract_form_data(item) {
    var formData = {};
    
    item.find('input, textarea, select').each(function() {
        var $field = $(this);
        var name = $field.attr('name');
        var type = $field.attr('type');
        
        if (name) {
            if (type === 'checkbox' || type === 'radio') {
                formData[name] = {
                    value: $field.val(),
                    checked: $field.prop('checked')
                };
            } else {
                formData[name] = {
                    value: $field.val()
                };
            }
        }
    });
    
    return formData;
}

/**
 * Stellt Formulardaten in einem MBlock Item wieder her
 * @param {jQuery} item - MBlock Item
 * @param {Object} formData - Formulardaten
 */
function mblock_restore_form_data(item, formData) {
    // Erst alle Felder zurücksetzen
    item.find('input, textarea, select').each(function() {
        var $field = $(this);
        var type = $field.attr('type');
        
        if (type === 'checkbox' || type === 'radio') {
            $field.prop('checked', false);
        } else if (type !== 'hidden') {
            $field.val('');
        }
    });
    
    // Dann gespeicherte Daten wiederherstellen
    Object.keys(formData).forEach(function(fieldName) {
        var fieldData = formData[fieldName];
        var $field = item.find('[name="' + fieldName + '"]');
        
        if ($field.length > 0) {
            var type = $field.attr('type');
            
            if (type === 'checkbox' || type === 'radio') {
                $field.val(fieldData.value);
                $field.prop('checked', fieldData.checked);
            } else {
                // Für unique Felder keine Werte wiederherstellen
                if (!$field.attr('data-unique') && !$field.attr('data-unique-int')) {
                    $field.val(fieldData.value);
                }
            }
        }
    });
}
