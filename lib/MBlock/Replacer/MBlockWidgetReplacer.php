<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\Replacer;


use DOMElement;
use MBlock\Decorator\MBlockFormItemDecorator;
use MBlock\DTO\MBlockItem;
use rex_article;
use rex_url;
use function Matrix\identity;

class MBlockWidgetReplacer extends MBlockElementReplacer
{
    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param null $nestedCount
     * @author Joachim Doerr
     */
    protected static function processMedia(DOMElement $dom, MBlockItem $item, $nestedCount = null)
    {
        // set system name
        $item->setSystemName('REX_MEDIA');
        // has children ?
        if ($dom->hasChildNodes()) {
            // replace name
            self::replaceName($dom->firstChild, $item, $nestedCount, 'REX_INPUT_MEDIA');
            // add value
            self::replaceValue($dom->firstChild, $item);
            // create id and stuff
            $name = $dom->firstChild->getAttribute('name');
            $dom->firstChild->setAttribute('id', str_replace(array('REX_INPUT_VALUE', '][', '[', ']'), array('REX_MEDIA', '_', '_', ''), $name));
            // set mblock data
            $dom->firstChild->setAttribute('data-mblock', true);
            // change onclick id
            self::replaceOnClick($dom, $item, 'REXMedia', '(', ')', str_replace('REX_MEDIA_', '', '\'' . $dom->firstChild->getAttribute('id') . '\''));
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param null $nestedCount
     * @author Joachim Doerr
     */
    protected static function processMediaList(DOMElement $dom, MBlockItem $item, $nestedCount = null)
    {
        // set system name
        $item->setSystemName('REX_MEDIALIST');
        $name = '';
        // has children ?
        if ($dom->hasChildNodes()) {
            // modify input
            $result = self::listInputHandling($dom, $item, $nestedCount, 'MEDIALIST');
            // modify select
            self::listSelectHandling($dom, $item, $nestedCount, 'MEDIALIST', $result['val'], $result['name']);
            // change click id
            self::replaceOnClick($dom, $item, 'REXMedialist', '(\'', '\'', str_replace(array('REX_INPUT_VALUE', '][', '[', ']'), '', $result['name']));
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param null $nestedCount
     * @author Joachim Doerr
     */
    protected static function processLink(DOMElement $dom, MBlockItem $item, $nestedCount = null)
    {
        // set system name
        $item->setSystemName('REX_LINK');
        $id = '';
        $val = '';
        // has children ?
        if ($dom->hasChildNodes()) {
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('input') as $child) {
                if ($child->getAttribute('type') == 'hidden') {
                    // replace name
                    self::replaceName($child, $item, $nestedCount);
                    // add value
                    self::replaceValue($child, $item);
                    // create id and stuff
                    $name = $child->getAttribute('name');
                    $id = str_replace(array('REX_INPUT_VALUE', '][', '[', ']'), array('REX_LINK', '_', '_', ''), $name);
                    $child->setAttribute('id', $id);
                    $val = $child->getAttribute('value');
                }
                // set mblock data
                $child->setAttribute('data-mblock', true);
            }
            // remove name
            $dom->firstChild->removeAttribute('name');
            $dom->firstChild->setAttribute('id', $id . '_NAME');
            // add link art name
            self::addArtName($dom->firstChild, $item, $val);
            // change click id
            self::replaceOnClick($dom, $item, 'openLinkMap', '(\'', '\',', $id);
            self::replaceOnClick($dom, $item, 'REXLink', '(', ')', str_replace('REX_LINK_', '', '\'' . $id . '\''));
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param null $nestedCount
     * @author Joachim Doerr
     */
    protected static function processCustomLink(DOMElement $dom, MBlockItem $item, $nestedCount = null)
    {
        // set system name
        $item->setSystemName('REX_LINK');
        $id = '';
        $val = '';
        // has children ?
        if ($dom->hasChildNodes()) {
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('input') as $child) {
                // hidden input
                self::replaceName($child, $item, 'REX_INPUT_LINK');
                // add value
                self::replaceValue($child, $item);
                $name = $child->getAttribute('name');
                if (strpos($name, 'REX_INPUT_VALUE') !== false) {
                    $parent = $child->parentNode;
                    $id = str_replace(array('REX_INPUT_VALUE', '][', '[', ']'), array('', '', '', ''), $name);
                    $oldId = $parent->getAttribute('data-id');
                    $parent->setAttribute('data-id', $id);
                    $child->setAttribute('id', str_replace('REX_LINK_' . $oldId, 'REX_LINK_' . $id, $child->getAttribute('id')));
                    $dom->firstChild->setAttribute('id', str_replace('REX_LINK_' . $oldId, 'REX_LINK_' . $id, $child->getAttribute('id') . '_NAME'));
                    $val = $child->getAttribute('value');
                }
            }
            // remove name
            if ($dom->firstChild) {
                $dom->firstChild->removeAttribute('name');
                // add link art name
                if (is_numeric($val)) {
                    self::addArtName($dom->firstChild, $item, $val);
                } else if (is_string($val)) {
                    $dom->firstChild->setAttribute('value', $val);
                }
            }
            // set link id
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
     * @param null $nestedCount
     * @author Joachim Doerr
     */
    public static function processImageList(DOMElement $dom, MBlockItem $item, $nestedCount = null)
    {
        // set system name
        $item->setSystemName('REX_MEDIALIST');
        $val = '';
        $id = '';
        /** @var DOMElement $child */
        foreach ($dom->getElementsByTagName('input') as $child) {
            if ($child->hasAttribute('value')) {
                $val = $child->getAttribute('value');
            }
            if ($child->hasAttribute('name')) {
                $name = $child->getAttribute('name');
                $id = str_replace(array('REX_INPUT_VALUE', '][', '[', ']'), '', $name);
            }
        }

        if (!empty($val)) {
            $val = explode(',', $val);
            if (is_array($val) && sizeof($val) > 0) {

                /** @var DOMElement $child */
                foreach ($dom->getElementsByTagName('select') as $child) {
                    /** @var DOMElement $childNode */
                    foreach ($child->childNodes as $key => $childNode) {
                        $childNode->setAttribute('value', $childNode->nodeValue);
                        $childNode->setAttribute('data-key', $key);
                        $childNode->nodeValue = htmlentities($childNode->nodeValue);
                        $childNode->removeAttribute('selected');
                    }
                }
                foreach ($dom->getElementsByTagName('ul') as $child) {
//                    foreach ($child->childNodes as $childNode) {
//                        $child->removeChild($childNode);
//                    }
                    $child->setAttribute('id', 'REX_IMGLIST_' . $id);
                    foreach ($val as $key => $file) {
                        if (!empty($file)) {
                            $li = new DOMElement('li', $file);
                            $child->appendChild($li);
                        }
                    }
                    /** @var DOMElement $childNode */
                    foreach ($child->childNodes as $key => $childNode) {
                        $file = $childNode->nodeValue;
                        if (!empty($file)) {
                            $childNode->setAttribute('value', $file);
                            $childNode->setAttribute('data-value', $file);
                            $childNode->setAttribute('data-key', $key);
                            $childNode->nodeValue = '';
                            $image = new DOMElement('img');
                            $childNode->appendChild($image);
                        }
                    }
                }
                foreach ($dom->getElementsByTagName('img') as $child) {
                    $file = $child->parentNode->getAttribute('value');
                    $url = rex_url::backendController(['rex_media_type' => 'rex_medialistbutton_preview', 'rex_media_file' => $file], false);
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'svg') {
                        $url = rex_url::media($file);
                    }
                    $child->setAttribute('src', $url);
                    $child->setAttribute('class', 'thumbnail');
                }
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param null $nestedCount
     * @author Joachim Doerr
     */
    protected static function processLinkList(DOMElement $dom, MBlockItem $item, $nestedCount = null)
    {
        // set system name
        $item->setSystemName('REX_LINKLIST');
        $name = '';
        // has children ?
        if ($dom->hasChildNodes()) {
            // modify input
            $result = self::listInputHandling($dom, $item, $nestedCount, 'LINKLIST');
            // modify select
            self::listSelectHandling($dom, $item, $nestedCount, 'LINKLIST', $result['val'], $result['name']);
            // change click id
            self::replaceOnClick($dom, $item, 'REXLinklist', '(\'', '\'', str_replace(array('REX_INPUT_VALUE', '][', '[', ']'), '', $result['name']));
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param null $nestedCount
     * @param string $type
     * @return array
     * @author Joachim Doerr
     */
    protected static function listInputHandling(DOMElement $dom, MBlockItem $item, $nestedCount = null, $type = 'LINKLIST')
    {
        $val = '';
        $name = '';
        /** @var DOMElement $child */
        foreach ($dom->getElementsByTagName('input') as $child) {
            if ($child->getAttribute('type') == 'hidden') {
                // replace name
                self::replaceName($child, $item, $nestedCount, 'REX_INPUT_' . $type);
                $name = $child->getAttribute('name');
                // change id
                $id = str_replace(array('REX_INPUT_VALUE', '][', '[', ']'), array('REX_' . $type, '', '_', ''), $name);
                $child->setAttribute('id', $id);
                // add value
                self::replaceValue($child, $item);
                $val = $child->getAttribute('value');
                // set mblock data
                $child->setAttribute('data-mblock', true);
            }
        }
        return array('name' => $name, 'val' => $val);
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param null $nestedCount
     * @param string $type
     * @param string $val
     * @param string $name
     * @author Joachim Doerr
     */
    protected static function listSelectHandling(DOMElement $dom, MBlockItem $item, $nestedCount = null, $type = 'LINKLIST', $val = '', $name = '')
    {
        /** @var DOMElement $child */
        foreach ($dom->getElementsByTagName('select') as $child) {
            if (strpos($child->getAttribute('id'), 'REX_' . $type . '_SELECT_') !== false) {
                // replace name
                $child->setAttribute('name', str_replace('REX_INPUT_VALUE', 'REX_' . $type . '_SELECT', $name));
                // change id
                $id = str_replace(array('REX_INPUT_VALUE', '][', '[', ']'), array('REX_' . $type . '_SELECT', '', '_', ''), $name);
                $child->setAttribute('id', $id);
                // add options
                if ($type === 'LINKLIST') {
                    self::addLinkSelectOptions($child, $item, $val);
                } else if ($type === 'MEDIALIST') {
                    self::addMediaSelectOptions($child, $item, $val);
                }
                // set mblock data
                $child->setAttribute('data-mblock', true);
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param $btnFindKey
     * @param string $prefix
     * @param string $suffix
     * @param null $name
     * @author Joachim Doerr
     */
    protected static function replaceOnClick(DOMElement $dom, MBlockItem $item, $btnFindKey, $prefix = '', $suffix = '', $name = null)
    {
        // find a buttons and replace id
        if ($dom->hasChildNodes()) {
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('a') as $child) {
                if ($child->hasAttribute('onclick')) {
                    if (strpos($child->getAttribute('onclick'), $btnFindKey) !== false) {
                        $id = str_replace(array('][', '[', ']'), array('_', '_', ''), $name);
                        $pattern = '/' . $btnFindKey . str_replace(array('(', '\''), array('\(', '\\\''), $prefix) . '(.*?)' . str_replace(')', '\)', $suffix) . '/i';
                        $child->setAttribute('onclick', preg_replace($pattern, $btnFindKey . $prefix . $id . $suffix, $child->getAttribute('onclick')));
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
    protected static function replaceDataId(DOMElement $dom, MBlockItem $item)
    {
        $name = $dom->getAttribute('name');
        $id = str_replace(array('][', '[', ']'), array('_', '_', ''), $name);
        $dom->setAttribute('data-id', $id);
    }

    /**
     * @param DOMElement $element
     * @param MBlockItem $item
     * @param array $nestedCount
     * @param string $elementType
     * @author Joachim Doerr
     */
    protected static function replaceName(DOMElement $element, MBlockItem $item, $nestedCount = array(), $elementType = 'REX_INPUT_LINK')
    {
        parent::replaceName($element, $item, $nestedCount);
        $element->setAttribute('name', str_replace($elementType, 'REX_INPUT_VALUE', $element->getAttribute('name')));
        $element->setAttribute('data-base-type', $elementType);
        $element->setAttribute('data-name-value', 'REX_INPUT_VALUE');
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param string $val
     * @author Joachim Doerr
     */
    protected static function addMediaSelectOptions(DOMElement $dom, MBlockItem $item, $val = '')
    {
        if (!empty($val)) {
            $val = explode(',', $val);
            foreach ($val as $id) {
                $dom->appendChild(new DOMElement('option', $id));
            }
            /** @var DOMElement $child */
            foreach ($dom->childNodes as $child) {
                $child->setAttribute('value', $child->nodeValue);
                $child->nodeValue = htmlentities($child->nodeValue);
                $child->removeAttribute('selected');
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param string $val
     * @author Joachim Doerr
     */
    protected static function addLinkSelectOptions(DOMElement $dom, MBlockItem $item, $val = '')
    {
        if (!empty($val)) {
            $val = explode(',', $val);
            foreach ($val as $id) {
                $dom->appendChild(new DOMElement('option', $id));
            }
            /** @var DOMElement $child */
            foreach ($dom->childNodes as $child) {
                $child->setAttribute('value', $child->nodeValue);
                $child->nodeValue = htmlentities(self::getLinkInfo($child->getAttribute('value'))['art_name'] . ' [' . $child->nodeValue . ']');
                $child->removeAttribute('selected');
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @param string $val
     * @author Joachim Doerr
     */
    protected static function addArtName(DOMElement $dom, MBlockItem $item, $val = '')
    {
        if (!empty($val)) {
            $val = intval($val);
            $linkInfo = self::getLinkInfo($val);
            $dom->setAttribute('value', $linkInfo['art_name'] . ' [' . $val . ']');
        }
    }

    /**
     * @param $id
     * @return array
     * @author Joachim Doerr
     */
    protected static function getLinkInfo($id)
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