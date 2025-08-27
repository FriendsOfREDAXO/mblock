/**
 * MBlock Features - Advanced functionality
 * 
 * Contains:
 * - Copy/Paste functionality (MBlockClipboard)
 * - Online/Offline toggle (MBlockOnlineToggle)
 * - REDAXO widget reinitialization
 * - REX_LINK field handling and AJAX functions
 * 
 * Depends on: mblock-core.js, mblock-management.js
 * 
 * @author joachim doerr
 * @version 2.0
 */

// Copy & Paste Funktionalität mit Session/Local Storage
var MBlockClipboard = {
    data: null,
    storageKey: 'mblock_clipboard',
    useSessionStorage: true, // true = Session Storage, false = Local Storage
    
    // Initialize clipboard from storage
    init: function() {
        try {
            const loaded = this.loadFromStorage();
            if (this.data) {
                // Clipboard data loaded
            }
        } catch (error) {
            console.warn('MBlock: Fehler beim Initialisieren des Clipboards:', error);
        }
    },
    
    // Get storage object (sessionStorage or localStorage)
    getStorage: function() {
        try {
            return this.useSessionStorage ? sessionStorage : localStorage;
        } catch (error) {
            console.warn('MBlock: Storage nicht verfügbar:', error);
            return null;
        }
    },
    
    // Save clipboard data to storage
    saveToStorage: function() {
        try {
            const storage = this.getStorage();
            if (storage && this.data) {
                storage.setItem(this.storageKey, JSON.stringify({
                    ...this.data,
                    // Add metadata
                    savedAt: new Date().toISOString(),
                    sessionId: this.getSessionId()
                }));
                return true;
            }
        } catch (error) {
            console.warn('MBlock: Fehler beim Speichern in Storage:', error);
        }
        return false;
    },
    
    // Load clipboard data from storage
    loadFromStorage: function() {
        try {
            const storage = this.getStorage();
            if (storage) {
                const stored = storage.getItem(this.storageKey);
                if (stored) {
                    const parsedData = JSON.parse(stored);
                    
                    // Check if data is still valid (max 24 hours for localStorage)
                    if (!this.useSessionStorage && parsedData.savedAt) {
                        const savedDate = new Date(parsedData.savedAt);
                        const now = new Date();
                        const hoursDiff = (now - savedDate) / (1000 * 60 * 60);
                        
                        if (hoursDiff > 24) {
                            this.clearStorage();
                            return false;
                        }
                    }
                    
                    this.data = parsedData;
                    this.updatePasteButtons();
                    return true;
                }
            }
        } catch (error) {
            console.warn('MBlock: Fehler beim Laden aus Storage:', error);
            this.clearStorage(); // Clear corrupted storage
        }
        return false;
    },
    
    // Clear storage
    clearStorage: function() {
        try {
            const storage = this.getStorage();
            if (storage) {
                storage.removeItem(this.storageKey);
            }
        } catch (error) {
            console.warn('MBlock: Fehler beim Leeren des Storages:', error);
        }
    },
    
    // Generate simple session ID
    getSessionId: function() {
        if (!this._sessionId) {
            this._sessionId = Date.now().toString() + Math.random().toString(36).substr(2, 9);
        }
        return this._sessionId;
    },
    
    // Toggle between session and local storage
    toggleStorageMode: function() {
        const oldData = this.data;
        this.clearStorage(); // Clear current storage
        
        this.useSessionStorage = !this.useSessionStorage;
        
        if (oldData) {
            this.data = oldData;
            this.saveToStorage(); // Save to new storage
        }
        
        return this.useSessionStorage;
    },
    
    // Show warning when trying to paste between different module types
    showModuleTypeMismatchWarning: function(currentType, clipboardType) {
        try {
            // Create temporary warning message
            const warningHtml = `
                <div class="alert alert-warning mblock-type-warning" style="margin: 10px 0; position: relative; z-index: 1000;">
                    <strong>Achtung:</strong> Das kopierte Element stammt aus einem anderen Modul-Typ. 
                    Das Einfügen ist nicht möglich.<br>
                    <small>Aktueller Typ: <code>${currentType}</code> | Zwischenablage: <code>${clipboardType}</code></small>
                    <button type="button" class="close" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none; font-size: 18px;" onclick="$(this).parent().fadeOut()">&times;</button>
                </div>
            `;
            
            // Find best place to show warning
            const targetElement = $('.mblock_wrapper').first();
            if (targetElement.length) {
                // Remove any existing warnings
                $('.mblock-type-warning').remove();
                
                // Add new warning
                targetElement.prepend(warningHtml);
                
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    $('.mblock-type-warning').fadeOut('slow');
                }, 5000);
                
            } else {
                // Fallback to browser alert
                alert('Das kopierte Element stammt aus einem anderen Modul und kann hier nicht eingefügt werden.');
            }
            
        } catch (error) {
            console.error('MBlock: Fehler beim Anzeigen der Modultyp-Warnung:', error);
            // Fallback to browser alert
            alert('Das kopierte Element kann hier nicht eingefügt werden (anderer Modul-Typ).');
        }
    },

    // Get module type/name from wrapper or form context
    getModuleType: function(wrapper) {
        try {
            
            // 1. Check form for hidden input with module_id (REDAXO standard!)
            const form = wrapper.closest('form');
            if (form.length) {
                const moduleInput = form.find('input[name="module_id"]').first();
                if (moduleInput.length) {
                    const moduleId = moduleInput.val();
                    if (moduleId) {
                        return 'module_' + moduleId;
                    }
                }
            }
            
            // 2. Fallback: Check in wrapper for other patterns
            const moduleInputWrapper = wrapper.find('input[name="module_id"]').first();
            if (moduleInputWrapper.length) {
                const moduleId = moduleInputWrapper.val();
                if (moduleId) {
                    return 'module_' + moduleId;
                }
            }
            
            // 3. Fallback: andere module_id patterns
            const moduleInputFallback = wrapper.find('input[name*="module_id"], input[name*="module_name"]').first();
            if (moduleInputFallback.length) {
                const moduleType = moduleInputFallback.val();
                if (moduleType) {
                    return 'module_' + moduleType;
                }
            }
            
            // 4. Check for form action or parent context
            if (form.length) {
                const action = form.attr('action') || '';
                const moduleMatch = action.match(/module_id=(\d+)/);
                if (moduleMatch) {
                    return 'module_' + moduleMatch[1];
                }
            }
            
            // 5. Check for unique class or id patterns on wrapper
            const classes = wrapper.attr('class') || '';
            const classMatch = classes.match(/mblock-module-(\w+)/);
            if (classMatch) {
                return classMatch[1];
            }
            
            // 6. Fallback: use closest identifying parent
            const parentWithId = wrapper.closest('[id]');
            if (parentWithId.length) {
                const id = parentWithId.attr('id');
                if (id.includes('module')) {
                    return id;
                }
            }
            
            // 6. Last resort: use URL parameters (nur innerhalb des gleichen Artikels!)
            const urlParams = new URLSearchParams(window.location.search);
            const moduleId = urlParams.get('module_id') || urlParams.get('article_id');
            if (moduleId) {
                return 'context_' + moduleId;
            }
            
            // Default fallback
            console.warn('MBlock: Keine Modul-ID erkannt - verwende unknown_module');
            return 'unknown_module';
            
        } catch (error) {
            console.warn('MBlock: Fehler beim Ermitteln des Modultyps:', error);
            return 'unknown_module';
        }
    },

    copy: function(element, item) {
        
        try {
            if (!item || !item.length) {
                console.warn('MBlock: Kein Item zum Kopieren gefunden');
                return false;
            }
            
            
            // Get module type from the closest mblock wrapper
            const wrapper = item.closest('.mblock_wrapper');
            const moduleType = this.getModuleType(wrapper);
            
            
            // Clone item completely
            const clonedItem = item.clone(true, true);
            
            // Convert selectpicker elements back to plain select elements for clean copying
            this.convertSelectpickerToPlainSelect(clonedItem);
            
            // Capture comprehensive form data
            const formData = this.captureComplexFormData(item);
            
            
            // Store in clipboard with metadata and form values
            this.data = {
                html: clonedItem.prop('outerHTML'),
                formData: formData,
                moduleType: moduleType, // Store module type
                timestamp: Date.now(),
                source: element.attr('class') || 'mblock_wrapper'
            };
            
            
            // Visual feedback
            this.showCopiedState(item);
            
            // Save to storage
            const saved = this.saveToStorage();
            
            // Update paste button states
            this.updatePasteButtons();
            
            return true;
            
        } catch (error) {
            console.error('MBlock: Fehler beim Kopieren:', error);
            return false;
        }
    },
    
    captureComplexFormData: function(item) {
        const formData = {};
        
        try {
            // Regular form elements
            item.find('input, textarea, select').each(function() {
                const $el = $(this);
                const name = $el.attr('name') || $el.attr('id');
                
                if (name) {
                    if ($el.is(':checkbox') || $el.is(':radio')) {
                        formData[name] = {
                            type: 'checkbox_radio',
                            value: $el.val(),
                            checked: $el.prop('checked'),
                            defaultValue: $el.attr('value')
                        };
                    } else if ($el.is('select')) {
                        const selectedOptions = [];
                        $el.find('option:selected').each(function() {
                            selectedOptions.push($(this).val());
                        });
                        formData[name] = {
                            type: 'select',
                            value: $el.val(),
                            selectedOptions: selectedOptions,
                            html: $el.html()
                        };
                    } else {
                        formData[name] = {
                            type: 'input',
                            value: $el.val(),
                            placeholder: $el.attr('placeholder')
                        };
                    }
                }
            });
            
            // CKEditor content (CKE5)
            item.find('.cke5-editor').each(function() {
                const $editor = $(this);
                const name = $editor.attr('name');
                if (name) {
                    // Try to get CKEditor content
                    let content = $editor.val();
                    
                    // Check if there's a CKEditor instance
                    const editorId = $editor.attr('id');
                    if (editorId && window.CKEDITOR && window.CKEDITOR.instances[editorId]) {
                        content = window.CKEDITOR.instances[editorId].getData();
                    }
                    
                    formData[name] = {
                        type: 'ckeditor',
                        value: content,
                        config: {
                            lang: $editor.attr('data-lang'),
                            profile: $editor.attr('data-profile')
                        }
                    };
                }
            });
            
            // REX_LINK widgets (comprehensive handling)
            item.find('input[id^="REX_LINK_"]').each(function() {
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
            
            // REX_MEDIA widgets
            item.find('input[id^="REX_MEDIA_"]').each(function() {
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
            
            // Additional field types can be added here...
            // (REX Media widgets, REX Link widgets, etc. - abbreviated for brevity)
            
            return formData;
            
        } catch (error) {
            console.error('MBlock: Fehler beim Erfassen der Formulardaten:', error);
            return formData;
        }
    },
    
    paste: function(element, afterItem) {
        try {
            // Load fresh data from storage in case it was updated in another tab
            this.loadFromStorage();
            
            if (!this.data) {
                console.warn('MBlock: Keine Daten in der Zwischenablage');
                const message = '❌ ' + mblock_get_text('mblock_toast_clipboard_empty', 'Keine Daten in der Zwischenablage');
                mblock_show_message(message, 'warning', 3000);
                return false;
            }
            
            // Check module type compatibility
            const currentWrapper = element.closest('.mblock_wrapper');
            const currentModuleType = this.getModuleType(currentWrapper);
            const clipboardModuleType = this.data.moduleType || 'unknown_module';
            
            if (currentModuleType !== clipboardModuleType) {
                console.warn('MBlock: Modultyp stimmt nicht überein. Paste abgebrochen.', {
                    current: currentModuleType,
                    clipboard: clipboardModuleType
                });
                const message = '⚠️ ' + mblock_get_text('mblock_toast_module_type_mismatch', 'Modultyp stimmt nicht überein') + ': ' + clipboardModuleType + ' ≠ ' + currentModuleType;
                mblock_show_message(message, 'error', 4000);
                
                // Show user feedback
                this.showModuleTypeMismatchWarning(currentModuleType, clipboardModuleType);
                return false;
            }
            
            
            // Create element from clipboard
            const pastedItem = $(this.data.html);
            
            // Clean up IDs and names to avoid conflicts
            this.cleanupPastedItem(pastedItem);
            
            // Insert item
            if (afterItem && afterItem.length) {
                // Destroy sortable before manipulation
                if (typeof MBlockSortable !== 'undefined') {
                    MBlockSortable.destroy(element);
                }
                
                afterItem.after(pastedItem);
            } else {
                element.prepend(pastedItem);
            }
            
            // Add unique ids
            mblock_set_unique_id(pastedItem, true);
            
            // CRITICAL: Reinitialize widgets BEFORE form data restoration
            if (typeof mblock_reinitialize_redaxo_widgets === 'function') {
                mblock_reinitialize_redaxo_widgets(pastedItem);
            }
            
            // Restore form values from clipboard with enhanced data restoration
            if (this.data.formData) {
                this.restoreComplexFormData(pastedItem, this.data.formData);
            }
            
            // Reinitialize sortable
            mblock_init_sort(element);
            
            // Trigger rex:ready event
            pastedItem.trigger('rex:ready', [pastedItem]);
            
            // Component reinitialization
            setTimeout(() => {
                // Initialize selectpicker
                if (typeof $.fn.selectpicker === 'function') {
                    var selects = pastedItem.find('select.mblock-needs-selectpicker');
                    if (selects.length) {
                        selects.removeClass('mblock-needs-selectpicker').addClass('selectpicker');
                        selects.selectpicker({ noneSelectedText: '—' });
                        selects.selectpicker('refresh');
                    }
                }
                
                // Trigger change events
                pastedItem.find('input, select, textarea').trigger('change');
            }, 50);
            
            // Scroll to pasted item
            setTimeout(() => {
                if (pastedItem && pastedItem.length && pastedItem.is(':visible')) {
                    mblock_smooth_scroll_to_element(pastedItem[0]);
                }
            }, 100);
            
            // ✨ Add glow effect to pasted item using utility
            setTimeout(() => {
                if (pastedItem && pastedItem.length && pastedItem.is(':visible')) {
                    MBlockUtils.animation.addGlowEffect(pastedItem, 'mblock-paste-glow', 1200);
                    
                    // Use bloecks Toast System for success feedback
                    if (typeof BLOECKS !== 'undefined' && BLOECKS.fireMBlockToast) {
                        const message = '✅ ' + mblock_get_text('mblock_toast_paste_success', 'Block erfolgreich eingefügt!');
                        BLOECKS.fireMBlockToast(message, 'success', 4000);
                    } else if (typeof BLOECKS !== 'undefined' && BLOECKS.showToast) {
                        const message = '✅ ' + mblock_get_text('mblock_toast_paste_success', 'Block erfolgreich eingefügt!');
                        BLOECKS.showToast(message, 'success', 4000);
                    }
                }
            }, 150);
            
            return true;
            
        } catch (error) {
            console.error('MBlock: Fehler beim Einfügen:', error);
            return false;
        }
    },
    
    cleanupPastedItem: function(item) {
        try {
            
            // Remove mblock-specific data attributes
            item.removeAttr('data-mblock_index');
            
            // Clean form elements
            item.find('input, textarea, select').each(function() {
                const $el = $(this);
                const name = $el.attr('name');
                if (name && name.indexOf('mblock_new_') === -1) {
                    $el.attr('name', 'mblock_new_' + name);
                }
                
                // DON'T clear values here - they will be restored later by restoreComplexFormData
                // Only clear specific input types that should always be empty
                const inputType = $el.attr('type');
                if (inputType === 'file') {
                    $el.val(''); // File inputs should always be cleared
                }
                
                // Keep unique values for unique fields
                if ($el.attr('data-unique') && !$el.val()) {
                    // Only generate unique value if field is empty
                    const unique_id = Math.random().toString(16).slice(2);
                    $el.val(unique_id);
                }
            });
            
            // Clean IDs that might cause conflicts
            item.find('[id]').each(function() {
                const $el = $(this);
                const id = $el.attr('id');
                if (id && !id.match(/^REX_/)) {
                    $el.removeAttr('id');
                }
            });
            
        } catch (error) {
            console.error('MBlock: Fehler beim Bereinigen des eingefügten Items:', error);
        }
    },

    restoreComplexFormData: function(pastedItem, formData) {
        try {
            
            Object.keys(formData).forEach(originalName => {
                const fieldData = formData[originalName];
                
                if (!fieldData || typeof fieldData !== 'object') return;
                
                // Find field by smart matching
                let $field = pastedItem.find(`[name="${originalName}"], [name="mblock_new_${originalName}"]`);
                
                if (!$field.length) {
                    return;
                }
                
                // Handle different field types
                switch (fieldData.type) {
                    case 'checkbox_radio':
                        $field.val(fieldData.value);
                        $field.prop('checked', fieldData.checked);
                        if (fieldData.defaultValue) {
                            $field.attr('value', fieldData.defaultValue);
                        }
                        break;
                        
                    case 'select':
                        // Restore select HTML if needed
                        if (fieldData.html) {
                            $field.html(fieldData.html);
                        }
                        $field.val(fieldData.value);
                        break;
                        
                    case 'ckeditor':
                        if (fieldData.value) {
                            $field.val(fieldData.value);
                            
                            // If CKEditor instance exists, set data
                            const editorId = $field.attr('id');
                            if (editorId && window.CKEDITOR && window.CKEDITOR.instances[editorId]) {
                                setTimeout(() => {
                                    window.CKEDITOR.instances[editorId].setData(fieldData.value);
                                }, 200);
                            }
                        }
                        break;
                        
                    case 'rex_link':
                        // Restore REX_LINK field values and functionality
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
                                            mblock_fetch_article_name(fieldData.value, $displayFieldFallback);
                                        }
                                    }
                                }
                            } else {
                                if (fieldData.displayValue) {
                                    $displayField.val(fieldData.displayValue);
                                } else if (fieldData.value) {
                                    // Auto-fetch article name if display value is missing
                                    mblock_fetch_article_name(fieldData.value, $displayField);
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
                        break;
                        
                    case 'rex_media':
                        // Restore REX_MEDIA field values and functionality
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
                                                
                                                // Also update numeric part for media function calls
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
                        break;
                        
                    case 'input':
                    default:
                        // Handle regular inputs and textareas
                        if (fieldData.value !== undefined) {
                            $field.val(fieldData.value);
                            
                            // Restore placeholder if available
                            if (fieldData.placeholder) {
                                $field.attr('placeholder', fieldData.placeholder);
                            }
                        }
                        break;
                }
            });
            
            
        } catch (error) {
            console.error('MBlock: Fehler beim Wiederherstellen komplexer Formulardaten:', error);
        }
    },
    
    showCopiedState: function(item) {
        // Visual feedback using centralized animation utility
        MBlockUtils.animation.addGlowEffect(item, 'mblock-copy-glow', 1000);
        
        // Use bloecks Toast System for additional feedback
        if (typeof BLOECKS !== 'undefined' && BLOECKS.fireMBlockToast) {
            const message = '📋 ' + mblock_get_text('mblock_toast_copy_success', 'Block erfolgreich kopiert!');
            BLOECKS.fireMBlockToast(message, 'success', 3000);
        } else if (typeof BLOECKS !== 'undefined' && BLOECKS.showToast) {
            const message = '📋 ' + mblock_get_text('mblock_toast_copy_success', 'Block erfolgreich kopiert!');
            BLOECKS.showToast(message, 'success', 3000);
        }
        
        // Optional: Also give feedback to the copy button if it exists
        const $copyBtn = item.find('.mblock-copy-btn');
        if ($copyBtn.length) {
            MBlockUtils.animation.addGlowEffect($copyBtn, 'is-copied', 1000);
        }
    },
    
    updatePasteButtons: function() {
        const hasData = !!this.data;
        
        
        if (hasData) {
            // Prüfe Modulkompatibilität für alle sichtbaren MBlock-Wrapper
            $('.mblock_wrapper').each((index, wrapperElement) => {
                const $wrapper = $(wrapperElement);
                const currentModuleType = this.getModuleType($wrapper);
                const clipboardModuleType = this.data.moduleType || 'unknown_module';
                
                // Finde Paste-Buttons in diesem Wrapper
                const $pasteButtons = $wrapper.find('.mblock-paste-btn');
                
                if (currentModuleType === clipboardModuleType) {
                    // Module kompatibel - Buttons aktivieren
                    $pasteButtons.removeClass('disabled').prop('disabled', false);
                    $pasteButtons.attr('title', 'Paste element (Module kompatibel)');
                } else {
                    // Module nicht kompatibel - Buttons deaktivieren
                    $pasteButtons.addClass('disabled').prop('disabled', true);
                    $pasteButtons.attr('title', `Cannot paste: Different module type (Current: ${currentModuleType}, Clipboard: ${clipboardModuleType})`);
                }
            });
        } else {
            // Keine Daten - alle Buttons deaktivieren
            $('.mblock-paste-btn').addClass('disabled').prop('disabled', true);
            $('.mblock-paste-btn').attr('title', 'No data in clipboard');
        }
        
        // Update toolbar visibility
        const toolbar = $('.mblock-copy-paste-toolbar');
        if (hasData) {
            toolbar.show();
        } else {
            toolbar.hide();
        }
        
        // Update button text with storage info  
        const storageInfo = this.useSessionStorage ? 'Session' : 'Local';
    },

    // Convert selectpicker elements back to plain select elements
    convertSelectpickerToPlainSelect: function(container) {
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
    
    clear: function() {
        this.data = null;
        this.clearStorage();
        this.updatePasteButtons();
    },
    
    // Get clipboard info for debugging
    getInfo: function() {
        return {
            hasData: !!this.data,
            storageMode: this.useSessionStorage ? 'Session' : 'Local',
            timestamp: this.data ? this.data.timestamp : null,
            savedAt: this.data ? this.data.savedAt : null,
            itemCount: this.data && this.data.formData ? Object.keys(this.data.formData).length : 0
        };
    }
};

// Online/Offline Toggle Funktionalität
var MBlockOnlineToggle = {
    
    toggle: function(element, item) {
        try {
            if (!item || !item.length) {
                console.warn('MBlock: Kein Item für Online/Offline Toggle gefunden');
                return false;
            }
            
            const isOnline = !item.hasClass('mblock-offline');
            const $toggleBtn = item.find('.mblock-online-toggle');
            const $icon = $toggleBtn.find('i');
            
            if (isOnline) {
                // Set to offline
                item.addClass('mblock-offline');
                $toggleBtn.removeClass('btn-online').addClass('btn-offline')
                    .attr('title', 'Set online');
                
                // Change icon
                if ($icon.length) {
                    $icon.removeClass('rex-icon-online').addClass('rex-icon-offline');
                } else {
                    $toggleBtn.html('<i class="rex-icon rex-icon-offline"></i>');
                }
                
                // Add hidden input to store offline state
                this.setOfflineState(item, true);
                
                
            } else {
                // Set to online
                item.removeClass('mblock-offline');
                $toggleBtn.removeClass('btn-offline').addClass('btn-online')
                    .attr('title', 'Set offline');
                
                // Change icon
                if ($icon.length) {
                    $icon.removeClass('rex-icon-offline').addClass('rex-icon-online');
                } else {
                    $toggleBtn.html('<i class="rex-icon rex-icon-online"></i>');
                }
                
                // Remove offline state
                this.setOfflineState(item, false);
                
            }
            
            return true;
            
        } catch (error) {
            console.error('MBlock: Fehler beim Online/Offline Toggle:', error);
            return false;
        }
    },
    
    setOfflineState: function(item, isOffline) {
        try {
            // Look for existing mblock_offline input (must be defined in template)
            const $offlineInput = item.find('input[name*="mblock_offline"]');
            
            if ($offlineInput.length) {
                // Simply set the value - field already exists in template
                $offlineInput.val(isOffline ? '1' : '0');
            } else {
                console.warn('MBlock: No mblock_offline input found - must be defined in template for this functionality');
            }
            
            
        } catch (error) {
            console.error('MBlock: Fehler beim Setzen des Offline-Status:', error);
        }
    },
    
    initializeStates: function(element) {
        try {
            
            // Initialize toggle buttons based on existing offline states
            element.find('> div.sortitem').each(function(index) {
                const $item = $(this);
                const itemIndex = $item.attr('data-mblock_index') || index;
                
                // Look for offline input with multiple strategies
                let $offlineInput = $item.find('input[name*="mblock_offline"]');
                
                // Fallback: try different name patterns
                if (!$offlineInput.length) {
                    $offlineInput = $item.find('input[name*="_offline"]');
                }
                if (!$offlineInput.length) {
                    $offlineInput = $item.find('input[value="1"][type="hidden"]');
                }
                
                const $toggleBtn = $item.find('.mblock-online-toggle');
                const $icon = $toggleBtn.find('i');
                
                if ($toggleBtn.length) {
                    const isOffline = $offlineInput.length && ($offlineInput.val() === '1' || $offlineInput.val() === 1);
                    
                    if (isOffline) {
                        // Item is offline
                        $item.addClass('mblock-offline');
                        $toggleBtn.removeClass('btn-online').addClass('btn-offline')
                            .attr('title', 'Set online');
                        
                        if ($icon.length) {
                            $icon.removeClass('rex-icon-online').addClass('rex-icon-offline');
                        } else {
                            $toggleBtn.html('<i class="rex-icon rex-icon-offline"></i>');
                        }
                    } else {
                        // Item is online (value is 0, empty, or input doesn't exist)
                        $item.removeClass('mblock-offline');
                        $toggleBtn.removeClass('btn-offline').addClass('btn-online')
                            .attr('title', 'Set offline');
                        
                        if ($icon.length) {
                            $icon.removeClass('rex-icon-offline').addClass('rex-icon-online');
                        } else {
                            $toggleBtn.html('<i class="rex-icon rex-icon-online"></i>');
                        }
                    }
                }
            });
            
            
        } catch (error) {
            console.error('MBlock: Fehler beim Initialisieren der Online/Offline-States:', error);
        }
    },

    // New method for auto-detected offline toggle buttons
    toggleAutoDetected: function(element, item, button) {
        try {
            if (!item || !item.length || !button || !button.length) {
                console.warn('MBlock: Kein Item oder Button für Auto-Detected Toggle gefunden');
                return false;
            }
            
            // Get current offline status from button data attribute
            const currentIsOffline = button.attr('data-offline') === '1';
            const newIsOffline = !currentIsOffline;
            
            // Find the corresponding mblock_offline input field
            const $offlineInput = item.find('input[name*="mblock_offline"]');
            
            if (!$offlineInput.length) {
                console.warn('MBlock: No mblock_offline input field found in item');
                return false;
            }
            
            // Update the input value
            $offlineInput.val(newIsOffline ? '1' : '0');
            
            // Update button appearance with improved colors
            const buttonClass = newIsOffline ? 'btn-danger' : 'btn-success'; // Red for offline, green for online
            const iconClass = newIsOffline ? 'rex-icon-offline' : 'rex-icon-online';
            const buttonTitle = newIsOffline ? 'Set online' : 'Set offline';
            const buttonText = newIsOffline ? 'Offline' : 'Online';
            
            // Update button attributes and classes
            button.removeClass('btn-default btn-warning btn-success btn-danger')
                  .addClass(buttonClass)
                  .attr('title', buttonTitle)
                  .attr('data-offline', newIsOffline ? '1' : '0');
            
            // Update icon and text
            const $icon = button.find('i');
            if ($icon.length) {
                $icon.removeClass('rex-icon-online rex-icon-offline')
                     .addClass(iconClass);
            }
            
            // Update button text
            const textContent = button.html().replace(/Offline|Online/, buttonText);
            button.html(textContent);
            
            // Update item CSS class
            if (newIsOffline) {
                item.addClass('mblock-offline');
            } else {
                item.removeClass('mblock-offline');
            }
                       
            return true;
            
        } catch (error) {
            console.error('MBlock: Fehler beim Auto-Detected Toggle:', error);
            return false;
        }
    }
};

/**
 * Critical function to reinitialize REDAXO Media and Link widgets in new blocks
 * This fixes the issue where media/link selection doesn't work in dynamically added blocks
 */
function mblock_reinitialize_redaxo_widgets(container) {
    try {
        if (!container || !container.length) {
            return false;
        }
        
        // Get context information
        const mblockIndex = parseInt(container.attr('data-mblock_index')) || 1;
        const mblockWrapper = container.closest('.mblock_wrapper');
        const mblockCount = mblockWrapper.find('.sortitem').length || 1;
        const isGridBlock = container.closest('.gridblock_wrapper').length > 0 || container.hasClass('gridblock-item');
        
        console.log('MBlock: Widget-Reinitialisierung gestartet', {
            mblockIndex: mblockIndex,
            isGridBlock: isGridBlock,
            containerClass: container.attr('class')
        });
        
        // 🔧 REX MEDIA widgets - Enhanced for GridBlock compatibility
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
        
        // 🔧 REX LINK widgets - Enhanced for GridBlock compatibility  
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
                        mblock_fetch_article_name(articleId, $displayField);
                    }
                }
            }
        });
        
        // 🔧 REX LINKLIST widgets
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
        
        // 🔧 GridBlock-specific: Trigger rex:ready event for custom widgets
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
                if (typeof $.fn.selectpicker === 'function') {
                    container.find('select.selectpicker').selectpicker('refresh');
                }
            }, 100);
        }
        
        // 🔧 General widget reinitialization
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

