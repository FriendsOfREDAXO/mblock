<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockBootstrapReplacer
{
    /**
     * @param MBlockItem $item
     * @param $count
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceTabIds(MBlockItem $item, $count)
    {
        // set phpquery document
        $document = phpQuery::newDocumentHTML($item->getForm());
        $item->addPayload('count-id', $count);
        // find input group
        if ($matches = $document->find('a[data-toggle="tab"]')) {
            /** @var DOMElement $match */
            foreach ($matches as $key => $match) {
                $item->addPayload('replace-id', $key);

                $href = str_replace('#','',$match->getAttribute('href'));
                $newHref = $href . '_' . $count . $_SESSION['mblock_count'] . '00' . $key;
                $match->setAttribute('href', '#' . $newHref);

                $parent = $match->parentNode->parentNode->parentNode;

                if ($parent->hasChildNodes()) {
                    /** @var DOMElement $childNode */
                    foreach ($parent->getElementsByTagName('div') as $childNode) {
                        if ($childNode->hasChildNodes()) {
                            /** @var DOMElement $child */
                            foreach ($childNode->getElementsByTagName('div') as $child) {
                                if ($child->getAttribute('id') == $href) {
                                    $child->setAttribute('id', $newHref);
                                }
                            }
                        }
                    }
                }
            }
        }

        // return the manipulated html output
        return $document->htmlOuter();
    }

    /**
     * @param MBlockItem $item
     * @param $count
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceCollapseIds(MBlockItem $item, $count)
    {
        // set phpquery document
        $document = phpQuery::newDocumentHTML($item->getForm());
        $item->addPayload('count-id', $count);

        if ($matches = $document->find('a[data-toggle="collapse"]')) {
            /** @var DOMElement $match */
            foreach ($matches as $key => $match) {
                $item->addPayload('replace-id', $key);

                $href = str_replace('#','',$match->getAttribute('data-target'));
                $newHref = $href . '_' . $count . $_SESSION['mblock_count'] . '00' . $key;
                $match->setAttribute('data-target', '#' . $newHref);
                if ($match->hasAttribute('data-parent')) {
                    $match->setAttribute('data-parent', '#accgr' . '_' . $count . $_SESSION['mblock_count'] . '00');
                }

                $next = $match->nextSibling;

                if ($next->getAttribute('id') == $href) {
                    $next->setAttribute('id', $newHref);
                }

                $parent = $match->parentNode->parentNode;

                if ($parent->hasAttribute('data-group-accordion') && $parent->getAttribute('data-group-accordion') == 1) {
                    $parent->setAttribute('id', 'accgr' . '_' . $count . $_SESSION['mblock_count'] . '00');
                }
            }
        }

        // return the manipulated html output
        return $document->htmlOuter();
    }
}