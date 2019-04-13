<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace Mblock\Handler;


use DOMElement;
use MBlock\DOM\MBlockDOMTrait;
use MBlock\Decorator\MBlockFormItemDecorator;
use MBlock\DTO\MBlockElement;
use MBlock\DTO\MBlockItem;
use MBlock\Parser\MBlockParser;
use MBlock\Provider\MBlockValueProvider;
use MBlock\Replacer\MBlockBootstrapReplacer;
use MBlock\Replacer\MBlockCountReplacer;
use MBlock\Replacer\MBlockElementReplacer;
use MBlock\Replacer\MBlockSystemButtonReplacer;
use MBlock\Utils\MBlockSettingsHelper;
use mblock_rex_form;
use MForm;
use rex_request;
use rex_yform;

class MBlockHandler
{
    use MBlockDOMTrait;

    /**
     * @var \DOMDocument
     */
    protected $formDomDocument;

    /**
     * @var string
     */
    protected $formHtml;

    /**
     * @var array
     */
    protected $values = array();

    /**
     * @var
     */
    private $val;

    /**
     * @var array
     */
    protected $settings = array();

    /**
     * @var MBlockItem[]
     */
    protected $items = array();

    /**
     * @var MBlockItem
     */
    protected $plainItem;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var mixed|string
     */
    protected $themeKey;

    /**
     * MBlockHandler constructor.
     * @param mixed $id
     * @param mixed|string $form
     * @param array $settings
     * @param array $values
     * @param string $theme
     * @author Joachim Doerr
     */
    public function __construct($id, $form, array $settings = array(), array $values = null, $theme = 'default')
    {
        if (strpos($id, '.') !== false) {
            $explodedId = explode('.', $id);
            if (is_numeric($explodedId[0]) && sizeof($explodedId) == 2) {
                $id = $explodedId[0];
                $settings['nested-value-key'] = $explodedId[1];
            }
        }

        $this->id = $id;
        $this->values = $values;
        $this->settings = $settings;
        $this->settings['id'] = $id;
        $this->themeKey = $theme;

        if (isset($settings['theme']) && is_string($settings['theme'])) {
            $this->themeKey = $settings['theme'];
        }

        $this->setForm($form);

        if (is_null($this->values)) {
            $this->readValues($id);
        }

        if (is_numeric($this->id)) {
            $this->id = (int) $this->id;
        }

        $this->setValByValue();
     }

    /**
     * 1. plain item creation
     * 2. mblock items for blocks creation
     *
     * Create all form items by loaded values
     * @return MBlockItem[]
     * @author Joachim Doerr
     */
    public function createItems()
    {
        // crate plain element
        $this->plainItem = new MBlockItem();
        $this->plainItem->setItemId(0)
            ->setFormHtml($this->formHtml)
            ->setValueId($this->id)
            ->setFormDomDocument(clone $this->formDomDocument)
            ->addPayload('plain_item', true);

        // loaded values?
        if (!is_null($this->val)) {
            // iterate value levels
            foreach ($this->val as $key => $value) {
                if (isset($this->val[$key])) {
                    // create block items by value
                    $this->items[$key] = new MBlockItem();
                    $this->items[$key]->setItemId($key)
                        ->setFormHtml($this->formHtml)
                        ->setValueId($this->id)
                        ->setVal($this->val[$key])
                        ->setFormDomDocument(clone $this->formDomDocument);
                }
            }
        }

        // key must be integer
        foreach ($this->items as $key => $item) {
            if (!is_int($key)) {
                unset($this->items[$key]);
            }
        }

        // don't loaded?
        if ((sizeof($this->items) <= 0 && (!isset($this->settings['initial_hidden']) or $this->settings['initial_hidden'] != 1))) {
            $this->items[0] = new MBlockItem();
            $this->items[0]->setItemId(0)
                ->setFormHtml($this->formHtml)
                ->setValueId($this->id)
                ->setFormDomDocument(clone $this->formDomDocument);
        }

        return $this->items;
    }

