<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockTemplateFileProvider
{
    const DEFAULT_THEME = 'default_theme';
    const THEME_PATH = 'mblock/templates/%s/';
    const ELEMENTS_PATH = 'elements/';

    /**
     * @param $templateType
     * @param string $subPath
     * @param null $theme
     * @param bool $stop
     * @return string
     * @author Joachim Doerr
     */
    public static function loadTemplate($templateType, $subPath = '', $theme = NULL, $stop = false)
    {
        if (is_null($theme)) {
            $theme = rex_addon::get('mblock')->getConfig('mblock_theme');
        }

        // set theme path to load type template file
        $path = rex_path::addonData(sprintf(self::THEME_PATH . $subPath, $theme));
        $file = "mblock_$templateType.ini"; // create file name

        // to print without template
        $templateString = '<mblock:output/><mblock:form/>';

        // is template file exist? and template type not html
        if (file_exists($path . $file)) {
            // load theme file
            $templateString = implode(file($path . $file, FILE_USE_INCLUDE_PATH));
        } else {
            // stop recursion is default theme not founding
            if (!$stop) return self::loadTemplate($templateType, $subPath, self::DEFAULT_THEME, true);
        }

        // exchange template string
        return $templateString;
    }
}