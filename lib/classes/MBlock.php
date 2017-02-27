<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// we need the vendors
include_once rex_path::addon('mblock', 'vendors/phpQuery/phpQuery.php');

class MBlock
{
    /**
     * @var array
     */
    private static $items = array();

    /**
     * @var array
     */
    private static $result;

    /**
     * @var array
     */
    private static $output = array();

    /**
     * @param $id
     * @param $form
     * @param array $settings
     * @return mixed
     */
    public static function show($id, $form, $settings = array())
    {
        // load rex value by id
        self::$result = MBlockValueHandler::loadRexVars();

        // is loaded
        if (array_key_exists('value', self::$result) && is_array(self::$result['value'][$id])) {
            // item result to item
            foreach (self::$result['value'][$id] as $jId => $values) {
                // init item
                self::$items[$jId] = new MBlockItem;
                self::$items[$jId]->setId($jId)
                    ->setValueId($id)
                    ->setResult($values)
                    ->setForm($form);
            }
        }

        // don't loaded?
        if (!self::$items) {
            // set plain item for add
            self::$items[0] = new MBlockItem();
            self::$items[0]->setId(0)
                ->setValueId($id)
                ->setResult(array())
                ->setForm($form);
        }

        // foreach rex value json items
        /** @var MBlockItem $item */
        foreach (static::$items as $count => $item) {
            // replace system button data
            $item->setForm(MBlockSystemButtonReplacer::replaceSystemButtons($item, ($count+1)));
            $item->setForm(MBlockEditorReplacer::replaceEditorArea($item, ($count+1)));
            $item->setForm(MBlockCountReplacer::replaceCountKeys($item, ($count+1)));

            // decorate item form
            if ($item->getResult()) {
                $item->setForm(MBlockFormItemDecorator::decorateFormItem($item));
                // custom link hidden to text
                $item->setForm(MBlockSystemButtonReplacer::replaceCustomLinkText($item));
            }

            // set only checkbox block holder
            $item->setForm(MBlockCheckboxReplacer::replaceCheckboxesBlockHolder($item, ($count+1)));

            // parse form item
            $element = new MBlockElement();
            $element->setForm($item->getForm());

            // parse element to output
            $output = MBlockParser::parseElement($element, 'element');

            // fix & error
            foreach ($item->getResult() as $result) {
                if (is_array($result) && array_key_exists('id', $result)) {
                    $output = str_replace($result['id'], $result['value'], $output);
                }
            }
            // add to output
            static::$output[] = $output;
        }

        // wrap parsed form items
        $wrapper = new MBlockElement();
        $wrapper->setOutput(implode('',static::$output))
            ->setSettings(MBlockSettingsHelper::getSettings($settings));

        // return wrapped from elements
        $output = MBlockParser::parseElement($wrapper, 'wrapper');

        // reset for multi block fields
        self::reset();

        // return output
        return $output;
    }

    /**
     * @author Joachim Doerr
     */
    private static function reset()
    {
        foreach (self::$items as $key => $item) {
            unset(self::$items[$key]);
        }
        foreach (self::$result as $key => $value) {
            unset(self::$result[$key]);
        }
        foreach (self::$output as $key => $value) {
            unset(self::$output[$key]);
        }
    }
}