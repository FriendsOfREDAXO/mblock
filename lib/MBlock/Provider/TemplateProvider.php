<?php

/**
 * @author https://github.com/FriendsOfREDAXO
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MBlock\Provider;

use FriendsOfRedaxo\MBlock\Utils\TemplateManager;

/**
 * Template Provider for MBlock
 * Provides template content based on selected theme
 */
class TemplateProvider
{
    /**
     * Get template content for element or wrapper
     * 
     * @param string $type 'element' or 'wrapper'
     * @param string $templateName Name of the template (default: from config)
     * @return string Template content
     */
    public static function getTemplate($type, $templateName = null)
    {
        if ($templateName === null) {
            $addon = \rex_addon::get('mblock');
            $templateName = $addon->getConfig('mblock_theme', 'standard');
        }
        
        // All templates come from data/templates/ directory
        $templatePath = \rex_path::addon('mblock', 'data/templates/' . $templateName . '/mblock_' . $type . '.ini');
        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }
        
        // Fallback if nothing is found
        return self::getFallbackTemplate($type);
    }
    
    /**
     * Get fallback template content
     * 
     * @param string $type 'element' or 'wrapper'
     * @return string Fallback template content
     */
    private static function getFallbackTemplate($type)
    {
        if ($type === 'element') {
            return '<div class="mblock_item">{content}<div class="mblock_controls">{move_up_button}{move_down_button}{copy_button}{paste_button}{delete_button}</div></div>';
        } else {
            return '<div class="mblock_wrapper" data-mblock-name="{name}">{elements}<div class="mblock_add">{add_button}</div></div>';
        }
    }
    
    /**
     * Check if template exists
     * 
     * @param string $templateName Name of the template
     * @return bool True if template exists
     */
    public static function templateExists($templateName)
    {
        // All templates in data/templates/ directory
        $templatePath = \rex_path::addon('mblock', 'data/templates/' . $templateName . '/');
        return is_dir($templatePath) && 
               file_exists($templatePath . 'mblock_element.ini') && 
               file_exists($templatePath . 'mblock_wrapper.ini');
    }
}