// 🔧 AJAX-Funktion zum Holen von Artikel-Namen für REX_LINK Felder
function mblock_fetch_article_name(articleId, $displayField) {
    if (!articleId || !$displayField || !$displayField.length) return;
    
    // Cache für bereits geladene Artikel-Namen
    if (!window.mblock_article_cache) {
        window.mblock_article_cache = {};
    }
    
    // Aus Cache verwenden falls vorhanden
    if (window.mblock_article_cache[articleId]) {
        $displayField.val(window.mblock_article_cache[articleId]);
        console.log('MBlock: Artikel-Name aus Cache:', window.mblock_article_cache[articleId], 'für ID:', articleId);
        return;
    }
    
    // AJAX-Request an REDAXO Structure Linkmap
    const currentClang = $('input[name="clang"]').val() || 1;
    const ajaxUrl = rex.backend + '?page=structure/linkmap&opener_input_field=temp&article_id=' + articleId + '&clang=' + currentClang;
    
    $.ajax({
        url: ajaxUrl,
        method: 'GET',
        timeout: 5000,
        success: function(response) {
            // Artikel-Name aus Response extrahieren
            let articleName = '';
            
            // Verschiedene Patterns versuchen
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
            
            // Fallback: ID mit Artikel-Prefix verwenden
            if (!articleName) {
                articleName = 'Artikel [' + articleId + ']';
            }
            
            // In Cache speichern und Display-Feld setzen
            window.mblock_article_cache[articleId] = articleName;
            $displayField.val(articleName);
            $displayField.trigger('change');
            
            console.log('MBlock: Artikel-Name per AJAX geholt:', articleName, 'für ID:', articleId);
        },
        error: function() {
            // Fallback bei AJAX-Fehler
            const fallbackName = 'Artikel [' + articleId + ']';
            window.mblock_article_cache[articleId] = fallbackName;
            $displayField.val(fallbackName);
            
            console.log('MBlock: Artikel-Name Fallback verwendet:', fallbackName, 'für ID:', articleId);
        }
    });
}

