/**
 * MBlock Features - Advanced functionality
 * 
 * Contains:
 * - Copy/Paste functionality (MBlockClipboard)
 * - Online/Offline toggle (MBlockOnlineToggle)
 * - Advanced features and toolbar functionality
 * 
 * Depends on: mblock-core.js, mblock-management.js, widgets.js, addonfixes.js
 * 
 * @author joachim doerr
 * @version 2.0
 */

// Copy & Paste functionality with Session/Local Storage
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
            console.warn('MBlock: Error initializing clipboard:', error);
        }
    },
    
    // Get storage object (sessionStorage or localStorage)
    getStorage: function() {
        try {
            return this.useSessionStorage ? sessionStorage : localStorage;
        } catch (e) {
            console.warn('MBlock: Storage not available, using memory fallback');
            return null;
        }
    },
    
    // Load data from storage
    loadFromStorage: function() {
        try {
            const storage = this.getStorage();
            if (!storage) return false;
            
            const savedData = storage.getItem(this.storageKey);
            if (savedData) {
                this.data = JSON.parse(savedData);
                console.log('MBlock: Clipboard data loaded from storage');
                return true;
            }
        } catch (error) {
            console.warn('MBlock: Error loading from storage:', error);
        }
        return false;
    },
    
    // Save data to storage
    saveToStorage: function() {
        try {
            const storage = this.getStorage();
            if (!storage || !this.data) return false;
            
            storage.setItem(this.storageKey, JSON.stringify(this.data));
            console.log('MBlock: Clipboard data saved to storage');
            return true;
        } catch (error) {
            console.warn('MBlock: Error saving to storage:', error);
            return false;
        }
    },
    
    // Clear storage
    clearStorage: function() {
        try {
            const storage = this.getStorage();
            if (storage) {
                storage.removeItem(this.storageKey);
                console.log('MBlock: Clipboard storage cleared');
            }
        } catch (error) {
            console.warn('MBlock: Error clearing storage:', error);
        }
    },
    
    // Get module type for compatibility checking
    getModuleType: function(element) {
        try {
            // Try multiple methods to get module type
            const moduleAttr = element.attr('data-module-id') || element.attr('data-module') || element.data('module');
            if (moduleAttr) return moduleAttr;
            
            // Try to extract from wrapper classes or data
            const className = element.attr('class') || '';
            const classMatch = className.match(/mblock-module-(\w+)/);
            if (classMatch) return classMatch[1];
            
            // Try parent elements
            const $parent = element.closest('[data-module], [data-module-id]');
            if ($parent.length) {
                return $parent.attr('data-module') || $parent.attr('data-module-id');
            }
            
            // Fallback: use form action or page info
            const formAction = $('form').attr('action') || '';
            const moduleMatch = formAction.match(/module_id[=\/](\d+)/);
            if (moduleMatch) return 'module_' + moduleMatch[1];
            
            return 'default_module';
        } catch (error) {
            console.warn('MBlock: Error getting module type:', error);
            return 'unknown_module';
        }
    },
    
    showModuleTypeMismatchWarning: function(currentType, clipboardType) {
        // Additional user feedback for module type mismatch
        console.warn('MBlock: Module type mismatch - cannot paste from', clipboardType, 'to', currentType);
    },
    
    copy: function(element, item) {
        try {
            if (!item || !item.length) {
                console.warn('MBlock: No item to copy');
                return false;
            }
            
            // Get module type for compatibility checking
            const currentWrapper = element.closest('.mblock_wrapper');
            const moduleType = this.getModuleType(currentWrapper);
            
            console.log('MBlock: Starting copy operation for module type:', moduleType);
            
            // Clone the item for safe manipulation
            const itemClone = item.clone();
            
            // Prepare selectpicker elements for cloning
            if (typeof MBlockWidgets !== 'undefined' && MBlockWidgets.selectpicker) {
                MBlockWidgets.selectpicker.convertToPlain(itemClone);
            }
            
            // Capture current form data for complex restoration
            const formData = this.captureFormData(item);
            
            // Store clipboard data with metadata
            this.data = {
                html: itemClone.prop('outerHTML'),
                formData: formData,
                moduleType: moduleType,
                timestamp: Date.now(),
                savedAt: new Date().toLocaleString()
            };
            
            // Save to storage
            this.saveToStorage();
            
            // Update UI state
            this.updatePasteButtons();
            
            // Show user feedback
            this.showCopiedState(item);
            
            console.log('MBlock: Copy completed for module:', moduleType);
            return true;
            
        } catch (error) {
            console.error('MBlock: Error during copy:', error);
            return false;
        }
    },
    
    captureFormData: function(item) {
        const formData = {};
        
        try {
            // Basic form elements (inputs, textareas, selects)
            item.find('input, textarea, select').each(function() {
                const $el = $(this);
                const name = $el.attr('name');
                const type = $el.attr('type') || $el.prop('tagName').toLowerCase();
                
                if (!name) return;
                
                if (type === 'checkbox' || type === 'radio') {
                    formData[name] = {
                        type: 'checkbox_radio',
                        value: $el.val(),
                        checked: $el.is(':checked'),
                        defaultValue: $el.attr('value')
                    };
                } else if (type === 'select') {
                    formData[name] = {
                        type: 'select',
                        value: $el.val(),
                        html: $el.html()
                    };
                } else {
                    formData[name] = {
                        type: 'input',
                        value: $el.val(),
                        placeholder: $el.attr('placeholder') || ''
                    };
                }
            });
            
            // CKEditor content using addon fixes
            if (typeof MBlockAddonFixes !== 'undefined' && MBlockAddonFixes.ckeditor) {
                MBlockAddonFixes.ckeditor.captureContent(item, formData);
            }
            
            // REX widgets using widgets module
            if (typeof MBlockWidgets !== 'undefined') {
                if (MBlockWidgets.link) {
                    MBlockWidgets.link.captureData(item, formData);
                }
                if (MBlockWidgets.media) {
                    MBlockWidgets.media.captureData(item, formData);
                }
            }
            
            return formData;
            
        } catch (error) {
            console.error('MBlock: Error capturing form data:', error);
            return formData;
        }
    },
    
    paste: function(element, afterItem) {
        try {
            // Load fresh data from storage in case it was updated in another tab
            this.loadFromStorage();
            
            if (!this.data) {
                console.warn('MBlock: No data in clipboard');
                const message = '❌ ' + mblock_get_text('mblock_toast_clipboard_empty', 'No data in clipboard');
                mblock_show_message(message, 'warning', 3000);
                return false;
            }
            
            // Check module type compatibility
            const currentWrapper = element.closest('.mblock_wrapper');
            const currentModuleType = this.getModuleType(currentWrapper);
            const clipboardModuleType = this.data.moduleType || 'unknown_module';
            
            if (currentModuleType !== clipboardModuleType) {
                console.warn('MBlock: Module type mismatch. Paste aborted.', {
                    current: currentModuleType,
                    clipboard: clipboardModuleType
                });
                const message = '⚠️ ' + mblock_get_text('mblock_toast_module_type_mismatch', 'Module type mismatch') + ': ' + clipboardModuleType + ' ≠ ' + currentModuleType;
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
            if (typeof MBlockWidgets !== 'undefined' && MBlockWidgets.reinitializeAll) {
                MBlockWidgets.reinitializeAll(pastedItem);
            }
            
            // Destroy existing CKEditor5 instances before reindexing
            if (typeof MBlockAddonFixes !== 'undefined' && MBlockAddonFixes.ckeditor) {
                MBlockAddonFixes.ckeditor.destroyInstances(pastedItem);
            }
            
            // Restore NON-CKEditor form values first
            if (this.data.formData) {
                this.restoreNonCKEditorFormData(pastedItem, this.data.formData);
            }
            
            // Reinitialize sortable
            mblock_init_sort(element);
            
            // Trigger rex:ready event for full reinitialization (including CKEditor5)
            pastedItem.trigger('rex:ready', [pastedItem]);
            
            // CRITICAL: Handle nested MBlock wrappers inside pasted content (GridBlock compatibility)
            if (typeof MBlockUtils.nested !== 'undefined') {
                MBlockUtils.nested.initializeNested(pastedItem);
            }

            // Wait for CKEditor5 initialization, then restore content
            if (this.data.formData && typeof MBlockAddonFixes !== 'undefined' && MBlockAddonFixes.ckeditor) {
                setTimeout(() => {
                    MBlockAddonFixes.ckeditor.restoreContent(pastedItem, this.data.formData);
                }, 500);
            }
            
            // Component reinitialization
            setTimeout(() => {
                // Initialize selectpicker
                if (typeof MBlockWidgets !== 'undefined' && MBlockWidgets.selectpicker) {
                    MBlockWidgets.selectpicker.initialize(pastedItem);
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
                    
                    // Centralized success feedback (uses BLOECKS when available, otherwise MBLOCK fallback)
                    const message = '✅ ' + mblock_get_text('mblock_toast_paste_success', 'Block successfully pasted!');
                    mblock_show_message(message, 'success', 4000);
                }
            }, 150);
            
            return true;
            
        } catch (error) {
            console.error('MBlock: Error during paste:', error);
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
                // Keep CKEditor and REX widget IDs - they need proper reindexing
                if (id && !id.match(/^(REX_|ck)/)) {
                    $el.removeAttr('id');
                }
            });
            
        } catch (error) {
            console.error('MBlock: Error cleaning up pasted item:', error);
        }
    },

    restoreNonCKEditorFormData: function(pastedItem, formData) {
        try {
            Object.keys(formData).forEach(originalName => {
                const fieldData = formData[originalName];
                
                if (!fieldData || typeof fieldData !== 'object') return;
                if (fieldData.type === 'ckeditor') return; // Skip CKEditor fields
                
                // Find field by smart matching
                let $field = pastedItem.find(`[name="${originalName}"], [name="mblock_new_${originalName}"]`);
                
                if (!$field.length) {
                    return;
                }
                
                // Handle different field types (except ckeditor)
                this.restoreFieldData($field, fieldData, pastedItem, originalName);
            });
        } catch (error) {
            console.error('MBlock: Error restoring non-CKEditor data:', error);
        }
    },

    restoreFieldData: function($field, fieldData, pastedItem, originalName) {
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
                
            case 'rex_link':
                if (typeof MBlockWidgets !== 'undefined' && MBlockWidgets.link) {
                    MBlockWidgets.link.restoreData(pastedItem, fieldData, $field);
                }
                break;
                
            case 'rex_media':
                if (typeof MBlockWidgets !== 'undefined' && MBlockWidgets.media) {
                    MBlockWidgets.media.restoreData(pastedItem, fieldData, $field);
                }
                break;
                
            default:
                // Handle regular input fields
                if (fieldData.value !== undefined) {
                    $field.val(fieldData.value);
                    if (fieldData.placeholder) {
                        $field.attr('placeholder', fieldData.placeholder);
                    }
                }
                break;
        }
    },
    
    showCopiedState: function(item) {
        // Visual feedback using centralized animation utility
        MBlockUtils.animation.addGlowEffect(item, 'mblock-copy-glow', 1000);
        
        // Centralized copy feedback
        const copyMessage = '📋 ' + mblock_get_text('mblock_toast_copy_success', 'Block successfully copied!');
        mblock_show_message(copyMessage, 'success', 3000);
            
        // Optional: Also give feedback to the copy button if it exists
        const $copyBtn = item.find('.mblock-copy-btn');
        if ($copyBtn.length) {
            MBlockUtils.animation.addGlowEffect($copyBtn, 'is-copied', 1000);
        }
    },
    
    updatePasteButtons: function() {
        const hasData = !!this.data;
        
        if (hasData) {
            // Check module compatibility for all visible MBlock wrappers
            $('.mblock_wrapper').each((index, wrapperElement) => {
                const $wrapper = $(wrapperElement);
                const currentModuleType = this.getModuleType($wrapper);
                const clipboardModuleType = this.data.moduleType || 'unknown_module';
                
                // Find paste buttons in this wrapper
                const $pasteButtons = $wrapper.find('.mblock-paste-btn');
                
                if (currentModuleType === clipboardModuleType) {
                    // Module compatible - enable buttons
                    $pasteButtons.removeClass('disabled').prop('disabled', false);
                    $pasteButtons.attr('title', 'Paste element (Module compatible)');
                } else {
                    // Module not compatible - disable buttons
                    $pasteButtons.addClass('disabled').prop('disabled', true);
                    $pasteButtons.attr('title', `Cannot paste: Different module type (Current: ${currentModuleType}, Clipboard: ${clipboardModuleType})`);
                }
            });
        } else {
            // No data - disable all buttons
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

// Online/Offline Toggle functionality
var MBlockOnlineToggle = {
    
    toggle: function(element, item) {
        try {
            if (!item || !item.length) {
                console.warn('MBlock: No item found for Online/Offline Toggle');
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
            console.error('MBlock: Error in Online/Offline Toggle:', error);
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
            console.error('MBlock: Error setting offline status:', error);
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
            console.error('MBlock: Error initializing Online/Offline states:', error);
        }
    },

    // New method for auto-detected offline toggle buttons
    toggleAutoDetected: function(element, item, button) {
        try {
            if (!item || !item.length || !button || !button.length) {
                console.warn('MBlock: No item or button found for Auto-Detected Toggle');
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
            console.error('MBlock: Error in Auto-Detected Toggle:', error);
            return false;
        }
    }
};

// Toolbar initialization with better modular structure
function mblock_init_toolbar(element) {
    try {
        // Only initialize if Copy/Paste is enabled
        if (!checkCopyPasteEnabled()) {
            return;
        }
        
        // Centralized toolbar event configuration
        const toolbarEvents = [
            {
                selector: '.mblock-copy-paste-toolbar .mblock-paste-btn',
                handler: function (e) {
                    e.preventDefault();
                    try {
                        const $this = $(this);
                        if (!MBlockUtils.state.isDisabled($this)) {
                            if (typeof MBlockClipboard !== 'undefined') {
                                MBlockClipboard.paste(element, false); // false = insert at beginning
                            }
                        }
                    } catch (error) {
                        console.error('MBlock: Error in toolbar paste click handler:', error);
                    }
                    return false;
                }
            },
            {
                selector: '.mblock-copy-paste-toolbar .mblock-clear-clipboard',
                handler: function (e) {
                    e.preventDefault();
                    try {
                        if (typeof MBlockClipboard !== 'undefined') {
                            MBlockClipboard.clear();
                        }
                    } catch (error) {
                        console.error('MBlock: Error in clear clipboard click handler:', error);
                    }
                    return false;
                }
            }
        ];

        // Bind all toolbar events
        toolbarEvents.forEach(({selector, handler}) => {
            const elements = MBlockUtils.dom.findElement(element, selector);
            MBlockUtils.events.bindSafe(elements, 'click', handler);
        });
            
    } catch (error) {
        console.error('MBlock: Error in mblock_init_toolbar:', error);
    }
}

// Export for module systems (if used)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { MBlockClipboard, MBlockOnlineToggle };
}