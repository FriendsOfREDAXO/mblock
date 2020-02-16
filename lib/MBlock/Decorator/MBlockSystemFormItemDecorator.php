<?php
/**
 * User: joachimdorr
 * Date: 15.02.20
 * Time: 20:32
 */

namespace MBlock\Decorator;


use DOMElement;
use MBlock\DOM\MBlockDOMTrait;
use MBlock\DTO\MBlockItem;
use MBlock\Replacer\MBlockSystemButtonReplacer;

class MBlockSystemFormItemDecorator extends MBlockSystemButtonReplacer
{
    use MBlockDOMTrait;

    /**
     * @param MBlockItem $item
     * @param array $nestedCount
     * @author Joachim Doerr
     */
    public static function decorateSystemFormItem(MBlockItem $item, $nestedCount = array())
    {
        // set dom document
        $dom = $item->getFormDomDocument();
        if ($dom instanceof \DOMDocument) {
            // find input group
            if ($matches = self::getElementsByClass($dom, 'div.input-group')) {
                /** @var DOMElement $match */
                foreach ($matches as $key => $match) {
                    if ($match->hasChildNodes()) {
                        /** @var DOMElement $child */
                        foreach ($match->getElementsByTagName('input') as $child) {
                            if (strpos($match->getAttribute('class'), 'custom-link') !== false) {
                                continue;
                            }
                            if ($child instanceof DOMElement && !$child->hasAttribute('data-mblock')) {
                                $id = $child->getAttribute('id');
                                $type = $child->getAttribute('type');

                                if (strpos($id, 'REX_LINK_') !== false && $type == 'hidden') {
                                    self::processLink($match, $item, $nestedCount);
                                }
                                if (strpos($id, 'REX_MEDIA_') !== false && $type == 'text') {
                                    self::processMedia($match, $item, $nestedCount);
                                }
                                if (strpos($id, 'REX_LINKLIST_') !== false) {
                                    // link list button
                                    self::processLinkList($match, $item, $nestedCount);
                                }
                            }


//                            if (strpos($child->getAttribute('name'), 'REX_INPUT_LINK') !== false && $child->getAttribute('type') == 'text') {
//                                self::processLink($match, $item, $nestedCount);
//                            }

//                                if ($child instanceof DOMElement && !$child->hasAttribute('data-mblock')) { // && $child->getAttribute('type') == 'hidden') {
//                                // set id and name
//                                $id = $child->getAttribute('id');
//                                $type = $child->getAttribute('type');
//                                dump($type);
//
//                                // process by type
//                                if (strpos($id, 'REX_MEDIA_') !== false && $type == 'text') {
//                                    // media button
//                                    self::processMedia($match, $item, $nestedCount);
//                                }
//                                if (strpos($id, 'REX_MEDIALIST_') !== false) {
//                                    // media list button
//                                    self::processMediaList($match, $item, $nestedCount);
//                                }
//                                if (strpos($id, 'REX_LINK_') !== false && $type == 'text') {
//                                    // link button
//                                    if (strpos($match->getAttribute('class'), 'custom-link') !== false) {
////                                        self::processCustomLink($match, $item, $nestedCount);
//                                    } else {
//                                        self::processLink($match, $item, $nestedCount);
//                                    }
//                                }
//                                if (strpos($id, 'REX_LINKLIST_') !== false) {
//                                    // link list button
//                                    self::processLinkList($match, $item, $nestedCount);
//                                }
//                            }
                        }
                        $match->setAttribute('data-mblock', true);
                    }
                }
            }
        }
    }

    /**
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    public static function decorateCustomLinkFormItem(MBlockItem $item)
    {
        // set dom document
        $dom = $item->getFormDomDocument();
        if ($dom instanceof \DOMDocument) {
            // find custom-link
            if ($matches = self::getElementsByClass($dom, 'div.custom-link')) {
                /** @var DOMElement $match */
                foreach ($matches as $key => $match) {
                    if ($match->hasChildNodes() && $match->hasAttribute('data-mblock')) {
                        $value = '';
                        /** @var DOMElement $child */
                        foreach ($match->getElementsByTagName('input') as $child) {
                            if ($child->getAttribute('type') == 'hidden') {
                                $value = $child->getAttribute('value');
                                break;
                            }
                        }
                        /** @var DOMElement $child */
                        foreach ($match->getElementsByTagName('input') as $child) {
                            if ($child->getAttribute('type') == 'text') {
                                // is numeric also link
                                if (is_numeric($value)) {
                                    // add link art name
                                    $linkInfo = self::getLinkInfo($value);
                                    $child->setAttribute('value', $linkInfo['art_name']);
                                } else {
                                    $child->setAttribute('value', $value);
                                }
                                break;
                            }
                        }
                        $match->setAttribute('data-mblock', true);
                    }
                }
            }
        }
    }


}