<?php
/**
 * Author: Joachim Doerr
 * Date: 30.07.16
 * Time: 22:36
 */

class JBlockTemplateFileProvider
{
    const DEFAULT_THEME = 'default_theme';
    const THEME_PATH = 'jblock/templates/%s/';
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
            $theme = rex_addon::get('mform')->getConfig('mform_theme');
        }

        // set theme path to load type template file
        $path = rex_path::addonData(sprintf(self::THEME_PATH . $subPath, $theme));
        $file = "jblock_$templateType.ini"; // create file name

        // to print without template
        $templateString = '<jblock:output/><jblock:form/>';

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