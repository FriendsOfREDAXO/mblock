/**
 * Created by joachimdoerr on 30.07.16.
 */
// add for use parsley in new rex5 backend mform forms
$(function () {
    initjBlock();
    $(document).on('pjax:end', function() {
        initjBlock();
    });
});

function initjBlock() {
    var rform = $('#REX_FORM'),
        jblock = $('.jblock_wrapper');
    // init by siteload
    if (rform.length && jblock.length) {
        initsort(jblock);
    }
}

// List with handle
function initsort(element) {
    // reindex
    reindexit(element);
    // add linking
    addlinking(element);
    // init sortable
    sortit(element);
}

function sortit(element) {
    element.sortable({
        handle: '.sorthandle',
        animation: 150,
        onEnd: function () {
            reindexit(element);
        }
    });
}

function reindexit(element) {
    element.find('> div').each(function(index) {

        if (index == 0) {
            $(this).find('.removeme').hide();
        } else {
            $(this).find('.removeme').show();
        }

        // find input elements
        $(this).find('input,textarea,select').each(function() {
            var attr = $(this).attr('name');
            // For some browsers, `attr` is undefined; for others,
            // `attr` is false. Check for both.
            if (typeof attr !== typeof undefined && attr !== false) {
                var value = $(this).attr('name').replace($(this).attr('name').match(/\]\[\d+\]\[/g), '][' + index + '][');
                $(this).attr('name', value);
            }

            replacefor(element, $(this), index);

            // select rex button
            if ($(this).prop("nodeName") == 'SELECT' && (
                    $(this).attr('id').indexOf("REX_MEDIALIST_SELECT_") >= 0 ||
                    $(this).attr('id').indexOf("REX_LINKLIST_SELECT_") >= 0
                )) {
                $(this).attr('id', $(this).attr('id').replace(/\d+/, index));
                if ($(this).attr('name').length) {
                    $(this).attr('name', $(this).attr('name').replace(/\d+/, index));
                }
            }

            // input rex button
            if ($(this).prop("nodeName") == 'INPUT' && (
                    $(this).attr('id').indexOf("REX_LINK_") >= 0 ||
                    $(this).attr('id').indexOf("REX_LINKLIST_") >= 0 ||
                    $(this).attr('id').indexOf("REX_MEDIA_") >= 0 ||
                    $(this).attr('id').indexOf("REX_MEDIALIST_") >= 0
                )) {
                $(this).attr('id', $(this).attr('id').replace(/\d+/, index));
                // button
                $(this).parent().find('a.btn-popup').each(function(){
                    $(this).attr('onclick', $(this).attr('onclick').replace(/\(\d+/, '(' + index));
                    $(this).attr('onclick', $(this).attr('onclick').replace(/_\d+/, '_' + index));
                });
            }
        });
    });
}

function replacefor(element, item, index) {
    element.find('label').each(function() {
        if ($(this).attr('for') == item.attr('id')) {
            var id = $(this).attr('for') + '_' + index;
            $(this).attr('for', id);
            item.attr('id', id);
        }
    });
    replacecheckboxfor(element);
}

function replacecheckboxfor(element) {
    element.find('input:checkbox').each(function() {
        $(this).parent().find('label').attr('for', $(this).attr('id'));
    });
}

function additem(element, item) {
    if (item.parent().hasClass(element.attr('class'))) {
        // unset sortable
        element.sortable("destory");
        // add element
        item.after(item.clone());
        // reinit
        initsort(element);
    }
}

function removeitem(element, item) {
    if (item.parent().hasClass(element.attr('class'))) {
        // unset sortable
        element.sortable("destory");
        // remove element
        item.remove();
        // reinit
        initsort(element);
    }
}

function addlinking(element) {
    element.find('> div .addme').unbind().bind('click', function() {
        additem(element, $(this).closest('div[class^="sortitem"]'));
        return false;
    });
    element.find('> div .removeme').unbind().bind('click', function() {
        removeitem(element, $(this).closest('div[class^="sortitem"]'));
        return false;
    });
}
