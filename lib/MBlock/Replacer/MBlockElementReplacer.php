<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\Replacer;


use DOMDocument;
use DOMElement;
use MBlock\DOM\MBlockDOMTrait;
use MBlock\DTO\MBlockItem;

class MBlockElementReplacer
{
    use MBlockDOMTrait;

    /**
     * @param DOMElement $element
     * @param MBlockItem $item
     * @param array $nestedCount
     * @author Joachim Doerr
     */
    protected static function replaceName(DOMElement $element, MBlockItem $item, $nestedCount = array())
    {
        $name = $element->getAttribute('name');

        // third
        preg_match("/(\[\w+\])(\[\d\])(\[\w+\])(\[\d\])(\[\w+\])(\[\d\])/", $name, $nestedThirdMatch);
        // second
        preg_match("/(\[\w+\])(\[\d\])(\[\w+\])(\[\d\])/", $name, $nestedMatch);
        // default
        preg_match("/(\[\w+\])(\[\d\])/", $name, $defaultMatch);

        if (is_array($nestedCount) && sizeof($nestedCount) == 2 && !empty($nestedThirdMatch)) {

            $defaultValueName = $nestedThirdMatch[1];
            $defaultCount = $nestedCount[sizeof($nestedCount)-2];
            $secondValueName = $nestedThirdMatch[3];
            $secondCount = $nestedCount[sizeof($nestedCount)-1];
            $thirdValueName = $nestedThirdMatch[5];
            $thirdCount = $item->getItemId();

            $replace = sprintf('%s[%s]%s[%s]%s[%s]', $defaultValueName, $defaultCount, $secondValueName, $secondCount, $thirdValueName, $thirdCount);
            $element->setAttribute('name', str_replace($nestedThirdMatch[0], $replace, $name));

        } else if (is_array($nestedCount) && sizeof($nestedCount) == 1 && !empty($nestedMatch)) {

            $defaultValueName = $nestedMatch[1];
            $defaultCount = $nestedCount[sizeof($nestedCount)-1];
            $secondValueName = $nestedMatch[3];
            $secondCount = $item->getItemId();

            $replace = sprintf('%s[%s]%s[%s]', $defaultValueName, $defaultCount, $secondValueName, $secondCount);
            $element->setAttribute('name', str_replace($nestedMatch[0], $replace, $name));

        } else if (!empty($defaultMatch)) {

            $defaultValueName = $defaultMatch[1];
            $defaultCount = $item->getItemId();

            $replace = sprintf('%s[%s]', $defaultValueName, $defaultCount);
            $element->setAttribute('name', str_replace($defaultMatch[0], $replace, $name));
        }

        $name = explode('[', $element->getAttribute('name'));
        $element->setAttribute('data-name-value', $name[0]);
    }

    /**
     * @param DOMElement $element
     * @param MBlockItem $item
     * @param bool $valueEmpty
     * @author Joachim Doerr
     */
    protected static function replaceValue(DOMElement $element, MBlockItem $item, $valueEmpty = false)
    {
        // get value key by name
        $matches = self::getName($element);
        // found
        if ($matches) {
            // node name switch
            switch ($element->nodeName) {
                default:
                case 'input':
                    if ($matches && is_array($item->getVal()) && array_key_exists($matches[1], $item->getVal())) {
                        $element->setAttribute('value', $item->getVal()[$matches[1]]);
                    }
                    // set default value or empty it
                    if ($valueEmpty) {
                        $element->setAttribute('value', ($element->hasAttribute('data-default-value')) ? $element->getAttribute('data-default-value') : '');
                    }
                    break;
                case 'textarea':
                    if ($matches && is_array($item->getVal()) && array_key_exists($matches[1], $item->getVal())) {
                        $result = $item->getResult() ?: $item->getVal();
                        $id = uniqid(md5(rand(1000, 9999)), true);
                        // node value cannot contains &
                        // so set a unique id there we replace later with the right value
                        $element->nodeValue = $id;

                        $valueResult = $result[$matches[1]];

                        // add the id to the result value
                        $result[$matches[1]] = array('id' => $id, 'value' => $valueResult);

                        // reset result
                        $item->setResult($result);
                    }
                    if ($valueEmpty) {
                        $element->nodeValue = ($element->hasAttribute('data-default-value')) ? $element->getAttribute('data-default-value') : '';
                    }
                    break;
            }
        }
    }

