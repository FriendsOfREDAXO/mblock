/**
 * MBlock Widgets - Widget-specific code and reinitialization
 * 
 * Contains:
 * - REDAXO Media and Link widget handling
 * - Selectpicker and form widget utilities
 * - Widget reinitialization for new blocks
 * - REX field processing and validation
 * 
 * @author joachim doerr
 * @version 2.0  
 */

// Widget-specific utilities and handlers
const MBlockWidgets = {
    
    // REX Media widget utilities
    media: {
        /**
         * Capture REX_MEDIA widget data for clipboard
         * @param {jQuery} container - Container with media widgets
         * @param {Object} formData - Form data object to populate
         */
        captureData(container, formData) {
            // REX_MEDIA widgets
            container.find('input[id^="REX_MEDIA_"]').each(function() {
                const $hiddenInput = $(this);
                const hiddenId = $hiddenInput.attr('id');
                const name = $hiddenInput.attr('name');
                
                if (hiddenId && !hiddenId.includes('_NAME') && $hiddenInput.attr('type') === 'hidden') {
                    const mediaFileName = $hiddenInput.val();
                    const displayId = hiddenId + '_NAME';
                    const $displayInput = $('#' + displayId);
                    
                    formData[name] = {
                        type: 'rex_media',
                        value: mediaFileName,
                        hiddenId: hiddenId,
                        displayId: displayId,
                        displayValue: $displayInput.length ? $displayInput.val() : '',
                        buttonOnclicks: {}
                    };
                    
                    // Capture media widget button onclick attributes
                    const $mediaContainer = $hiddenInput.closest('.input-group, .rex-js-widget-media');
                    if ($mediaContainer.length) {
                        $mediaContainer.find('.btn-popup').each(function(index) {
                            const $btn = $(this);
                            const onclick = $btn.attr('onclick');
                            if (onclick) {
                                formData[name].buttonOnclicks['btn_' + index] = onclick;
                            }
                        });
                    }
                }
            });
        },

        /**
         * Restore REX_MEDIA widget data after paste
         * @param {jQuery} pastedItem - Pasted item container
         * @param {Object} fieldData - Field data to restore
         * @param {jQuery} $field - Field element
         */
        restoreData(pastedItem, fieldData, $field) {
            if (fieldData.value !== undefined) {
                $field.val(fieldData.value);
                
                // Find and restore display field
                const $displayField = pastedItem.find('#' + fieldData.displayId);
                if (!$displayField.length) {
                    // Fallback: find by pattern matching if ID changed
                    const fieldId = $field.attr('id');
                    if (fieldId) {
                        const $displayFieldFallback = pastedItem.find('#' + fieldId + '_NAME');
                        if ($displayFieldFallback.length && fieldData.displayValue) {
                            $displayFieldFallback.val(fieldData.displayValue);
                        }
                    }
                } else {
                    if (fieldData.displayValue) {
                        $displayField.val(fieldData.displayValue);
                    }
                }
                
                // Restore button onclick handlers
                if (fieldData.buttonOnclicks) {
                    const $mediaContainer = $field.closest('.input-group, .rex-js-widget-media');
                    if ($mediaContainer.length) {
                        $mediaContainer.find('.btn-popup').each(function(index) {
                            const $btn = $(this);
                            const onclickKey = 'btn_' + index;
                            if (fieldData.buttonOnclicks[onclickKey]) {
                                let onclick = fieldData.buttonOnclicks[onclickKey];
                                
                                // Update REX_MEDIA IDs in onclick handlers
                                const newFieldId = $field.attr('id');
                                if (newFieldId && fieldData.hiddenId !== newFieldId) {
                                    onclick = onclick.replace(new RegExp(fieldData.hiddenId, 'g'), newFieldId);
                                    
                                    // Also update numeric part for deleteREXMedia calls
                                    const oldNumericId = fieldData.hiddenId.replace('REX_MEDIA_', '');
                                    const newNumericId = newFieldId.replace('REX_MEDIA_', '');
                                    onclick = onclick.replace(new RegExp("'" + oldNumericId + "'", 'g'), "'" + newNumericId + "'");
                                }
                                
                                $btn.attr('onclick', onclick);
                            }
                        });
                    }
                }
            }
        },

        /**
         * Reinitialize REX_MEDIA widgets in container
         * @param {jQuery} container - Container with media widgets
         * @param {boolean} isGridBlock - Whether this is a GridBlock context
         */
        reinitialize(container, isGridBlock = false) {
            container.find('input[id^="REX_MEDIA_"]').each(function() {
                const $input = $(this);
                const inputId = $input.attr('id');
                const inputName = $input.attr('name');
                
                if (inputId) {
                    console.log('MBlock: Reinitialisiere REX_MEDIA Widget:', inputId, 'Name:', inputName);
                    
                    // Find media widget container - try multiple selectors
                    let $widget = $input.closest('.rex-js-widget-media');
                    if (!$widget.length) {
                        $widget = $input.closest('.form-group, .col-sm-10, .input-group');
                    }
                    
                    if ($widget.length) {
                        // Find all media buttons
                        const $mediaButtons = $widget.find('.btn-popup, a[onclick*="REXMedia"], a[onclick*="openREXMedia"]');
                        
                        console.log('MBlock: Gefundene Media-Buttons:', $mediaButtons.length);
                        
                        $mediaButtons.each(function() {
                            const $btn = $(this);
                            let onclick = $btn.attr('onclick');
                            
                            if (onclick) {
                                console.log('MBlock: Original onclick:', onclick);
                                
                                // Extract the media ID from the input ID (REX_MEDIA_123456 -> 123456)
                                const mediaIdMatch = inputId.match(/REX_MEDIA_(\d+)/);
                                if (mediaIdMatch) {
                                    const mediaId = mediaIdMatch[1];
                                    let newOnclick = onclick;
                                    
                                    // Update different types of media function calls
                                    if (onclick.includes('openREXMedia')) {
                                        newOnclick = onclick.replace(/openREXMedia\([^,)]+/, `openREXMedia('${mediaId}'`);
                                    } else if (onclick.includes('viewREXMedia')) {
                                        newOnclick = onclick.replace(/viewREXMedia\([^,)]+/, `viewREXMedia('${mediaId}'`);
                                    } else if (onclick.includes('deleteREXMedia')) {
                                        newOnclick = onclick.replace(/deleteREXMedia\([^,)]+/, `deleteREXMedia('${mediaId}'`);
                                    } else if (onclick.includes('addREXMedia')) {
                                        newOnclick = onclick.replace(/addREXMedia\([^,)]+/, `addREXMedia('${mediaId}'`);
                                    }
                                    // GridBlock-specific patterns
                                    else if (onclick.includes('openMedia')) {
                                        newOnclick = onclick.replace(/openMedia\([^,)]+/, `openMedia('${mediaId}'`);
                                    } else if (onclick.includes('deleteMedia')) {
                                        newOnclick = onclick.replace(/deleteMedia\([^,)]+/, `deleteMedia('${mediaId}'`);
                                    }
                                    
                                    if (newOnclick !== onclick) {
                                        $btn.attr('onclick', newOnclick);
                                        console.log('MBlock: Aktualisiert onclick:', newOnclick);
                                    }
                                }
                            }
                        });
                        
                        // GridBlock-specific: Update data attributes if present
                        if (isGridBlock) {
                            const $preview = $widget.find('.rex-media-preview, [data-media-id]');
                            if ($preview.length) {
                                const mediaValue = $input.val();
                                if (mediaValue) {
                                    $preview.attr('data-media-id', mediaValue);
                                    console.log('MBlock: GridBlock Media-Preview aktualisiert:', mediaValue);
                                }
                            }
                        }
                    } else {
                        console.warn('MBlock: Kein Media-Widget-Container gefunden für:', inputId);
                    }
                }
            });
        }
    },

    // REX Link widget utilities
    link: {
        /**
         * Capture REX_LINK widget data for clipboard
         * @param {jQuery} container - Container with link widgets
         * @param {Object} formData - Form data object to populate
         */
        captureData(container, formData) {
            // REX_LINK widgets (comprehensive handling)
            container.find('input[id^="REX_LINK_"]').each(function() {
                const $hiddenInput = $(this);
                const hiddenId = $hiddenInput.attr('id');
                const name = $hiddenInput.attr('name');
                
                // Only process hidden inputs (not the _NAME display inputs)
                if (hiddenId && !hiddenId.includes('_NAME') && $hiddenInput.attr('type') === 'hidden') {
                    const articleId = $hiddenInput.val();
                    const displayId = hiddenId + '_NAME';
                    const $displayInput = $('#' + displayId);
                    
                    formData[name] = {
                        type: 'rex_link',
                        value: articleId,
                        hiddenId: hiddenId,
                        displayId: displayId,
                        displayValue: $displayInput.length ? $displayInput.val() : '',
                        // Store onclick attributes from buttons for later restoration
                        buttonOnclicks: {}
                    };
                    
                    // Capture button onclick attributes
                    const $linkContainer = $hiddenInput.closest('.input-group');
                    if ($linkContainer.length) {
                        $linkContainer.find('.btn-popup').each(function(index) {
                            const $btn = $(this);
                            const onclick = $btn.attr('onclick');
                            if (onclick) {
                                formData[name].buttonOnclicks['btn_' + index] = onclick;
                            }
                        });
                    }
                }
            });
        },

        /**
         * Restore REX_LINK widget data after paste
         * @param {jQuery} pastedItem - Pasted item container
         * @param {Object} fieldData - Field data to restore
         * @param {jQuery} $field - Field element
         */
        restoreData(pastedItem, fieldData, $field) {
            if (fieldData.value !== undefined) {
                $field.val(fieldData.value);
                
                // Find and restore display field
                const $displayField = pastedItem.find('#' + fieldData.displayId);
                if (!$displayField.length) {
                    // Fallback: find by pattern matching if ID changed
                    const fieldId = $field.attr('id');
                    if (fieldId) {
                        const $displayFieldFallback = pastedItem.find('#' + fieldId + '_NAME');
                        if ($displayFieldFallback.length) {
                            if (fieldData.displayValue) {
                                $displayFieldFallback.val(fieldData.displayValue);
                            } else if (fieldData.value) {
                                // Auto-fetch article name if display value is missing
                                MBlockWidgets.link.fetchArticleName(fieldData.value, $displayFieldFallback);
                            }
                        }
                    }
                } else {
                    if (fieldData.displayValue) {
                        $displayField.val(fieldData.displayValue);
                    } else if (fieldData.value) {
                        // Auto-fetch article name if display value is missing
                        MBlockWidgets.link.fetchArticleName(fieldData.value, $displayField);
                    }
                }
                
                // Restore button onclick handlers
                if (fieldData.buttonOnclicks) {
                    const $linkContainer = $field.closest('.input-group');
                    if ($linkContainer.length) {
                        $linkContainer.find('.btn-popup').each(function(index) {
                            const $btn = $(this);
                            const onclickKey = 'btn_' + index;
                            if (fieldData.buttonOnclicks[onclickKey]) {
                                let onclick = fieldData.buttonOnclicks[onclickKey];
                                
                                // Update REX_LINK IDs in onclick handlers
                                const newFieldId = $field.attr('id');
                                if (newFieldId && fieldData.hiddenId !== newFieldId) {
                                    onclick = onclick.replace(new RegExp(fieldData.hiddenId, 'g'), newFieldId);
                                    
                                    // Also update numeric part for deleteREXLink calls
                                    const oldNumericId = fieldData.hiddenId.replace('REX_LINK_', '');
                                    const newNumericId = newFieldId.replace('REX_LINK_', '');
                                    onclick = onclick.replace(new RegExp("'" + oldNumericId + "'", 'g'), "'" + newNumericId + "'");
                                }
                                
                                $btn.attr('onclick', onclick);
                            }
                        });
                    }
                }
            }
        },

        /**
         * Reinitialize REX_LINK widgets in container
         * @param {jQuery} container - Container with link widgets
         * @param {boolean} isGridBlock - Whether this is a GridBlock context
         */
        reinitialize(container, isGridBlock = false) {
            container.find('input[id^="REX_LINK_"]').each(function() {
                const $input = $(this);
                const inputId = $input.attr('id');
                const inputName = $input.attr('name');
                
                // Only process hidden inputs (not the _NAME display inputs)
                if (inputId && !inputId.includes('_NAME') && $input.attr('type') === 'hidden') {
                    console.log('MBlock: Reinitialisiere REX_LINK Widget:', inputId, 'Name:', inputName);
                    
                    // Find link widget container
                    let $widget = $input.closest('.rex-js-widget-link, .form-group, .input-group');
                    if (!$widget.length) {
                        $widget = $input.parent();
                    }
                    
                    if ($widget.length) {
                        // Find all link buttons
                        const $linkButtons = $widget.find('.btn-popup, a[onclick*="REXLink"], a[onclick*="openLinkMap"]');
                        
                        console.log('MBlock: Gefundene Link-Buttons:', $linkButtons.length);
                        
                        $linkButtons.each(function() {
                            const $btn = $(this);
                            let onclick = $btn.attr('onclick');
                            
                            if (onclick) {
                                console.log('MBlock: Original Link onclick:', onclick);
                                
                                // Extract the link ID from the input ID (REX_LINK_123456 -> 123456)
                                const linkIdMatch = inputId.match(/REX_LINK_(\d+)/);
                                if (linkIdMatch) {
                                    const linkId = linkIdMatch[1];
                                    let newOnclick = onclick;
                                    
                                    // Update different types of link function calls
                                    if (onclick.includes('openLinkMap')) {
                                        newOnclick = onclick.replace(/openLinkMap\([^,)]+/, `openLinkMap('${inputId}'`);
                                    } else if (onclick.includes('deleteREXLink')) {
                                        newOnclick = onclick.replace(/deleteREXLink\([^,)]+/, `deleteREXLink('${linkId}'`);
                                    }
                                    // GridBlock-specific patterns
                                    else if (onclick.includes('openLink')) {
                                        newOnclick = onclick.replace(/openLink\([^,)]+/, `openLink('${inputId}'`);
                                    } else if (onclick.includes('deleteLink')) {
                                        newOnclick = onclick.replace(/deleteLink\([^,)]+/, `deleteLink('${linkId}'`);
                                    }
                                    
                                    if (newOnclick !== onclick) {
                                        $btn.attr('onclick', newOnclick);
                                        console.log('MBlock: Aktualisiert Link onclick:', newOnclick);
                                    }
                                }
                            }
                        });
                        
                        // Auto-populate display field if empty
                        const displayId = inputId + '_NAME';
                        const $displayField = container.find('#' + displayId);
                        const articleId = $input.val();
                        
                        if ($displayField.length && articleId && !$displayField.val()) {
                            console.log('MBlock: Auto-populate Link display field for:', displayId);
                            MBlockWidgets.link.fetchArticleName(articleId, $displayField);
                        }
                    }
                }
            });
        },

        /**
         * AJAX function to fetch article name for REX_LINK fields
         * @param {string} articleId - Article ID to fetch name for
         * @param {jQuery} $displayField - Display field to populate
         */
        fetchArticleName(articleId, $displayField) {
            if (!articleId || !$displayField || !$displayField.length) return;
            
            // Cache for already loaded article names
            if (!window.mblock_article_cache) {
                window.mblock_article_cache = {};
            }
            
            // Use from cache if available
            if (window.mblock_article_cache[articleId]) {
                $displayField.val(window.mblock_article_cache[articleId]);
                console.log('MBlock: Artikel-Name aus Cache:', window.mblock_article_cache[articleId], 'für ID:', articleId);
                return;
            }
            
            // AJAX request to REDAXO Structure Linkmap
            const currentClang = $('input[name="clang"]').val() || 1;
            const ajaxUrl = rex.backend + '?page=structure/linkmap&opener_input_field=temp&article_id=' + articleId + '&clang=' + currentClang;
            
            $.ajax({
                url: ajaxUrl,
                method: 'GET',
                timeout: 5000,
                success: function(response) {
                    // Extract article name from response
                    let articleName = '';
                    
                    // Try different patterns
                    const patterns = [
                        /<a[^>]+onclick="[^"]*selectLink[^"]*"[^>]*>([^<]+)</gi,
                        /<span[^>]*class="[^"]*article[^"]*"[^>]*>([^<]+)</gi,
                        /article_name['"]*:\s*['"]([^'"]+)['"]/gi,
                        /"name"\s*:\s*"([^"]+)"/gi
                    ];
                    
                    for (const pattern of patterns) {
                        const match = pattern.exec(response);
                        if (match && match[1] && match[1].trim()) {
                            articleName = match[1].trim();
                            break;
                        }
                    }
                    
                    // Fallback: use ID with article prefix
                    if (!articleName) {
                        articleName = 'Artikel [' + articleId + ']';
                    }
                    
                    // Store in cache and set display field
                    window.mblock_article_cache[articleId] = articleName;
                    $displayField.val(articleName);
                    $displayField.trigger('change');
                    
                    console.log('MBlock: Artikel-Name per AJAX geholt:', articleName, 'für ID:', articleId);
                },
                error: function() {
                    // Fallback on AJAX error
                    const fallbackName = 'Artikel [' + articleId + ']';
                    window.mblock_article_cache[articleId] = fallbackName;
                    $displayField.val(fallbackName);
                    
                    console.log('MBlock: Artikel-Name Fallback verwendet:', fallbackName, 'für ID:', articleId);
                }
            });
        }
    },

    // Selectpicker utilities
    selectpicker: {
        /**
         * Convert selectpicker elements back to plain select elements
         * @param {jQuery} container - Container with selectpicker elements
         */
        convertToPlain(container) {
            try {
                // Find all select elements that have selectpicker class or are inside bootstrap-select wrappers
                const $selectElements = container.find('select.selectpicker, .bootstrap-select select');
                
                $selectElements.each(function() {
                    const $select = $(this);
                    
                    // Store current value
                    const selectedValue = $select.val();
                    
                    // Create clean select element
                    const $cleanSelect = $select.clone();
                    
                    // Remove ALL selectpicker and bootstrap-select related classes and attributes
                    $cleanSelect.removeClass('selectpicker bs-select-hidden');
                    $cleanSelect.removeAttr('data-live-search data-live-search-placeholder tabindex aria-describedby');
                    $cleanSelect.removeData(); // Remove all data attributes
                    $cleanSelect.css('display', ''); // Reset any inline styles
                    
                    // Add marker class for later initialization
                    $cleanSelect.addClass('mblock-needs-selectpicker');
                    
                    // Restore selected value
                    $cleanSelect.val(selectedValue);
                    
                    // Find the outermost bootstrap-select wrapper(s) around this select
                    const $bootstrapWrappers = $select.parents('.bootstrap-select');
                    
                    if ($bootstrapWrappers.length > 0) {
                        // Replace the outermost wrapper with our clean select
                        const $outermostWrapper = $bootstrapWrappers.last();
                        $outermostWrapper.replaceWith($cleanSelect);
                    } else {
                        // If no wrapper, just replace the select itself
                        $select.replaceWith($cleanSelect);
                    }
                });
                
                // Clean up any remaining empty bootstrap-select containers
                container.find('.bootstrap-select').each(function() {
                    const $wrapper = $(this);
                    if (!$wrapper.find('select').length) {
                        $wrapper.remove();
                    }
                });
                
            } catch (error) {
                console.error('MBlock: Error converting selectpicker to plain select:', error);
            }
        },

        /**
         * Initialize selectpicker on elements
         * @param {jQuery} container - Container with elements to initialize
         */
        initialize(container) {
            if (typeof $.fn.selectpicker === 'function') {
                var selects = container.find('select.mblock-needs-selectpicker');
                if (selects.length) {
                    selects.removeClass('mblock-needs-selectpicker').addClass('selectpicker');
                    selects.selectpicker({ noneSelectedText: '—' });
                    selects.selectpicker('refresh');
                }
            }
        }
    },

    // General widget reinitialization
    /**
     * Critical function to reinitialize REDAXO Media and Link widgets in new blocks
     * This fixes the issue where media/link selection doesn't work in dynamically added blocks
     * @param {jQuery} container - Container with widgets to reinitialize
     */
    reinitializeAll(container) {
        try {
            if (!container || !container.length) {
                return false;
            }
            
            // Get context information
            const mblockIndex = parseInt(container.attr('data-mblock_index')) || 1;
            const mblockWrapper = container.closest('.mblock_wrapper');
            const mblockCount = mblockWrapper.find('.sortitem').length || 1;
            const isGridBlock = MBlockAddonFixes.gridblock.isGridBlock(container);
            
            console.log('MBlock: Widget-Reinitialisierung gestartet', {
                mblockIndex: mblockIndex,
                isGridBlock: isGridBlock,
                containerClass: container.attr('class')
            });
            
            // Reinitialize REX Media widgets
            this.media.reinitialize(container, isGridBlock);
            
            // Reinitialize REX Link widgets  
            this.link.reinitialize(container, isGridBlock);
            
            // REX LINKLIST widgets
            container.find('input[id^="REX_LINKLIST_"]').each(function() {
                const $input = $(this);
                const inputId = $input.attr('id');
                
                if (inputId) {
                    console.log('MBlock: Reinitialisiere REX_LINKLIST Widget:', inputId);
                    
                    let $widget = $input.closest('.rex-js-widget-linklist, .form-group');
                    if (!$widget.length) {
                        $widget = $input.parent();
                    }
                    
                    if ($widget.length) {
                        // Update linklist buttons
                        $widget.find('.btn-popup, a[onclick*="openLinklistMap"]').each(function() {
                            const $btn = $(this);
                            let onclick = $btn.attr('onclick');
                            
                            if (onclick && onclick.includes('openLinklistMap')) {
                                const newOnclick = onclick.replace(/openLinklistMap\([^,)]+/, `openLinklistMap('${inputId}'`);
                                if (newOnclick !== onclick) {
                                    $btn.attr('onclick', newOnclick);
                                    console.log('MBlock: Aktualisiert Linklist onclick:', newOnclick);
                                }
                            }
                        });
                    }
                }
            });
            
            // GridBlock-specific: Trigger rex:ready event for custom widgets
            if (isGridBlock) {
                console.log('MBlock: GridBlock erkannt - triggere rex:ready Event');
                
                // Trigger rex:ready specifically for GridBlock
                container.trigger('rex:ready', [container]);
                
                // Also trigger on individual form elements
                container.find('input, select, textarea').trigger('rex:ready');
                
                // GridBlock-specific media widget reinitialization
                if (typeof window.gridblock_reinit_widgets === 'function') {
                    window.gridblock_reinit_widgets(container);
                }
                
                // Try to reinitialize selectpicker in GridBlock context
                setTimeout(() => {
                    this.selectpicker.initialize(container);
                }, 100);
            }
            
            // General widget reinitialization
            setTimeout(() => {
                // Trigger rex:ready event for general REDAXO widget initialization
                container.trigger('rex:ready', [container]);
                
                // Also trigger change events to ensure proper widget state
                container.find('input, select, textarea').trigger('change');
                
                console.log('MBlock: Rex:ready events getriggert');
            }, 50);
            
            console.log('MBlock: REDAXO widgets erfolgreich reinitialisiert für', isGridBlock ? 'GridBlock' : 'Standard MBlock');
            
            return true;
            
        } catch (error) {
            console.error('MBlock: Fehler bei der Reinitialisierung der REDAXO Widgets:', error);
            return false;
        }
    }
};

