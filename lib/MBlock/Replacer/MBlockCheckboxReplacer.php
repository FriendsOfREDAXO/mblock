<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\Replacer;


use DOMElement;
use MBlock\DTO\MBlockItem;

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
        // set dom document
        $dom = $item->getForm();
        if ($dom instanceof \DOMDocument) {
            $holderInput = false;
            if (!is_null($item->getSubId())) {
                $holderName = "REX_INPUT_VALUE[{$item->getValueId()}][{$item->getSubId()}][{$item->getItemId()}][checkbox_block_hold]";
            } else {
                $holderName = "REX_INPUT_VALUE[{$item->getValueId()}][{$item->getItemId()}][checkbox_block_hold]";
            }
            // find input group
            if ($matches = $dom->getElementsByTagName('input')) {
                /** @var DOMElement $match */
                foreach ($matches as $key => $match) {
                    if (!$match->hasAttribute('data-mblock')) {
                        switch ($match->getAttribute('type')) {
                            case 'checkbox':
                                $holderInput = true;
                                break;
                        }
                        $match->setAttribute('data-mblock', true);
                    }
                }
            }
            // return the manipulated html output
            return ($holderInput) ? '<input type="hidden" class="not_delete" name="' . $holderName . '" value="hold_block">' . $dom->saveHTML() : $dom->saveHTML();
        }
    }
}