// 🚀 AUTO-INITIALIZATION: Befülle leere REX_LINK Display-Felder beim Seitenladen
$(document).ready(function() {
    // Warte bis REDAXO vollständig geladen ist
    setTimeout(function() {
        mblock_initialize_empty_rex_link_fields();
    }, 500);
    
    // 🔧 TAB-SUPPORT: Initialisiere REX_LINK-Felder wenn Tabs gewechselt werden
    $(document).on('shown.bs.tab', function(e) {
        // Verzögere die Initialisierung, da Tab-Inhalte Zeit brauchen um sichtbar zu werden
        setTimeout(function() {
            console.log('MBlock: Bootstrap Tab gewechselt - initialisiere REX_LINK-Felder...');
            mblock_initialize_empty_rex_link_fields();
        }, 100);
    });
    
    // Alternative für verschiedene Tab-Systeme (Bootstrap 3/4/5 + MForm)
    $(document).on('click', '.nav-tabs a, .nav-pills a, [data-toggle="tab"], [data-bs-toggle="tab"], .mform-tabs a', function() {
        setTimeout(function() {
            console.log('MBlock: Tab-Click erkannt - initialisiere REX_LINK-Felder...');
            mblock_initialize_empty_rex_link_fields();
        }, 200);
    });
    
    // 🔧 MForm-spezifische Tab-Events
    $(document).on('mform:tabChanged mform:tabShow', function(e) {
        setTimeout(function() {
            console.log('MBlock: MForm Tab-Event - initialisiere REX_LINK-Felder...');
            mblock_initialize_empty_rex_link_fields();
        }, 150);
    });
});

