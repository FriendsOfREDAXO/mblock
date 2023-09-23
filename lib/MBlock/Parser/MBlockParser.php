<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockParser
{
    /**
     * @param string $templateType
     * @param null $theme
     * @return mixed
     * @author Joachim Doerr
     */
    public static function parseElement(MBlockElement $element, $templateType, $theme = null)
    {
        return str_replace(
            array_merge([' />'], $element->getKeys()),
            array_merge(['/>'], $element->getValues()),
            MBlockTemplateFileProvider::loadTemplate($templateType, '', $theme));
    }
}
