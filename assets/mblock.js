/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

let mblock = '.mblock_wrapper',
    debug_view = true,
    timer,
    scroll_delay = 200,
    scroll_speed = 500;

$(document).on('rex:ready', function (e, container) {
    container.find(mblock).each(function () {
        mblock_init($(this));
    });
});

const mblock_module = (function () {
    let callbacks = {
        remove_item_start: [],
        add_item_start: [],
        reindex_end: [],
        lastAction: ''
    };
    let mod = {};

    mod.affectedItem = {};

    // Register a callback
    // @input event string name of the event
    // @input f function callback
    // @output void
    mod.registerCallback = function (event, f) {
        callbacks[event].push(f);
    };

    // @input event string name of the event
    // @output []function
    mod.getRegisteredCallbacks = function (event) {
        if (typeof callbacks[event] === 'undefined') {
            return [];
        }
        else {
            return callbacks[event];
        }
    };

    // @input event string name of the event
    // @output void
    mod.executeRegisteredCallbacks = function (event) {
        let list = mod.getRegisteredCallbacks(event);
        for (let i = 0; i < list.length; i++) {
            list[i](event === 'reindex_end' && mod.affectedItem);
        }
    };

    return mod;
})();

function mblock_init(mblock) {
    // init by siteload
    if (mblock.length) {
        mblock.each(function () {
            if (!$(this).data('mblock_run')) {
                $(this).data('mblock_run', 1);
                mblock_sort($(this));
                mblock_set_unique_id($(this), false);

                if ($(this).data('min') === 1 && $(this).data('max') === 1) {
                    $(this).addClass('hide_removeadded').addClass('hide_sorthandle');
                }
            }
        });
    }
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
}

function mblock_remove(element) {
    let finded = element.find('> div');

    if (finded.length === 1) {
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
        if ((index + 1) === element.data('min') && finded.length === element.data('min')) {
            $(this).find('.removeme').prop('disabled', true);
        }
        if (index === 0) {
            $(this).find('.moveup').prop('disabled', true);
        } else {
            $(this).find('.moveup').prop('disabled', false);
        }
        if ((index + 1) === finded.length) { // if max count?
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
            mblock_module.lastAction = 'sort';
            mblock_module.affectedItem = $(event.item);
            mblock_reindex(element);
        }
    });
}

function mblock_debug_view_msg(element, msg, view_class) {
    if (debug_view) { // if debug mode active add a debug msg
        element.find('.' + view_class).remove();
        element.append('<div class="' + view_class + '">elementvalue:' + msg + '</div>');
    }
}

function mblock_debug_view_border(element, color) {
    if (debug_view) { // if debug mode active add a border
        element.css('border', '1px solid ' + color);
    }
}

function mblock_set_index(element, item_index, nested, nested_item_index) {
    element.data('item-count', item_index);
    if (nested) element.data('parent-item-count', nested_item_index);

    let name_value = element.data('name-value'),
        value_id = element.data('value-id'),
        group_value = element.data('group-value'),
        parent_item_count = element.data('parent-item-count'),
        item_count = element.data('item-count'),
        item_value = element.data('item-value');

    if (typeof parent_item_count !== "undefined" && typeof group_value !== "undefined") {
        nested = true;
    }

    // create value name for nested or default
    let value_name = (nested)
        ? name_value + '[' + value_id + '][' + parent_item_count + '][' + group_value + '][' + item_count + '][' + item_value + ']'
        : name_value + '[' + value_id + '][' + item_count + '][' + item_value + ']';

    // final set attr name to element
    element.attr('name', value_name);

    // debug view
    mblock_debug_view_msg(element.parent(), 'elementvalue: ' + value_name, 'mblock-debug-view-index');
    if (nested) mblock_debug_view_border(element, 'blue'); else mblock_debug_view_border(element, 'red');
}

