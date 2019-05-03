<?php

use MBlock\DOM\MBlockDOMTrait;
use MBlock\Decorator\MBlockFormItemDecorator;
use MBlock\DTO\MBlockItem;

/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockValueReplacer
{
    use MBlockDOMTrait;

    /**
     * TODO set default values
     *
     * @param DOMDocument $dom
     * @param bool $setDefaultValue
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceValueSetEmpty(DOMDocument $dom, $setDefaultValue = false)
    {
        // remove unused sortitems
        if ($matches = $dom->getElementsByTagName('div')) {
            $count=0;
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                if ($match->getAttribute('class') == 'sortitem') {
                    $count++;
                    if ($count > 1) {
                        $match->parentNode->removeChild($match);
                    }
                }
            }
        }
            // find inputs
        if ($matches = $dom->getElementsByTagName('input')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // label for and id change
                switch ($match->getAttribute('type')) {
                    case 'checkbox':
                    case 'radio':
                        // replace checked
                        self::replaceChecked($match);
                        break;
                    default:
                        // replace value by json key
                        self::replaceValue($match);
                }
            }
        }

        // find textareas
        if ($matches = $dom->getElementsByTagName('textarea')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // replace value by json key
                self::replaceValue($match);
            }
        }

        // find selects
        if ($matches = $dom->getElementsByTagName('select')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // replace value by json key
                if ($match->hasChildNodes()) {
                    /** @var DOMElement $child */
                    foreach ($match->childNodes as $child) {
                        switch ($child->nodeName) {
                            case 'optgroup':
                                foreach ($child->childNodes as $nodeChild)
                                    self::replaceOptionSelect($match, $nodeChild);
                                break;
                            case 'option':
                                if (isset($child->tagName)) {
                                    self::replaceOptionSelect($match, $child);
                                    break;
                                }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param DOMElement $element
     * @author Joachim Doerr
     */
    protected static function replaceValue(DOMElement $element)
    {
        // get value key by name
        if ($matches = MBlockFormItemDecorator::getName($element)) {
            // node name switch
            switch ($element->nodeName) {
                default:
                case 'input':
                    $element->setAttribute('value', ($element->hasAttribute('data-default-value')) ? $element->getAttribute('data-default-value') : '');
                    break;
                case 'textarea':
                    $element->nodeValue = ($element->hasAttribute('data-default-value')) ? $element->getAttribute('data-default-value') : '';
                    break;
            }
        }
    }

    /**
     * @param DOMElement $element
     * @author Joachim Doerr
     */
    protected static function replaceChecked(DOMElement $element)
    {
        // get value key by name
        if ($matches = MBlockFormItemDecorator::getName($element)) {
            // unset select
            if ($element->getAttribute('checked')) {
                $element->removeAttribute('checked');
            }
        }
    }

    /**
     * @param DOMElement $select
     * @param DOMElement $option
     * @author Joachim Doerr
     */
    protected static function replaceOptionSelect(DOMElement $select, DOMElement $option)
    {
        // get value key by name
        if ($matches = MBlockFormItemDecorator::getName($select)) {
            // unset select
            if ($option->hasAttribute('selected')) {
                $option->removeAttribute('selected');
            }
        }
    }
}