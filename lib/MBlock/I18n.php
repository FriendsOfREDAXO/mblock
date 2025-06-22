<?php
/**
 * MBlock v3.5 - Internationalization Helper
 * Provides translations for JavaScript tooltips
 * @author MBlock v3.5 Extension
 * @package redaxo5
 * @license MIT
 */

class MBlockI18n
{
    /**
     * Get all v3.5 tooltip translations for JavaScript
     * @return array
     */
    public static function getTooltipTranslations()
    {
        return [
            'toggle_active' => rex_i18n::msg('mblock_v35_tooltip_toggle_active'),
            'toggle_inactive' => rex_i18n::msg('mblock_v35_tooltip_toggle_inactive'),
            'move_up' => rex_i18n::msg('mblock_v35_tooltip_move_up'),
            'move_down' => rex_i18n::msg('mblock_v35_tooltip_move_down'),
            'drag_handle' => rex_i18n::msg('mblock_v35_tooltip_drag_handle'),
            'add' => rex_i18n::msg('mblock_v35_tooltip_add'),
            'delete' => rex_i18n::msg('mblock_v35_tooltip_delete'),
            'copy' => rex_i18n::msg('mblock_v35_tooltip_copy'),
            'paste' => rex_i18n::msg('mblock_v35_tooltip_paste'),
            'block_active' => rex_i18n::msg('mblock_v35_block_active'),
            'block_inactive' => rex_i18n::msg('mblock_v35_block_inactive'),
        ];
    }
    
    /**
     * Output translations as JavaScript object
     * @return string
     */
    public static function getJavaScriptTranslations()
    {
        $translations = self::getTooltipTranslations();
        return 'window.mblock_i18n = ' . json_encode($translations, JSON_UNESCAPED_UNICODE) . ';';
    }
    
    /**
     * Get a single translation
     * @param string $key
     * @param string $fallback
     * @return string
     */
    public static function getTranslation($key, $fallback = '')
    {
        $translations = self::getTooltipTranslations();
        return isset($translations[$key]) ? $translations[$key] : $fallback;
    }
    
    /**
     * Check if all required translations exist
     * @return array - Array of missing translations
     */
    public static function checkTranslations()
    {
        $required = [
            'mblock_v35_tooltip_toggle_active',
            'mblock_v35_tooltip_toggle_inactive', 
            'mblock_v35_tooltip_move_up',
            'mblock_v35_tooltip_move_down',
            'mblock_v35_tooltip_drag_handle',
            'mblock_v35_tooltip_add',
            'mblock_v35_tooltip_delete',
            'mblock_v35_tooltip_copy',
            'mblock_v35_tooltip_paste',
            'mblock_v35_block_active',
            'mblock_v35_block_inactive'
        ];
        
        $missing = [];
        foreach ($required as $key) {
            $translation = rex_i18n::msg($key);
            // Check if translation is missing (returns the key itself)
            if ($translation === $key) {
                $missing[] = $key;
            }
        }
        
        return $missing;
    }
    
    /**
     * Generate HTML script tag with translations
     * @return string
     */
    public static function generateScriptTag()
    {
        $jsTranslations = self::getJavaScriptTranslations();
        return '<script type="text/javascript">' . $jsTranslations . '</script>';
    }
}
