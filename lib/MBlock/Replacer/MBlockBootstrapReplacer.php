<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */



namespace FriendsOfRedaxo\MBlock\Replacer;

use FriendsOfRedaxo\MBlock\DTO\MBlockItem;

class MBlockBootstrapReplacer
{
    use \FriendsOfRedaxo\MBlock\Decorator\MBlockDOMTrait;

    /**
     * @param MBlockItem $item
     * @param $count
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceTabIds(MBlockItem $item, $count)
    {
        // set dom document
        $dom = self::createDom($item->getForm());
        $item->addPayload('count-id', $count);
        // find tab group
        if ($matches = self::getElementsByData($dom, 'a[data-toggle="tab"]')) {
            /** @var DOMElement $match */
            foreach ($matches as $key => $match) {
                $item->addPayload('replace-id', $key);

                $href = str_replace('#','',$match->getAttribute('href'));
                $mblockCount = MBlockSessionHelper::getCurrentCount();
                $newHref = $href . '_' . $count . $mblockCount . '00' . $key;
                $match->setAttribute('href', '#' . $newHref);

                // Sichere DOM-Navigation mit Null-Checks
                $currentParent = $match->parentNode;
                if ($currentParent && $currentParent->parentNode && $currentParent->parentNode->parentNode) {
                    $targetParent = $currentParent->parentNode->parentNode;
                    
                    if ($targetParent instanceof DOMElement && $targetParent->hasChildNodes()) {
                        /** @var DOMElement $childNode */
                        foreach ($targetParent->getElementsByTagName('div') as $childNode) {
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
        }
        // return the manipulated html output
        return self::saveHtml($dom);
    }

    /**
     * @param MBlockItem $item
     * @param $count
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceCollapseIds(MBlockItem $item, $count)
    {
        // set dom document
        $dom = self::createDom($item->getForm());
        $item->addPayload('count-id', $count);
        // find tab group
        if ($matches = self::getElementsByData($dom, 'a[data-toggle="collapse"]')) {
            /** @var DOMElement $match */
            foreach ($matches as $key => $match) {
                $item->addPayload('replace-id', $key);

                $href = str_replace('#','',$match->getAttribute('data-target'));
                $mblockCount = MBlockSessionHelper::getCurrentCount();
                $newHref = $href . '_' . $count . $mblockCount . '00' . $key;
                $match->setAttribute('data-target', '#' . $newHref);
                if ($match->hasAttribute('data-parent')) {
                    $match->setAttribute('data-parent', '#accgr' . '_' . $count . $mblockCount . '00');
                }

                $next = $match->nextSibling;

                if ($next instanceof DOMElement && $next->hasAttribute('id') && $next->getAttribute('id') == $href) {
                    $next->setAttribute('id', $newHref);
                }

                // Sichere DOM-Navigation mit Null-Checks  
                $currentParent = $match->parentNode;
                if ($currentParent && $currentParent->parentNode) {
                    $targetParent = $currentParent->parentNode;
                    
                    if ($targetParent instanceof DOMElement && 
                        $targetParent->hasAttribute('data-group-accordion') && 
                        $targetParent->getAttribute('data-group-accordion') == 1) {
                        $targetParent->setAttribute('id', 'accgr' . '_' . $count . $mblockCount . '00');
                    }
                }
            }
        }
        // return the manipulated html output
        return self::saveHtml($dom);
    }
}