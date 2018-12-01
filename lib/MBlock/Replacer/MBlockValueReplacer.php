<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockValueReplacer
{
    use \MBlock\Decorator\MBlockDOMTrait;

    /**
     * @param MBlockItem $item
     * @param $count
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceValueSetEmpty(MBlockItem $item, $setDefaultValue = false)
    {
        // set phpquery document
        $dom = self::createDom($item->getForm());

        // find inputs
        if ($matches = $dom->getElementsByTagName('input')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // label for and id change
                switch ($match->getAttribute('type')) {
                    case 'checkbox':
                    case 'radio':
                        // replace checked
                        self::replaceChecked($match, $item);
                        break;
                    default:
                        // replace value by json key
                        self::replaceValue($match, $item);
                }
            }
        }

        // find textareas
        if ($matches = $dom->getElementsByTagName('textarea')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // replace value by json key
                self::replaceValue($match, $item);
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
                                    self::replaceOptionSelect($match, $nodeChild, $item);
                                break;
                            case 'option':
                                if (isset($child->tagName)) {
                                    self::replaceOptionSelect($match, $child, $item);
                                    break;
                                }
                        }
                    }
                }
            }
        }


        return self::saveHtml($dom);
    }

    /**
     * @param DOMElement $element
     * @param MBlockItem $item
     * @param bool $valueEmpty
     * @author Joachim Doerr
     */
    protected static function replaceValue(DOMElement $element, MBlockItem $item)
    {
        // get value key by name
        $matches = MBlockFormItemDecorator::getName($element);

        // found
        if ($matches) {
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
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceChecked(DOMElement $element, MBlockItem $item)
    {
        // get value key by name
        $matches = MBlockFormItemDecorator::getName($element);

        // found
        if ($matches) {
            // unset select
            if ($element->getAttribute('checked')) {
                $element->removeAttribute('checked');
            }
        }
    }

    /**
     * @param DOMElement $select
     * @param DOMElement $option
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceOptionSelect(DOMElement $select, DOMElement $option, MBlockItem $item)
    {
        // get value key by name
        $matches = MBlockFormItemDecorator::getName($select);

        if ($matches) {
            // unset select
            if ($option->hasAttribute('selected')) {
                $option->removeAttribute('selected');
            }
        }
    }
}