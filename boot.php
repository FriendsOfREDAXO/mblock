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

    // Use Sortable.js from bloecks addon (required dependency)
    $bloecksAddon = rex_addon::get('bloecks');
    if ($bloecksAddon && $bloecksAddon->isAvailable()) {
        // Use bloecks Sortable.js
        rex_view::addJsFile($bloecksAddon->getAssetsUrl('js/sortable.min.js'));
        // Add bloecks CSS for consistent styling
        rex_view::addCssFile($bloecksAddon->getAssetsUrl('css/bloecks.css'));
    }
    // Note: bloecks Addon is required for MBlock functionality

    // 🔧 Development/Production Asset Management
    // 
    // Options:
    // - 'auto'  : Auto-detect based on environment (recommended)
    // - 'dev'   : Always use mblock.js (development/debugging)  
    // - 'prod'  : Always use mblock.min.js (production/performance)
    //
    $assetMode = 'auto'; // Change this to 'dev' or 'prod' to override
    
    // Auto-detection logic
    if ($assetMode === 'auto') {
        // Use minified in production, development version otherwise
        $isProduction = (
            !rex::isDebugMode() &&                    // Debug mode disabled
            !rex_addon::get('debug')->isAvailable()   // Debug addon not active
        );
        $useMinified = $isProduction;
    } else {
        $useMinified = ($assetMode === 'prod');
    }
    
    $jsFile = $useMinified ? 'mblock.min.js' : 'mblock.js';
    $debugInfo = $useMinified ? 'Production (minified)' : 'Development (source)';
    
    // Add debug comment for developers
    if (rex::isDebugMode()) {
        rex_view::setJsProperty('mblock_asset_mode', $debugInfo);
    }
    
    // Always add our assets
    rex_view::addJsFile($this->getAssetsUrl($jsFile));
    rex_view::addCssFile($this->getAssetsUrl('mblock.css'));
    
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
