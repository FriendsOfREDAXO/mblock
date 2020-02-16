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
     * @author Joachim Doerr
     */
    protected static function processCustomLink(DOMElement $dom, MBlockItem $item)
    {
        if ($dom->hasAttribute('data-id')) {
            self::replaceDataId($dom, $item); // TODO check brauchen wird das wirklich?
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
            if ($dom->firstChild) {
                $dom->firstChild->removeAttribute('name');
                // add link art name
                self::addArtName($dom->firstChild, $item);
            }

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
    protected static function processLinkList(DOMElement $dom, MBlockItem $item, $nestedCount = null)
    {
        // set system name
        $item->setSystemName('REX_LINKLIST');
        $name = '';
        $val = '';
        // has children ?
        if ($dom->hasChildNodes()) {
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('input') as $child) {
                if ($child->getAttribute('type') == 'hidden') {
                    // replace name
                    self::replaceName($child, $item, $nestedCount, 'REX_INPUT_LINKLIST');
                    $name = $child->getAttribute('name');
                    // change id
                    $id = str_replace(array('REX_INPUT_VALUE', '][', '[', ']'),array('REX_LINKLIST', '','_',''), $name);
                    $child->setAttribute('id', $id);
                    // add value
                    self::replaceValue($child, $item);
                    $val = $child->getAttribute('value');
                    // set mblock data
                    $child->setAttribute('data-mblock', true);
                }
            }
            /** @var DOMElement $child */
            foreach ($dom->getElementsByTagName('select') as $child) {
                if (strpos($child->getAttribute('id'), 'REX_LINKLIST_SELECT_') !== false) {
                    // replace name
                    $child->setAttribute('name', str_replace('REX_INPUT_VALUE', 'REX_LINKLIST_SELECT', $name));
                    // change id
                    $id = str_replace(array('REX_INPUT_VALUE', '][', '[', ']'),array('REX_LINKLIST_SELECT', '','_',''), $name);
                    $child->setAttribute('id', $id);
                    // add options
                    self::addLinkSelectOptions($child, $item, $val);
                    // set mblock data
                    $child->setAttribute('data-mblock', true);
                }
            }
            // change click id
            self::replaceOnClick($dom, $item, 'REXLinklist', '(\'', '\'', str_replace(array('REX_INPUT_VALUE', '][', '[', ']'), '', $name));
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
            $plainId = array_filter(explode('.', $item->getPlainId()));
            $namePrefix = '';
            foreach ($plainId as $id) {
                $namePrefix .= "[$id][0]";
            }
            $element->setAttribute('name', str_replace(array($name, '[' . $item->getSystemId() . ']'), array('REX_INPUT_VALUE', $namePrefix . '[' . $item->getSystemId() . ']'), $element->getAttribute('name')));
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
            $dom->setAttribute('value', $linkInfo['art_name'] . ' [' .$val. ']' );
        }
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