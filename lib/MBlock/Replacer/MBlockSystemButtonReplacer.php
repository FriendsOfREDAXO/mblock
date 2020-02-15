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
use function Matrix\identity;

class MBlockSystemButtonReplacer extends MBlockElementReplacer
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
            // replace name first child is input
            if (strrpos($dom->firstChild->getAttribute('name'), 'REX_INPUT_MEDIA') !== false) {
                // REX_INPUT_MEDIA
                self::replaceSystemName($dom->firstChild, $item, 'REX_INPUT_MEDIA', $nestedCount);
            }
            // change onclick id
            self::replaceOnClick($dom, $item, 'REXMedia(', '(', ',');
            self::replaceOnClick($dom, $item, 'REXMedia(', '(', ')');
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
                    if ($child->getAttribute('type') == 'hidden') {
                        $name = $child->getAttribute('name');
                    }
                }
                /** @var DOMElement $child */
                foreach ($dom->getElementsByTagName('select') as $child) {
                    if (strpos($child->getAttribute('id'), 'REX_MEDIALIST_SELECT_') !== false) {
                        // replace name
                        $child->setAttribute('name', str_replace($item->getSystemId(), $item->getItemId(), $child->getAttribute('name')));
                        // add options
                        self::addMediaSelectOptions($dom->firstChild, $item, $name);
                    }
                }
                // change click id
                self::replaceOnClick($dom, $item, 'REXMedialist(', '(', ',');
                self::replaceOnClick($dom, $item, 'REXMedialist(', '(', ')');
            }
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
                $child->setAttribute('data-mblock', true);
            }
            // remove name
            $dom->firstChild->removeAttribute('name');
            $dom->firstChild->setAttribute('id', $id . '_NAME');
            // add link art name
            self::addArtName($dom->firstChild, $item, $val);
            // change click id
            // REX_LINK_1
            // openLinkMap('REX_LINK_1', '&clang=1&category_id=0');return false;
            // REX_LINK_1_0_1
            // openLinkMap('REX_LINK_1_0_1', '&clang=1&category_id=0');return false;
            self::replaceOnClick($dom, $item, 'openLinkMap', '(\'', '\',', $id);
            // REX_LINK_1
            // deleteREXLink(1);return false;
            self::replaceOnClick($dom, $item, 'REXLink', '(', ')', str_replace('REX_LINK_', '', $id));
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
        $id = $item->getPayload('count-id') . rex_session('mblock_count') . '00' . $item->getPayload('replace-id');
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
                    self::replaceId($child, $item); // TODO check ist das nötig?
                }
            }
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('select') as $child) {
                if (strpos($child->getAttribute('id'), 'REX_LINKLIST_SELECT_') !== false) {
                    // replace name
                    $child->setAttribute('name', str_replace($item->getSystemId(), $item->getItemId(), $child->getAttribute('name')));
                    // add options
                    self::addLinkSelectOptions($child, $item, $name);
                }
            }
            // change click id
            self::replaceOnClick($dom, $item, 'REXLinklist(', '(', ',');
            // change click id
            self::replaceOnClick($dom, $item, 'deleteREXLinklist(', '(', ')');
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
            foreach($dom->getElementsByTagName('a') as $child) {
                if ($child->hasAttribute('onclick')) {
                    if (strpos($child->getAttribute('onclick'), $btnFindKey) !== false) {
                        $id = str_replace(array('][', '[', ']'), array('_', '_', ''), $name);
                        $pattern = '/'.$btnFindKey.str_replace(array('(','\''), array('\(','\\\''), $prefix).'(.*?)'.str_replace(')','\)', $suffix).'/i';
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
    protected static function replaceId(DOMElement $dom, MBlockItem $item)
    {
        // get input id
        $name = $dom->getAttribute('name');
        $id = str_replace(array('][', '[', ']'),array('_','_',''), $name);
        $dom->setAttribute('id', $id);
        $dom->setAttribute('data-id', $id);
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
     * @author Joachim Doerr
     */
    protected static function replaceName(DOMElement $element, MBlockItem $item, $nestedCount = array())
    {
        parent::replaceName($element, $item, $nestedCount);
        $element->setAttribute('name', str_replace('REX_INPUT_LINK', 'REX_INPUT_VALUE', $element->getAttribute('name')));
        $element->setAttribute('data-base-type', 'REX_INPUT_LINK');
        $element->setAttribute('data-name-value', 'REX_INPUT_VALUE');
    }

    /**
     * @param DOMElement $element
     * @param MBlockItem $item
     * @param $name
     * @param array $nestedCount
     * @author Joachim Doerr
     */
    protected static function replaceSystemName(DOMElement $element, MBlockItem $item, $name, $nestedCount = array())
    {
        // get name
        $matches = MBlockFormItemDecorator::getName($element);
        // found
        if ($matches) {
            // set system id
            $item->setSystemId($matches[1]);
            // and replace name attribute
            $replaceName = str_replace(strtoupper('_input'), '', $name);
            $plainId = array_filter(explode('.', $item->getPlainId()));
            $namePrefix = '';
            foreach ($plainId as $id) {
                $namePrefix .= "[$id][0]";
            }
            $element->setAttribute('name', str_replace(array($name, '[' . $item->getSystemId() . ']'), array('REX_INPUT_VALUE', $namePrefix . '[' . $replaceName . '_' . $item->getSystemId() . ']'), $element->getAttribute('name')));
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
                array_key_exists($item->getSystemName() . '_' . $item->getSystemId(), $item->getResult()) ||
                array_key_exists(strtolower($item->getSystemName()) . '_' . $item->getSystemId(), $item->getResult())
            )
        ) {
            $key = (isset($item->getResult()[$item->getSystemName() . '_' . $item->getSystemId()])) ? $item->getSystemName() . '_' . $item->getSystemId() : strtolower($item->getSystemName()) . '_' . $item->getSystemId();
            $resultItems = explode(',', $item->getResult()[$key]);
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
            if ($resultItems[0] != '') {
                foreach ($resultItems as $resultItem) {
                    $dom->appendChild(new DOMElement('option', $resultItem));
                }
                /** @var DOMElement $child */
                foreach ($dom->childNodes as $child) {
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
     * @param string $val
     * @author Joachim Doerr
     */
    protected static function addArtName(DOMElement $dom, MBlockItem $item, $val = '')
    {
        if (!empty($val)) {
            $val = intval($val);
            $linkInfo = self::getLinkInfo($val);
            $dom->setAttribute('value', $linkInfo['art_name'] . ' [' .$val. ']' );
        }
//        if (is_array($item->getResult()) && (
//                array_key_exists($item->getSystemName() . '_' . $item->getSystemId(), $item->getResult()) OR
//                array_key_exists(strtolower($item->getSystemName()) . '_' . $item->getSystemId(), $item->getResult())
//            )
//        ) {
//            $key = (isset($item->getResult()[$item->getSystemName() . '_' . $item->getSystemId()])) ? $item->getSystemName() . '_' . $item->getSystemId() : strtolower($item->getSystemName()) . '_' . $item->getSystemId();
//            $linkInfo = self::getLinkInfo($item->getResult()[$key]);
//            $dom->setAttribute('value', $linkInfo['art_name']);
//        }
    }

    /**
     * @param $name
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function setSystemIdByName($name, MBlockItem $item)
    {
        if ($name != '' && preg_match('/\_\d+/', $name, $matches))
            $item->setSystemId(str_replace('_','', $matches[0]));
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