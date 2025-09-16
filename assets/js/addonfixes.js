/**
 * MBlock Addon Fixes - Compatibility fixes for GridBlock and CKEditor5
 * 
 * Contains:
 * - GridBlock compatibility utilities
 * - CKEditor5 specific handling
 * - Nested MBlock initialization fixes
 * - GridBlock widget reinitialization
 * 
 * @author joachim doerr  
 * @version 2.0
 */

// GridBlock and CKE5 specific utilities
const MBlockAddonFixes = {
    
    // GridBlock compatibility utilities
    gridblock: {
        /**
         * Clean up duplicate elements in nested MBlocks (GridBlock compatibility)
         * @param {jQuery} container - Container to clean
         */
        cleanupDuplicates(container) {
            try {
                if (!container || !container.length) return;
                
                container.find('.mblock_wrapper').each(function() {
                    const $wrapper = $(this);
                    
                    // Remove duplicate single-add buttons
                    const $addButtons = $wrapper.find('> .mblock-single-add');
                    if ($addButtons.length > 1) {
                        console.log('MBlock: Removing duplicate single-add buttons');
                        $addButtons.slice(1).remove(); // Keep first, remove rest
                    }
                    
                    // Remove single-add button if there are sortitems
                    const $sortItems = $wrapper.find('> .sortitem');
                    if ($sortItems.length > 0 && $addButtons.length > 0) {
                        console.log('MBlock: Removing single-add button (sortitems exist)');
                        $addButtons.remove();
                    }
                });
            } catch (error) {
                console.error('MBlock: Error cleaning up nested duplicates:', error);
            }
        },

        /**
         * Initialize nested MBlocks safely (GridBlock compatibility)
         * @param {jQuery} container - Container with nested MBlocks
         */
        initializeNested(container) {
            try {
                if (!container || !container.length) return;
                
                container.find('.mblock_wrapper').each(function() {
                    const $nestedWrapper = $(this);
                    if ($nestedWrapper.length) {
                        // Clean up first
                        MBlockAddonFixes.gridblock.cleanupDuplicates($nestedWrapper.parent());
                        
                        // Reset initialization flag
                        $nestedWrapper.removeData('mblock_run');
                        
                        // Initialize
                        console.log('MBlock: Safe initialization of nested wrapper');
                        mblock_init($nestedWrapper);
                    }
                });
            } catch (error) {
                console.error('MBlock: Error initializing nested MBlocks:', error);
            }
        },

        /**
         * Check if current context is GridBlock
         * @param {jQuery} container - Container to check
         * @returns {boolean}
         */
        isGridBlock(container) {
            if (!container || !container.length) return false;
            return container.closest('.gridblock_wrapper').length > 0 || container.hasClass('gridblock-item');
        }
    },

    // CKEditor5 specific utilities
    ckeditor: {
        /**
         * Destroy existing CKEditor5 instances before reindexing
         * @param {jQuery} container - Container with editors
         */
        destroyInstances(container) {
            try {
                container.find('.cke5-editor').each(function() {
                    const $textarea = $(this);
                    const editorId = $textarea.attr('id');
                    
                    if (editorId && typeof ckeditors !== 'undefined' && ckeditors[editorId]) {
                        console.log('MBlock: Destroying CKEditor5 instance before reinit:', editorId);
                        try {
                            ckeditors[editorId].destroy();
                            delete ckeditors[editorId];
                        } catch (e) {
                            console.warn('MBlock: Error destroying CKEditor5:', e);
                        }
                    }
                    
                    // Remove CKEditor DOM elements
                    $textarea.next('.ck-editor').remove();
                    $textarea.show(); // Show textarea again
                });
            } catch (error) {
                console.error('MBlock: Error destroying CKEditor5 instances:', error);
            }
        },

        /**
         * Capture CKEditor content for clipboard operations
         * @param {jQuery} container - Container with editors
         * @param {Object} formData - Form data object to populate
         */
        captureContent(container, formData) {
            try {
                // CKEditor content (CKE5)
                container.find('.cke5-editor, textarea[data-cke5-config]').each(function() {
                    const $textarea = $(this);
                    const editorId = $textarea.attr('id');
                    const name = $textarea.attr('name');
                    
                    if (editorId && name) {
                        console.log('MBlock Copy: Processing CKEditor5 field', name, {
                            editorId: editorId,
                            ckeditorsAvailable: typeof ckeditors !== 'undefined',
                            ckeditorInstance: (typeof ckeditors !== 'undefined' && ckeditors[editorId]) ? 'found' : 'not found'
                        });
                        
                        let editorContent = $textarea.val();
                        // Try CKEditor5 first (global ckeditors object)
                        if (editorId && typeof ckeditors !== 'undefined' && ckeditors[editorId]) {
                            try {
                                const editorData = ckeditors[editorId].getData();
                                if (editorData) {
                                    editorContent = editorData;
                                    console.log('MBlock Copy: Got CKEditor5 content:', editorContent.substring(0, 100) + '...');
                                }
                            } catch (e) {
                                console.warn('MBlock Copy: Failed to get CKEditor5 data, using textarea value:', e);
                            }
                        }
                        // Fallback to CKEditor4
                        else if (window.CKEDITOR && window.CKEDITOR.instances[editorId]) {
                            try {
                                const editorData = window.CKEDITOR.instances[editorId].getData();
                                if (editorData) {
                                    editorContent = editorData;
                                    console.log('MBlock Copy: Got CKEditor4 content:', editorContent.substring(0, 100) + '...');
                                }
                            } catch (e) {
                                console.warn('MBlock Copy: Failed to get CKEditor4 data, using textarea value:', e);
                            }
                        }
                        
                        formData[name] = {
                            type: 'ckeditor',
                            value: editorContent,
                            config: {}
                        };
                        
                        // Store important configuration attributes for restoration
                        const configAttrs = ['cke5-config', 'cke5-toolbar', 'cke5-height', 'cke5-readonly'];
                        configAttrs.forEach(attr => {
                            const value = $textarea.attr('data-' + attr);
                            if (value) {
                                formData[name].config[attr] = value;
                            }
                        });
                    }
                });
            } catch (error) {
                console.error('MBlock: Error capturing CKEditor content:', error);
            }
        },

        /**
         * Restore CKEditor content after paste operation
         * @param {jQuery} pastedItem - Pasted item container
         * @param {Object} formData - Form data with editor content
         */
        restoreContent(pastedItem, formData) {
            try {
                Object.keys(formData).forEach(originalName => {
                    const fieldData = formData[originalName];
                    
                    if (!fieldData || typeof fieldData !== 'object') return;
                    if (fieldData.type !== 'ckeditor') return; // Only CKEditor fields
                    
                    // Find field by smart matching
                    let $field = pastedItem.find(`[name="${originalName}"], [name="mblock_new_${originalName}"]`);
                    
                    if (!$field.length || !fieldData.value) return;
                    
                    console.log('MBlock Restore: Processing CKEditor field', originalName, 'with content:', fieldData.value.substring(0, 100) + '...');
                    
                    // Always set the textarea value first
                    $field.val(fieldData.value);
                    
                    // Store the data for later initialization
                    const editorId = $field.attr('id');
                    if (editorId) {
                        console.log('MBlock Restore: Setting up restoration for editor', editorId);
                        
                        // Enhanced restoration with multiple attempts and immediate check
                        const restoreCKE5Content = function(attempt = 0) {
                            const maxAttempts = 15; // Reduced from 25 to 15
                            
                            console.log('MBlock Restore: Attempt', attempt + 1, 'for editor', editorId);
                            
                            // Try CKEditor5 first
                            if (typeof ckeditors !== 'undefined' && ckeditors[editorId]) {
                                try {
                                    ckeditors[editorId].setData(fieldData.value);
                                    console.log('✅ MBlock Restore: CKEditor5 content restored for', editorId);
                                    return;
                                } catch (e) {
                                    console.warn('MBlock Restore: Failed to restore CKEditor5 content:', e);
                                }
                            }
                            // Fallback to CKEditor4
                            else if (window.CKEDITOR && window.CKEDITOR.instances[editorId]) {
                                try {
                                    window.CKEDITOR.instances[editorId].setData(fieldData.value);
                                    console.log('✅ MBlock Restore: CKEditor4 content restored for', editorId);
                                    return;
                                } catch (e) {
                                    console.warn('MBlock Restore: Failed to restore CKEditor4 content:', e);
                                }
                            }
                            
                            // If editor is not ready yet and we haven't exceeded max attempts
                            if (attempt < maxAttempts) {
                                setTimeout(() => restoreCKE5Content(attempt + 1), 300); // Increased delay
                            } else {
                                console.warn('❌ MBlock Restore: Timeout restoring content for', editorId, 'after', maxAttempts, 'attempts');
                            }
                        };
                        
                        // Start the restoration process with initial delay
                        setTimeout(() => restoreCKE5Content(0), 100);
                    }
                });
            } catch (error) {
                console.error('MBlock: Error restoring CKEditor content:', error);
            }
        }
    }
};

