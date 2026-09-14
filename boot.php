<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

/** @var rex_addon $this */

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

    // Sortable.js handling:
    // - Prefer the Sortable.js from the `bloecks` addon if it is available AND drag&drop is enabled there
    // - Otherwise fall back to the bundled Sortable placed in this addon's assets folder
    $bloecksAddon = rex_addon::get('bloecks');
    if ($bloecksAddon->isAvailable() && $bloecksAddon->getConfig('drag_drop', true)) {
        // Use bloecks Sortable.js when bloecks is present and d&d is enabled
        rex_view::addJsFile($bloecksAddon->getAssetsUrl('js/sortable.min.js'));
        rex_view::setJsProperty('mblock_sortable_source', 'bloecks');
    } else {
        // fall back to bundled Sortable (inside this addon's assets)
        rex_view::addJsFile($this->getAssetsUrl('sortable.min.js'));
        rex_view::setJsProperty('mblock_sortable_source', 'mblock');
    }

    // Minifiziertes JavaScript, sobald weder Debug-Modus noch Debug-Addon aktiv sind
    $useMinified = !rex::isDebugMode() && !rex_addon::get('debug')->isAvailable();
    $jsFile = $useMinified ? 'mblock.min.js' : 'mblock.js';
    $debugInfo = $useMinified ? 'Production (minified)' : 'Development (source)';
    
    // Expose the local sortable asset URL so the client can dynamically load it
    // when a runtime fallback is needed (prevents duplicate loads when other addons
    // already provide Sortable.js).
    rex_view::setJsProperty('mblock_sortable_local_url', $this->getAssetsUrl('sortable.min.js'));

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
        'module_type_mismatch' => $this->i18n('mblock_toast_module_type_mismatch'),
        'confirm_title' => $this->i18n('mblock_confirm_title'),
        'confirm_cancel' => $this->i18n('mblock_confirm_cancel'),
        'confirm_ok' => $this->i18n('mblock_confirm_ok'),
    ]);
}

// Alte globale Klassennamen (MBlock 3.x) als Aliase auf die Namespace-Klassen, damit
// bestehender Projektcode weiterlaeuft. Ersetzt die frueheren Shim-Dateien in lib/deprecated.
foreach ([
    'MBlock' => \FriendsOfRedaxo\MBlock\MBlock::class,
    'MBlockBootstrapReplacer' => \FriendsOfRedaxo\MBlock\Replacer\MBlockBootstrapReplacer::class,
    'MBlockCheckboxReplacer' => \FriendsOfRedaxo\MBlock\Replacer\MBlockCheckboxReplacer::class,
    'MBlockCountReplacer' => \FriendsOfRedaxo\MBlock\Replacer\MBlockCountReplacer::class,
    'MBlockSystemButtonReplacer' => \FriendsOfRedaxo\MBlock\Replacer\MBlockSystemButtonReplacer::class,
    'MBlockValueReplacer' => \FriendsOfRedaxo\MBlock\Replacer\MBlockValueReplacer::class,
    'MBlockElement' => \FriendsOfRedaxo\MBlock\DTO\MBlockElement::class,
    'MBlockItem' => \FriendsOfRedaxo\MBlock\DTO\MBlockItem::class,
    'MBlockFormItemDecorator' => \FriendsOfRedaxo\MBlock\Decorator\MBlockFormItemDecorator::class,
    'MBlockJsonHelper' => \FriendsOfRedaxo\MBlock\Utils\MBlockJsonHelper::class,
    'MBlockPageHelper' => \FriendsOfRedaxo\MBlock\Utils\MBlockPageHelper::class,
    'MBlockSessionHelper' => \FriendsOfRedaxo\MBlock\Utils\MBlockSessionHelper::class,
    'MBlockSettingsHelper' => \FriendsOfRedaxo\MBlock\Utils\MBlockSettingsHelper::class,
    'MBlockParser' => \FriendsOfRedaxo\MBlock\Parser\MBlockParser::class,
    'MBlockRexFormProcessor' => \FriendsOfRedaxo\MBlock\Processor\MBlockRexFormProcessor::class,
    'mblock_rex_form' => \FriendsOfRedaxo\MBlock\Processor\mblock_rex_form::class,
    'MBlockTemplateFileProvider' => \FriendsOfRedaxo\MBlock\Provider\MBlockTemplateFileProvider::class,
    'MBlockValueHandler' => \FriendsOfRedaxo\MBlock\Handler\MBlockValueHandler::class,
] as $legacyClass => $class) {
    if (!class_exists($legacyClass, false)) {
        class_alias($class, $legacyClass);
    }
}

// Sichere Session-Reset mit optimiertem MBlockSessionHelper
\FriendsOfRedaxo\MBlock\Utils\MBlockSessionHelper::resetCountIfNeeded();
