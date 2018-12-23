<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\Parser;


use MBlock\DTO\MBlockElement;
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
                'iterate_index' => $element->getIterateIndex(),
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
    }
}
