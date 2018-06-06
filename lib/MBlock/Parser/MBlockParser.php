<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\Parser;


use MBlock\DTO\MBlockElement;
use MBlock\Provider\MBlockTemplateFileProvider;
use rex_exception;
use rex_fragment;
use rex_logger;

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
        $fragment = new rex_fragment(
            array(
                'index' => $element->getIndex(),
                'settings' => $element->getSettings(),
                'output' => $element->getOutput(),
                'form' => $element->getForm(),
            )
        );
        try {
            return $fragment->parse($theme . '_theme/' . $templateType . '.php');
        } catch (rex_exception $e) {
            rex_logger::logException($e);
            return null;
        }

//        return str_replace(
//            array_merge(array(' />'), $element->getKeys()),
//            array_merge(array('/>'), $element->getValues()),
//            MBlockTemplateFileProvider::loadTemplate($templateType, '', $theme));
    }
}
