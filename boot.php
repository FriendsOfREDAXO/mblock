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
    // MBlock ships its own Sortable.js and will only load it when bloecks is NOT available
    // or when bloecks has drag & drop disabled.
    $bloecksAddon = rex_addon::get('bloecks');
    $bloecksDragDropEnabled = false;
    
    if ($bloecksAddon && $bloecksAddon->isAvailable()) {
        // Use bloecks CSS for consistent styling when present
        rex_view::addCssFile($bloecksAddon->getAssetsUrl('css/bloecks.css'));
        
        // Check if drag & drop is enabled in bloecks
        $bloecksDragDropEnabled = (bool) $bloecksAddon->getConfig('enable_drag_drop', false);
    }
    
    // Load our bundled Sortable.js if bloecks is not available OR if drag & drop is disabled in bloecks
    if (!$bloecksAddon || !$bloecksAddon->isAvailable() || !$bloecksDragDropEnabled) {
        rex_view::addJsFile($this->getAssetsUrl('sortable.min.js'));
    }

    // 🔧 MBlock JavaScript Asset Management
    // Load minified version for optimal performance
    rex_view::addJsFile($this->getAssetsUrl('mblock.min.js'));
    
    // Add CSS
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
