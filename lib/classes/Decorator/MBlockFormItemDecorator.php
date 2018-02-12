<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockFormItemDecorator
{
    /**
     * @param MBlockItem $item
     * @return String
     * @author Joachim Doerr
     */
    static public function decorateFormItem(MBlockItem $item)
    {
        // set phpquery document
        $document = phpQuery::newDocumentHTML($item->getForm());

        // find inputs
        if ($matches = $document->find('input')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // label for and id change
                self::replaceForId($document, $match, $item);
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
        if ($matches = $document->find('textarea')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // label for and id change
                self::replaceForId($document, $match, $item);
                // replace attribute id
                self::replaceName($match, $item);
                // replace value by json key
                self::replaceValue($match, $item);
            }
        }

        // find selects
        if ($matches = $document->find('select')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // continue by media elements
                if (strpos($match->getAttribute('id'), 'REX_MEDIA') !== false
                    or strpos($match->getAttribute('id'), 'REX_LINK') !== false) {
                    continue;
                }
                // label for and id change
                self::replaceForId($document, $match, $item);
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
                            default:
                                if($child->tagName) {
                                    self::replaceOptionSelect($match, $child, $item);
                                    break;
                                }
                        }
                    }
                }
            }
        }

        // return the manipulated html output
        return $document->htmlOuter();
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceName(DOMElement $dom, MBlockItem $item)
    {
        // replace attribute id
        preg_match('/\]\[\d+\]\[/', $dom->getAttribute('name'), $matches);
        if ($matches) $dom->setAttribute('name', str_replace($matches[0], '][' . $item->getId() . '][', $dom->getAttribute('name')));
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceValue(DOMElement $dom, MBlockItem $item)
    {
        // get value key by name
        $matches = self::getName($dom);

        // found
        if ($matches) {
            // node name switch
            switch ($dom->nodeName) {
                default:
                case 'input':
                    if ($matches && array_key_exists($matches[1], $item->getResult())) $dom->setAttribute('value', $item->getResult()[$matches[1]]);
                    break;
                case 'textarea':
                    if ($matches && array_key_exists($matches[1], $item->getResult())) {
                        $result = $item->getResult();
                        $id = uniqid(md5(rand(1000,9999)),true);
                        // node value cannot contains &
                        // so set a unique id there we replace later with the right value
                        $dom->nodeValue = $id;

                        // add the id to the result value
                        $result[$matches[1]] = array('id'=>$id, 'value'=>$result[$matches[1]]);

                        // reset result
                        $item->setResult($result);
                    }
                    break;
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceSelectedData(DOMElement $dom, MBlockItem $item)
    {
        // get value key by name
        $matches = self::getName($dom);

        // found
        if ($matches) {
            // node name switch
            switch ($dom->nodeName) {
                default:
                case 'select':
                    if ($matches && array_key_exists($matches[1], $item->getResult())) $dom->setAttribute('data-selected', $item->getResult()[$matches[1]]);
                    break;
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceChecked(DOMElement $dom, MBlockItem $item)
    {
        // get value key by name
        $matches = self::getName($dom);

        // found
        if ($matches) {
            // unset select
            if ($dom->getAttribute('checked')) {
                $dom->removeAttribute('checked');
            }
            // set select by value = result
            if ($matches && array_key_exists($matches[1], $item->getResult()) && $item->getResult()[$matches[1]] == $dom->getAttribute('value')) {
                $dom->setAttribute('checked', 'checked');
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
                    $values = explode(',',$item->getResult()[$matches[1]]);
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
     * @param phpQueryObject $document
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @return bool
     * @author Joachim Doerr
     */
    protected static function replaceForId(phpQueryObject $document, DOMElement $dom, MBlockItem $item)
    {
        // get input id
        $domId = $dom->getAttribute('id');
        
        if (!$domId) {
            return true;
        }

        if (strpos($domId, 'REX_MEDIA') !== false
            or strpos($dom->getAttribute('class'), 'redactorEditor') !== false
            or strpos($domId, 'REX_LINK') !== false) {
            return false;
        }

        $id = preg_replace('/(_\d+){2}/i', '_' . $item->getId(), $domId);
        $dom->setAttribute('id', $id);
        // find label with for
        $matches = $document->find('label');

        if ($matches) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                $for = $match->getAttribute('for');
                if ($for == $domId) {
                    $match->setAttribute('for', $id);
                }
            }
        }
        return true;
    }

    /**
     * @param DOMElement $dom
     * @return mixed
     * @author Joachim Doerr
     */
    public static function getName(DOMElement $dom)
    {
        preg_match('/^.*?\[(\w+)\]$/i', str_replace('[]','',$dom->getAttribute('name')), $matches);
        return $matches;
    }

//    static protected function get_html_from_node($node){
//        $html = '';
//        $children = $node->childNodes;
//
//        foreach ($children as $child) {
//            $tmp_doc = new DOMDocument();
//            $tmp_doc->appendChild($tmp_doc->importNode($child,true));
//            $html .= $tmp_doc->saveHTML();
//        }
//        return $html;
//    }

}
