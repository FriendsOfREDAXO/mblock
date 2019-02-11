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
    // add linking
    mblock_add(element);
    // remove mblock_remove
    mblock_remove(element);
    // init sortable
    mblock_sort_it(element);
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
    let finded = element.find('> div.sortitem');

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
            // trigger event
            let iClone = $(event.item);
            iClone.trigger('rex:change', [iClone]);
        }
    });
}

function mblock_reindex(element) {

    let parent_mblocks = element.parents(mblock);

    element.find('> div.sortitem').each(function (index) {
        let sortitem = $(this),
            parent_sortitem = sortitem.parents('div.sortitem').first();

        sortitem.attr('data-default-count', index)
            .find('input,textarea,select,button:not(.addme,.removeme,.moveup,.movedown)')
            .each(function () {

            let name = $(this).data('name-value'),
                this_name = $(this).attr('name'),
                res_third = this_name.match(/(\[\w+\])(\[\d\])(\[\w+\])(\[\d\])(\[\w+\])(\[\d\])/),
                res_second = this_name.match(/(\[\w+\])(\[\d\])(\[\w+\])(\[\d\])/),
                res_default = this_name.match(/(\[\w+\])(\[\d\])/);

            if (res_third !== null && parent_mblocks.length === 2) {

                let $defaultValueName = res_third[1],
                    $defaultCount = parent_sortitem.parents('div.sortitem').first().attr('data-default-count'), // res_third[2],
                    $secondValueName = res_third[3],
                    $secondCount = parent_sortitem.attr('data-default-count'), // res_third[4],
                    $thirdValueName = res_third[5],
                    $thirdCount = index,
                    $replace = name + $defaultValueName + '[' + $defaultCount + ']' + $secondValueName + '[' + $secondCount + ']' + $thirdValueName + '[' + $thirdCount + ']';

                $(this).attr('name', this_name.replace(name + res_third[0], $replace));

            } else if (res_second !== null && parent_mblocks.length === 1) {

                let $defaultValueName = res_second[1],
                    $defaultCount = parent_sortitem.first().attr('data-default-count'), // res_second[2],
                    $secondValueName = res_second[3],
                    $secondCount = index,
                    $replace = name + $defaultValueName + '[' + $defaultCount + ']' + $secondValueName + '[' + $secondCount + ']';

                $(this).attr('name', this_name.replace(name + res_second[0], $replace));

                sortitem.find(mblock).each(function () {
                    mblock_reindex($(this));
                });

            } else if (res_default !== null) {

                let $defaultValueName = res_default[1],
                    $defaultCount = index,
                    $replace = name + $defaultValueName + '[' + $defaultCount + ']';

                $(this).attr('name', this_name.replace(name + res_default[0], $replace));

                sortitem.find(mblock).each(function () {
                    mblock_reindex($(this));
                });
            }

        });
    });

    mblock_replace_for(element);
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
                    let label = mblock.find('label[for="' + id + '"]');
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
        item.insertBefore(prev);
        // set last user action
        mblock_reindex(element);
        mblock_remove(element);
        // trigger event
        let iClone = prev;
        iClone.trigger('rex:change', [iClone]);
    }, 150);
}

function mblock_movedown(element, item) {
    let next = item.next();
    if (next.length == 0) return;

    setTimeout(function () {
        item.insertAfter(next);
        // set last user action
        mblock_reindex(element);
        mblock_remove(element);
        // trigger event
        let iClone = next;
        iClone.trigger('rex:change', [iClone]);
    }, 150);
}

function mblock_scroll(element, item) {
    if (element.data().hasOwnProperty('smooth-scroll')) {
        if (element.data('smooth-scroll') === true) {
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