    /**
     * @param DOMElement $element
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceSelectedData(DOMElement $element, MBlockItem $item)
    {
        // get value key by name
        $matches = self::getName($element);
        // found
        if ($matches) {
            // node name switch
            switch ($element->nodeName) {
                default:
                case 'select':
                    // if ($matches && array_key_exists($matches[1], $item->getVal())) $element->setAttribute('data-selected', $item->getVal()[$matches[1]]);
                    if ($matches && is_array($item->getVal()) && array_key_exists($matches[1], $item->getVal())) {
                        $element->setAttribute('data-selected', (!$element->hasAttribute('multiple')) ? $item->getVal()[$matches[1]] : rex_escape(json_encode($item->getVal()[$matches[1]]), 'html_attr'));
                    }
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
        $matches = self::getName($element);
        // found
        if ($matches) {
            // unset select
            if ($element->getAttribute('checked')) {
                $element->removeAttribute('checked');
            }
            // set select by value = result
            if ($matches && is_array($item->getVal()) && array_key_exists($matches[1], $item->getVal()) && $item->getVal()[$matches[1]] == $element->getAttribute('value')) {
                $element->setAttribute('checked', 'checked');
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
        $matches = self::getName($select);
        // found
        if ($matches) {
            // unset select
            if ($option->hasAttribute('selected')) {
                $option->removeAttribute('selected');
            }

            // set select by value = result
            if ($matches && is_array($item->getVal()) && array_key_exists($matches[1], $item->getVal())) {

                if (is_array($item->getVal()[$matches[1]])) {
                    $values = $item->getVal()[$matches[1]];
                } else {
                    $values = explode(',',$item->getVal()[$matches[1]]);
                }

                foreach ($values as $value) {
                    if ($value == $option->getAttribute('value')) {
                        $option->setAttribute('selected', 'selected');
                    }
                }
            }
        }
    }

    /**
     * @param DOMElement $element
     * @param null $index
     * @return bool
     * @author Joachim Doerr
     */
    public static function replaceForId(DOMElement $element, $index = '')
    {
        // get input id
        if (!$elementId = $element->getAttribute('id')) return true;

        // ignore system elements
        if (strpos($elementId, 'REX_MEDIA') !== false
            or strpos($elementId, 'REX_LINK') !== false) {
            return false;
        }

        $id = str_replace(array('][', '[', ']'),array('_','_',''), $element->getAttribute('name'));
        // $id = preg_replace('/(\[|\])/m', '', str_replace('-','_', $element->getAttribute('name')));
        $id = (!empty($index)) ? $id . '_' . $index : $id;
        $element->setAttribute('id', $id);

        if ($element->parentNode->nodeName == 'label') {
            $element->parentNode->setAttribute('for', $id);
        } else {
            if ($match = $element->parentNode->parentNode->getElementsByTagName('label')) {
                /** @var DOMElement $label */
                foreach ($match as $label) {
                    if ($label->getAttribute('for') == $elementId) {
                        $label->setAttribute('for', $id);
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param DOMElement $element
     * @param \DOMNodeList $elements
     * @author Joachim Doerr
     */
    public static function replaceForIdForRadio(DOMElement $element, \DOMNodeList $elements)
    {
        $name = $element->getAttribute('name');
        /** @var DOMElement $item */
        foreach ($elements as $index => $item) {
            if ($item->getAttribute('name') == $name) {
                self::replaceForId($item, $index);
            }
        }
    }

    /**
     * @param DOMElement $element
     * @return mixed
     * @author Joachim Doerr
     */
    public static function getName(DOMElement $element)
    {
        preg_match('/^.*?\[(\w+)\]$/i', str_replace('[]','',$element->getAttribute('name')), $matches);
        return $matches;
    }
}