// AUTO-INITIALIZATION: Fill empty REX_LINK display fields on page load
$(document).ready(function() {
    // Wait until REDAXO is fully loaded
    setTimeout(function() {
        MBlockWidgets.link.initializeEmptyFields();
    }, 500);
    
    // TAB-SUPPORT: Initialize REX_LINK fields when tabs are switched
    $(document).on('shown.bs.tab', function(e) {
        // Delay initialization as tab contents need time to become visible
        setTimeout(function() {
            console.log('MBlock: Bootstrap Tab gewechselt - initialisiere REX_LINK-Felder...');
            MBlockWidgets.link.initializeEmptyFields();
        }, 100);
    });
    
    // Alternative for different tab systems (Bootstrap 3/4/5 + MForm)
    $(document).on('click', '.nav-tabs a, .nav-pills a, [data-toggle="tab"], [data-bs-toggle="tab"], .mform-tabs a', function() {
        setTimeout(function() {
            console.log('MBlock: Tab-Click erkannt - initialisiere REX_LINK-Felder...');
            MBlockWidgets.link.initializeEmptyFields();
        }, 200);
    });
    
    // MForm-specific tab events
    $(document).on('mform:tabChanged mform:tabShow', function(e) {
        setTimeout(function() {
            console.log('MBlock: MForm Tab-Event - initialisiere REX_LINK-Felder...');
            MBlockWidgets.link.initializeEmptyFields();
        }, 150);
    });
});

