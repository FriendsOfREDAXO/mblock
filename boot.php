<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

if (rex::isBackend() && is_object(rex::getUser())) {

    // check theme css exists
    MBlockThemeHelper::themeBootCheck($this->getConfig('mblock_theme'));

    // use theme helper class
    if (MBlockThemeHelper::getCssAssets($this->getConfig('mblock_theme'))) {
        // foreach all css files
        foreach (MBlockThemeHelper::getCssAssets($this->getConfig('mblock_theme')) as $css) {
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
            return MBlockRexFormProcessor::postPostSaveAction($params->getSubject(), $form, $_POST); // execute post post
        else
            return $params->getSubject();
    });

    // assets - intelligent loading based on debug mode and available files
    // Debug mode: Load separate files for easier debugging and development
    // Production mode: Load minified bundle for optimal performance
    $isDebugMode = rex::isDebugMode();
    static $hasBundleDist = null;
    if ($hasBundleDist === null) {
        $hasBundleDist = file_exists($this->getPath('assets/dist/mblock.min.js'));
    }
    
    if (!$isDebugMode && $hasBundleDist) {
        // Production mode - use minified bundle (16.8KB, no console logs)
        rex_view::addJsFile($this->getAssetsUrl('dist/mblock_sortable.min.js'));
        rex_view::addJsFile($this->getAssetsUrl('dist/mblock_smooth_scroll.min.js'));
        rex_view::addJsFile($this->getAssetsUrl('dist/mblock.min.js'));
        rex_view::addCssFile($this->getAssetsUrl('dist/mblock.css'));
    } else {
        // Debug mode or no bundle available - use separate files for debugging
        rex_view::addJsFile($this->getAssetsUrl('mblock_sortable.js'));
        rex_view::addJsFile($this->getAssetsUrl('mblock_smooth_scroll.js'));
        rex_view::addJsFile($this->getAssetsUrl('mblock.js')); // Contains console logs for debugging
        rex_view::addCssFile($this->getAssetsUrl('mblock.css'));
    }
    
    // MBlock v4.0 - Add internationalization support for JavaScript
    rex_extension::register('OUTPUT_FILTER', function(rex_extension_point $ep) {
        if (rex::isBackend()) {
            $content = $ep->getSubject();
            
            // Only add translations if MBlock is being used (contains mblock_wrapper)
            if (strpos($content, 'mblock_wrapper') !== false) {
                $i18nScript = MBlockI18n::generateScriptTag();
                
                // Insert before closing </head> tag
                $content = str_replace('</head>', $i18nScript . "\n</head>", $content);
            }
            
            return $content;
        }
        return $ep->getSubject();
    });
}

if (isset($_SESSION['mblock_count'])) {
    // reset mblock page count
    $_SESSION['mblock_count'] = 0;
}
