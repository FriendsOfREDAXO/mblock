<?php
/**
 * Created by PhpStorm.
 * User: joachimdoerr
 * Date: 30.07.16
 * Time: 21:53
 */

// we need the vendors
include_once rex_path::addon('jblock', 'vendors/phpQuery/phpQuery.php');

class JBlock
{
    /**
     * @var array
     */
    static private $items = array();

    /**
     * @var array
     */
    static private $result;

    /**
     * @var array
     */
    static private $output = array();

    /**
     * @param $id
     * @param $form
     * @return mixed
     */
    static public function show($id, $form)
    {
        // load rex value by id
        self::$result = JBlockValueHandler::loadRexVars();

        // item result to item
        foreach (self::$result['value'][$id] as $jId => $values) {
            // init item
            self::$items[$jId] = new JBlockItem;
            self::$items[$jId]->setId($jId)
                ->setValueId($id)
                ->setResult($values)
                ->setForm($form);
        }

        if (!self::$items) {
            self::$items[0] = new JBlockItem();
            self::$items[0]->setId(0)
                ->setValueId($id)
                ->setResult()
                ->setForm($form);
        }

        // foreach rex value json items
        /** @var JBlockItem $item */
        foreach (static::$items as $item) {
            // decorate item form
            if ($item->getResult()) $item->setForm(JBlockFormItemDecorator::decorateFormItem($item));

            // parse form item
            $element = new JBlockElement();
            $element->setForm($item->getForm());
            static::$output[] = JBlockParser::parseElement($element, 'element');
        }

//        echo '<pre>';
//        print_r($item);
//        echo '</pre>';
        // wrap parsed form items
        $wrapper = new JBlockElement();
        $wrapper->setOutput(implode('',static::$output));

        // return wrapped from elements
        return JBlockParser::parseElement($wrapper, 'wrapper');
    }
}