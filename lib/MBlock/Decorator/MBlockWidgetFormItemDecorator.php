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
use MBlock\Replacer\MBlockWidgetReplacer;

class MBlockWidgetFormItemDecorator extends MBlockWidgetReplacer
{
    use MBlockDOMTrait;

    /**
     * @param MBlockItem $item
     * @param array $nestedCount
     * @author Joachim Doerr
     */
    public static function decorateCustomLinkFormItem(MBlockItem $item, $nestedCount = array())
    {
        // set dom document
        $dom = $item->getFormDomDocument();
        if ($dom instanceof \DOMDocument) {
            // find input group
            if ($matches = self::getElementsByClass($dom, 'div.custom-link')) {
                /** @var DOMElement $match */
                foreach ($matches as $key => $match) {
                    if ($match->hasChildNodes() && !$match->hasAttribute('data-mblock')) {
                        self::processCustomLink($match, $item, $nestedCount);
                        $match->setAttribute('data-mblock', true);
                    }
                }
            }
        }
    }

    /**
     * @param MBlockItem $item
     * @param array $nestedCount
     * @author Joachim Doerr
     */
    public static function decorateImagesListFormItem(MBlockItem $item, $nestedCount = array())
    {
        // set dom document
        $dom = $item->getFormDomDocument();
        if ($dom instanceof \DOMDocument) {
            if ($matches = self::getElementsByClass($dom, 'div.custom-imglist')) {
                /** @var DOMElement $match */
                foreach ($matches as $key => $match) {
                    if ($match->hasChildNodes() && !$match->hasAttribute('data-mblock')) {
                        self::processImageList($match, $item, $nestedCount);
//                        $match->setAttribute('data-mblock', true);
                    }
                }
            }
        }
    }
}