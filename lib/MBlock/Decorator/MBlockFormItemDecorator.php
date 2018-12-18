<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockFormItemDecorator
{
    use \MBlock\Decorator\MBlockDOMTrait;

    /**
     * @param MBlockItem $item
     * @return String
     * @author Joachim Doerr
     */
    static public function decorateFormItem(MBlockItem $item)
    {
        $dom = self::createDom($item->getForm());

        // find inputs
        if ($matches = $dom->getElementsByTagName('input')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // label for and id change
                self::replaceForId($dom, $match, $item);
                // replace attribute id
                self::replaceName($match, $item);
                // change checked or value by type
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
                // label for and id change
                self::replaceForId($dom, $match, $item);
                // replace attribute id
                self::replaceName($match, $item);
                // replace value by json key
                self::replaceValue($match, $item);
            }
        }

        // find selects
        if ($matches = $dom->getElementsByTagName('select')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // continue by media elements
                if (strpos($match->getAttribute('id'), 'REX_MEDIA') !== false
                    or strpos($match->getAttribute('id'), 'REX_LINK') !== false) {
                    continue;
                }
                // label for and id change
                self::replaceForId($dom, $match, $item);
                // replace attribute id
                self::replaceName($match, $item);
                // replace selected data
                self::replaceSelectedData($match, $item);
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

        // return the manipulated html output
        return self::saveHtml($dom);
    }

    /**
     * @param DOMElement $element
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceName(DOMElement $element, MBlockItem $item)
    {
        // replace attribute id
        preg_match('/\]\[\d+\]\[/', $element->getAttribute('name'), $matches);
        if ($matches) $element->setAttribute('name', str_replace($matches[0], '][' . $item->getId() . '][', $element->getAttribute('name')));
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
                    if ($matches && array_key_exists($matches[1], $item->getResult())) {
                        $element->setAttribute('value', $item->getResult()[$matches[1]]);
                    }
                    // set default value or empty it
                    if ($valueEmpty) {
                        $element->setAttribute('value', ($element->hasAttribute('data-default-value')) ? $element->getAttribute('data-default-value') : '');
                    }
                    break;
                case 'textarea':
                    if ($matches && array_key_exists($matches[1], $item->getResult())) {
                        $result = $item->getResult();
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
                    if ($matches && array_key_exists($matches[1], $item->getResult())) {
                        $element->setAttribute('data-selected', (!$element->hasAttribute('multiple')) ? $item->getResult()[$matches[1]] : rex_escape(json_encode($item->getResult()[$matches[1]]), 'html_attr'));
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
            if ($matches && array_key_exists($matches[1], $item->getResult()) && $item->getResult()[$matches[1]] == $element->getAttribute('value')) {
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

        if ($matches) {
            // unset select
            if ($option->hasAttribute('selected')) {
                $option->removeAttribute('selected');
            }

            // set select by value = result
            if ($matches && array_key_exists($matches[1], $item->getResult())) {

                if (is_array($item->getResult()[$matches[1]])) {
                    $values = $item->getResult()[$matches[1]];
                } else {
                    $values = explode(',', $item->getResult()[$matches[1]]);
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
     * @param DOMDocument $dom
     * @param DOMElement $element
     * @param MBlockItem $item
     * @return bool
     * @author Joachim Doerr
     */
    protected static function replaceForId(DOMDocument $dom, DOMElement $element, MBlockItem $item)
    {
        // get input id
        if (!$elementId = $element->getAttribute('id')) return true;

        // ignore system elements
        if (strpos($elementId, 'REX_MEDIA') !== false
            or strpos($elementId, 'REX_LINK') !== false) {
            return false;
        }

        $id = preg_replace('/(_\d+){2}/i', '_' . $item->getId(), str_replace('-', '_', $elementId));
        $element->setAttribute('id', $id);
        // find label with for
        $matches = $dom->getElementsByTagName('label');

        if ($matches) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                $for = $match->getAttribute('for');
                if ($for == $elementId) {
                    $match->setAttribute('for', $id);
                }
            }
        }
        return true;
    }

    /**
     * @param DOMElement $element
     * @return mixed
     * @author Joachim Doerr
     */
    public static function getName(DOMElement $element)
    {
        preg_match('/^.*?\[(\w+)\]$/i', str_replace('[]', '', $element->getAttribute('name')), $matches);
        return $matches;
    }
}
