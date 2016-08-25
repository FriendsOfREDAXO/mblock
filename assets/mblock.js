/**
 * Created by joachimdoerr on 30.07.16.
 */
$(function () {
    initmblock();
    $(document).on('pjax:end', function() {
        initmblock();
    });
});

function initmblock() {
    var mblock = $('.mblock_wrapper');
    // init by siteload
    if ($('#REX_FORM').length && mblock.length) {
        mblock.each(function(){
            // alert('test1');
            initmblocksort($(this));
        });
    }
}

// List with handle
function initsort(element) {
    // reindex
    reindexit(element);
    // init
    initmblocksort(element);
}

function initmblocksort(element) {
    // add linking
    addlinking(element);
    // remove removeme
    removeme(element);
    // init sortable
    sortit(element);
}

function removeme(element) {
    var finded = element.find('> div');

    if (finded.length == 1) {
        finded.find('.removeme').prop('disabled', true);
    } else {
        finded.find('.removeme').prop('disabled', false);
    }

    // has data?
    if(element.data().hasOwnProperty('max')) {
        if (finded.length >= element.data('max')) {
            element.find('.addme').prop('disabled', true);
        } else {
            element.find('.addme').prop('disabled', false);
        }
    }

    if(element.data().hasOwnProperty('min')) {
        if (finded.length <= element.data('min')) {
            element.find('.removeme').prop('disabled', true);
        } else {
            element.find('.removeme').prop('disabled', false);
        }
    }

    finded.each(function(index){
        // min removeme hide
        if ((index+1)==element.data('min') && finded.length == element.data('min')) {
            $(this).find('.removeme').prop('disabled', true);
        }
        if (index==0) {
            $(this).find('.moveup').prop('disabled', true);
        } else {
            $(this).find('.moveup').prop('disabled', false);
        }
        if ((index + 1)== finded.length) { // if max count?
            $(this).find('.movedown').prop('disabled', true);
        } else {
            $(this).find('.movedown').prop('disabled', false);
        }
    });
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
    // remove removeme
    removeme(element);

    var initredactor = false;

    element.find('> div').each(function(index) {
        // find input elements
        $(this).find('input,textarea,select').each(function() {
            var attr = $(this).attr('name');
            // For some browsers, `attr` is undefined; for others,
            // `attr` is false. Check for both.
            if (typeof attr !== typeof undefined && attr !== false) {
                var value = $(this).attr('name').replace($(this).attr('name').match(/\]\[\d+\]\[/g), '][' + index + '][');
                $(this).attr('name', value);
            }

            if ($(this).attr('id')) {
                replacefor(element, $(this), index);
            }

            // select rex button
            if ($(this).prop("nodeName") == 'SELECT' && (
                    $(this).attr('id').indexOf("REX_MEDIALIST_SELECT_") >= 0 ||
                    $(this).attr('id').indexOf("REX_LINKLIST_SELECT_") >= 0
                )) {
                $(this).attr('id', $(this).attr('id').replace(/\d+/, '100' + index));
                if ($(this).attr('name') != undefined) {
                    $(this).attr('name', $(this).attr('name').replace(/\d+/, '100' + index));
                }
            }

            // input rex button
            if ($(this).prop("nodeName") == 'INPUT' && (
                    $(this).attr('id').indexOf("REX_LINK_") >= 0 ||
                    $(this).attr('id').indexOf("REX_LINKLIST_") >= 0 ||
                    $(this).attr('id').indexOf("REX_MEDIA_") >= 0 ||
                    $(this).attr('id').indexOf("REX_MEDIALIST_") >= 0
                )) {
                $(this).attr('id', $(this).attr('id').replace(/\d+/, '100' + index));
                // button
                $(this).parent().find('a.btn-popup').each(function(){
                    $(this).attr('onclick', $(this).attr('onclick').replace(/\(\d+/, '(100' + index));
                    $(this).attr('onclick', $(this).attr('onclick').replace(/_\d+/, '_100' + index));
                });
            }
        });

        $(this).find('.redactor-box').each(function(){
            initredactor = true;
            $(this).find('textarea').each(function(){
                if($(this).attr('id')) {
                    $(this).attr('id', $(this).attr('id').replace(/\d+/, '100' + index));
                }
            });
        });

    });

    if (initredactor) {

        $('.redactor-box').each(function(){
            var area;
            $(this).find('textarea').each(function(){
                if($(this).attr('id')) {
                    area = $(this).clone().css('display','block');
                }
            });
            if (area.length) {
                initredactor = true;
                $(this).parent().append(area);
                $(this).remove();
            }
        });

        if(typeof redactorInit === 'function') redactorInit();
    }
}

function replacefor(element, item, index) {
    if (item.attr('id').indexOf("REX_MEDIA") >= 0 ||
        item.attr('id').indexOf("REX_LINK") >= 0 ||
        item.attr('id').indexOf("redactor") >= 0
    ) { } else {
        item.attr('id', item.attr('id').replace(/_\d_+/, '_' + index + '_'));
        if (item.parent().find('label').length) {
            label = item.parent().find('label');
        }
        if (item.parent().parent().find('label').length) {
            label = item.parent().parent().find('label');
        }
        if (label.length) {
            label.attr('for', label.attr('for').replace(/_\d_+/, '_' + index + '_'));
        }
    }
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

        if(element.data().hasOwnProperty('input_delete')) {
            if (element.data('input_delete') == true) {
                item.next().find('input, textarea').val('');
                item.next().find('option:selected').removeAttr("selected");
                item.next().find('input:checked').removeAttr("checked");
                item.next().find('select').each(function () {
                    if ($(this).attr('id').indexOf("REX_MEDIALIST") >= 0
                        || $(this).attr('id').indexOf("REX_LINKLIST") >= 0
                    ) {
                        $(this).find('option').remove();
                    }
                });
            }
        }

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

function moveup(element, item) {
    var prev = item.prev();
    if (prev.length == 0) return;
    prev.css('z-index', 99).addClass('mblock_animate').css({ 'position': 'relative', 'top': item.outerHeight(true) });
    item.css('z-index', 100).addClass('mblock_animate').css({ 'position': 'relative', 'top': - prev.outerHeight(true) });

    setTimeout(function(){
        prev.removeClass('mblock_animate').css({ 'z-index': '', 'top': '', 'position': '' });
        item.removeClass('mblock_animate').css({ 'z-index': '', 'top': '', 'position': '' });
        item.insertBefore(prev);
        reindexit(element);
    },150);
}

function movedown(element, item) {
    var next = item.next();
    if (next.length == 0) return;

    next.css('z-index', 99).addClass('mblock_animate').css({ 'position': 'relative', 'top': - item.outerHeight(true) });
    item.css('z-index', 100).addClass('mblock_animate').css({ 'position': 'relative', 'top': next.outerHeight(true) });

    setTimeout(function(){
        next.removeClass('mblock_animate').css({ 'z-index': '', 'top': '', 'position': '' });
        item.removeClass('mblock_animate').css({ 'z-index': '', 'top': '', 'position': '' });
        item.insertAfter(next);
        reindexit(element);
    },150);
}

function addlinking(element) {
    element.find('> div .addme').unbind().bind('click', function() {
        if (!$(this).prop('disabled')) {
            additem(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
    element.find('> div .removeme').unbind().bind('click', function() {
        if (!$(this).prop('disabled')) {
            removeitem(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
    element.find('> div .moveup').unbind().bind('click', function() {
        if (!$(this).prop('disabled')) {
            moveup(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
    element.find('> div .movedown').unbind().bind('click', function() {
        if (!$(this).prop('disabled')) {
            movedown(element, $(this).closest('div[class^="sortitem"]'));
        }
        return false;
    });
}