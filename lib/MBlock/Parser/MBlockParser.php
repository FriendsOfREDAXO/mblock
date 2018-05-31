<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

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
        return str_replace(
            array_merge(array(' />'), $element->getKeys()),
            array_merge(array('/>'), $element->getValues()),
            MBlockTemplateFileProvider::loadTemplate($templateType, '', $theme));
    }
}