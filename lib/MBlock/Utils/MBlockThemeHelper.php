<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\Utils;


use rex_path;

class MBlockThemeHelper
{
    /**
     * @return array
     */
    public static function getThemesInformation()
    {
        $themeInfo = array();
        $path = implode('/', array('themes'));
        if (file_exists(rex_path::addonAssets('mblock', $path))) {
            foreach (scandir(rex_path::addonAssets('mblock', $path)) as $item) {
                if ($item == '.' or $item == '..') {
                    continue;
                }
                $path = implode('/', array('themes', $item));
                if (is_dir(rex_path::addonAssets('mblock', $path))) {
                    $dirName = explode('_', $item);
                    $themeInfo[$item] = array(
                        'theme_name' => $dirName[0],
                        'theme_screen_name' => ucwords(str_replace('_', ' ', $item)),
                        'theme_path' => $item
                    );
                    foreach (scandir(rex_path::addonAssets('mblock', $path)) as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) == 'css') {
                            $path = implode('/', array('themes', $item, $file));
                            $themeInfo[$item]['theme_css_data'][] = rex_path::addonAssets('mblock', $path);

                            if (file_exists(rex_path::addonAssets('mblock', $path))) {
                                $themeInfo[$item]['theme_css_assets'][] = array(
                                    'full_path' => rex_path::addonAssets('mblock', $path),
                                    'path' => $path
                                );
                            }
                        }
                    }
                }
            }
        }
        // return theme info array
        return $themeInfo;
    }

    /**
     * @param $theme
     * @return array
     * @author Joachim Doerr
     */
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
}