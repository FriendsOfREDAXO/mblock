/**
 * Created by joachimdoerr on 30.07.16.
 */

let mblock = '.mblock_wrapper';

$(document).on('rex:ready', function (e, container) {
    container.find(mblock).each(function () {
        mblock_init($(this));
    });
});

// MBlock v3.5 - Toggle functionality and modern controls
function mblock_init_v35_controls(element) {
    // Get translations (fallback to English if not available)
    const i18n = window.mblock_i18n || {
        toggle_active: 'Deactivate block',
        toggle_inactive: 'Activate block',
        move_up: 'Move block up',
        move_down: 'Move block down', 
        drag_handle: 'Move block by drag & drop',
        add: 'Add block',
        delete: 'Delete block'
    };
    
    // Add modern control buttons to each block that doesn't have them yet
    element.find('> div.sortitem').each(function(index) {
        const $block = $(this);
        const blockIndex = $block.data('mblock_index') || (index + 1);
        
        // Remove old controls first to avoid duplicates
        $block.find('.mblock-controls').remove();
        
        // MBlock v3.5 - Remove old buttons completely to avoid conflicts  
        mblock_clean_old_buttons($block);
        
        // Create modern control group with translated tooltips
        const controls = $(`
            <div class="mblock-controls">
                <button type="button" class="btn mblock-toggle-btn active" 
                        data-toggle="tooltip" title="${i18n.toggle_active}"
                        data-block-index="${blockIndex}">
                    <i class="rex-icon fa-eye"></i>
                </button>
                <button type="button" class="btn mblock-move-btn mblock-move-up " 
                        data-toggle="tooltip" title="${i18n.move_up}">
                    <i class="rex-icon fa-arrow-up"></i>
                </button>
                <button type="button" class="btn mblock-move-btn mblock-move-down " 
                        data-toggle="tooltip" title="${i18n.move_down}">
                    <i class="rex-icon fa-arrow-down"></i>
                </button>
                <button type="button" class="btn mblock-move-btn sorthandle" 
                        data-toggle="tooltip" title="${i18n.drag_handle}">
                    <i class="rex-icon fa-arrows"></i>
                </button>
                <button type="button" class="btn mblock-add-btn " 
                        data-toggle="tooltip" title="${i18n.add}">
                    <i class="rex-icon fa-plus"></i>
                </button>
                <button type="button" class="btn mblock-delete-btn " 
                        data-toggle="tooltip" title="${i18n.delete}">
                    <i class="rex-icon fa-trash"></i>
                </button>
            </div>
        `);
        
        $block.prepend(controls);
        
        // Initialize tooltips if available
        if (typeof $().tooltip === 'function') {
            controls.find('[data-toggle="tooltip"]').tooltip();
        }
    });
    
    // Bind toggle functionality
    mblock_bind_toggle_events(element);
    
    // Bind move button functionality (up/down arrows)
    mblock_bind_move_events(element);
    
    // Bind add and delete events
    mblock_bind_add_delete_events(element);
}

