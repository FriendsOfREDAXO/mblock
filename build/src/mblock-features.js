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
            if (this.data) {}
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
                this.data.savedAt = new Date().toISOString();
                storage.setItem(this.storageKey, JSON.stringify(this.data));
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
                    this.data = JSON.parse(stored);
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
            this._sessionId = Date.now().toString() + Math.random().toString(36).substring(2, 11);
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
                targetElement.before(warningHtml);
            } else {
                $('body').prepend(warningHtml);
            }

        } catch (error) {
            console.error('MBlock: Fehler beim Anzeigen der Modultyp-Warnung:', error);
            // Fallback: show minimal notification div
            $('body').prepend('<div style="background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 4px; z-index: 9999;">Das kopierte Element kann hier nicht eingefügt werden (anderer Modul-Typ).</div>');
        }
    },

    // Get module type/name from wrapper or form context
    getModuleType: function(wrapper) {
        try {

            // 1. Check form for hidden input with module_id (REDAXO standard!)
            const form = wrapper.closest('form');
            if (form.length) {
                const moduleIdInput = form.find('input[name="module_id"]').first();
                if (moduleIdInput.length) {
                    return 'module_' + moduleIdInput.val();
                }
            }

            // 2. Fallback: Check in wrapper for other patterns
            const moduleInputWrapper = wrapper.find('input[name="module_id"]').first();
            if (moduleInputWrapper.length) {
                return 'module_' + moduleInputWrapper.val();
            }

            // 3. Fallback: andere module_id patterns
            const moduleInputFallback = wrapper.find('input[name*="module_id"], input[name*="module_name"]').first();
            if (moduleInputFallback.length) {
                const name = moduleInputFallback.attr('name');
                const value = moduleInputFallback.val();
                return name + '_' + value;
            }

            // 4. Check for form action or parent context
            if (form.length) {
                const action = form.attr('action');
                if (action) {
                    const urlParams = new URLSearchParams(action.split('?')[1]);
                    const moduleId = urlParams.get('module_id');
                    if (moduleId) {
                        return 'module_' + moduleId;
                    }
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
                return 'parent_' + parentWithId.attr('id');
            }

            // 6. Last resort: use URL parameters (nur innerhalb des gleichen Artikels!)
            const urlParams = new URLSearchParams(window.location.search);
            const moduleId = urlParams.get('module_id') || urlParams.get('article_id');
            if (moduleId) {
                return 'url_module_' + moduleId;
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
                console.warn('MBlock: Invalid item passed to copy');
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
                moduleType: moduleType,
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
                const $field = $(this);
                const name = $field.attr('name');
                const type = $field.attr('type');
                if (name) {
                    if (type === 'checkbox' || type === 'radio') {
                        formData[name] = {
                            type: 'checkbox_radio',
                            checked: $field.is(':checked'),
                            value: $field.val()
                        };
                    } else if ($field.is('select')) {
                        formData[name] = {
                            type: 'select',
                            value: $field.val(),
                            multiple: $field.prop('multiple')
                        };
                    } else {
                        formData[name] = {
                            type: 'input',
                            value: $field.val()
                        };
                    }
                }
            });

            // CKEditor content (CKE5)
            item.find('.cke5-editor').each(function() {
                const $editor = $(this);
                const editorId = $editor.attr('id');
                if (editorId && window.CKEDITOR && window.CKEDITOR.instances[editorId]) {
                    const content = window.CKEDITOR.instances[editorId].getData();
                    formData[editorId] = {
                        type: 'ckeditor',
                        value: content
                    };
                }
            });

            // REX_LINK widgets (comprehensive handling)
            item.find('input[id^="REX_LINK_"]').each(function() {
                const $linkInput = $(this);
                const id = $linkInput.attr('id');
                const value = $linkInput.val();
                formData[id] = {
                    type: 'rex_link',
                    value: value
                };

                // Also capture display field if it exists
                const displayId = id.replace('REX_LINK_', 'REX_LINK_NAME_');
                const $displayField = $('#' + displayId);
                if ($displayField.length) {
                    formData[displayId] = {
                        type: 'rex_link_display',
                        value: $displayField.val()
                    };
                }
            });

            // REX_MEDIA widgets
            item.find('input[id^="REX_MEDIA_"]').each(function() {
                const $mediaInput = $(this);
                const id = $mediaInput.attr('id');
                const value = $mediaInput.val();
                formData[id] = {
                    type: 'rex_media',
                    value: value
                };
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
                mblock_show_message(mblock_get_text('mblock_toast_clipboard_empty', 'Keine Daten in der Zwischenablage'), 'warning', 3000);
                return false;
            }

            // Check module type compatibility
            const currentWrapper = element.closest('.mblock_wrapper');
            const currentModuleType = this.getModuleType(currentWrapper);
            const clipboardModuleType = this.data.moduleType || 'unknown_module';
            if (currentModuleType !== clipboardModuleType) {
                this.showModuleTypeMismatchWarning(currentModuleType, clipboardModuleType);
                return false;
            }


            // Create element from clipboard
            const pastedItem = $(this.data.html);

            // Clean up IDs and names to avoid conflicts
            this.cleanupPastedItem(pastedItem);

            // Insert item
            if (afterItem && afterItem.length) {
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

            // Destroy existing CKEditor5 instances before reindexing
            pastedItem.find('.cke5-editor').each(function() {
                const $editor = $(this);
                const editorId = $editor.attr('id');
                if (editorId && window.CKEDITOR && window.CKEDITOR.instances[editorId]) {
                    window.CKEDITOR.instances[editorId].destroy();
                }
            });

            // Restore NON-CKEditor form values first
            if (this.data.formData) {
                this.restoreNonCKEditorFormData(pastedItem, this.data.formData);
            }

            // Reinitialize sortable
            mblock_init_sort(element);

            // Trigger rex:ready event for full reinitialization (including CKEditor5)
            pastedItem.trigger('rex:ready', [pastedItem]);

            // CRITICAL: Handle nested MBlock wrappers inside pasted content (GridBlock compatibility)
            try {
                pastedItem.find('.mblock_wrapper').each(function() {
                    const $nestedWrapper = $(this);
                    if (!$nestedWrapper.data('mblock_initialized')) {
                        mblock_init($nestedWrapper);
                        $nestedWrapper.data('mblock_initialized', true);
                    }
                });
            } catch (e) {
                console.warn('MBlock: Error initializing nested MBlocks in pasted content:', e);
            }

            // Wait for CKEditor5 initialization, then restore content
            if (this.data.formData) {
                setTimeout(() => {
                    this.restoreCKEditorFormData(pastedItem, this.data.formData);
                }, 100);
            }

            // Component reinitialization
            setTimeout(() => {
                // Reinitialize selectpickers
                if (typeof $.fn.selectpicker === 'function') {
                    pastedItem.find('select.selectpicker').selectpicker('refresh');
                }

                // Reinitialize chosen
                if (typeof $.fn.chosen === 'function') {
                    pastedItem.find('select').chosen('destroy').chosen();
                }

                // Trigger change events
                pastedItem.find('input, select, textarea').trigger('change');
            }, 50);

            // Scroll to pasted item
            setTimeout(() => {
                if (pastedItem && pastedItem.length && pastedItem.is(':visible')) {
                    mblock_smooth_scroll_to_element(pastedItem.get(0));
                }
            }, 100);

            // ✨ Add glow effect to pasted item using utility
            setTimeout(() => {
                MBlockUtils.animation.flashEffect(pastedItem);
                mblock_show_message(mblock_get_text('mblock_toast_paste_success', 'Block erfolgreich eingefügt!'), 'success', 3000);
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
                const $field = $(this);
                // Remove IDs to avoid conflicts
                $field.removeAttr('id');
                // Clean names
                const name = $field.attr('name');
                if (name) {
                    const cleanName = name.replace(/_unique_\w+/, '');
                    $field.attr('name', cleanName);
                }
            });

            // Clean IDs that might cause conflicts
            item.find('[id]').each(function() {
                const $element = $(this);
                const id = $element.attr('id');
                if (id && (id.includes('REX_') || id.includes('mblock'))) {
                    $element.removeAttr('id');
                }
            });

        } catch (error) {
            console.error('MBlock: Fehler beim Bereinigen des eingefügten Items:', error);
        }
    },
    restoreNonCKEditorFormData: function(pastedItem, formData) {
        try {
            Object.keys(formData).forEach(originalName => {
                const fieldData = formData[originalName];
                if (fieldData.type !== 'ckeditor') {
                    this.restoreFieldData(pastedItem.find('[name="' + originalName + '"]'), fieldData, pastedItem, originalName);
                }
            });
        } catch (error) {
            console.error('MBlock: Fehler beim Wiederherstellen der Nicht-CKEditor-Daten:', error);
        }
    },
    restoreCKEditorFormData: function(pastedItem, formData) {
        try {
            Object.keys(formData).forEach(originalName => {
                const fieldData = formData[originalName];
                if (fieldData.type === 'ckeditor') {
                    const $editor = pastedItem.find('#' + originalName);
                    if ($editor.length && window.CKEDITOR && window.CKEDITOR.instances[originalName]) {
                        window.CKEDITOR.instances[originalName].setData(fieldData.value);
                    }
                }
            });
        } catch (error) {
            console.error('MBlock: Fehler beim Wiederherstellen der CKEditor-Daten:', error);
        }
    },
    restoreFieldData: function($field, fieldData, pastedItem, originalName) {
        // Handle different field types
        switch (fieldData.type) {
            case 'checkbox_radio':
                if (fieldData.checked) {
                    $field.prop('checked', true);
                }
                break;
            case 'select':
                $field.val(fieldData.value);
                if (typeof $.fn.selectpicker === 'function') {
                    $field.selectpicker('refresh');
                }
                break;
            case 'rex_link':
                $field.val(fieldData.value);
                // Trigger article name fetch if value exists
                if (fieldData.value) {
                    const displayId = originalName.replace('REX_LINK_', 'REX_LINK_NAME_');
                    const $displayField = pastedItem.find('#' + displayId);
                    if ($displayField.length) {
                        mblock_fetch_article_name(fieldData.value, $displayField);
                    }
                }
                break;
            case 'rex_media':
                $field.val(fieldData.value);
                break;
            default:
                // Handle regular input fields
                if (fieldData.value !== undefined) {
                    $field.val(fieldData.value);
                }
                break;
        }
    },
    showCopiedState: function(item) {
        // Visual feedback using centralized animation utility
        MBlockUtils.animation.addGlowEffect(item, 'mblock-copy-glow', 1000);

        // Centralized copy feedback
        const copyMessage = '📋 ' + mblock_get_text('mblock_toast_copy_success', 'Block erfolgreich kopiert!');
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
            // Prüfe Modulkompatibilität für alle sichtbaren MBlock-Wrapper
            $('.mblock_wrapper').each((index, wrapperElement) => {
                const $wrapper = $(wrapperElement);
                const wrapperModuleType = this.getModuleType($wrapper);
                const clipboardModuleType = this.data.moduleType || 'unknown_module';
                const isCompatible = wrapperModuleType === clipboardModuleType;

                // Update paste buttons in this wrapper
                $wrapper.find('.mblock-paste-btn').each(function() {
                    const $btn = $(this);
                    $btn.toggleClass('disabled', !isCompatible);
                    $btn.prop('disabled', !isCompatible);
                    $btn.attr('title', isCompatible ?
                        'Block einfügen' :
                        'Block kann nicht eingefügt werden (anderer Modul-Typ)');
                });
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
                const $wrapper = $select.closest('.bootstrap-select');
                if ($wrapper.length) {
                    // Move select out of wrapper and remove wrapper
                    $wrapper.before($select);
                    $wrapper.remove();
                }

                // Remove selectpicker classes and data
                $select.removeClass('selectpicker');
                $select.removeAttr('data-live-search');
                $select.removeAttr('data-size');
                $select.removeAttr('data-style');
                $select.show(); // Make sure it's visible
            });

            // Clean up any remaining empty bootstrap-select containers
            container.find('.bootstrap-select').each(function() {
                const $wrapper = $(this);
                if ($wrapper.find('select').length === 0) {
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
                console.warn('MBlock: Invalid item passed to toggle');
                return false;
            }

            const isOnline = !item.hasClass('mblock-offline');
            const $toggleBtn = item.find('.mblock-online-toggle');
            const $icon = $toggleBtn.find('i');
            if (isOnline) {
                // Set offline
                item.addClass('mblock-offline');
                $toggleBtn.attr('title', 'Set online');
                if ($icon.length) {
                    $icon.removeClass('rex-icon-online').addClass('rex-icon-offline');
                }
                $toggleBtn.find('.toggle-text').text('Offline');
                this.setOfflineState(item, true);
            } else {
                // Set online
                item.removeClass('mblock-offline');
                $toggleBtn.attr('title', 'Set offline');
                if ($icon.length) {
                    $icon.removeClass('rex-icon-offline').addClass('rex-icon-online');
                }
                $toggleBtn.find('.toggle-text').text('Online');
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
                $offlineInput.val(isOffline ? '1' : '0');
            } else {
                console.warn('MBlock: No mblock_offline input found in item');
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
                const $offlineInput = $item.find('input[name*="mblock_offline"]');
                const $toggleBtn = $item.find('.mblock-online-toggle');
                if ($offlineInput.length && $toggleBtn.length) {
                    const isOffline = $offlineInput.val() === '1';
                    if (isOffline) {
                        $item.addClass('mblock-offline');
                        $toggleBtn.attr('title', 'Set online');
                        $toggleBtn.find('i').removeClass('rex-icon-online').addClass('rex-icon-offline');
                        $toggleBtn.find('.toggle-text').text('Offline');
                    } else {
                        $item.removeClass('mblock-offline');
                        $toggleBtn.attr('title', 'Set offline');
                        $toggleBtn.find('i').removeClass('rex-icon-offline').addClass('rex-icon-online');
                        $toggleBtn.find('.toggle-text').text('Online');
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
                console.warn('MBlock: Invalid parameters for toggleAutoDetected');
                return false;
            }

            // Get current offline status from button data attribute
            const currentIsOffline = button.attr('data-offline') === '1';
            const newIsOffline = !currentIsOffline;

            // Find the corresponding mblock_offline input field
            const $offlineInput = item.find('input[name*="mblock_offline"]');
            if (!$offlineInput.length) {
                console.warn('MBlock: No mblock_offline input found for auto-detected toggle');
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
                $icon.removeClass('rex-icon-online rex-icon-offline').addClass(iconClass);
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
            console.warn('MBlock: Invalid container passed to reinitialize_redaxo_widgets');
            return false;
        }

        // Get context information
        const mblockIndex = parseInt(container.attr('data-mblock_index')) || 1;
        const mblockWrapper = container.closest('.mblock_wrapper');
        const mblockCount = mblockWrapper.find('.sortitem').length || 1;
        const isGridBlock = container.closest('.gridblock_wrapper').length > 0 || container.hasClass('gridblock-item');// 🔧 REX MEDIA widgets - Enhanced for GridBlock compatibility
        container.find('input[id^="REX_MEDIA_"]').each(function() {
            const $mediaInput = $(this);
            const originalId = $mediaInput.attr('id');
            const newId = originalId.replace(/_\d+$/, '_' + mblockIndex);
            $mediaInput.attr('id', newId);

            // Update associated button if it exists
            const buttonSelector = 'a[href*="media_id=' + originalId.replace('REX_MEDIA_', '') + '"]';
            container.find(buttonSelector).each(function() {
                const $button = $(this);
                const href = $button.attr('href');
                if (href) {
                    const newHref = href.replace(/media_id=\d+/, 'media_id=' + mblockIndex);
                    $button.attr('href', newHref);
                }
            });
        });

        // 🔧 REX LINK widgets - Enhanced for GridBlock compatibility
        container.find('input[id^="REX_LINK_"]').each(function() {
            const $linkInput = $(this);
            const originalId = $linkInput.attr('id');
            const newId = originalId.replace(/_\d+$/, '_' + mblockIndex);
            $linkInput.attr('id', newId);

            // Update associated button if it exists
            const buttonSelector = 'a[href*="link_id=' + originalId.replace('REX_LINK_', '') + '"]';
            container.find(buttonSelector).each(function() {
                const $button = $(this);
                const href = $button.attr('href');
                if (href) {
                    const newHref = href.replace(/link_id=\d+/, 'link_id=' + mblockIndex);
                    $button.attr('href', newHref);
                }
            });
        });

        // 🔧 REX LINKLIST widgets
        container.find('input[id^="REX_LINKLIST_"]').each(function() {
            const $linklistInput = $(this);
            const originalId = $linklistInput.attr('id');
            const newId = originalId.replace(/_\d+$/, '_' + mblockIndex);
            $linklistInput.attr('id', newId);
        });

        // 🔧 GridBlock-specific: Trigger rex:ready event for custom widgets
        if (isGridBlock) {
            container.trigger('rex:ready', [container]);
        }

        // 🔧 General widget reinitialization
        setTimeout(() => {
            // Reinitialize any custom widgets that might need it
            if (typeof $.fn.selectpicker === 'function') {
                container.find('select.selectpicker').selectpicker('refresh');
            }

            if (typeof $.fn.chosen === 'function') {
                container.find('select').chosen('destroy').chosen();
            }
        }, 50);return true;

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
        $displayField.val(window.mblock_article_cache[articleId]);return;
    }

    // AJAX-Request an REDAXO Structure Linkmap
    const currentClang = $('input[name="clang"]').val() || 1;
    const params = new URLSearchParams({
        page: 'structure/linkmap',
        opener_input_field: 'temp',
        article_id: articleId,
        clang: currentClang
    });
    const ajaxUrl = rex.backend + '?' + params.toString();
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
                if (match && match[1]) {
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
            $displayField.trigger('change');},
        error: function() {
            // Fallback bei AJAX-Fehler
            const fallbackName = 'Artikel [' + articleId + ']';
            window.mblock_article_cache[articleId] = fallbackName;
            $displayField.val(fallbackName);}
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
            mblock_initialize_empty_rex_link_fields();
        }, 100);
    });

    // Alternative für verschiedene Tab-Systeme (Bootstrap 3/4/5 + MForm)
    $(document).on('click', '.nav-tabs a, .nav-pills a, [data-toggle="tab"], [data-bs-toggle="tab"], .mform-tabs a', function() {
        setTimeout(function() {
            mblock_initialize_empty_rex_link_fields();
        }, 200);
    });

    // 🔧 MForm-spezifische Tab-Events
    $(document).on('mform:tabChanged mform:tabShow', function(e) {
        setTimeout(function() {
            mblock_initialize_empty_rex_link_fields();
        }, 150);
    });
});

