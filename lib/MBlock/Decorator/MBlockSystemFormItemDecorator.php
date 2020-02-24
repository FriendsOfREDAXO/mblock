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

class MBlockSystemFormItemDecorator extends MBlockWidgetReplacer
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
                    if ($match->hasChildNodes() && !(strpos($match->getAttribute('class'), 'custom-link') !== false)) {
                        /** @var DOMElement $child */
                        foreach ($match->getElementsByTagName('input') as $child) {
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
                                    self::processLinkList($match, $item, $nestedCount);
                                }
                                if (strpos($id, 'REX_MEDIALIST_') !== false) {
                                    self::processMediaList($match, $item, $nestedCount);
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