function mblock_bind_toggle_events(element) {
    // Get translations (fallback to English if not available)
    const i18n = window.mblock_i18n || {
        toggle_active: 'Deactivate block',
        toggle_inactive: 'Activate block'
    };
    
    // MBlock v3.5 - Use event delegation to avoid multiple handlers
    element.off('click.mblock-toggle').on('click.mblock-toggle', '.mblock-toggle-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $btn = $(this);
        const $block = $btn.closest('.sortitem');
        const isActive = $btn.hasClass('active');
        
        // Get current block index dynamically (more reliable)
        const blockIndex = $block.index() + 1; // 1-based index for display
        const arrayIndex = $block.index();     // 0-based index for arrays
        
        // Find existing hidden mblock_active field
        let $hiddenField = $block.find('input[name*="[mblock_active]"]');
        
        // Toggle state
        if (isActive) {
            // User wants to DEACTIVATE block - create field with value 0
            if ($hiddenField.length === 0) {
                // Try to find any input field to extract the pattern
                const $anyInput = $block.find('input, select, textarea').first();
                if ($anyInput.length > 0) {
                    const fieldName = $anyInput.attr('name');
                    if (fieldName) {
                        // Extract pattern like "REX_INPUT_VALUE[2][0]" from field name
                        const matches = fieldName.match(/^(REX_INPUT_VALUE\[\d+\]\[\d+\])/);
                        if (matches) {
                            const baseName = matches[1];
                            const newFieldName = baseName + '[mblock_active]';
                            $hiddenField = $('<input type="hidden" name="' + newFieldName + '" value="0" class="mblock-toggle-field">');
                            $block.append($hiddenField);
                            
                            if (window.console && window.rex_debug) {
                                console.log('MBlock v4.0 - Created INACTIVE toggle field:', newFieldName);
                            }
                        } else {
                            // Fallback: try to construct from block structure
                            const wrapper = $block.closest('.mblock_wrapper');
                            const wrapperIndex = wrapper.find('> div.sortitem').index($block);
                            const valueId = wrapper.attr('data-value-id') || '1';
                            const fallbackName = 'REX_INPUT_VALUE[' + valueId + '][' + wrapperIndex + '][mblock_active]';
                            $hiddenField = $('<input type="hidden" name="' + fallbackName + '" value="0" class="mblock-toggle-field">');
                            $block.append($hiddenField);
                            
                            if (window.console && window.rex_debug) {
                                console.log('MBlock v4.0 - Created INACTIVE toggle field (fallback):', fallbackName);
                            }
                        }
                    }
                }
            } else {
                // Field exists, set to inactive
                $hiddenField.val('0');
            }
            
            // Update button appearance
            $btn.removeClass('active').addClass('inactive');
            $btn.find('i').removeClass('fa-eye').addClass('fa-eye-slash');
            $block.addClass('mblock-inactive');
            $btn.attr('title', i18n.toggle_inactive);
            
        } else {
            // User wants to ACTIVATE block - remove field or set to 1
            if ($hiddenField.length > 0) {
                // Remove the field entirely (active is default)
                $hiddenField.remove();
                
                if (window.console && window.rex_debug) {
                    console.log('MBlock v3.5 - Removed toggle field (block now active)');
                }
            }
            
            // Update button appearance
            $btn.removeClass('inactive').addClass('active');
            $btn.find('i').removeClass('fa-eye-slash').addClass('fa-eye');
            $block.removeClass('mblock-inactive');
            $btn.attr('title', i18n.toggle_active);
        }
        
        // Update tooltip if available
        if (typeof $().tooltip === 'function') {
            $btn.tooltip('destroy').tooltip();
        }
        
        // Smooth animation
        $block.fadeOut(150).fadeIn(150);
        
        // Debug output if REDAXO debug mode is active
        if (window.console && window.rex_debug) {
            console.log('MBlock v3.5 - Toggle state changed:', {
                blockIndex: blockIndex,
                isActive: !isActive,
                hiddenFieldValue: $hiddenField.val(),
                element: element.attr('id') || 'unnamed'
            });
        }
        
        return false;
    });
}

function mblock_bind_move_events(element) {
    // MBlock v3.5 - Use event delegation for move buttons with separate namespaces
    element.off('click.mblock-move-up').on('click.mblock-move-up', '.mblock-move-up', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $btn = $(this);
        const $block = $btn.closest('.sortitem');
        
        console.log('MBlock v3.5 - Move Up clicked', { disabled: $btn.prop('disabled') });
        
        if (!$btn.prop('disabled')) {
            mblock_moveup(element, $block);
        }
        return false;
    });
    
    element.off('click.mblock-move-down').on('click.mblock-move-down', '.mblock-move-down', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $btn = $(this);
        const $block = $btn.closest('.sortitem');
        
        console.log('MBlock v3.5 - Move Down clicked', { disabled: $btn.prop('disabled') });
        
        if (!$btn.prop('disabled')) {
            mblock_movedown(element, $block);
        }
        return false;
    });
}

