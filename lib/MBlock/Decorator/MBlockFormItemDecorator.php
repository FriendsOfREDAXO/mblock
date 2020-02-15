<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\Decorator;


use DOMElement;
use MBlock\DOM\MBlockDOMTrait;
use MBlock\DTO\MBlockItem;
use MBlock\Replacer\MBlockElementReplacer;

class MBlockFormItemDecorator extends MBlockElementReplacer
{
    use MBlockDOMTrait;

    /**
     * @param MBlockItem $item
     * @param array $nestedCount
     * @author Joachim Doerr
     */
    static public function decorateFormItem(MBlockItem $item, $nestedCount = array())
    {
        $dom = $item->getFormDomDocument();
        if ($dom instanceof \DOMDocument) {
            // find inputs
            if ($matches = $dom->getElementsByTagName('input')) {
                /** @var DOMElement $match */
                foreach ($matches as $match) {
                    if (!$match->hasAttribute('data-mblock')) {
                        // continue by media elements
                        if (strpos($match->getAttribute('id'), 'REX_MEDIA') !== false
                            or strpos($match->getAttribute('id'), 'REX_LINK') !== false) {
                            continue;
                        }
                        // replace attribute id
                        self::replaceName($match, $item, $nestedCount);
                        // change checked or value by type
                        switch ($match->getAttribute('type')) {
                            case 'checkbox':
                            case 'radio':
                                // replace checked
                                self::replaceChecked($match, $item);
                                self::replaceForIdForRadio($match, $matches);
                            break;
                            default:
                                // label for and id change
                                self::replaceForId($match);
                                // replace value by json key
                                self::replaceValue($match, $item);
                        }
                        $match->setAttribute('data-mblock', true);
                    }
                }
            }

            // find textareas
            if ($matches = $dom->getElementsByTagName('textarea')) {
                /** @var DOMElement $match */
                foreach ($matches as $match) {
                    if (!$match->hasAttribute('data-mblock')) {
                        // replace attribute id
                        self::replaceName($match, $item, $nestedCount);
                        // label for and id change
                        self::replaceForId($match);
                        // replace value by json key
                        self::replaceValue($match, $item);
                        $match->setAttribute('data-mblock', true);
                    }
                }
            }

            // find selects
            if ($matches = $dom->getElementsByTagName('select')) {
                /** @var DOMElement $match */
                foreach ($matches as $match) {
                    if (!$match->hasAttribute('data-mblock')) {
                        // continue by media elements
                        if (strpos($match->getAttribute('id'), 'REX_MEDIA') !== false
                            or strpos($match->getAttribute('id'), 'REX_LINK') !== false) {
                            continue;
                        }
                        // replace attribute id
                        self::replaceName($match, $item, $nestedCount);
                        // label for and id change
                        self::replaceForId($match);
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
                        $match->setAttribute('data-mblock', true);
                    }
                }
            }
        }
    }
}
