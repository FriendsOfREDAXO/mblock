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
        // find input elements
        $(this).find('label,input,textarea,select').each(function() {
            var value = $(this).attr('name').replace($(this).attr('name').match(/\]\[\d+\]\[/g), '][' + index + '][');
            $(this).attr('name', value);
            $(this).val(value);
        });
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
    });
    element.find('> div .removeme').unbind().bind('click', function() {
        removeitem(element, $(this).closest('div[class^="sortitem"]'));
    });
}