function mblock_reindex_element_fix(element, item_index, nested, nested_item_index) {
    // checkbox problem fix
    if (element.attr('type') === 'checkbox') {
        element.unbind().bind('change', function () {
            if (element.is(':checked')) {
                element.val(1);
            } else {
                element.val(0);
            }
        });
        return; // break
    }
    // radio problem fix
    if (element.attr('type') === 'radio' && element.attr('data-value')) {
        element.val(element.attr('data-value'));
        return; // break
    }
    // // select rex button
    // if (element.prop("nodeName") === 'SELECT' && element.attr('id') && (
    //     element.attr('id').indexOf("REX_MEDIALIST_SELECT_") >= 0 ||
    //     element.attr('id').indexOf("REX_LINKLIST_SELECT_") >= 0
    // )) {
    //     element.parent().data('eindex', eindex);
    //     element.attr('id', element.attr('id').replace(/_\d+/, '_' + sindex + '' + mblock_instance +   '00' + eindex));
    //     if (element.attr('name') !== undefined) {
    //         element.attr('name', element.attr('name').replace(/_\d+/, '_' + sindex + '' + mblock_instance + '00' + eindex));
    //     }
    // }
    //
    // // input rex button
    // if ($(this).prop("nodeName") == 'INPUT' && $(this).attr('id') && (
    //     $(this).attr('id').indexOf("REX_LINKLIST_") >= 0 ||
    //     $(this).attr('id').indexOf("REX_MEDIALIST_") >= 0
    // )) {
    //     if ($(this).parent().data('eindex')) {
    //         eindex = $(this).parent().data('eindex');
    //     }
    //     $(this).attr('id', $(this).attr('id').replace(/\d+/, sindex + '' + mblock_instance + '00' + eindex));
    //
    //     // button
    //     $(this).parent().find('a.btn-popup').each(function () {
    //         $(this).attr('onclick', $(this).attr('onclick').replace(/\(\d+/, '(' + sindex + '' + mblock_instance + '00' + eindex));
    //         $(this).attr('onclick', $(this).attr('onclick').replace(/_\d+/, '_' + sindex + '' + mblock_instance + '00' + eindex));
    //     });
    // }
    //
    // // input rex button
    // if ($(this).prop("nodeName") == 'INPUT' && $(this).attr('id') && (
    //     $(this).attr('id').indexOf("REX_LINK_") >= 0 ||
    //     $(this).attr('id').indexOf("REX_MEDIA_") >= 0
    // )) {
    //     if ($(this).attr('type') != 'hidden') {
    //         if ($(this).parent().data('eindex')) {
    //             eindex = $(this).parent().data('eindex');
    //         }
    //         $(this).attr('id', $(this).attr('id').replace(/\d+/, sindex + '' + mblock_instance + '00' + eindex));
    //
    //         if ($(this).next().attr('type') == 'hidden') {
    //             $(this).next().attr('id', $(this).next().attr('id').replace(/\d+/, sindex + '' + mblock_instance + '00' + eindex));
    //         }
    //
    //         // button
    //         $(this).parent().find('a.btn-popup').each(function () {
    //             if ($(this).attr('onclick')) {
    //                 $(this).attr('onclick', $(this).attr('onclick').replace(/\(\d+/, '(' + sindex + '' + mblock_instance + '00' + eindex));
    //                 $(this).attr('onclick', $(this).attr('onclick').replace(/_\d+/, '_' + sindex + '' + mblock_instance + '00' + eindex));
    //             }
    //         });
    //     }
    // }

}

function mblock_reindex_flow(elements, nested = false, nested_item_index = 0) {
    // iterate block items
    elements.each(function (item_index) {
        let nested_elements = [];
        let instance_id = $(this).parent().data('mblock-instance');

        // set item index
        $(this).data('mblock-iterate-index', (item_index + 1));

        // debug view
        mblock_debug_view_msg($(this), 'iterate-index:' + (item_index + 1), 'mblock-debug-view-iterate');

        // find input elements
        $(this).find('input,textarea,select,button:not(.addme,.removeme,.moveup,.movedown)').each(function (element_each_index) {
            let parent_mblock = $(this).parents(mblock).eq(0);
            if (parent_mblock.data('mblock-instance') !== instance_id) {
                nested_elements[parent_mblock.data('mblock-instance')] = parent_mblock;
                return;
            }
            if (nested) {
                // set tag name value by index
                mblock_set_index($(this), item_index, true, nested_item_index);
                // fix for checkbox, radio and system buttons
                mblock_reindex_element_fix($(this), item_index, true, nested_item_index);
            } else {
                // set tag name value by index
                mblock_set_index($(this), item_index, false);
                // fix for checkbox, radio and system buttons
                mblock_reindex_element_fix($(this), item_index, false);
            }
        });

        // there are nested elements exist?
        if (nested_elements.length) {
            // each nested elements
            nested_elements.forEach(function (nested_element) {
                // execute the reindex flow for the nested elements
                mblock_reindex_flow(nested_element.find('> div.sortitem'), true, item_index);
            });
        }
    });
}

