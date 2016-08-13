<?php

if (rex::isBackend() && is_object(rex::getUser())) {
//    echo '<pre>';
//    print_r(rex_addon::get('jblock')->getConfig('jblock_theme'));
//    echo '</pre>';
    // check theme css is exists
    JBlockThemeHelper::themeBootCheck(rex_addon::get('jblock')->getConfig('jblock_theme'));

    // use theme helper class
    if (JBlockThemeHelper::getCssAssets(rex_addon::get('jblock')->getConfig('jblock_theme'))) {
        // foreach all css files
        foreach (JBlockThemeHelper::getCssAssets(rex_addon::get('jblock')->getConfig('jblock_theme')) as $css) {
            // add assets css file
            rex_view::addCssFile($this->getAssetsUrl($css));
        }
    }
    rex_view::addJsFile($this->getAssetsUrl('jquery.fn.sortable.min.js'));
    rex_view::addJsFile($this->getAssetsUrl('jblock.js'));
    rex_view::addCssFile($this->getAssetsUrl('jblock.css'));

//    rex_perm::register('slice_ui[json]', null, rex_perm::OPTIONS);

}