<?php

/**
 * Created by PhpStorm.
 * User: joachimdoerr
 * Date: 01.08.16
 * Time: 20:43
 */
class JBlockSystemButtonReplacer extends JBlockFormItemDecorator
{
    public static function replaceNameId(JBlockItem $item)
    {
        $document = phpQuery::newDocumentHTML($item->getForm());

        // find input
        $matches = $document->find('.rex-js-widget-media input');

        if ($matches) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // replace name
                self::replaceName($match, $item, 'REX_INPUT_MEDIA');
                // label for and id change
                self::changeForId($document, $match, $item);
            }
        }

        // find input
        $matches = $document->find('.rex-js-widget-medialist input');

        if ($matches) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // replace name
                self::replaceName($match, $item, 'REX_INPUT_MEDIALIST');
                self::changeForId($document, $match, $item, false);
            }
        }

        // label for and id change
        $matches = $document->find('.rex-js-widget-medialist select');

        if ($matches) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // replace name
                self::changeForId($document, $match, $item);

                self::addSelectOptions($match, $item);
            }
        }


        // return the manipulated html output
        return $document->htmlOuter();
    }

    protected static function changeForId(phpQueryObject $document, DOMElement $dom, JBlockItem $item, $replaceButtons = true)
    {
        // get input id
        $id = $dom->getAttribute('id');
        preg_match('/_\d/', $id, $matches);

        if ($matches) {
            $id = str_replace($matches[0], '_' . $item->getId(), $id);
            // replace id
            $dom->setAttribute('id', $id);

            if ($replaceButtons) {
                // find a buttons and replace id
                $matches = $document->find('.rex-js-widget a.btn-popup');

                if ($matches) {
                    /** @var DOMElement $match */
                    foreach ($matches as $match) {
                        $match->setAttribute('onclick', str_replace($item->getSystemId(), $item->getId(), $match->getAttribute('onclick')));
                    }
                }
            }
        }
    }

    protected static function replaceName(DOMElement $dom, JBlockItem $item, $name)
    {
        $matches = self::getName($dom);
        if ($matches) {
            $item->setSystemId($matches[1]);
            // replace
            $dom->setAttribute('name', str_replace(array($name, '[' . $item->getSystemId() . ']'), array('REX_INPUT_VALUE', '[' . $item->getValueId() . '][0][' . $name . '_' . $item->getSystemId() . ']'), $dom->getAttribute('name')));
        }
    }

    protected static function addSelectOptions(DOMElement $dom, JBlockItem $item)
    {

    }
}