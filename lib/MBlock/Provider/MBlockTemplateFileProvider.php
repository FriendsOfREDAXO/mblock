<?php
/**
 * @author https://github.com/FriendsOfREDAXO
 * @package redaxo5
 * @license MIT
 */



namespace FriendsOfRedaxo\MBlock\Provider;

use rex_addon;
use rex_path;

class MBlockTemplateFileProvider
{
    const DEFAULT_THEME = 'default_theme';
    const THEME_PATH = 'templates/%s/';
    const ELEMENTS_PATH = 'elements/';

    /**
     * @param string $templateType
     * @param string $subPath
     * @param string|null $theme
     * @param bool $stop
     * @return string
     * @author Joachim Doerr
     */
    public static function loadTemplate($templateType, $subPath = '', $theme = NULL, $stop = false)
    {
        if (is_null($theme)) {
            $theme = rex_addon::get('mblock')->getConfig('mblock_theme', self::DEFAULT_THEME);
        }

        $file = "mblock_$templateType.ini"; // create file name

        // 1. First check for custom templates in data folder
        $dataPath = rex_path::addonData('mblock', 'templates/' . $theme . '/' . $subPath);
        
        // 2. Fallback to addon templates folder
        $addonPath = rex_path::addon('mblock', sprintf(self::THEME_PATH . $subPath, $theme));
        
        // to print without template
        $templateString = '<mblock:output/><mblock:form/>';

        // Check custom templates first (data folder)
        if (file_exists($dataPath . $file)) {
            // load custom theme file from data folder
            $templateString = (string) file_get_contents($dataPath . $file);
        } 
        // Check addon templates second (addon folder)
        elseif (file_exists($addonPath . $file)) {
            // load default theme file from addon folder
            $templateString = (string) file_get_contents($addonPath . $file);
        } 
        else {
            // stop recursion if default theme not found
            if (!$stop) return self::loadTemplate($templateType, $subPath, self::DEFAULT_THEME, true);
        }

        // exchange template string
        return $templateString;
    }

    /**
     * Get available template paths for debugging/info
     * @param string|null $theme
     * @return array{custom: string, default: string}
     */
    public static function getTemplatePaths($theme = null)
    {
        if (is_null($theme)) {
            $theme = rex_addon::get('mblock')->getConfig('mblock_theme', self::DEFAULT_THEME);
        }

        return [
            'custom' => rex_path::addonData('mblock', 'templates/' . $theme . '/'),
            'default' => rex_path::addon('mblock', sprintf(self::THEME_PATH, $theme))
        ];
    }
}