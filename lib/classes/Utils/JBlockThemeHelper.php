<?php
/**
 * User: joachimdoerr
 * Date: 24.07.16
 * Time: 13:38
 */

class JBlockThemeHelper
{
    /**
     * @return array
     */
    public static function getThemesInformation()
    {
        $themeInfo = array();
        $path = implode('/', array('templates'));
        foreach (scandir(rex_path::addonData('jblock', $path)) as $item) {
            if ($item == '.' or $item == '..') {
                continue;
            }
            $path = implode('/', array('templates', $item));
            if (is_dir(rex_path::addonData('jblock', $path))) {
                $dirName = explode('_', $item);
                $themeInfo[$item] = array(
                    'theme_name' => $dirName[0],
                    'theme_screen_name' => ucwords(str_replace('_', ' ', $item)),
                    'theme_path' => $item
                );
                foreach (scandir(rex_path::addonData('jblock', $path)) as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) == 'css') {
                        $path = implode('/', array('templates', $item, $file));
                        $themeInfo[$item]['theme_css_data'][] = rex_path::addonData('jblock', $path);

                        if (file_exists(rex_path::addonAssets('jblock', $path))) {
                            $themeInfo[$item]['theme_css_assets'][] = array(
                                'full_path' => rex_path::addonAssets('jblock', $path),
                                'path' => $path
                            );
                        }
                    }
                }
            }
        }
        // return theme info array
        return $themeInfo;
    }

    public static function copyThemeCssToAssets()
    {
        // copy all theme css files to assets folder
        foreach (self::getThemesInformation() as $theme) {
            if (array_key_exists('theme_css_data', $theme)) {
                #rex_file::copy($theme, );
                foreach ($theme['theme_css_data'] as $css) {
                    rex_file::copy($css, rex_path::addonAssets('jblock', implode('/', array('templates', $theme['theme_path'], pathinfo($css, PATHINFO_BASENAME)))));
                }
            }
        }
    }

    public static function getCssAssets($theme)
    {
        $themeInfo = self::getThemesInformation();
        $cssList = array();
        if (array_key_exists($theme, $themeInfo) && array_key_exists('theme_css_assets', $themeInfo[$theme])) {
            foreach ($themeInfo[$theme]['theme_css_assets'] as $css) {
                $cssList[] = $css['path'];
            }
        }
        return $cssList;
    }

    public static function themeBootCheck($theme)
    {
        $themeInfo = self::getThemesInformation();
        if (array_key_exists($theme, $themeInfo)
            && (
                !array_key_exists('theme_css_assets', $themeInfo[$theme])
                && array_key_exists('theme_css_data', $themeInfo[$theme])
            )
        ) {
            self::copyThemeCssToAssets();
        }
    }
}