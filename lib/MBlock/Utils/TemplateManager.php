<?php

/**
 * @author https://github.com/FriendsOfREDAXO
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MBlock\Utils;

/**
 * Template Manager for MBlock
 * Handles template selection, CSS copying and template management
 */
class TemplateManager
{
    /**
     * Get all available templates from data/templates directory
     * Only built-in templates are offered, no custom templates
     * 
     * @return array Array with template key => display name
     */
    public static function getAvailableTemplates()
    {
        // Only built-in templates are available
        $availableTemplates = array(
            'standard' => \rex_i18n::msg('mblock_theme_standard'),
            'modern' => 'Modern',
            'akg_skin' => 'AKG Skin',
            'retro_8bit' => 'Retro 8bit'
        );
        
        // Only return templates that actually exist
        $templatesPath = \rex_path::addon('mblock', 'data/templates/');
        $existingTemplates = array();
        
        foreach ($availableTemplates as $key => $label) {
            if (is_dir($templatesPath . $key)) {
                $existingTemplates[$key] = $label;
            }
        }
        
        return $existingTemplates;
    }
    
    /**
     * Copy template CSS file to assets directory
     * 
     * @param string $templateName Name of the template
     * @return bool True if CSS was copied or no CSS exists, false on error
     */
    public static function copyTemplateCSS($templateName)
    {
        if ($templateName === 'standard') {
            return true; // Standard theme doesn't need CSS copying
        }
        
        $templatePath = \rex_path::addon('mblock', 'data/templates/' . $templateName . '/');
        $cssFile = $templatePath . $templateName . '.css';
        
        // Use the correct assets path for addon assets
        $assetsPath = \rex_path::addonAssets('mblock') . '/';
        $targetCssFile = $assetsPath . $templateName . '.css';
        
        // If no CSS file exists, that's okay
        $cssContent = \rex_file::get($cssFile);
        if ($cssContent === false) {
            return true;
        }
        
        // Create assets directory if it doesn't exist
        if (!is_dir($assetsPath)) {
            \rex_dir::create($assetsPath);
        }
        
        // Remove existing CSS file first (always overwrite)
        if (\rex_file::get($targetCssFile)) {
            \rex_file::delete($targetCssFile);
        }
        
        // Copy CSS file using rex_file
        $result = \rex_file::put($targetCssFile, $cssContent);
        
        return $result;
    }
    
    /**
     * Get the CSS file URL for a template
     * 
     * @param string $templateName Name of the template
     * @return string|null CSS file URL or null if no CSS exists
     */
    public static function getTemplateCSSUrl($templateName)
    {
        if ($templateName === 'standard') {
            return null; // Standard theme CSS is handled separately
        }
        
        $assetsPath = \rex_path::addonAssets('mblock') . '/';
        $cssFile = $assetsPath . $templateName . '.css';
        
        if (\rex_file::get($cssFile)) {
            return \rex_url::addonAssets('mblock', $templateName . '.css');
        }
        
        return null;
    }
    
    /**
     * Remove template CSS file from assets directory
     * 
     * @param string $templateName Name of the template
     * @return bool True on success or if file doesn't exist
     */
    public static function removeTemplateCSS($templateName)
    {
        if ($templateName === 'standard') {
            return true; // Standard theme doesn't have removable CSS
        }
        
        $assetsPath = \rex_path::addonAssets('mblock') . '/';
        $cssFile = $assetsPath . $templateName . '.css';
        
        if (\rex_file::get($cssFile)) {
            return \rex_file::delete($cssFile);
        }
        
        return true; // File doesn't exist, that's okay
    }
    
    /**
     * Check if a template has its own CSS file
     * 
     * @param string $templateName Name of the template
     * @return bool True if template has its own CSS file
     */
    public static function hasTemplateCSS($templateName)
    {
        if ($templateName === 'standard') {
            return false; // Standard theme CSS is handled differently
        }
        
        $templatePath = \rex_path::addon('mblock', 'data/templates/' . $templateName . '/');
        $cssFile = $templatePath . $templateName . '.css';
        
        return \rex_file::get($cssFile) !== false;
    }
    
    /**
     * Clean up all template CSS files from assets directory
     * 
     * @return int Number of files removed
     */
    public static function cleanupAllTemplateCSS()
    {
        $assetsPath = \rex_path::addonAssets('mblock') . '/';
        $templatesPath = \rex_path::addon('mblock', 'data/templates/');
        $removedCount = 0;
        
        if (!is_dir($templatesPath)) {
            return $removedCount;
        }
        
        // Get all template directories
        $templateDirs = scandir($templatesPath);
        foreach ($templateDirs as $dir) {
            if ($dir !== '.' && $dir !== '..' && is_dir($templatesPath . $dir)) {
                $cssFile = $assetsPath . $dir . '.css';
                if (\rex_file::get($cssFile)) {
                    if (\rex_file::delete($cssFile)) {
                        $removedCount++;
                    }
                }
            }
        }
        
        return $removedCount;
    }
}
