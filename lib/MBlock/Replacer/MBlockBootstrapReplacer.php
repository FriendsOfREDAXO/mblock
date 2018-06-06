<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\Replacer;


use DOMElement;
use MBlock\DTO\MBlockItem;

class MBlockBootstrapReplacer
{
    use \MBlock\Decorator\MBlockDOMTrait;

    /**
     * @param MBlockItem $item
     * @param $count
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceTabIds(MBlockItem $item, $count)
    {
        // set dom document
        $dom = $item->getForm();
        $item->addPayload('count-id', $count);
        if ($dom instanceof \DOMDocument) {
            // find tab group
            if ($matches = self::getElementsByData($dom, 'a[data-toggle="tab"]')) {
                /** @var DOMElement $match */
                foreach ($matches as $key => $match) {
                    if (!$match->hasAttribute('data-mblock')) {
                        $item->addPayload('replace-id', $key);

                        $href = str_replace('#', '', $match->getAttribute('href'));
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
                        $match->setAttribute('data-mblock', true);
                    }
                }
            }
            // return the manipulated html output
            // return $dom->saveHTML();
        }
    }

    /**
     * @param MBlockItem $item
     * @param $count
     * @author Joachim Doerr
     */
    public static function replaceCollapseIds(MBlockItem $item, $count)
    {
        // set dom document
        $dom = $item->getForm();
        $item->addPayload('count-id', $count);

        if ($dom instanceof \DOMDocument) {
            // find tab group
            if ($matches = self::getElementsByData($dom, 'a[data-toggle="collapse"]')) {
                /** @var DOMElement $match */
                foreach ($matches as $key => $match) {
                    if (!$match->hasAttribute('data-mblock')) {
                        $item->addPayload('replace-id', $key);

                        $href = str_replace('#', '', $match->getAttribute('data-target'));
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
                        $match->setAttribute('data-mblock', true);
                    }
                }
            }
        }
        // return the manipulated html output
        // return $dom->saveHTML();
    }
}