// 🔧 Befülle alle leeren REX_LINK Display-Felder mit Artikel-Namen
function mblock_initialize_empty_rex_link_fields() {
    try {let foundFields = 0;
        let processedFields = 0;

        // Finde alle REX_LINK Hidden-Inputs mit Werten (auch in versteckten Tabs)
        $('input[id^="REX_LINK_"]').each(function() {
            const $linkInput = $(this);
            const linkId = $linkInput.attr('id');
            const articleId = $linkInput.val();
            if (articleId && articleId !== '0') {
                foundFields++;

                // Finde zugehöriges Display-Feld
                const displayId = linkId.replace('REX_LINK_', 'REX_LINK_NAME_');
                const $displayField = $('#' + displayId);
                if ($displayField.length && !$displayField.val()) {
                    processedFields++;
                    mblock_fetch_article_name(articleId, $displayField);
                }
            }
        });} catch (error) {
        console.error('MBlock: Fehler beim Initialisieren der REX_LINK Display-Felder:', error);
    }
}

// 🔧 CKEditor5 Content Restoration after rex:ready
$(document).on('rex:ready', function(e, container) {
    // Restore CKEditor5 content for pasted items
    container.find('.cke5-editor[data-cke5-restore-content]').each(function() {
        const $editor = $(this);
        const editorId = $editor.attr('id');
        const restoreContent = $editor.attr('data-cke5-restore-content');
        if (editorId && restoreContent) {
            if (window.CKEDITOR && window.CKEDITOR.instances[editorId]) {
                window.CKEDITOR.instances[editorId].setData(restoreContent);
            }
            $editor.removeAttr('data-cke5-restore-content');
        }
    });
});

// Export for module systems (if used)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { MBlockClipboard, MBlockOnlineToggle };
}