// 🔧 Befülle alle leeren REX_LINK Display-Felder mit Artikel-Namen
function mblock_initialize_empty_rex_link_fields() {
    try {
        console.log('MBlock: Initialisiere leere REX_LINK Display-Felder...');
        let foundFields = 0;
        let processedFields = 0;
        
        // Finde alle REX_LINK Hidden-Inputs mit Werten (auch in versteckten Tabs)
        $('input[id^="REX_LINK_"]').each(function() {
            const $hiddenInput = $(this);
            const hiddenId = $hiddenInput.attr('id');
            const articleId = $hiddenInput.val();
            foundFields++;
            
            console.log('MBlock: Prüfe REX_LINK Feld:', hiddenId, 'Wert:', articleId, 'Typ:', $hiddenInput.attr('type'));
            
            // Nur Hidden Inputs mit Werten bearbeiten (nicht die _NAME Felder)
            if (hiddenId && !hiddenId.includes('_NAME') && 
                $hiddenInput.attr('type') === 'hidden' && 
                articleId && articleId.trim() !== '') {
                
                // Finde das zugehörige Display-Feld
                const displayId = hiddenId + '_NAME';
                const $displayField = $('#' + displayId);
                
                console.log('MBlock: Suche Display-Feld:', displayId, 'gefunden:', $displayField.length, 'aktueller Wert:', $displayField.val());
                
                if ($displayField.length) {
                    const currentDisplayValue = $displayField.val() || '';
                    
                    // Nur befüllen wenn das Display-Feld leer ist
                    if (currentDisplayValue.trim() === '') {
                        console.log('MBlock: Befülle leeres REX_LINK Display-Feld:', displayId, 'für Artikel:', articleId);
                        mblock_fetch_article_name(articleId, $displayField);
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
}

// Export for module systems (if used)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { MBlockClipboard, MBlockOnlineToggle };
}