// CKEditor5 Content Restoration after rex:ready
$(document).on('rex:ready', function(e, container) {
    // Restore CKEditor5 content for pasted items
    container.find('.cke5-editor[data-cke5-restore-content]').each(function() {
        const $editor = $(this);
        const editorId = $editor.attr('id');
        const restoreContent = $editor.attr('data-cke5-restore-content');
        
        if (editorId && restoreContent) {
            console.log('MBlock: Attempting to restore CKEditor5 content for', editorId);
            
            // Wait for CKEditor5 to be fully initialized
            const checkAndRestore = function(attempts = 0) {
                if (attempts > 20) { // Max 4 seconds (20 * 200ms)
                    console.warn('MBlock: Timeout restoring CKEditor5 content for', editorId);
                    $editor.removeAttr('data-cke5-restore-content');
                    return;
                }
                
                if (typeof ckeditors !== 'undefined' && ckeditors[editorId]) {
                    try {
                        ckeditors[editorId].setData(restoreContent);
                        $editor.removeAttr('data-cke5-restore-content');
                        console.log('MBlock: Successfully restored CKEditor5 content for', editorId);
                        return;
                    } catch (e) {
                        console.warn('MBlock: Error setting CKEditor5 data:', e);
                    }
                }
                
                // Try again after a short delay
                setTimeout(() => checkAndRestore(attempts + 1), 200);
            };
            
            // Start checking after a small initial delay
            setTimeout(() => checkAndRestore(), 300);
        }
    });
});

// Export for module systems (if used)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { MBlockAddonFixes };
}