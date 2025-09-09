<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */



if (rex::isBackend() && is_object(rex::getUser())) {

    // register extensions
    // alfred post post
    rex_extension::register('REX_FORM_SAVED', function (rex_extension_point $params) {
        /** @var rex_form|null $form */
        $form = ($params->hasParam('form')) ? $params->getParam('form') : null;
        if ($form instanceof mblock_rex_form)
            return \FriendsOfRedaxo\MBlock\Processor\MBlockRexFormProcessor::postPostSaveAction($params->getSubject(), $form, $_POST); // execute post post
        else
            return $params->getSubject();
    });

    // Prefer Bloecks styles if available, but do NOT require bloecks for functionality.
    // MBlock ships its own Sortable.js and will only load it when bloecks is NOT available.
    $bloecksAddon = rex_addon::get('bloecks');
    if ($bloecksAddon && $bloecksAddon->isAvailable()) {
        // Use bloecks CSS for consistent styling when present. Do NOT auto-load bloecks' Sortable
        // to avoid duplicate global Sortable registrations.
        rex_view::addCssFile($bloecksAddon->getAssetsUrl('css/bloecks.css'));
    } else {
        // Bloecks not available — register our bundled Sortable implementation.
        rex_view::addJsFile($this->getAssetsUrl('sortable.min.js'));
    }

    // 🔧 MBlock JavaScript Asset Management
    // 
    // Options:
    // - 'auto'     : Auto-detect based on environment (recommended)
    // - 'modular'  : Always use modular files (debugging)
    // - 'combined' : Always use combined file (development)
    // - 'prod'     : Always use minified file (production)
    //
    $assetMode = 'auto'; // Change to override auto-detection
    
    // Auto-detection logic
    if ($assetMode === 'auto') {
        // Use combined file approach for simplicity and compatibility
        $useMinified = (
            !rex::isDebugMode() &&                    // Debug mode disabled
            !rex_addon::get('debug')->isAvailable()   // Debug addon not active
        );
        $debugInfo = $useMinified ? 'Production (minified)' : 'Development (combined)';
    } else {
        $useMinified = ($assetMode === 'prod');
        $useModular = ($assetMode === 'modular');
        $debugInfo = $assetMode;
    }
    
    if (isset($useModular) && $useModular) {
        // 📦 Load modular JavaScript files (Advanced debugging)
        rex_view::addJsFile($this->getAssetsUrl('mblock-core.js'));
        rex_view::addJsFile($this->getAssetsUrl('mblock-management.js'));
        rex_view::addJsFile($this->getAssetsUrl('mblock-features.js'));
        $debugInfo = 'Modular (3 files)';
    } else {
        // 📦 Load combined/minified file (Standard approach)
        $jsFile = $useMinified ? 'mblock.min.js' : 'mblock.js';
        rex_view::addJsFile($this->getAssetsUrl($jsFile));
        $debugInfo .= ' (' . $jsFile . ')';
    }
    
    // Add CSS
    rex_view::addCssFile($this->getAssetsUrl('mblock.css'));
    
    // Add debug info for developers
    if (rex::isDebugMode()) {
        rex_view::setJsProperty('mblock_asset_mode', $debugInfo);
    }
    
    // Add custom template CSS if selected and available
    $selectedTemplate = $this->getConfig('mblock_theme', 'default_theme');
    if ($selectedTemplate !== 'default_theme') {
        $templateCSSUrl = \FriendsOfRedaxo\MBlock\Utils\TemplateManager::getTemplateCSSUrl($selectedTemplate);
        if ($templateCSSUrl) {
            rex_view::addCssFile($templateCSSUrl);
        }
    }
    
    // 🌍 Make toast message translations available to frontend
    rex_view::setJsProperty('mblock_i18n', [
        'copy_success' => $this->i18n('mblock_toast_copy_success'),
        'paste_success' => $this->i18n('mblock_toast_paste_success'), 
        'clipboard_empty' => $this->i18n('mblock_toast_clipboard_empty'),
        'module_type_mismatch' => $this->i18n('mblock_toast_module_type_mismatch')
    ]);
}

// Sichere Session-Reset mit optimiertem MBlockSessionHelper
\FriendsOfRedaxo\MBlock\Utils\MBlockSessionHelper::resetCountIfNeeded();