function mblock_reindex(element) {
    mblock_reindex_flow(element.find('> div.sortitem'));

    /*
    // TODO DO WE NEED THAT?
    // iterate block items
    element.find('> div.sortitem').each(function (item_index) {

        $(this).find('a[data-toggle="collapse"]').each(function (key) {
            eindex = key + 1;
            sindex = item_index + 1;
            togglecollase = $(this);
            if (!$(this).attr('data-ignore-mblock')) {
                href = $(this).attr('data-target');
                container = togglecollase.parent().find(href);
                group = togglecollase.parent().parent().parent().find('.panel-group');
                nexit = container.attr('id').replace(/_\d+/, '_' + sindex + '' + mblock_instance + '00' + eindex);

                container.attr('id', nexit);
                togglecollase.attr('data-target', '#' + nexit);

                if (group.length) {
                    parentit = group.attr('id').replace(/_\d+/, '_' + sindex + '' + mblock_instance + '00');
                    group.attr('id', parentit);
                    togglecollase.attr('data-parent', '#' + parentit);
                }
            }
        });

        $(this).find('a[data-toggle="tab"]').each(function (key) {
            eindex = key + 1;
            sindex = item_index + 1;
            toggletab = $(this);
            href = $(this).attr('href');
            container = toggletab.parent().parent().parent().find('.tab-content ' + href);
            nexit = container.attr('id').replace(/_\d+/, '_' + sindex + '' + mblock_instance + '00' + eindex);

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
        });

        $(this).find('.custom-link').each(function (key) {
            eindex = key + 1;
            sindex = item_index + 1;
            customlink = $(this);
            $(this).find('input').each(function () {
                if ($(this).attr('id')) {
                    $(this).attr('id', $(this).attr('id').replace(/\d+/, sindex + '' + mblock_instance + '00' + eindex));
                }
            });
            $(this).find('a.btn-popup').each(function () {
                if ($(this).attr('id')) {
                    $(this).attr('id', $(this).attr('id').replace(/\d+/, sindex + '' + mblock_instance + '00' + eindex));
                }
            });
            customlink.attr('data-id', sindex + '' + mblock_instance + '00' + eindex);
            if (typeof mform_custom_link === 'function') mform_custom_link(customlink);
        });
    });
    */

    // TODO DO WE NEED THAT?
    // if not removing, sets "for" attribute for most elements to make them work properly
    if (mblock_module.lastAction != 'remove_item') {
        mblock_replace_for(element);
    }

    mblock_module.executeRegisteredCallbacks('reindex_end');
}


function mblock_replace_for(element) {
    element.find(' > div').each(function () {
        let mblock = $(this);
        mblock.find('input:not(:checkbox):not(:radio),textarea,select').each(function () {
            let el = $(this);
            let id = el.attr('id');
            if (typeof id !== typeof undefined && id !== false) {
                if (!(id.indexOf("REX_MEDIA") >= 0 ||
                    id.indexOf("REX_LINK") >= 0 ||
                    id.indexOf("markitup") >= 0)
                ) {
                    let name = el.attr('name').replace(/(\[|\])/gm, '');
                    el.attr('id', name);
                    mblock.find('label[for="' + id + '"]').attr('for', name);
                }
            }
        });
    });
}

function mblock_add_item(element, item) {

    mblock_module.executeRegisteredCallbacks('add_item_start');

    if (item.parent().hasClass(element.attr('class'))) {
        // unset sortable
        element.mblock_sortable("destroy");

        let iClone = item.clone();

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

        // delete values
        if (element.data('input-delete') === 1) {

            if (iClone.find('.mblock_wrapper').length) {
                iClone.find('.mblock_wrapper').each(function () {
                    $(this).find('> div.sortitem').each(function (index) {
                        if (index > 0) {
                            $(this).remove();
                        }
                    });
                });
            }

            iClone.find('input:not(.not_delete), textarea').val('');
            iClone.find('textarea').html('');
            iClone.find('option:selected').removeAttr("selected");
            iClone.find('input:checked').removeAttr("checked");
            iClone.find('select').each(function () {
                $(this).attr('data-selected', '');
                if ($(this).attr('id') && ($(this).attr('id').indexOf("REX_MEDIALIST") >= 0
                    || $(this).attr('id').indexOf("REX_LINKLIST") >= 0
                )) {
                    $(this).find('option').remove();
                }
            });
        }

        // add clone
        item.after(iClone);

        // set currently affected item
        mblock_module.affectedItem = iClone;

        mblock_set_unique_id(iClone, true);
        // set count
        mblock_set_count(element, item);
        // set last user action
        mblock_module.lastAction = 'add_item';
        // reinit
        mblock_init_sort(element);
        // scroll to item
        mblock_scroll(element, iClone);

        element.trigger('mblock:add', [element]);
    }
}

