<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

if (rex::isBackend() && is_object(rex::getUser())) {
    // check theme css is exists
    MBlockThemeHelper::themeBootCheck(rex_addon::get('mblock')->getConfig('mblock_theme'));

    // use theme helper class
    if (MBlockThemeHelper::getCssAssets(rex_addon::get('mblock')->getConfig('mblock_theme'))) {
        // foreach all css files
        foreach (MBlockThemeHelper::getCssAssets(rex_addon::get('mblock')->getConfig('mblock_theme')) as $css) {
            // add assets css file
            rex_view::addCssFile($this->getAssetsUrl($css));
        }
    }
    rex_view::addJsFile($this->getAssetsUrl('mblock_sortable.min.js'));
    rex_view::addJsFile($this->getAssetsUrl('mblock_smooth_scroll.min.js'));
    rex_view::addJsFile($this->getAssetsUrl('mblock.min.js'));
    rex_view::addCssFile($this->getAssetsUrl('mblock.min.css'));
}