    /**
     * @param null $nestedCount
     * @return MBlockItem[]
     * @author Joachim Doerr
     */
    public function iterateItems($nestedCount = array())
    {
        if (sizeof($this->items) > 0) {
            /** @var MBlockItem $item */
            foreach ($this->items as $count => $item) {
                // nested mblock?
                $mblockWrapper = self::getElementsByClass($item->getFormDomDocument(), 'div.mblock_wrapper');
                if (sizeof($mblockWrapper) > 0) {
                    foreach ($mblockWrapper as $wrapper) {
                        $this->handleNestedMBlock($item, $wrapper, $nestedCount);
                    }
                }
                $this->executeItemManipulations($item, ($count +1), $nestedCount);
                // parse form item
                $element = new MBlockElement();
                $element->setForm(self::saveHtml($item->getFormDomDocument()))
                    ->setIterateIndex(($count + 1))
                    ->setSettings($this->settings);

                $item->setElement($element);
            }
        }

        return $this->items;
    }

    /**
     * @return MBlockItem[]
     * @author Joachim Doerr
     */
    public function parseItemElements()
    {
        if (sizeof($this->items) > 0) {
            /** @var MBlockItem $item */
            foreach ($this->items as $count => $item) {
                $this->executeElementParsing($item);
            }
        }
        return $this->items;
    }

    /**
     * @param MBlockItem $item
     * @return MBlockItem
     * @author Joachim Doerr
     */
    private function executeElementParsing(MBlockItem $item)
    {
        $this->elementParse($item->getElement(), $this->themeKey);
        // fix & error
        if (is_array($item->getResult()) && sizeof($item->getResult()) > 0) {
            foreach ($item->getResult() as $result) {
                if (is_array($result) && array_key_exists('id', $result)) {
                    $item->getElement()->setOutput(str_replace($result['id'], $result['value'], $item->getElement()->getOutput()));
                }
            }
        }
        return $item;
    }

