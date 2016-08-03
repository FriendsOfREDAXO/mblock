<?php
/**
 * Created by PhpStorm.
 * User: joachimdoerr
 * Date: 30.07.16
 * Time: 22:37
 */

class JBlockParser
{
    /**
     * @param JBlockElement $element
     * @param string $templateType
     * @return mixed
     * @author Joachim Doerr
     */
    public static function parseElement(JBlockElement $element, $templateType)
    {
        return str_replace(
            array_merge(array(' />'),$element->getKeys()),
            array_merge(array('/>'), $element->getValues()),
            JBlockTemplateFileProvider::loadTemplate($templateType));
    }
}