// Extension to link utilities for initialization
MBlockWidgets.link.initializeEmptyFields = function() {
    try {
        console.log('MBlock: Initialisiere leere REX_LINK Display-Felder...');
        let foundFields = 0;
        let processedFields = 0;
        
        // Find all REX_LINK Hidden inputs with values (also in hidden tabs)
        $('input[id^="REX_LINK_"]').each(function() {
            const $hiddenInput = $(this);
            const hiddenId = $hiddenInput.attr('id');
            const articleId = $hiddenInput.val();
            foundFields++;
            
            console.log('MBlock: Prüfe REX_LINK Feld:', hiddenId, 'Wert:', articleId, 'Typ:', $hiddenInput.attr('type'));
            
            // Only process hidden inputs with values (not the _NAME fields)
            if (hiddenId && !hiddenId.includes('_NAME') && 
                $hiddenInput.attr('type') === 'hidden' && 
                articleId && articleId.trim() !== '') {
                
                // Find the corresponding display field
                const displayId = hiddenId + '_NAME';
                const $displayField = $('#' + displayId);
                
                console.log('MBlock: Suche Display-Feld:', displayId, 'gefunden:', $displayField.length, 'aktueller Wert:', $displayField.val());
                
                if ($displayField.length) {
                    const currentDisplayValue = $displayField.val() || '';
                    
                    // Only fill if display field is empty
                    if (currentDisplayValue.trim() === '') {
                        console.log('MBlock: Befülle leeres REX_LINK Display-Feld:', displayId, 'für Artikel:', articleId);
                        MBlockWidgets.link.fetchArticleName(articleId, $displayField);
                        processedFields++;
                    } else {
                        console.log('MBlock: Display-Feld bereits befüllt:', displayId, 'Wert:', currentDisplayValue);
                    }
                } else {
                    console.warn('MBlock: Display-Feld nicht gefunden:', displayId, '(möglicherweise in verstecktem Tab)');
                }
            }
        });
        
        console.log('MBlock: REX_LINK Initialisierung abgeschlossen. Gefunden:', foundFields, 'Verarbeitet:', processedFields);
        
    } catch (error) {
        console.error('MBlock: Fehler beim Initialisieren der REX_LINK Display-Felder:', error);
    }
};

// Create global reference for backward compatibility
window.mblock_reinitialize_redaxo_widgets = MBlockWidgets.reinitializeAll.bind(MBlockWidgets);
window.mblock_fetch_article_name = MBlockWidgets.link.fetchArticleName.bind(MBlockWidgets.link);

// Export for module systems (if used)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { MBlockWidgets };
}