<?php
/**
 * MBlock Theme Helper
 * 
 * @package redaxo\mblock
 * @author Friends Of REDAXO
 * @since 4.0.0
 */

namespace FriendsOfRedaxo\MBlock\Utils;

use rex;
use rex_addon;
use rex_view;

class MBlockThemeHelper
{
    /**
     * Get available themes information
     * 
     * @return array
     */
    public static function getThemesInformation()
    {
        $themes = [];
        
        // Standard Theme
        $themes[] = [
            'theme_path' => 'default',
            'theme_screen_name' => 'Standard Theme',
            'theme_description' => 'Das klassische MBlock Design mit CSS Custom Properties',
            'theme_version' => '4.0.0'
        ];
        
        // Glass Morphing Theme
        $themes[] = [
            'theme_path' => 'glass',
            'theme_screen_name' => 'Glass Morphing',
            'theme_description' => 'Modernes Glasmorphismus Design mit Backdrop-Filter',
            'theme_version' => '4.0.0'
        ];
        
        return $themes;
    }
    
    /**
     * Get current theme
     * 
     * @return string
     */
    public static function getCurrentTheme()
    {
        $addon = rex_addon::get('mblock');
        return $addon->getConfig('mblock_theme', 'default');
    }
    
    /**
     * Get theme CSS file path
     * 
     * @param string $theme
     * @return string
     */
    public static function getThemeCssPath($theme = null)
    {
        if ($theme === null) {
            $theme = self::getCurrentTheme();
        }
        
        $addon = rex_addon::get('mblock');
        $assetsPath = $addon->getAssetsPath();
        
        switch ($theme) {
            case 'glass':
                return $assetsPath . 'mblock_theme_glass.css';
            case 'default':
            default:
                return $assetsPath . 'mblock.css';
        }
    }
    
    /**
     * Get theme CSS URL
     * 
     * @param string $theme
     * @return string
     */
    public static function getThemeCssUrl($theme = null)
    {
        if ($theme === null) {
            $theme = self::getCurrentTheme();
        }
        
        $addon = rex_addon::get('mblock');
        $assetsUrl = $addon->getAssetsUrl();
        
        switch ($theme) {
            case 'glass':
                return $assetsUrl . 'mblock_theme_glass.css';
            case 'default':
            default:
                return $assetsUrl . 'mblock.css';
        }
    }
    
    /**
     * Include current theme CSS
     */
    public static function includeThemeCSS()
    {
        $theme = self::getCurrentTheme();
        $cssUrl = self::getThemeCssUrl($theme);
        
        // Include CSS in backend
        if (rex::isBackend()) {
            rex_view::addCssFile($cssUrl);
        }
    }
    
    /**
     * Get theme preview HTML
     * 
     * @param string $theme
     * @return string
     */
    public static function getThemePreview($theme)
    {
        $preview = '<div class="mblock-theme-preview" data-theme="' . $theme . '">';
        
        switch ($theme) {
            case 'glass':
                $preview .= '
                    <div class="mblock-preview-block glass-effect">
                        <div class="mblock-preview-drag">⋮⋮</div>
                        <div class="mblock-preview-content">
                            <div class="mblock-preview-field"></div>
                            <div class="mblock-preview-field"></div>
                        </div>
                        <div class="mblock-preview-buttons">
                            <span class="btn-preview glass-btn">+</span>
                            <span class="btn-preview glass-btn">×</span>
                        </div>
                    </div>
                ';
                break;
            case 'default':
            default:
                $preview .= '
                    <div class="mblock-preview-block default-effect">
                        <div class="mblock-preview-drag">⋮⋮</div>
                        <div class="mblock-preview-content">
                            <div class="mblock-preview-field"></div>
                            <div class="mblock-preview-field"></div>
                        </div>
                        <div class="mblock-preview-buttons">
                            <span class="btn-preview default-btn">+</span>
                            <span class="btn-preview default-btn">×</span>
                        </div>
                    </div>
                ';
                break;
        }
        
        $preview .= '</div>';
        
        return $preview;
    }
}
