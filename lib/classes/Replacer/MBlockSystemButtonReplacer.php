<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockSystemButtonReplacer
{
    /**
     * @param MBlockItem $item
     * @param $count
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceSystemButtons(MBlockItem $item, $count)
    {
        // set phpquery document
        $document = phpQuery::newDocumentHTML($item->getForm());
        $item->addPayload('count-id', $count);

        // find input group
        if ($matches = $document->find('div.input-group')) {
            /** @var DOMElement $match */
            foreach ($matches as $key => $match) {
                $item->addPayload('replace-id', $key);
                if ($match->hasChildNodes()) {
                    /** @var DOMElement $child */
                    foreach ($match->getElementsByTagName('input') as $child) {
                        if ($child instanceof DOMElement) {
                            // set id and name
                            $id = $child->getAttribute('id');
                            $name = $child->getAttribute('name');

                            // process by type
                            if (strpos($id, 'REX_MEDIA_') !== false) {
                                // media button
                                self::processMedia($document, $match, $item);
                            }
                            if (strpos($id, 'REX_MEDIALIST_') !== false) {
                                // medialist button
                                self::processMediaList($document, $match, $item);
                            }
                            if (strpos($name, 'REX_LINK_') !== false) {
                                // link button
                                if (strpos($match->getAttribute('class'), 'custom-link') !== false) {
                                    self::processCustomLink($document, $match, $item);
                                } else {
                                    self::processLink($document, $match, $item);
                                }

                            }
                            if (strpos($id, 'REX_LINKLIST_') !== false) {
                                // linklist button
                                self::processLinkList($document, $match, $item);
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
     * @param phpQueryObject $document
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function processMedia(phpQueryObject $document, DOMElement $dom, MBlockItem $item)
    {
        // set system name
        $item->setSystemName('REX_INPUT_MEDIA');
        // has children ?
        if ($dom->hasChildNodes()) {
            // replace name first child is input
            self::replaceName($dom->firstChild, $item, 'REX_INPUT_MEDIA');
            // change for id
            self::replaceId($dom->firstChild, $item);
            // change onclick id
            self::replaceOnClick($document, $item, 'REXMedia(', '(', ',');
            self::replaceOnClick($document, $item, 'REXMedia(', '(', ')');
        }
    }

    /**
     * @param phpQueryObject $document
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function processMediaList(phpQueryObject $document, DOMElement $dom, MBlockItem $item)
    {
        // set system name
        $item->setSystemName('REX_INPUT_MEDIALIST');

        // has children ?
        if ($dom->hasChildNodes()) {
            if ($dom->firstChild->hasAttribute('name')) {
                // remove name
                $dom->firstChild->removeAttribute('name');
                // change id
                self::replaceId($dom->firstChild, $item);
                /** @var DOMElement $child */
                foreach ($dom->getElementsByTagName('input') as $child) {
                    if ($child->getAttribute('type') == 'hidden') {
                        // replace name
                        self::replaceName($child, $item, 'REX_INPUT_MEDIALIST');
                        // change id
                        self::replaceId($child, $item);
                    }
                }
                // add options
                self::addMediaSelectOptions($dom->firstChild, $item);
                // change click id
                self::replaceOnClick($document, $item, 'REXMedialist(', '(', ',');
                self::replaceOnClick($document, $item, 'REXMedialist(', '(', ')');
            }
        }
    }

    /**
     * @param phpQueryObject $document
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function processLink(phpQueryObject $document, DOMElement $dom, MBlockItem $item)
    {
        // set system name
        $item->setSystemName('REX_INPUT_LINK');
        // has children ?
        if ($dom->hasChildNodes()) {
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('input') as $child) {
                // hidden input
                if (strpos($child->getAttribute('name'), 'REX_INPUT_LINK') !== false) {
                    // replace name
                    self::replaceName($child, $item, 'REX_INPUT_LINK');
                }
                // change id
                self::replaceId($child, $item);
            }
            // remove name
            $dom->firstChild->removeAttribute('name');
            // add link art name
            self::addArtName($dom->firstChild, $item);
            // change click id
            self::replaceOnClick($document, $item, 'REXLink(', '(', ')');
            // change click id
            self::replaceOnClick($document, $item, 'openLinkMap(', '_', '\'');
        }
    }

    /**
     * @param phpQueryObject $document
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function processCustomLink(phpQueryObject $document, DOMElement $dom, MBlockItem $item)
    {
        if ($dom->hasAttribute('data-id')) {
            self::replaceDataId($dom, $item);
        }

        // set system name
        $item->setSystemName('REX_INPUT_LINK');

        $id = $item->getPayload('count-id') .'00' . $item->getPayload('replace-id');

        // has children ?
        if ($dom->hasChildNodes()) {
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('input') as $child) {
                // hidden input
                if (strpos($child->getAttribute('name'), 'REX_INPUT_LINK') !== false) {
                    // replace name
                    self::replaceName($child, $item, 'REX_INPUT_LINK');
                }
                // change id
                $attrId = preg_replace('/\d+/', $id, $child->getAttribute('id'));
                $child->setAttribute('id', $attrId);
            }
            // remove name
            $dom->firstChild->removeAttribute('name');
            // add link art name
            self::addArtName($dom->firstChild, $item);

            if ($parent = $dom->parentNode) {
                if ($parent->hasChildNodes()) {
                    foreach ($parent->getElementsByTagName('a') as $child) {
                        $attrId = preg_replace('/\d+/', $id, $child->getAttribute('id'));
                        $child->setAttribute('id', $attrId);
                    }
                }
            }
        }
    }

    /**
     * @param phpQueryObject $document
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function processLinkList(phpQueryObject $document, DOMElement $dom, MBlockItem $item)
    {
        // set system name
        $item->setSystemName('REX_INPUT_LINKLIST');
        // has children ?
        if ($dom->hasChildNodes()) {
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('input') as $child) {
                if ($child->getAttribute('type') == 'hidden') {
                    // replace name
                    self::replaceName($child, $item, 'REX_INPUT_LINKLIST');
                    // change id
                    self::replaceId($child, $item);
                }
            }
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('select') as $child) {
                if (strpos($child->getAttribute('id'), 'REX_LINKLIST_SELECT_') !== false) {
                    // replace name
                    $child->setAttribute('name', str_replace($item->getSystemId(), $item->getId(), $child->getAttribute('name')));
                    // replace id
                    self::replaceId($child, $item);
                    // add options
                    self::addLinkSelectOptions($child, $item);
                }
            }
            // change click id
            self::replaceOnClick($document, $item, 'REXLinklist(', '(', ',');
            // change click id
            self::replaceOnClick($document, $item, 'deleteREXLinklist(', '(', ')');
        }
    }

    /**
     * @param phpQueryObject $document
     * @param MBlockItem $item
     * @param $btnFindKey
     * @param string $prefix
     * @param string $suffix
     * @author Joachim Doerr
     */
    protected static function replaceOnClick(phpQueryObject $document, MBlockItem $item, $btnFindKey, $prefix = '', $suffix = '')
    {
        // find a buttons and replace id
        if ($matches = $document->find('a.btn-popup')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                if (strpos($match->getAttribute('onclick'), $btnFindKey) !== false) {
                    $match->setAttribute('onclick', str_replace($prefix . $item->getSystemId() . $suffix, $prefix . $item->getPayload('count-id') .'00'. $item->getPayload('replace-id') . $suffix, $match->getAttribute('onclick')));
                }
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     * @return string|null
     */
    protected static function replaceId(DOMElement $dom, MBlockItem $item)
    {
        // get input id
        $id = $dom->getAttribute('id');
        preg_match('/_\d/', $id, $matches);
        // found
        if ($matches) {
            // replace id
            $dom->setAttribute('id', str_replace($matches[0], '_' . $item->getPayload('count-id') .'00' . $item->getPayload('replace-id'), $id));
            return $dom->getAttribute('id');
        }
        return null;
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceDataId(DOMElement $dom, MBlockItem $item)
    {
        // get input id
        $dom->setAttribute('data-id', $item->getPayload('count-id') .'00' . $item->getPayload('replace-id'));
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param $name
     * @author Joachim Doerr
     */
    protected static function replaceName(DOMElement $dom, MBlockItem $item, $name)
    {
        // get name
        $matches = MBlockFormItemDecorator::getName($dom);
        // found
        if ($matches) {
            // set system id
            $item->setSystemId($matches[1]);
            // and replace name attribute
            $dom->setAttribute('name', str_replace(array($name, '[' . $item->getSystemId() . ']'), array('REX_INPUT_VALUE', '[' . $item->getValueId() . '][0][' . $name . '_' . $item->getSystemId() . ']'), $dom->getAttribute('name')));
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function addMediaSelectOptions(DOMElement $dom, MBlockItem $item)
    {
        if (is_array($item->getResult()) && array_key_exists($item->getSystemName() . '_' . $item->getSystemId(), $item->getResult())) {
            $resultItems = explode(',', $item->getResult()[$item->getSystemName() . '_' . $item->getSystemId()]);
            foreach ($resultItems as $resultItem) {
                $dom->appendChild(new DOMElement('option', $resultItem));
            }
            /** @var DOMElement $child */
            foreach ($dom->childNodes as $child) {
                $child->setAttribute('value', $child->nodeValue);
                $child->removeAttribute('selected');
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function addLinkSelectOptions(DOMElement $dom, MBlockItem $item)
    {
        if (is_array($item->getResult()) && array_key_exists($item->getSystemName() . '_' . $item->getSystemId(), $item->getResult())) {
            $resultItems = explode(',', $item->getResult()[$item->getSystemName() . '_' . $item->getSystemId()]);
            if ($resultItems[0] != '') {
                foreach ($resultItems as $resultItem) {
                    $dom->appendChild(new DOMElement('option', $resultItem));
                }
                /** @var DOMElement $child */
                foreach ($dom->childNodes as $child) {
                    $child->setAttribute('value', $child->nodeValue);
                    $child->nodeValue = self::getLinkInfo($child->getAttribute('value'))['art_name'];
                    $child->removeAttribute('selected');
                }
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function addArtName(DOMElement $dom, MBlockItem $item)
    {
        if (is_array($item->getResult()) && array_key_exists($item->getSystemName() . '_' . $item->getSystemId(), $item->getResult())) {
            $linkInfo = self::getLinkInfo($item->getResult()[$item->getSystemName() . '_' . $item->getSystemId()]);
            $dom->setAttribute('value', $linkInfo['art_name']);
        }
    }

    /**
     * @param $id
     * @return array
     * @author Joachim Doerr
     */
    private static function getLinkInfo($id)
    {
        $art_name = '';
        $category = 0;
        $art = rex_article::get($id);
        if ($art instanceof rex_article) {
            $art_name = $art->getName();
            $category = $art->getCategoryId();
        }
        return array('art_name' => $art_name, 'category_id' => $category);
    }
}