function mblock_bind_add_delete_events(element) {
    // MBlock v3.5 - Use event delegation for add/delete buttons
    element.off('click.mblock-add').on('click.mblock-add', '.mblock-add-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $btn = $(this);
        const $block = $btn.closest('.sortitem');
        
        if (!$btn.prop('disabled')) {
            element.attr('data-mblock_clicked_add_item', $block.attr('data-mblock_index'));
            mblock_add_item(element, $block);
        }
        return false;
    });
    
    element.off('click.mblock-delete').on('click.mblock-delete', '.mblock-delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $btn = $(this);
        const $block = $btn.closest('.sortitem');
        
        if (!$btn.prop('disabled')) {
            $block.remove();
            mblock_reindex(element);
            mblock_update_button_states(element);
            
            // Trigger change event
            element.trigger('mblock:change', [element]);
        }
        return false;
    });
}

// MBlock v3.5 - Update move button states based on position
function mblock_update_move_button_states(element) {
    const $blocks = element.find('> div.sortitem');
    
    if (window.console && window.rex_debug) {
        console.log('MBlock v3.5 - Updating move button states for', $blocks.length, 'blocks');
    }
    
    $blocks.each(function(index) {
        const $block = $(this);
        const $moveUpBtn = $block.find('.mblock-move-up');
        const $moveDownBtn = $block.find('.mblock-move-down');
        
        // Disable move up for first block
        if (index === 0) {
            $moveUpBtn.prop('disabled', true).addClass('disabled');
        } else {
            $moveUpBtn.prop('disabled', false).removeClass('disabled');
        }
        
        // Disable move down for last block
        if (index === $blocks.length - 1) {
            $moveDownBtn.prop('disabled', true).addClass('disabled');
        } else {
            $moveDownBtn.prop('disabled', false).removeClass('disabled');
        }
        
        if (window.console && window.rex_debug) {
            console.log('MBlock v3.5 - Block', (index + 1), ':', {
                upDisabled: $moveUpBtn.prop('disabled'),
                downDisabled: $moveDownBtn.prop('disabled'),
                isFirst: index === 0,
                isLast: index === $blocks.length - 1
            });
        }
    });
}

// MBlock v3.5 - Helper function to clean old buttons and ensure v3.5 compatibility
function mblock_clean_old_buttons(block) {
    // Remove all old-style buttons that are not part of v3.5 controls
    block.find('.btn-default, .btn-success, .btn-danger, .btn-info').not('.mblock-controls .btn').each(function() {
        const $btn = $(this);
        
        // Double-check this is actually an old mblock button
        if ($btn.text().includes('+') || $btn.text().includes('-') || 
            $btn.find('.fa-plus, .fa-minus, .fa-arrow-up, .fa-arrow-down, .fa-trash').length > 0) {
            console.log('MBlock v3.5 - Removing old button:', $btn.attr('class'), $btn.text());
            $btn.remove();
        }
    });
    
    // Remove any loose button containers that might be left
    block.find('.btn-group').each(function() {
        if ($(this).find('button').length === 0) {
            $(this).remove();
        }
    });
}

// Remove the old AJAX functions as they're no longer needed

function mblock_restore_toggle_states(element) {
    // Restore toggle states based on existing mblock_active fields
    element.find('> div.sortitem').each(function(index) {
        const $block = $(this);
        const blockIndex = $block.data('mblock_index') || index;
        const $toggleBtn = $block.find('.mblock-toggle-btn');
        const $hiddenField = $block.find('input[name*="[mblock_active]"]');
        
        // Check if block is inactive based on hidden field value
        const isActive = $hiddenField.length === 0 || $hiddenField.val() === '1';
        
        if (!isActive && $toggleBtn.length) {
            $toggleBtn.removeClass('active').addClass('inactive');
            $toggleBtn.find('i').removeClass('fa-eye').addClass('fa-eye-slash');
            $block.addClass('mblock-inactive');
            $toggleBtn.attr('title', 'Block aktivieren');
        }
    });
}

