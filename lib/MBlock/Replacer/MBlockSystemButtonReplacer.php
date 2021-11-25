<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockSystemButtonReplacer
{
    use \MBlock\Decorator\MBlockDOMTrait;
    const REX_VERSION = '5.12.1';

    /**
     * @param MBlockItem $item
     * @return String
     * @author Joachim Doerr
     */
    public static function replaceCustomLinkText(MBlockItem $item)
    {
        // set dom document
        $dom = self::createDom($item->getForm());
        // find custom-link
        if ($matches = self::getElementsByClass($dom, 'div.custom-link')) {
            /** @var DOMElement $match */
            foreach ($matches as $key => $match) {
                if ($match->hasChildNodes()) {
                    $value = '';
                    /** @var DOMElement $child */
                    foreach ($match->getElementsByTagName('input') as $child) {
                        if ($child->getAttribute('type') == 'hidden') {
                            $value = $child->getAttribute('value');
                            break;
                        }
                    }
                    /** @var DOMElement $child */
                    foreach ($match->getElementsByTagName('input') as $child) {
                        if ($child->getAttribute('type') == 'text') {
                            // is numeric also link
                            if (is_numeric($value)) {
                                // add link art name
                                $linkInfo = self::getLinkInfo($value);
                                $child->setAttribute('value', $linkInfo['art_name']);
                            } else {
                                $child->setAttribute('value', $value);
                            }
                            break;
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
    public static function replaceSystemButtons(MBlockItem $item, $count)
    {
        // set dom document
        $dom = self::createDom($item->getForm());
        $item->addPayload('count-id', $count);
        // find input group
        if ($matches = self::getElementsByClass($dom, 'div.input-group')) {
            /** @var DOMElement $match */
            foreach ($matches as $key => $match) {
                $item->addPayload('replace-id', $key);
                if ($match->hasChildNodes()) {
                    /** @var DOMElement $child */
                    foreach ($match->getElementsByTagName('input') as $child) {
                        if ($child instanceof DOMElement) { // && $child->getAttribute('type') == 'hidden') {
                            // set id and name
                            $id = $child->getAttribute('id');
                            $name = $child->getAttribute('name');
                            $type = $child->getAttribute('type');

                            // process by type
                            if (strpos($id, 'REX_MEDIA_') !== false && $type == 'text') {
                                // media button
                                self::processMedia($match, $item);
                            }
                            if (strpos($id, 'REX_MEDIALIST_') !== false) {
                                // medialist button
                                self::processMediaList($match, $item);
                            }
                            if (strpos($name, 'REX_LINK_') !== false && $type == 'text') {
                                // link button
                                if (strpos($match->getAttribute('class'), 'custom-link') !== false) {
                                    self::processCustomLink($match, $item);
                                } else {
                                    self::processLink($match, $item);
                                }
                            }
                            if (strpos($id, 'REX_LINKLIST_') !== false) {
                                // linklist button
                                self::processLinkList($match, $item);
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
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function processMedia(DOMElement $dom, MBlockItem $item)
    {
        // set system name
        $item->setSystemName('REX_MEDIA');
        // has children ?
        if ($dom->hasChildNodes()) {
            // replace name first child is input
            if (strrpos($dom->firstChild->getAttribute('name'), 'REX_INPUT_MEDIA') !== false) {
                self::replaceName($dom->firstChild, $item, 'REX_INPUT_MEDIA');
            }
            // change for id
            self::replaceId($dom->firstChild, $item);
            // change onclick id
            if (rex_version::compare(rex::getVersion(),self::REX_VERSION, '>=')) {
                self::replaceOnClick($dom, $item, 'REXMedia(', '(\'?', '\'?,', '(\'', '\',');
                self::replaceOnClick($dom, $item, 'REXMedia(', '(\'?', '\'?\)', '(\'', '\')');
            } else {
                self::replaceOnClick($dom, $item, 'REXMedia(', '(', ',', '(', ',');
                self::replaceOnClick($dom, $item, 'REXMedia(', '(', ')', '(', ')');
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function processMediaList(DOMElement $dom, MBlockItem $item)
    {
        // set system name
        $item->setSystemName('REX_MEDIALIST');
        $name = '';
        // has children ?
        if ($dom->hasChildNodes()) {
            if ($dom->firstChild->hasAttribute('name')) {
                // remove name
                // $dom->firstChild->removeAttribute('name');
                /** @var DOMElement $child */
                foreach ($dom->getElementsByTagName('input') as $child) {
                    if (strrpos($child->getAttribute('name'), 'REX_INPUT_MEDIALIST') !== false) {
                        // replace name
                        self::replaceName($child, $item, 'REX_INPUT_MEDIALIST');
                    }
                    // change id
                    self::replaceId($child, $item);
                    if ($child->getAttribute('type') == 'hidden') {
                        $name = $child->getAttribute('name');
                    }
                }
                /** @var DOMElement $child */
                foreach ($dom->getElementsByTagName('select') as $child) {
                    if (strpos($child->getAttribute('id'), 'REX_MEDIALIST_SELECT_') !== false) {
                        // replace name
                        $child->setAttribute('name', str_replace($item->getSystemId(), $item->getId(), $child->getAttribute('name')));
                        // change id
                        self::replaceId($child, $item);
                        // add options
                        self::addMediaSelectOptions($dom->firstChild, $item, $name);
                    }
                }
                // change click id
                if (rex_version::compare(rex::getVersion(),self::REX_VERSION, '>=')) {
                    self::replaceOnClick($dom, $item, 'REXMedialist(', '(\'?', '\'?,', '(\'', '\',');
                    self::replaceOnClick($dom, $item, 'REXMedialist(', '(\'?', '\'?\)', '(\'', '\')');
                } else {
                    self::replaceOnClick($dom, $item, 'REXMedialist(', '(', ',', '(', ',');
                    self::replaceOnClick($dom, $item, 'REXMedialist(', '(', ')', '(', ')');
                }
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function processLink(DOMElement $dom, MBlockItem $item)
    {
        // set system name
        $item->setSystemName('REX_LINK');
        $name = '';
        // has children ?
        if ($dom->hasChildNodes()) {
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('input') as $child) {
                // hidden input
                if (strrpos($child->getAttribute('name'), 'REX_INPUT_LINK') !== false) {
                    // replace name
                    self::replaceName($child, $item, 'REX_INPUT_LINK');
                }
                // change id
                self::replaceId($child, $item);
                if ($child->getAttribute('type') == 'hidden') {
                    $name = $child->getAttribute('name');
                }
            }
            // remove name
            $dom->firstChild->removeAttribute('name');
            // add link art name
            self::addArtName($dom->firstChild, $item, $name);
            // change click id
            if (rex_version::compare(rex::getVersion(),self::REX_VERSION, '>=')) {
                self::replaceOnClick($dom, $item, 'REXLink(', '(\'?', '\'?\)','(\'', '\')');
                self::replaceOnClick($dom, $item, 'openLinkMap(', '_', '\'', '_', '\'');
            } else {
                self::replaceOnClick($dom, $item, 'REXLink(', '(', ')', '(', ')');
                self::replaceOnClick($dom, $item, 'openLinkMap(', '_', '\'', '_', '\'');
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function processCustomLink(DOMElement $dom, MBlockItem $item)
    {
        if ($dom->hasAttribute('data-id')) {
            self::replaceDataId($dom, $item);
        }
        // set system name
        $item->setSystemName('REX_LINK');
        $id = $item->getPayload('count-id') . $_SESSION['mblock_count'] . '00' . $item->getPayload('replace-id');
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
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function processLinkList(DOMElement $dom, MBlockItem $item)
    {
        // set system name
        $item->setSystemName('REX_LINKLIST');
        $name = '';
        // has children ?
        if ($dom->hasChildNodes()) {
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('input') as $child) {
                if ($child->getAttribute('type') == 'hidden') {
                    if (strrpos($child->getAttribute('name'), 'REX_INPUT_LINKLIST') !== false) {
                        // replace name
                        self::replaceName($child, $item, 'REX_INPUT_LINKLIST');
                    }
                    $name = $child->getAttribute('name');
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
                    self::addLinkSelectOptions($child, $item, $name);
                }
            }
            if (rex_version::compare(rex::getVersion(),self::REX_VERSION, '>=')) {
                self::replaceOnClick($dom, $item, 'REXLinklist(', '(\'?', '\'?,','(\'', '\',');
                self::replaceOnClick($dom, $item, 'deleteREXLinklist(', '(\'?', '\'?', '(\'', '\'');
            } else {
                self::replaceOnClick($dom, $item, 'REXLinklist(', '(', ',', '(', ',');
                self::replaceOnClick($dom, $item, 'deleteREXLinklist(', '(', ')', '(', ')');
            }

        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param $btnFindKey
     * @param string $prefix
     * @param string $suffix
     * @author Joachim Doerr
     */
    protected static function replaceOnClick(DOMElement $dom, MBlockItem $item, $btnFindKey, $searchPrefix = '', $searchSuffix = '', $prefix = '', $suffix = '')
    {
        // find a buttons and replace id
        if ($dom->hasChildNodes()) {
            /** @var DOMElement $child */
            foreach($dom->getElementsByTagName('a') as $child) {
                if ($child->hasAttribute('onclick')) {
                    if (strpos($child->getAttribute('onclick'), $btnFindKey) !== false) {
                        $child->setAttribute('onclick', preg_replace('/\\'.$searchPrefix.'\d\\'.$searchSuffix.'/', $prefix . $item->getPayload('count-id') . $_SESSION['mblock_count'] . '00' . $item->getPayload('replace-id') . $suffix, $child->getAttribute('onclick')));
                    }
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
        $dom->setAttribute('id', preg_replace('/\_\d+/', '_' . $item->getPayload('count-id') . $_SESSION['mblock_count'] . '00' . $item->getPayload('replace-id'), $dom->getAttribute('id')));
        return $dom->getAttribute('id');
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceDataId(DOMElement $dom, MBlockItem $item)
    {
        // get input id
        $dom->setAttribute('data-id', $item->getPayload('count-id') . $_SESSION['mblock_count'] . '00' . $item->getPayload('replace-id'));
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
            $replaceName = str_replace(strtoupper('_input'), '', $name);
            $dom->setAttribute('name', str_replace(array($name, '[' . $item->getSystemId() . ']'), array('REX_INPUT_VALUE', '[' . $item->getValueId() . '][0][' . $replaceName . '_' . $item->getSystemId() . ']'), $dom->getAttribute('name')));
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function addMediaSelectOptions(DOMElement $dom, MBlockItem $item, $name)
    {
        self::setSystemIdByName($name, $item);

        if (is_array($item->getResult()) && (
                array_key_exists($item->getSystemName() . '_' . $item->getSystemId(), $item->getResult()) OR
                array_key_exists(strtolower($item->getSystemName()) . '_' . $item->getSystemId(), $item->getResult())
            )
        ) {
            $key = (isset($item->getResult()[$item->getSystemName() . '_' . $item->getSystemId()])) ? $item->getSystemName() . '_' . $item->getSystemId() : strtolower($item->getSystemName()) . '_' . $item->getSystemId();
            $resultItems = explode(',', $item->getResult()[$key]);
            if (sizeof($resultItems) > 0) {
                foreach ($resultItems as $resultItem) {
                    if (!empty($resultItem)) {
                        $dom->appendChild(new DOMElement('option', $resultItem));
                    }
                }
                /** @var DOMElement $child */
                foreach ($dom->childNodes as $child) {
                    if ($child->nodeName != 'option') { // Patch xampp gegen ooops
                        continue;
                    }
                    $child->setAttribute('value', $child->nodeValue);
                    $child->removeAttribute('selected');
                }
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param string $name
     * @author Joachim Doerr
     */
    protected static function addLinkSelectOptions(DOMElement $dom, MBlockItem $item, $name = '')
    {
        self::setSystemIdByName($name, $item);

        if (is_array($item->getResult()) && (
                array_key_exists($item->getSystemName() . '_' . $item->getSystemId(), $item->getResult()) OR
                array_key_exists(strtolower($item->getSystemName()) . '_' . $item->getSystemId(), $item->getResult())
            )
        ) {
            $key = (isset($item->getResult()[$item->getSystemName() . '_' . $item->getSystemId()])) ? $item->getSystemName() . '_' . $item->getSystemId() : strtolower($item->getSystemName()) . '_' . $item->getSystemId();
            $resultItems = explode(',', $item->getResult()[$key]);
            if (sizeof($resultItems) > 0) {
                foreach ($resultItems as $resultItem) {
                    if (!empty($resultItem)) {
                        $dom->appendChild(new DOMElement('option', $resultItem));
                    }
                }
                /** @var DOMElement $child */
                foreach ($dom->childNodes as $child) {
                    if ($child->nodeName != 'option') { // Patch xampp gegen ooops
                       continue;
                    }                    
                    $child->setAttribute('value', $child->nodeValue);
                    $child->nodeValue = htmlentities(self::getLinkInfo($child->getAttribute('value'))['art_name']);
                    $child->removeAttribute('selected');
                }
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param string $name
     * @author Joachim Doerr
     */
    protected static function addArtName(DOMElement $dom, MBlockItem $item, $name = '')
    {
        self::setSystemIdByName($name, $item);

        if (is_array($item->getResult()) && (
                array_key_exists($item->getSystemName() . '_' . $item->getSystemId(), $item->getResult()) OR
                array_key_exists(strtolower($item->getSystemName()) . '_' . $item->getSystemId(), $item->getResult())
            )
        ) {
            $key = (isset($item->getResult()[$item->getSystemName() . '_' . $item->getSystemId()])) ? $item->getSystemName() . '_' . $item->getSystemId() : strtolower($item->getSystemName()) . '_' . $item->getSystemId();
            $linkInfo = self::getLinkInfo($item->getResult()[$key]);
            $dom->setAttribute('value', $linkInfo['art_name']);
        }
    }

    /**
     * @param $name
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    private static function setSystemIdByName($name, MBlockItem $item)
    {
        if ($name != '' && preg_match('/\_\d+/', $name, $matches))
            $item->setSystemId(str_replace('_','', $matches[0]));
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
