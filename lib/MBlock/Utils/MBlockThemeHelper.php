<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockThemeHelper
{
    /**
     * @return array
     */
    public static function getThemesInformation()
    {
        $themeInfo = [];
        $path = implode('/', ['templates']);
        foreach (scandir(rex_path::addonData('mblock', $path)) as $item) {
            if ('.' == $item || '..' == $item) {
                continue;
            }
            $path = implode('/', ['templates', $item]);
            if (is_dir(rex_path::addonData('mblock', $path))) {
                $dirName = explode('_', $item);
                $themeInfo[$item] = [
                    'theme_name' => $dirName[0],
                    'theme_screen_name' => ucwords(str_replace('_', ' ', $item)),
                    'theme_path' => $item,
                ];
                foreach (scandir(rex_path::addonData('mblock', $path)) as $file) {
                    if ('css' == pathinfo($file, PATHINFO_EXTENSION)) {
                        $path = implode('/', ['templates', $item, $file]);
                        $themeInfo[$item]['theme_css_data'][] = rex_path::addonData('mblock', $path);

                        if (file_exists(rex_path::addonAssets('mblock', $path))) {
                            $themeInfo[$item]['theme_css_assets'][] = [
                                'full_path' => rex_path::addonAssets('mblock', $path),
                                'path' => $path,
                            ];
                        }
                    }
                }
            }
        }
        // return theme info array
        return $themeInfo;
    }

    /**
     * @author Joachim Doerr
     */
    public static function copyThemeCssToAssets()
    {
        // copy all theme css files to assets folder
        foreach (self::getThemesInformation() as $theme) {
            if (array_key_exists('theme_css_data', $theme)) {
                // rex_file::copy($theme, );
                foreach ($theme['theme_css_data'] as $css) {
                    rex_file::copy($css, rex_path::addonAssets('mblock', implode('/', ['templates', $theme['theme_path'], pathinfo($css, PATHINFO_BASENAME)])));
                }
            }
        }
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    public static function getCssAssets($theme)
    {
        $themeInfo = self::getThemesInformation();
        $cssList = [];
        if (array_key_exists($theme, $themeInfo) && array_key_exists('theme_css_assets', $themeInfo[$theme])) {
            foreach ($themeInfo[$theme]['theme_css_assets'] as $css) {
                $cssList[] = $css['path'];
            }
        }
        return $cssList;
    }

    /**
     * @author Joachim Doerr
     */
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
