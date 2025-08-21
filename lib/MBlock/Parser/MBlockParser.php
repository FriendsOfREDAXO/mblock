<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */



namespace FriendsOfRedaxo\MBlock\Parser;

use FriendsOfRedaxo\MBlock\DTO\MBlockElement;
use FriendsOfRedaxo\MBlock\Provider\MBlockTemplateFileProvider;
use rex_i18n;

class MBlockParser
{
    /**
     * @param MBlockElement $element
     * @param string $templateType
     * @param null $theme
     * @return mixed
     * @author Joachim Doerr
     */
    public static function parseElement(MBlockElement $element, $templateType, $theme = null)
    {
        $template = MBlockTemplateFileProvider::loadTemplate($templateType, '', $theme);
        
        // Replace language placeholders first
        $template = self::replaceLanguagePlaceholders($template);
        
        // Replace element placeholders
        $output = str_replace(
            array_merge(array(' />'), $element->getKeys()),
            array_merge(array('/>'), $element->getValues()),
            $template);
            
        // Replace language placeholders in the output again (for dynamic content like buttons)
        return self::replaceLanguagePlaceholders($output);
    }
    
    /**
     * Replace language placeholders in template
     * @param string $template
     * @return string
     * @author Joachim Doerr
     */
    private static function replaceLanguagePlaceholders($template)
    {
        // Find all {{language_key}} patterns
        if (preg_match_all('/\{\{([a-zA-Z_]+)\}\}/', $template, $matches)) {
            foreach ($matches[1] as $index => $langKey) {
                $langValue = rex_i18n::msg($langKey, $langKey); // fallback to key if not found
                $template = str_replace($matches[0][$index], $langValue, $template);
            }
        }
        
        return $template;
    }
}