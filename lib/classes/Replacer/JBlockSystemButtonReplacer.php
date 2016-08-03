<?php

/**
 * Created by PhpStorm.
 * User: joachimdoerr
 * Date: 01.08.16
 * Time: 20:43
 */
class JBlockSystemButtonReplacer extends JBlockFormItemDecorator
{
    /**
     * @param JBlockItem $item
     * @return String
     * @author Joachim Doerr
     */
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
                // label for and id change
                self::changeForId($document, $match, $item, false);
            }
        }

        // label for and id change
        $matches = $document->find('.rex-js-widget-medialist select');

        if ($matches) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // label for and id change
                self::changeForId($document, $match, $item);

                self::addMediaSelectOptions($match, $item);
            }
        }


        // return the manipulated html output
        return $document->htmlOuter();
    }

    /**
     * @param phpQueryObject $document
     * @param DOMElement $dom
     * @param JBlockItem $item
     * @param bool $replaceButtons
     * @author Joachim Doerr
     * @return bool
     */
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
        return true;
    }

    /**
     * @param DOMElement $dom
     * @param JBlockItem $item
     * @param $name
     * @author Joachim Doerr
     */
    protected static function replaceName(DOMElement $dom, JBlockItem $item, $name)
    {
        $matches = self::getName($dom);
        if ($matches) {
            $item->setSystemId($matches[1])
                ->setSystemName($name);
            // replace
            $dom->setAttribute('name', str_replace(array($name, '[' . $item->getSystemId() . ']'), array('REX_INPUT_VALUE', '[' . $item->getValueId() . '][0][' . $name . '_' . $item->getSystemId() . ']'), $dom->getAttribute('name')));
        }
    }

    /**
     * @param DOMElement $dom
     * @param JBlockItem $item
     * @author Joachim Doerr
     */
    protected static function addMediaSelectOptions(DOMElement $dom, JBlockItem $item)
    {
        if (is_array($item->getResult()) && array_key_exists($item->getSystemName() . '_' . $item->getSystemId(), $item->getResult())) {

            $resultItems = explode(',',$item->getResult()[$item->getSystemName() . '_' . $item->getSystemId()]);

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
}