/**
 * Created by joachimdoerr on 30.07.16.
 */

let mblock = '.mblock_wrapper';
let DEBUG = true;

$(document).on('rex:ready', function (e, container) {
    container.find(mblock).each(function (index) {
        mblock_init($(this), index);
    });
});

function mblock_init(element, index) {
    if (!element.data('mblock_run')) {

        if (DEBUG) console.log('execute mblock_init: ' + element.attr('class') + ' index: ' + index);

        element.data('mblock_run', 1);

        mblock_sort(element);
        mblock_add(element);
        mblock_set_unique_id(element, false);

        if (element.data('min') == 1 && element.data('max') == 1) {
            element.addClass('hide_removeadded').addClass('hide_sorthandle');
        }
    }
    mblock_add_plus(element);
}

// List with handle
function mblock_init_sort(element) {
    // reindex
    mblock_reindex(element);
    // init
    mblock_sort(element);
}

function mblock_sort(element) {
    // remove mblock_remove
    mblock_set_addAndRemoveBtn_visibility(element);
    // init sortable
    mblock_init_sortability(element);
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

// show or hide add and remove buttons
function mblock_set_addAndRemoveBtn_visibility(element) {
    let finded = element.find('> div.sortitem'),
        removeme = finded.find('> .removeadded .removeme'),
        addme = finded.find('> .removeadded .addme'),
        copyme = finded.find('> .removeadded .copyme');

    if (finded.length == 1) {
        removeme.prop('disabled', true);
        removeme.attr('data-disabled', true);
    } else {
        removeme.prop('disabled', false);
        removeme.attr('data-disabled', false);
    }

    // has data?
    if (element.data().hasOwnProperty('max')) {
        if (finded.length >= element.data('max')) {
            addme.prop('disabled', true);
            addme.attr('data-disabled', true);
            copyme.prop('disabled', true);
            copyme.attr('data-disabled', true);
        } else {
            addme.prop('disabled', false);
            addme.attr('data-disabled', false);
            copyme.prop('disabled', false);
            copyme.attr('data-disabled', false);
        }
    }

    if (element.data().hasOwnProperty('min')) {
        if (finded.length <= element.data('min')) {
            removeme.prop('disabled', true);
            removeme.attr('data-disabled', true);
        } else {
            removeme.prop('disabled', false);
            removeme.attr('data-disabled', false);
        }
    }

    finded.each(function (index) {
        // min removeme hide
        let tremoveme = $(this).find('> .removeadded .removeme'),
            taddme = $(this).find('> .removeadded .addme'),
            tmoveup = $(this).find('> .removeadded .moveup'),
            tmovedown = $(this).find('> .removeadded .movedown');
        if ((index + 1) == element.data('min') && finded.length == element.data('min')) {
            tremoveme.prop('disabled', true);
            tremoveme.attr('data-disabled', true);
        }
        if (index == 0) {
            tmoveup.prop('disabled', true);
            tmoveup.attr('data-disabled', true);
        } else {
            tmoveup.prop('disabled', false);
            tmoveup.attr('data-disabled', false);
        }
        if ((index + 1) == finded.length) { // if max count?
            tmovedown.prop('disabled', true);
            tmovedown.attr('data-disabled', true);
        } else {
            tmovedown.prop('disabled', false);
            tmovedown.attr('data-disabled', false);
        }
    });
}

function mblock_init_sortability(element) {
    element.mblock_sortable({
        handle: '.sorthandle',
        animation: 150,
        onEnd: function (event) {
            mblock_reindex(element);
            element.removeClass('sortable_start');
            // trigger event
            let iClone = $(event.item);

            // set checked
            resetChecked(element);

            // trigger event
            iClone.trigger('rex:change', [iClone]);
        },
        onStart: function (event) {
            checkedFind(element);
            element.addClass('sortable_start');
        }
    });
}

function mblock_reindex(element) {

    let parent_mblocks = element.parents(mblock);

    element.find('> div.sortitem').each(function (index) {
        let sortitem = $(this),
            parent_sortitem = sortitem.parents('div.sortitem').first();

        sortitem.attr('data-default-count', index).find('input,textarea,select,button').each(function () {

            if (DEBUG) console.log($(this).attr('type'));

            let name = $(this).data('name-value'),
                this_name = $(this).attr('name'),
                res_third, res_second, res_default;

            if ((typeof this_name !== typeof undefined && this_name !== false)) {
                res_third = this_name.match(/(\[\w+\])(\[\d\])(\[\w+\])(\[\d\])(\[\w+\])(\[\d\])/);
                res_second = this_name.match(/(\[\w+\])(\[\d\])(\[\w+\])(\[\d\])/);
                res_default = this_name.match(/(\[\w+\])(\[\d\])/);
            }

            if (DEBUG) console.log([name, this_name, res_third, res_second, res_default]);

            if (res_third !== null && Array.isArray(res_third) && parent_mblocks.length === 2) {

                let $defaultValueName = res_third[1],
                    $defaultCount = parent_sortitem.parents('div.sortitem').first().attr('data-default-count'), // res_third[2],
                    $secondValueName = res_third[3],
                    $secondCount = parent_sortitem.attr('data-default-count'), // res_third[4],
                    $thirdValueName = res_third[5],
                    $thirdCount = index,
                    $replace = name + $defaultValueName + '[' + $defaultCount + ']' + $secondValueName + '[' + $secondCount + ']' + $thirdValueName + '[' + $thirdCount + ']';

                $(this).attr('name', this_name.replace(name + res_third[0], $replace));
                if (DEBUG) console.log($(this).attr('name'));

            } else if (res_second !== null && Array.isArray(res_second) && parent_mblocks.length === 1) {

                let $defaultValueName = res_second[1],
                    $defaultCount = parent_sortitem.first().attr('data-default-count'), // res_second[2],
                    $secondValueName = res_second[3],
                    $secondCount = index,
                    $replace = name + $defaultValueName + '[' + $defaultCount + ']' + $secondValueName + '[' + $secondCount + ']';

                $(this).attr('name', this_name.replace(name + res_second[0], $replace));
                if (DEBUG) console.log($(this).attr('name'));

                sortitem.find(mblock).each(function () {
                    mblock_reindex($(this));
                });

            } else if (res_default !== null && Array.isArray(res_default)) {

                let $defaultValueName = res_default[1],
                    $defaultCount = index,
                    $replace = name + $defaultValueName + '[' + $defaultCount + ']';

                if (DEBUG) console.log(name + res_default[0]);
                if (DEBUG) console.log($replace);
                $(this).attr('name', this_name.replace(name + res_default[0], $replace));
                if (DEBUG) console.log($(this).attr('name'));

                sortitem.find(mblock).each(function () {
                    mblock_reindex($(this));
                });
            }

            mblock_modify_system_buttons($(this));
            mblock_modify_system_list_buttons($(this));
            mblock_modify_custom_link_buttons($(this));
            mblock_modify_custom_imglist_buttons($(this));
            mblock_replace_for($(this));
        });
    });

}

function mblock_modify_system_list_buttons(element) {
    if (element.prop("nodeName") === 'INPUT' && element.attr('id') && (
        element.attr('id').indexOf("REX_LINKLIST_") >= 0 || element.attr('id').indexOf("REX_MEDIALIST_") >= 0
    )) {
        let element_type = (element.attr('id').indexOf("REX_LINKLIST_") >= 0) ? 'REX_LINKLIST' : 'REX_MEDIALIST',
            this_id = element.attr('name').replace(/\]\[/g, ''),
            old_id = element.attr('id');

        if (element.attr('type') === 'hidden') {
            if (DEBUG) console.log(this_id);

            this_id = this_id.replace(/\[/g, '_');
            this_id = this_id.replace(/\]/g, '');

            let select_id = this_id.replace('REX_INPUT_VALUE', element_type + '_SELECT'),
                select_name = element.attr('name').replace('REX_INPUT_VALUE', element_type + '_SELECT');

            this_id = this_id.replace('REX_INPUT_VALUE', element_type);

            // if (DEBUG) console.log(this_id);
            // if (DEBUG) console.log(select_id);
            // if (DEBUG) console.log(select_name);

            element.prev().attr('name', select_name).attr('id', select_id);
            element.attr('id', this_id);

            replace_btn_popup_id(element, element_type, old_id, this_id);
        }
    }
}

function mblock_modify_custom_imglist_buttons(element) {
    if (element.prop("nodeName") === 'INPUT' && element.attr('id') && (
        element.attr('id').indexOf("REX_LINKLIST_") >= 0 || element.attr('id').indexOf("REX_MEDIALIST_") >= 0
    )) {
        let this_id = element.attr('id').replace('REX_MEDIALIST_', '');
        element.parent().parent().attr('data-widget-id', this_id);
        element.parent().find('ul').attr('id', 'REX_IMGLIST_' + this_id);
    }
}

function mblock_modify_custom_link_buttons(element) {
    if (element.prop("nodeName") === 'INPUT' && element.attr('id') &&
        element.parent().attr('class').indexOf("custom-link") >= 0 && element.attr('name')
    ) {
        element = element.parent();

        let inputs = element.find('input'),
            nameInput;

        inputs.each(function(){
            if($(this).attr('name')) {
                nameInput = $(this);

                let this_id = $(this).attr('name').replace(/\]\[/g, '');
                this_id = this_id.replace(/\[/g, '');
                this_id = this_id.replace(/\]/g, '');
                this_id = this_id.replace('REX_INPUT_VALUE', '');

                nameInput.attr('id', 'REX_LINK_' + this_id);
                nameInput.prev().attr('id', 'REX_LINK_' + this_id + '_NAME');

                element.attr('data-id', this_id);
                element.find('a.btn-popup').each(function () {
                    if ($(this).attr('id')) {
                        $(this).attr('id', $(this).attr('id').replace(/\d+$/g, this_id));
                    }
                });

            }
        });
    }
}

function mblock_modify_system_buttons(element) {
    // modify elements
    // input rex button
    if (element.prop("nodeName") === 'INPUT' && element.attr('id') && (
        element.attr('id').indexOf("REX_LINK_") >= 0 || element.attr('id').indexOf("REX_MEDIA_") >= 0
    )) {
        let element_type = (element.attr('id').indexOf("REX_LINK_") >= 0) ? 'REX_LINK' : 'REX_MEDIA',
            element_attr_type = (element.attr('id').indexOf("REX_LINK_") >= 0) ? 'hidden' : 'text';

        if (element.attr('type') === element_attr_type && element.attr('name')) {
            if (DEBUG) console.log(element.attr('name'));

            let this_id = element.attr('name').replace(/\]\[/g, '_'),
                prevInput = element.prev(),
                old_id = element.attr('id');

            this_id = this_id.replace(/\[/g, '_');
            this_id = this_id.replace(/\]/g, '');
            this_id = this_id.replace('REX_INPUT_VALUE', element_type);

            element.attr('id', this_id);
            if (DEBUG) console.log(element.attr('id'));

            prevInput.attr('id', this_id + '_NAME');
            if (DEBUG) console.log(prevInput.attr('id'));

            replace_btn_popup_id(element, element_type, old_id, this_id);
        }
    }
}

function replace_btn_popup_id(element, element_type, old_id, this_id) {
    old_id = old_id.replace(element_type + '_', '');
    this_id = this_id.replace(element_type + '_', '');

    if (DEBUG) console.log(element.parent().find('a.btn-popup').length + ' old_id:' + old_id + ' id:' + this_id);

    // button
    element.parent().find('a.btn-popup').each(function () {
        if ($(this).attr('onclick')) {
            if (DEBUG) console.log($(this).attr('onclick'));
            $(this).attr('onclick', $(this).attr('onclick').replace(old_id, this_id));
            if (DEBUG) console.log($(this).attr('onclick'));
        }
    });
}

function mblock_replace_for(element) {

    element.find('> div.sortitem').each(function () {
        let mblock = $(this);
        mblock.find('input:not(:checkbox):not(:radio),textarea,select').each(function () {
            let el = $(this),
                id = el.attr('id'),
                name = el.attr('name');
            if ((typeof id !== typeof undefined && id !== false) && (typeof name !== typeof undefined && name !== false)) {
                if (!(id.indexOf("REX_MEDIA") >= 0 ||
                    id.indexOf("REX_LINK") >= 0 ||
                    id.indexOf("redactor") >= 0 ||
                    id.indexOf("markitup") >= 0)
                ) {
                    let label = el.parent().parent().find('label[for="' + id + '"]');
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
    let iClone = $($.parseHTML(element.data('mblock-plain-sortitem')));

    // fix for checkbox and radio bug
    iClone.find('input:radio, input:checkbox').each(function () {
        $(this).parent().removeAttr('for');
    });

    if (item === false) {
        element.prepend(iClone); // add clone

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
    // add buttons
    mblock_buttons(element, iClone);
    // trigger rex ready
    iClone.trigger('rex:ready', [iClone]);
}

function mblock_copy_item(element, item) {
    if (item.parent().hasClass(element.attr('class'))) {
        // unset sortable
        element.mblock_sortable("destroy");

        let iClone = item.clone();

        // set select values for clone
        item.find('select').each(function(i) {
            iClone.find('select').eq(i).val($(this).val())
        });
        
        // fix for checkbox and radio bug
        iClone.find('input:radio, input:checkbox').each(function(){
            $(this).parent().removeAttr('for'); // ugly!
        });

        // fix lost checked from parent item
        // $(this).attr('name', 'mblock_new_' + $(this).attr('name')); // wtf!?
        // fix lost value
        checkedFind(iClone);

        // add clone
        item.after(iClone);

        // add unique id
        mblock_set_unique_id(iClone, true);
        // reinit
        mblock_init_sort(element);
        // scroll to item
        mblock_scroll(element, iClone);
        // add buttons
        mblock_buttons(element, iClone);
        // trigger rex ready
        iClone.trigger('rex:ready', [iClone]);
    }
}





function mblock_set_unique_id(item, input_delete) {
    item.find('input').each(function () {
        let unique_id = Math.random().toString(16).slice(2),
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
    let countItem = item.next().find('span.mb_count'),
        count = element.find('> div.sortitem').length;

    if (element.data('latest')) {
        count = element.data('latest') + 1;
    }

    countItem.text(count);
    element.data('latest', count);
}

function mblock_remove_item(element, item) {
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
    let prev = item.prev();
    if (prev.length == 0) return;

    setTimeout(function () {
        // fix checked bug
        checkedFind(prev);

        item.insertBefore(prev);
        // set last user action
        mblock_reindex(element);
        mblock_set_addAndRemoveBtn_visibility(element);

        // set checked
        resetChecked(prev);

        // trigger event
        let iClone = prev;
        iClone.trigger('rex:change', [iClone]);
    }, 150);
}

function mblock_movedown(element, item) {
    let next = item.next();
    if (next.length == 0) return;

    setTimeout(function () {
        // fix checked bug
        checkedFind(next);

        item.insertAfter(next);

        // set last user action
        mblock_reindex(element);
        mblock_set_addAndRemoveBtn_visibility(element);

        // set checked
        resetChecked(next);

        // trigger event
        let iClone = next;
        iClone.trigger('rex:change', [iClone]);
    }, 150);
}

function checkedFind(element) {
    element.find('input:radio, input:checkbox').each(function(){
        // fix lost checked from parent item
        // fix lost value
        $(this).attr('data-value', $(this).val());
        if($(this).is(':checked')) {
            $(this).attr('data-checked', 1);
        }
    });
}

function resetChecked(element) {
    element.find('input:radio, input:checkbox').each(function(){
        if($(this).attr('data-checked') == 1) {
            $(this).prop('checked', true);
            $(this).attr('checked', 'checked');
        }
    });
}

function mblock_scroll(element, item) {
    if (element.data('smooth-scroll') === 1 || element.data('smooth-scroll-prev') === 1) {
        $.mblockSmoothScroll({
            scrollTarget: item,
            speed: 500
        });
    }
}

function mblock_add(element) {
    element.find('> div.sortitem').each(function () {
        mblock_buttons(element, $(this));
    });
}

function mblock_buttons(element, sortitem) {
    sortitem.find('> .removeadded > .copyme').on('click', function () {
        if (!$(this).prop('disabled')) {
            let sortitem = $(this).parent().parent();
            element.attr('data-mblock_clicked_copy_item', sortitem.attr('data-mblock_index'));
            console.log('copy');
            console.log(sortitem);
            console.log(element);
            mblock_copy_item(element, sortitem);
        }
        return false;
    });
    sortitem.find('> .removeadded > .addme').on('click', function () {
        if (!$(this).prop('disabled')) {
            let sortitem = $(this).parent().parent();
            element.attr('data-mblock_clicked_add_item', sortitem.attr('data-mblock_index'));
            mblock_add_item(element, sortitem);
        }
        return false;
    });
    sortitem.find('> .removeadded > .removeme').on('click', function () {
        if (!$(this).prop('disabled')) {
            let sortitem = $(this).parent().parent();
            mblock_remove_item(element, sortitem);
        }
        return false;
    });
    sortitem.find('> .removeadded > .moveup').on('click', function () {
        if (!$(this).prop('disabled')) {
            let sortitem = $(this).parent().parent();
            mblock_moveup(element, sortitem);
        }
        return false;
    });
    sortitem.find('> .removeadded > .movedown').on('click', function () {
        if (!$(this).prop('disabled')) {
            let sortitem = $(this).parent().parent();
            mblock_movedown(element, sortitem);
        }
        return false;
    });
    sortitem.find('> .removeadded > .visibility').on('click', function () {
        let sortitem = $(this).parent().parent();
        if (!sortitem.hasClass('visibility-hidden')) {
            sortitem.addClass('visibility-hidden');
            $(this).find('i').addClass('rex-icon-invisible').removeClass('rex-icon-visible');
        } else {
            sortitem.removeClass('visibility-hidden');
            $(this).find('i').addClass('rex-icon-visible').removeClass('rex-icon-invisible');
        }
        return false;
    });
}
