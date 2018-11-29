<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockCheckboxReplacer
{
    use \MBlock\Decorator\MBlockDOMTrait;

    /**
     * @param MBlockItem $item
     * @param $count
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceCheckboxesBlockHolder(MBlockItem $item, $count)
    {
        // set phpquery document
        $dom = self::createDom($item->getForm());
        $holderInput = false;
        $holderName = "REX_INPUT_VALUE[{$item->getValueId()}][{$item->getId()}][checkbox_block_hold]";

        // find input group
        if ($matches = $dom->getElementsByTagName('input')) {
            /** @var DOMElement $match */
            foreach ($matches as $key => $match) {
                switch ($match->getAttribute('type')) {
                    case 'checkbox':
                        $holderInput = true;
                        break;
                }
            }
        }

        // return the manipulated html output
        return ($holderInput) ? '<input type="hidden" class="not_delete" name="' . $holderName . '" value="hold_block">' . self::saveHtml($dom) : self::saveHtml($dom);
    }
}