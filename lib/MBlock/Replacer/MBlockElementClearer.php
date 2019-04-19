<?php
/**
 * User: joachimdoerr
 * Date: 2019-04-17
 * Time: 13:31
 */

namespace MBlock\Replacer;


use DOMDocument;
use DOMElement;

class MBlockElementClearer
{
    /**
     * @param DOMDocument $dom
     * @author Joachim Doerr
     */
    public static function clearFormElements(DOMDocument $dom)
    {
        // inputs
        if ($matches = $dom->getElementsByTagName('input')) {
            /** @var DOMElement $input */
            foreach ($matches as $input) {
                switch ($input->getAttribute('type')) {
                    case 'checkbox':
                    case 'radio':
                        $input->removeAttribute('checked');
                        break;
                    default:
                        $input->setAttribute('value', '');
                        break;
                }
            }
        }

        // textareas
        if ($matches = $dom->getElementsByTagName('textarea')) {
            /** @var DOMElement $textarea */
            foreach ($matches as $textarea) {
                $textarea->nodeValue = '';
            }
        }

        // selects
        if ($matches = $dom->getElementsByTagName('select')) {
            foreach ($matches as $select) {
                // replace value by json key
                if ($select->hasChildNodes()) {
                    /** @var DOMElement $child */
                    foreach ($select->childNodes as $child) {
                        switch ($child->nodeName) {
                            case 'optgroup':
                                foreach ($child->childNodes as $nodeChild)
                                    $nodeChild->removeAttribute('selected');
                                break;
                            case 'option':
                                if (isset($child->tagName)) {
                                    $child->removeAttribute('selected');
                                    break;
                                }
                                break;
                        }
                    }
                }
            }
        }
    }

}