function mblock_set_unique_id(item, input_delete) {
    item.find('input').each(function () {
        let unique_id = Math.random().toString(16).slice(2),
            unique_int = parseInt(Math.random() * 1000000000000);

        if ($(this).attr('data-unique-int') === 1) {
            unique_id = unique_int;
        }
        if ($(this).attr('data-unique') === 1 || $(this).attr('data-unique-int') === 1) {
            if (input_delete === true) {
                $(this).val('');
            }
            if ($(this).val() === '') {
                $(this).val(unique_id);
            }
        }
    });
}

function mblock_set_count(element, item) {
    let countItem = item.next().find('span.mb_count'),
        count = element.find('> div').length;

    if (element.data('latest')) {
        count = element.data('latest') + 1;
    }

    countItem.text(count);
    element.data('latest', count);
}

function mblock_remove_item(element, item) {
    mblock_module.executeRegisteredCallbacks('remove_item_start');
    if (element.data().hasOwnProperty('delete-confirm')) {
        if (!confirm(element.data('delete-confirm'))) {
            return false;
        }
    }

    if (item.parent().hasClass(element.attr('class'))) {
        // unset sortable
        element.mblock_sortable("destory");
        // set prev item
        let prevItem = item.prev();
        // is prev exist?
        if (!prevItem.hasClass('sortitem')) {
            prevItem = item.next(); // go to next
        }
        // set currently affected item
        mblock_module.affectedItem = item;
        // remove element
        item.remove();
        // set last user action
        mblock_module.lastAction = 'remove_item';
        // reinit
        mblock_init_sort(element);
        // scroll to item
        mblock_scroll(element, prevItem);
    }
}

function mblock_moveup(element, item) {
    let prev = item.prev();
    if (prev.length === 0) return;

    // set currently affected item
    mblock_module.affectedItem = item;

    item.insertBefore(prev);
    // set last user action
    mblock_module.lastAction = 'moveup';
    mblock_reindex(element);
    mblock_remove(element);
}

function mblock_movedown(element, item) {
    let next = item.next();
    if (next.length === 0) return;

    // set currently affected item
    mblock_module.affectedItem = item;

    item.insertAfter(next);
    // set last user action
    mblock_module.lastAction = 'movedown';
    mblock_reindex(element);
    mblock_remove(element);
}

function mblock_scroll(element, item) {
    if (element.data('smooth-scroll') === 1) {
        let scrolling_delay = (element.data('smooth-scroll-delay') > 0) ? element.data('smooth-scroll-delay') : scroll_delay,
            scrolling_speed = (element.data('smooth-scroll-speed') > 0) ? element.data('smooth-scroll-speed') : scroll_speed;
        window.clearTimeout(timer);
        timer = window.setTimeout(mblock_smooth_scroll_it(item, scrolling_speed), scrolling_delay, clearTimeout(timer));
    }
}

function mblock_smooth_scroll_it(element, scrolling_speed) {
    $.mblockSmoothScroll({
        scrollTarget: element,
        speed: scrolling_speed
    });
}

function mblock_add(element) {
    element.find('> div .addme').unbind().bind('click', function () {
        if (!$(this).prop('disabled')) {
            let $item = $(this).parents('.sortitem');
            element.attr('data-mblock_clicked_add_item', $item.data('mblock-iterate-index'));
            mblock_add_item(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
    element.find('> div .removeme').unbind().bind('click', function () {
        if (!$(this).prop('disabled')) {
            mblock_remove_item(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
    element.find('> div .moveup').unbind().bind('click', function () {
        if (!$(this).prop('disabled')) {
            mblock_moveup(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
    element.find('> div .movedown').unbind().bind('click', function () {
        if (!$(this).prop('disabled')) {
            mblock_movedown(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
}
