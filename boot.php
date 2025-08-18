<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

if (rex::isBackend() && is_object(rex::getUser())) {

    // check theme css exists
    \FriendsOfRedaxo\MBlock\Utils\MBlockThemeHelper::themeBootCheck($this->getConfig('mblock_theme'));

    // use theme helper class
    if (\FriendsOfRedaxo\MBlock\Utils\MBlockThemeHelper::getCssAssets($this->getConfig('mblock_theme'))) {
        // foreach all css files
        foreach (\FriendsOfRedaxo\MBlock\Utils\MBlockThemeHelper::getCssAssets($this->getConfig('mblock_theme')) as $css) {
            // add assets css file
            rex_view::addCssFile($this->getAssetsUrl($css));
        }
    }

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

    // Use Sortable.js from bloecks addon if available, otherwise fallback to bundled version
    $bloecksAddon = rex_addon::get('bloecks');
    if ($bloecksAddon && $bloecksAddon->isAvailable()) {
        // Use bloecks Sortable.js
        rex_view::addJsFile($bloecksAddon->getAssetsUrl('js/sortable.min.js'));
        // Add bloecks CSS for consistent styling
        rex_view::addCssFile($bloecksAddon->getAssetsUrl('css/bloecks.css'));
    } else {
        // Fallback to bundled sortable
        rex_view::addJsFile($this->getAssetsUrl('mblock_sortable.min.js'));
    }

    // Always add our own assets
    rex_view::addJsFile($this->getAssetsUrl('mblock_smooth_scroll.min.js'));
    rex_view::addJsFile($this->getAssetsUrl('mblock.js'));
    rex_view::addCssFile($this->getAssetsUrl('mblock.css'));
}

// Sichere Session-Reset mit optimiertem MBlockSessionHelper
\FriendsOfRedaxo\MBlock\Utils\MBlockSessionHelper::resetCountIfNeeded();
