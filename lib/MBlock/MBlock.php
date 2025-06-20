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
     * @var array - Stores toggle states for blocks
     */
    private static $toggleStates = array();

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
     * Get only active blocks - new simplified API for v3.5
     * @param string $rexValue - The REX_VALUE reference like "REX_VALUE[1]"
     * @return array
     * @author MBlock v3.5
     */
    public static function getBlocks($rexValue)
    {
        // Load data using rex_var
        $blocks = rex_var::toArray($rexValue);
        
        if (!is_array($blocks)) {
            return array();
        }
        
        // Use the simplified ToggleHandler to filter active blocks
        $activeBlocks = MBlockToggleHandler::filterActiveBlocks($blocks);
        
        if (rex::isDebugMode()) {
            MBlockToggleHandler::debugToggleStates($blocks, 'MBlock::getBlocks()');
            dump('MBlock v3.5 getBlocks() - Active blocks:', $activeBlocks);
        }
        
        return $activeBlocks;
    }

    /**
     * Get all blocks including inactive ones
     * @param string $rexValue - The REX_VALUE reference like "REX_VALUE[1]"
     * @return array
     * @author MBlock v3.5
     */
    public static function getAllBlocks($rexValue)
    {
        $blocks = rex_var::toArray($rexValue);
        
        if (!is_array($blocks)) {
            return array();
        }
        
        if (rex::isDebugMode()) {
            dump('MBlock v3.5 getAllBlocks() - All blocks:', $blocks);
        }
        
        return $blocks;
    }

    /**
     * Check if a specific block is active - removed complex session logic
     * Now we check directly in the block data
     * @param string $valueId
     * @param int $blockIndex
     * @return bool
     * @author MBlock v3.5
     */
    private static function isBlockActive($valueId, $blockIndex)
    {
        // This method is now simplified - actual check happens in getBlocks()
        // keeping for backwards compatibility
        return true;
    }

    /**
     * Set toggle state for a block - simplified version
     * @param string $valueId
     * @param int $blockIndex
     * @param bool $active
     * @author MBlock v3.5
     */
    public static function setBlockToggle($valueId, $blockIndex, $active = true)
    {
        // This is now handled by the form data itself
        // The mblock_active field is automatically included in the form data
        if (rex::isDebugMode()) {
            dump('MBlock v3.5 setBlockToggle() - Toggle state will be saved with form data');
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
        $addItem = rex_escape('<div class="mblock-single-add"><span class="singleadded"><button type="button" class="btn btn-success mblock-add-btn" title="duplicate"><i class="rex-icon rex-icon-add-module"></i>' . $addText . '</button></span></div>');
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
        
        // MBlock v3.5 - Add hidden toggle field automatically
        $item->setForm(self::addToggleField($item, $count));

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
     * Add hidden toggle field to form - MBlock v3.5
     * @param MBlockItem $item
     * @param int $count
     * @return string
     * @author MBlock v3.5
     */
    private static function addToggleField(MBlockItem $item, $count)
    {
        $form = $item->getForm();
        
        // Get current toggle state from item result
        $result = $item->getResult();
        
        // Only add toggle field if block is explicitly set to inactive
        if (is_array($result) && isset($result['mblock_active']) && $result['mblock_active'] == '0') {
            // Block is inactive - add hidden field with value 0
            $hiddenField = '<input type="hidden" name="REX_INPUT_VALUE[' . $item->getValueId() . '][' . ($count - 1) . '][mblock_active]" value="0" class="mblock-toggle-field" />';
            $form .= $hiddenField;
            
            if (rex::isDebugMode()) {
                dump('MBlock v3.5 addToggleField() - Added INACTIVE toggle field:', $hiddenField);
            }
        }
        // If no mblock_active field or it's set to 1, don't add anything (active is default)
        
        return $form;
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