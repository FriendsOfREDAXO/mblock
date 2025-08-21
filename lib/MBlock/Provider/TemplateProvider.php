<?php

/**
 * @author https://github.com/FriendsOfREDAXO
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MBlock\Provider;

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
            $templateName = $addon->getConfig('mblock_theme', 'default_theme');
        }
        
        // Default theme comes from main templates/ directory
        if ($templateName === 'default_theme') {
            $defaultTemplatePath = \rex_path::addon('mblock', 'templates/default_theme/mblock_' . $type . '.ini');
            if (file_exists($defaultTemplatePath)) {
                return file_get_contents($defaultTemplatePath);
            }
        } else {
            // Custom templates come from data/templates/ directory
            $customTemplatePath = \rex_path::addon('mblock', 'data/templates/' . $templateName . '/mblock_' . $type . '.ini');
            if (file_exists($customTemplatePath)) {
                return file_get_contents($customTemplatePath);
            }
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
        if ($templateName === 'default_theme') {
            // Default theme should exist in main templates/ directory
            $templatePath = \rex_path::addon('mblock', 'templates/default_theme/');
            return is_dir($templatePath) && 
                   file_exists($templatePath . 'mblock_element.ini') && 
                   file_exists($templatePath . 'mblock_wrapper.ini');
        }
        
        // Custom templates in data/templates/ directory
        $templatePath = \rex_path::addon('mblock', 'data/templates/' . $templateName . '/');
        return is_dir($templatePath) && 
               file_exists($templatePath . 'mblock_element.ini') && 
               file_exists($templatePath . 'mblock_wrapper.ini');
    }
}
