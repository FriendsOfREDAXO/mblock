<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlock
{

    /**
     * @var array
     */
    private static $items = array();

    /**
     * @var array
     */
    private static $result = array();

    /**
     * @var array
     */
    private static $output = array();

    /**
     * MBlock constructor.
     * @author Joachim Doerr
     */
    public function __construct()
    {
        // create mblock page count is not exist
        if (!isset($_SESSION['mblock_count'])) {
            $_SESSION['mblock_count'] = 0;
        }
    }

    /**
     * @param $id
     * @param string|MForm|mblock_rex_form|rex_yform $form
     * @param array $settings
     * @param null $theme
     * @return mixed
     * @throws rex_sql_exception
     */
    public static function show($id, $form, $settings = array(), $theme = null)
    {
        $plain = false;
        if (!isset($_SESSION['mblock_count'])) {
            // set mblock count is not exist
            $_SESSION['mblock_count'] = 0;
        }
        $_SESSION['mblock_count']++;

        if (is_integer($id) or is_numeric($id)) {
            // load rex value by id
            self::$result = MBlockValueHandler::loadRexVars();

            if ($form instanceof MForm) {
                $form = $form->show();
            }
        } else {
            if (strpos($id, 'yform') !== false) {
                $table = explode('::', $id);

                if (sizeof($table) > 2) {
                    $id = $table[0] . '::' . $table[1];
                    $settings['type_key'] = $table[2];
                    $post = rex_request::post($table[1]);
                    if (!is_null($post) && isset($post[$settings['type_key']])) {
                        self::$result['value'][$id] = $post[$settings['type_key']];
                    }
                    if (sizeof($table) > 3) {
                        self::$result = MBlockValueHandler::loadFromTable($table);
                    }
                } else {
                    self::$result = rex_request::post($table[1]);
                }

                if ($form instanceof rex_yform) {
                    // get fields
                    $form->executeFields();
                    $formFields = $form->getObjectparams('form_output');

                    // rmeove submit button
                    array_pop($formFields);
                    array_pop($formFields);

                    // implode fields to html string
                    $form = implode('', $formFields);

                    preg_match_all('/name="([^"]*)"/', $form, $matches, PREG_SET_ORDER, 0);

                    foreach ($matches as $match) {

                        preg_match_all('/(-\d{1,2}-)|(-\w*-)/', $match[1], $subMatches);
                        $toReplace = $match[0];
                        $replaceWith = $match[0];

                        foreach ($subMatches[0] as $subMatch) {
                            $replaceWith = str_replace($subMatch, '[' . substr($subMatch, 1, -1) . ']', $replaceWith);
                        }

                        $form = str_replace($toReplace, $replaceWith, $form);
                    }
                }

            } else {
                // is table::column
                $table = explode('::', $id);
                self::$result = MBlockValueHandler::loadFromTable($table, rex_request::get('id', 'int', 0));

                if (sizeof($table) > 2) {
                    $id = $table[0] . '::' . $table[1];
                    $settings['type_key'] = array_pop($table);
                }

                if ($form instanceof mblock_rex_form) {
                    $form = $form->getElements();
                }

            }
        }

        // crate plain element
        $plainItem = new MBlockItem();
        $plainItem->setId(0)
            ->setValueId($id)
            ->setResult(array())
            ->setForm($form)
            ->addPayload('plain_item', true);

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

        // key must be integer
        foreach (self::$items as $key => $item) {
            if (!is_int($key)) {
                unset(self::$items[$key]);
            }
        }

        // create first element
        // don't loaded?
        if (!self::$items && (!isset($settings['initial_hidden']) or $settings['initial_hidden'] != 1)) {
            // set plain item for add
            $plain = true;
            self::$items[0] = new MBlockItem();
            self::$items[0]->setId(0)
                ->setValueId($id)
                ->setResult(array())
                ->setForm($form);
        }


        // foreach rex value json items
        /** @var MBlockItem $item */
        foreach (static::$items as $count => $item) {
            static::$output[] = self::createOutput($item, ($count + 1), $theme);
        }

        $addText = (isset($settings['initial_button_text'])) ? ' ' . $settings['initial_button_text'] : '';
        $addItem = rex_escape('<div class="mblock-single-add"><span class="singleadded"><button type="button" class="btn btn-default addme" title="duplicate"><i class="rex-icon rex-icon-add-module"></i>' . $addText . '</button></span></div>');
        $plainItem = rex_escape(self::createOutput($plainItem, 0, $theme));

        // wrap parsed form items
        $wrapper = new MBlockElement();
        $wrapper->setOutput(implode('', static::$output))
            ->setSettings(MBlockSettingsHelper::getSettings(array_merge($settings, ['mblock-plain-sortitem' => $plainItem, 'mblock-single-add' => $addItem])));

        // return wrapped from elements
        $output = MBlockParser::parseElement($wrapper, 'wrapper', $theme);


        if (($plain && array_key_exists('disable_null_view', $settings) && $settings['disable_null_view'] == true) and rex_request::get('function', 'string') != 'add') {

            $buttonText = 'Show MBlock';
            if (array_key_exists('null_view_button_text', $settings) && !empty($settings['null_view_button_text'])) {
                $buttonText = $settings['null_view_button_text'];
            }

            $uniqueId = uniqid();
            $output = '
                <div id="accordion' . $uniqueId . '" role="tablist">
                  <div class="panel mblock-hidden-panel">
                    <div id="collapse' . uniqid() . '" class="collapse in" role="tabpanel">
                        <a class="btn btn-primary" role="button" data-toggle="collapse" data-parent="#accordion' . $uniqueId . '" href="#collapse' . $uniqueId . '" aria-expanded="false" aria-controls="collapseTwo">' . $buttonText . '</a>
                    </div>
                  </div>
                  <div id="collapse' . $uniqueId . '" class="collapse" role="tabpanel">' . $output . '</div>
                </div>
            ';
        }

        // reset for multi block fields
        self::reset();

        // return output
        return $output;
    }

    /**
     * @param MBlockItem $item
     * @param $count
     * @param null $theme
     * @return mixed
     * @author Joachim Doerr
     */
    private static function createOutput(MBlockItem $item, $count, $theme = null)
    {
        $item->setForm(MBlockSystemButtonReplacer::replaceSystemButtons($item, $count));
        $item->setForm(MBlockCountReplacer::replaceCountKeys($item, $count));
        $item->setForm(MBlockBootstrapReplacer::replaceTabIds($item, $count));
        $item->setForm(MBlockBootstrapReplacer::replaceCollapseIds($item, $count));

        // decorate item form
        if ($item->getResult()) {
            $item->setForm(MBlockFormItemDecorator::decorateFormItem($item));
            // custom link hidden to text
            $item->setForm(MBlockSystemButtonReplacer::replaceCustomLinkText($item));
        } else {
            // no result set values to empty!
            $item->setForm(MBlockValueReplacer::replaceValueSetEmpty($item));
        }

        // set only checkbox block holder
        $item->setForm(MBlockCheckboxReplacer::replaceCheckboxesBlockHolder($item, $count));

        // parse form item
        $element = new MBlockElement();
        $element->setForm($item->getForm())
            ->setIndex($count);

        // parse element to output
        $output = MBlockParser::parseElement($element, 'element', $theme);

        // fix & error
        foreach ($item->getResult() as $result) {
            if (is_array($result) && array_key_exists('id', $result)) {
                $output = str_replace($result['id'], $result['value'], $output);
            }
        }

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