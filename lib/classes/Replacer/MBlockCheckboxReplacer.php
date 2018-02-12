<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockCheckboxReplacer
{
    /**
     * @param MBlockItem $item
     * @param $count
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceCheckboxesBlockHolder(MBlockItem $item, $count)
    {
        // set phpquery document
        $document = phpQuery::newDocumentHTML($item->getForm());
        $holderInput = false;
        $holderName = "REX_INPUT_VALUE[{$item->getValueId()}][{$item->getId()}][checkbox_block_hold]";

        // find input group
        if ($matches = $document->find('input')) {
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
        return ($holderInput) ? '<input type="hidden" class="not_delete" name="' . $holderName . '" value="hold_block">' . $document->htmlOuter() : $document->htmlOuter();
    }
}