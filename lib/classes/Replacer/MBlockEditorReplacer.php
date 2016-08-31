<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockEditorReplacer extends MBlockSystemButtonReplacer
{
    /**
     * @param MBlockItem $item
     * @param $count
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceEditorArea(MBlockItem $item, $count)
    {
        // set phpquery document
        $document = phpQuery::newDocumentHTML($item->getForm());
        $item->addPayload('count-id', $count);

        // find input group
        if ($matches = $document->find('textarea')) {
            /** @var DOMElement $match */
            foreach ($matches as $key => $match) {
                $item->addPayload('replace-id', $key);
                $class = $match->getAttribute('class');

                // process by type
                if (strpos($class, 'redactorEditor') !== false) {
                    // change for id
                    self::processRedactor($match, $item);
                }
                if (strpos($class, 'markitupEditor') !== false) {
                    // change for id
                    self::processMarkitup($match, $item);
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
    public static function processRedactor(DOMElement $dom, MBlockItem $item)
    {
        // change for id
        self::replaceId($dom, $item);
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    public static function processMarkitup(DOMElement $dom, MBlockItem $item)
    {
        // change for id
        self::replaceId($dom, $item);
    }
}