// Enhanced initialization function - simplified without AJAX calls
function mblock_init(element) {
    if (!element.data('mblock_run')) {
        element.data('mblock_run', 1);
        
        // Initialize v3.5 modern controls
        mblock_init_v35_controls(element);
        
        mblock_sort(element);
        mblock_set_unique_id(element, false);
        
        // Restore toggle states from form data
        mblock_restore_toggle_states(element);
        
        // Update move button states
        mblock_update_move_button_states(element);

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
    // MBlock v3.5 - Only initialize sortable, no event handlers  
    mblock_sort_it(element);
}

function mblock_sort(element) {
    // MBlock v3.5 - Simplified: only manage button states and sortable
    mblock_update_button_states(element);
    mblock_sort_it(element);
}

function mblock_add_plus(element) {
    if (!element.find('> div.sortitem').length) {
        element.prepend($($.parseHTML(element.data('mblock-single-add'))));
        
        // MBlock v3.5 - Use modern event handler (remove old .handler)
        element.find('> div.mblock-single-add .mblock-add-btn').off('.mblock-single').on('click.mblock-single', function () {
            mblock_add_item(element, false);
            $(this).parents('.mblock-single-add').remove();
        });
    }
}

function mblock_remove(element) {
    // MBlock v3.5 - Use new unified button state management
    mblock_update_button_states(element);
}

// MBlock v3.5 - Update all button states (add/delete limits, move states)
function mblock_update_button_states(element) {
    const $blocks = element.find('> div.sortitem');
    const minBlocks = element.data('min') || 0;
    const maxBlocks = element.data('max') || 999;
    
    // Update move button states
    mblock_update_move_button_states(element);
    
    // Update add/delete button states based on min/max limits
    $blocks.each(function() {
        const $block = $(this);
        const $addBtn = $block.find('.mblock-add-btn');
        const $deleteBtn = $block.find('.mblock-delete-btn');
        
        // Add button: disable if at max
        if ($blocks.length >= maxBlocks) {
            $addBtn.prop('disabled', true).addClass('disabled');
        } else {
            $addBtn.prop('disabled', false).removeClass('disabled');
        }
        
        // Delete button: disable if at min
        if ($blocks.length <= minBlocks) {
            $deleteBtn.prop('disabled', true).addClass('disabled');
        } else {
            $deleteBtn.prop('disabled', false).removeClass('disabled');
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

    // MBlock v3.5 - Remove any old button structures from server template
    mblock_clean_old_buttons(iClone);
    iClone.find('.mblock-controls').remove(); // Remove any existing controls to avoid duplicates

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
    
    // MBlock v3.5 - Add buttons to new block (this ensures clean v3.5 buttons)
    mblock_add_v35_buttons_to_new_block(element, iClone);
    
    // reinit sortable only (no old event handlers)
    mblock_init_sort(element);
    
    // MBlock v3.5 - Update button states
    mblock_update_button_states(element);
    
    // scroll to item
    mblock_scroll(element, iClone);
    // trigger rex ready
    iClone.trigger('rex:ready', [iClone]);
}

// MBlock v3.5 - Add buttons to newly created blocks
function mblock_add_v35_buttons_to_new_block(element, newBlock) {
    // Get translations (fallback to English if not available)
    const i18n = window.mblock_i18n || {
        toggle_active: 'Deactivate block',
        toggle_inactive: 'Activate block',
        move_up: 'Move block up',
        move_down: 'Move block down', 
        drag_handle: 'Move block by drag & drop',
        add: 'Add block',
        delete: 'Delete block'
    };
    
    // Get the block index for the new block
    const blockIndex = newBlock.data('mblock_index') || element.find('> div.sortitem').length;
    
    // Check if controls already exist (shouldn't, but safety check)
    if (newBlock.find('.mblock-controls').length === 0) {
        // Create modern control group with translated tooltips
        const controls = $(`
            <div class="mblock-controls">
                <button type="button" class="btn mblock-toggle-btn active" 
                        data-toggle="tooltip" title="${i18n.toggle_active}"
                        data-block-index="${blockIndex}">
                    <i class="rex-icon fa-eye"></i>
                </button>
                <button type="button" class="btn mblock-move-btn mblock-move-up " 
                        data-toggle="tooltip" title="${i18n.move_up}">
                    <i class="rex-icon fa-arrow-up"></i>
                </button>
                <button type="button" class="btn mblock-move-btn mblock-move-down " 
                        data-toggle="tooltip" title="${i18n.move_down}">
                    <i class="rex-icon fa-arrow-down"></i>
                </button>
                <button type="button" class="btn mblock-move-btn sorthandle" 
                        data-toggle="tooltip" title="${i18n.drag_handle}">
                    <i class="rex-icon fa-arrows"></i>
                </button>
                <button type="button" class="btn mblock-add-btn " 
                        data-toggle="tooltip" title="${i18n.add}">
                    <i class="rex-icon fa-plus"></i>
                </button>
                <button type="button" class="btn mblock-delete-btn " 
                        data-toggle="tooltip" title="${i18n.delete}">
                    <i class="rex-icon fa-trash"></i>
                </button>
            </div>
        `);
        
        newBlock.prepend(controls);
        
        // Initialize tooltips if available
        if (typeof $().tooltip === 'function') {
            controls.find('[data-toggle="tooltip"]').tooltip();
        }
    }
    
    // MBlock v3.5 - Events are already bound globally, no need to rebind
    // Just update button states for all blocks
    mblock_update_button_states(element);
    
    // Debug output
    if (window.console && window.rex_debug) {
        console.log('MBlock v3.5 - Added buttons to new block:', {
            blockIndex: blockIndex,
            element: element.attr('id') || 'unnamed'
        });
    }
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
        // Update move button states for v3.5
        mblock_update_move_button_states(element);
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
        // Update move button states for v3.5
        mblock_update_move_button_states(element);
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
    // MBlock v3.5 - Use only our new event handlers, no old ones
    // All events are now handled by the v3.5 button system
    
    // Ensure all blocks have v3.5 controls
    mblock_init_v35_controls(element);
    
    // Update button states
    mblock_update_move_button_states(element);
}

// MBlock v3.5 - Debug function for move buttons
function mblock_debug_move_buttons(element) {
    const $blocks = element.find('> div.sortitem');
    
    console.log('MBlock v3.5 - Debug Move Buttons:', {
        totalBlocks: $blocks.length,
        wrapperID: element.attr('id')
    });
    
    $blocks.each(function(index) {
        const $block = $(this);
        const $moveUp = $block.find('.mblock-move-up');
        const $moveDown = $block.find('.mblock-move-down');
        
        console.log('Block ' + (index + 1) + ':', {
            hasUpButton: $moveUp.length > 0,
            hasDownButton: $moveDown.length > 0,
            upDisabled: $moveUp.prop('disabled'),
            downDisabled: $moveDown.prop('disabled'),
            upClasses: $moveUp.attr('class'),
            downClasses: $moveDown.attr('class')
        });
    });
}

// MBlock v3.5 - Ensure functions are available globally for debugging
window.mblock_add_item = mblock_add_item;
window.mblock_moveup = mblock_moveup;
window.mblock_movedown = mblock_movedown;
window.mblock_init = mblock_init;
window.mblock_debug_move_buttons = mblock_debug_move_buttons;
