<?php
/**
 * Created by PhpStorm.
 * User: joachimdoerr
 * Date: 31.07.16
 * Time: 08:48
 */

class JBlockFormItemDecorator
{
    static public function decorateFormItem(JBlockItem $item)
    {
        $document = phpQuery::newDocumentHTML($item->getForm());

        // find input
        $matches = $document->find('input');

        /** @var DOMElement $match */
        foreach ($matches as $match) {
            // label for and id change
            self::changeFor($document, $match, $item);
            // replace attribute id
            self::changeName($match, $item);
            switch($match->getAttribute('type')) {
                case 'checkbox':
                case 'radio':
                    self::changeChecked($match, $item);
                    break;
                default:
                    // replace value by json key
                    self::changeValue($match, $item);
            }
        }

        $matches = $document->find('textarea');

        /** @var DOMElement $match */
        foreach ($matches as $match) {
            // label for and id change
            self::changeFor($document, $match, $item);
            // replace attribute id
            self::changeName($match, $item);
            // replace value by json key
            self::changeValue($match, $item);
        }

        $matches = $document->find('select');

        /** @var DOMElement $match */
        foreach ($matches as $match) {
            // label for and id change
            self::changeFor($document, $match, $item);
            // replace attribute id
            self::changeName($match, $item);
            // replace value by json key
            /** @var DOMElement $child */
            $children = $match->childNodes;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case 'optgroup':
                        foreach ($child->childNodes as $nodeChild)
                            self::changeOptionSelect($match, $nodeChild, $item);
                        break;
                    default:
                        self::changeOptionSelect($match, $child, $item);
                        break;
                }
            }
        }

        // return the manipulated html output
        return $document->htmlOuter();
    }

    static private function changeName(DOMElement $dom, JBlockItem $item)
    {
        // replace attribute id
        preg_match('/\]\[\d+\]\[/', $dom->getAttribute('name'), $matches);
        if ($matches) $dom->setAttribute('name', str_replace($matches[0], '][' . $item->getId() . '][', $dom->getAttribute('name')));
    }

    static private function changeValue(DOMElement $dom, JBlockItem $item)
    {
        // get value key by name
        $matches = self::getName($dom);
        // node name switch
        switch ($dom->nodeName) {
            default:
            case 'input':
                if ($matches) $dom->setAttribute('value', $item->getResult()[$matches[1]]);
                break;
            case 'textarea':
                if ($matches) $dom->nodeValue = $item->getResult()[$matches[1]];
                break;
        }
    }

    static private function changeChecked(DOMElement $dom, JBlockItem $item)
    {
        // get value key by name
        $matches = self::getName($dom);
        // unset select
        if ($dom->getAttribute('checked')) {
            $dom->removeAttribute('checked');
        }
        // set select by value = result
        if ($matches && $item->getResult()[$matches[1]] == $dom->getAttribute('value')) {
            $dom->setAttribute('checked', 'checked');
        }
    }

    static private function changeOptionSelect(DOMElement $select, DOMElement $option, JBlockItem $item)
    {
        // get value key by name
        $matches = self::getName($select);
        // unset select
        if ($option->getAttribute('selected')) {
            $option->removeAttribute('selected');
        }
        // set select by value = result
        if ($matches && $item->getResult()[$matches[1]] == $option->getAttribute('value')) {
            $option->setAttribute('selected', 'selected');
        }
    }

    static private function changeFor(phpQueryObject $document, DOMElement $dom, JBlockItem $item)
    {
        // get input id
        $id = $dom->getAttribute('id');
        $dom->setAttribute('id', $id . '_' . $item->getId());
        // find label with for
        $matches = $document->find('label');
        /** @var DOMElement $match */
        foreach ($matches as $match) {
            $for = $match->getAttribute('for');
            if ($for == $id) {
                $match->setAttribute('for', $id . '_' . $item->getId());
            }
        }
    }

    static private function getName(DOMElement $dom)
    {
        preg_match('/^.*?\[(\w+)\]$/i', $dom->getAttribute('name'), $matches);
        return $matches;
    }







//    static private function get_html_from_node($node){
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