    /**
     * @return mixed|string
     * @author Joachim Doerr
     */
    public function parseMBlockWrapper()
    {
        $addText = (isset($this->settings['initial_button_text'])) ? ' ' . $this->settings['initial_button_text'] : '';
        $addItem = rex_escape('<div class="mblock-single-add"><span class="singleadded"><button type="button" class="btn btn-default addme" title="duplicate"><i class="rex-icon rex-icon-add-module"></i>' . $addText . '</button></span></div>');

        $this->executePlainItemManipulation($this->plainItem)
            ->executeItemManipulations($this->plainItem, 0, null);

        // parse form item
        $element = new MBlockElement();
        $element->setForm(self::saveHtml($this->plainItem->getFormDomDocument()))
            ->setIterateIndex(0)
            ->setSettings($this->settings);

        $this->plainItem->setElement($element);

        $plainItem = rex_escape($this->executeElementParsing($this->plainItem)->getElement()->getOutput());

        // wrap parsed form items
        $wrapper = new MBlockElement();
        $wrapper->setOutput($this->getElementOutputs())
            ->setSettings(MBlockSettingsHelper::getSettings(array_merge($this->settings, ['mblock-plain-sortitem' => $plainItem, 'mblock-single-add' => $addItem])));

        return MBlockParser::parseElement($wrapper, 'wrapper', $this->themeKey);
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    private function getElementOutputs()
    {
        $output = '';
        if (sizeof($this->items) > 0) {
            /** @var MBlockItem $item */
            foreach ($this->items as $count => $item) {
                if (strpos($item->getElement()->getOutput(), 'mblock-sortitem-to-remove') !== false) {
                    $domElementOutput = self::createDom($item->getElement()->getOutput());
                    $sortItems = self::getElementsByClass($domElementOutput, 'div.mblock-sortitem-to-remove');
                    foreach ($sortItems as $nodeChild) {
                        $nodeChild->parentNode->removeChild($nodeChild);
                    }
                    $item->getElement()->setOutput(self::saveHtml($domElementOutput));
                }
                $output .= $item->getElement()->getOutput();
            }
        }
        return $output;
    }

    /**
     * @param MBlockElement $element
     * @param string $themeKey
     * @author Joachim Doerr
     * @return MBlockElement
     */
    private function elementParse(MBlockElement $element, $themeKey = 'default')
    {
        // parse element to output
        $element->setOutput(MBlockParser::parseElement($element, 'element', $themeKey));
        return $element;
    }

    /**
     * @param MBlockItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function executePlainItemManipulation(MBlockItem $item)
    {
        if ($matches = $item->formDomDocument->getElementsByTagName('div')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                if ($match->hasAttribute('class') && $match->getAttribute('class') == 'mblock_wrapper') {
                    if ($match->hasChildNodes()) {
                        $initialHidden = ($match->hasAttribute('data-initial_hidden') && $match->getAttribute('data-initial_hidden') == '1');
                        /** @var DOMElement $child */
                        foreach ($match->childNodes as $key => $child) {
                            // $this->settings['initial_hidden']
                            if ($child->hasAttribute('class') && $child->getAttribute('class') == 'sortitem'
                            ) {
                                if (($initialHidden === false && $key > 0) || $initialHidden === true)
                                    $match->removeChild($child);
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param MBlockItem $item
     * @param $count
     * @param null $nestedCount
     * @author Joachim Doerr
     * @return MBlockHandler
     */
    private function executeItemManipulations(MBlockItem $item, $count, $nestedCount = null)
    {
        // decorate item form
        MBlockFormItemDecorator::decorateFormItem($item, $nestedCount);
        // decorate item form
        MBlockElementReplacer::replaceForId($item);
        // decorate counting
        MBlockCountReplacer::replaceCountKeys($item, $count);
        // decorate tabs
        MBlockBootstrapReplacer::replaceTabIds($item, $count);
        // decorate collapse
        MBlockBootstrapReplacer::replaceCollapseIds($item, $count);
        // replace system button data
        MBlockSystemButtonReplacer::replaceSystemButtons($item, $count);
        // custom link hidden to text
        MBlockSystemButtonReplacer::replaceCustomLinkText($item);
        return $this;
    }

    /**
     * @param MBlockItem $item
     * @param \DOMElement $element
     * @author Joachim Doerr
     */
    private function handleNestedMBlock(MBlockItem $item, \DOMElement $element, $nestedCount = array())
    {
        $settings = array();

        /** @var \DOMAttr $attrNode */
        foreach ($element->attributes as $attrName => $attrNode) {
            if (strpos($attrName, 'data') !== false) {
                $settings[str_replace('data-', '', $attrName)] = $attrNode->value;
            }
        }

        $formHtml = $element->getAttribute('data-mblock-plain-sortitem');
        $nestedValueKey = $element->getAttribute('data-nested-value-key');
        $formHtmlDomDocument = self::createDom(htmlspecialchars_decode($formHtml));
        $sortItemForm = '';

        // get form
        foreach (self::getElementsByClass($formHtmlDomDocument, 'div.mblock-sortitem-form') as $nodeChild) {
            $sortItemForm = $nodeChild;
            break;
        }

        // remove items
        if ($sortItems = self::getElementsByClass($element, 'div.sortitem')) {
            if (is_array($sortItems) && sizeof($sortItems) > 0) {
                foreach ($sortItems as $key => $nodeChild) {
                    $nodeChild->setAttribute('class', 'mblock-sortitem-to-remove');
                }
            }
        }

        $values = array();

        if (is_array($item->getVal())) {
            foreach ($item->getVal() as $key => $val) {
                if (is_array($val) && $key == $nestedValueKey) {
                    $values[$key] = $val;
                }
            }
        }

        if ($sortItemForm instanceof \DOMElement && $sortItemForm->getAttribute('class') == 'mblock-sortitem-form' && sizeof($values) > 0) { // sort item === mblock sort wrapper

            $values = array('value' => array($this->id => $values[$nestedValueKey]));

            // add new nodes
            $subMblockHandler = new MBlockHandler($this->id . '.' . $nestedValueKey, self::innerHTML($sortItemForm), $settings, $values);
            // duplicate form elements by values
            $subMblockHandler->createItems();
            // remove data mblock flag
            $subMblockHandler->clearItems();
            // iterate items and create blocks
            $subMblockHandler->iterateItems(array_merge($nestedCount, array($item->getItemId())));
            // parse elements to mblock blocks
            $subMblockHandler->parseItemElements();

            // add blocks to element
            foreach ($subMblockHandler->items as $key => $item) {
                $this->appendHtml($element, $item->getElement()->getOutput());
            }

            // set mblock count data
            $element->setAttribute('data-mblock_count', rex_session('mblock_count'));
        } else {
            // TODO error log
            // dump($key);
        }
    }

    /**
     * @return MBlockItem[]
     * @author Joachim Doerr
     */
    private function clearItems()
    {
        if (sizeof($this->items) > 0) {
            /** @var MBlockItem $item */
            foreach ($this->items as $count => $item) {

                foreach (array('input', 'textarea', 'select') as $value) {
                    if ($item->getFormDomDocument() instanceof \DOMDocument && $matches = $item->getFormDomDocument()->getElementsByTagName($value)) {
                        /** @var \DOMElement $match */
                        foreach ($matches as $match) {
                            if ($match->hasAttribute('data-mblock')) {
                                $match->removeAttribute('data-mblock');
                            }
                        }
                    }
                }
            }
        }
        return $this->items;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    private function setValByValue()
    {
        $this->val = (is_array($this->values) && array_key_exists('value', $this->values) && isset($this->values['value'][$this->id])) ? $this->values['value'][$this->id] : null;
        return $this;
    }

    /**
     * @param $id
     * @return MBlockHandler
     * @author Joachim Doerr
     */
    private function readValues($id)
    {
        if (is_integer($id) or is_numeric($id)) {
            // load rex value by id
            $this->values = MBlockValueProvider::loadRexVars();
        } else if (is_bool($id)) {
            // TODO and now?
        } else {
            if (strpos($id, 'yform') !== false) {
                $table = explode('::', $id);

                if (sizeof($table) > 2) {
                    $this->id = $table[0] . '::' . $table[1];
                    $this->settings['type_key'] = $table[2];
                    $post = rex_request::post($table[1]);
                    if (!is_null($post) && isset($post[$this->settings['type_key']])) {
                        $this->values['value'][$id] = $post[$this->settings['type_key']];
                    }
                    if (sizeof($table) > 3) {
                        $this->values = MBlockValueProvider::loadFromTable($table);
                    }
                } else {
                    $this->values = rex_request::post($table[1]);
                }

            } else {
                // is table::column
                $table = explode('::', $id);
                $this->values = MBlockValueProvider::loadFromTable($table, rex_request::get('id', 'int', 0));

                if (sizeof($table) > 2) {
                    $this->id = $table[0] . '::' . $table[1];
                    $this->settings['type_key'] = array_pop($table);
                }
            }
        }
        return $this;
    }

    /**
     * @param string|mixed $form
     * @return MBlockHandler
     * @author Joachim Doerr
     */
    private function setForm($form)
    {
        // mform support
        if ($form instanceof MForm) {
            $form = $form->show();
        }

        // yform support
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

        // rex_form support
        if ($form instanceof mblock_rex_form) {
            $form = $form->getElements();
        }

        if ($form instanceof \DOMDocument) {
            // set directly instance of DOMElement
            $formDomDocument = $form;
        } else {
            // create dom document by form html
            $formDomDocument = self::createDom($form);
        }

        // TODO CLEANING

        // clean up form
        $inputs = $formDomDocument->getElementsByTagName('input');

        /** @var \DOMElement $input */
        foreach ($inputs as $input) {
            switch ($input->getAttribute('type')) {
                case 'checkbox':
                case 'radio':
                    $input->removeAttribute('checked');
                    break;
                default:
                    $input->setAttribute('value', '');
                    break;
            }
        }

        // set dom document
        $this->formDomDocument = $formDomDocument;

        // html form
        $this->formHtml = self::saveHtml($this->formDomDocument);

        return $this;
    }
}