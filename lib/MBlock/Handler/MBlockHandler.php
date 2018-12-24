<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace Mblock\Handler;


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
     * @var boolean
     */
    private $plain = false;

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

        $this->setValByValue();
    }

    /**
     * Create all form items by loaded values
     * @return MBlockItem[]
     * @author Joachim Doerr
     */
    public function createItems()
    {
        // crate plain element
        $this->plainItem = new MBlockItem();
        $this->plainItem->setItemId(0)
            ->setValueId($this->id)
            ->setResult(array())
            ->setForm($this->formDomDocument)
            ->addPayload('plain_item', true);

        // loaded values?
        if (!is_null($this->val)) {
            // iterate value levels
            foreach ($this->val as $key => $value) {
                if (isset($this->val[$key])) {
                    // create block items by value
                    $this->items[$key] = new MBlockItem();
                    $this->items[$key]->setItemId($key)
                        ->setValueId($this->id)
                        ->setVal($this->val[$key])
                        ->setForm(clone $this->formDomDocument);
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
        if (!$this->items && (!isset($this->settings['initial_hidden']) or $this->settings['initial_hidden'] != 1)) {
            // set plain item for base form
            $this->plain = true;
            $this->items[0] = new MBlockItem();
            $this->items[0]->setItemId(0)
                ->setValueId($this->id)
                ->setVal(array())
                ->setForm(clone $this->formDomDocument);
        }

        return $this->items;
    }

    /**
     * @param null $nestedCount
     * @return MBlockItem[]
     * @author Joachim Doerr
     */
    public function iterateItems($nestedCount = null)
    {
        if (sizeof($this->items) > 0) {
            /** @var MBlockItem $item */
            foreach ($this->items as $count => $item) {
                // nested mblock?
                $mblockWrapper = self::getElementsByClass($item->getForm(), 'div.mblock_wrapper');

                if (sizeof($mblockWrapper) > 0) {
                    foreach ($mblockWrapper as $mKey => $wrapper) {
                        $this->handleNestedMBlock($item, $wrapper, $mKey);
                    }
                }
                $this->executeItemManipulations($item, ($count +1), $nestedCount);
                // parse form item
                $element = new MBlockElement();
                $element->setForm(self::saveHtml($item->getForm()))
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

        $this->executeItemManipulations($this->plainItem, 0, null);

        // parse form item
        $element = new MBlockElement();
        $element->setForm(self::saveHtml($this->plainItem->getForm()))
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
     * @param $key
     * @author Joachim Doerr
     */
    private function handleNestedMBlock(MBlockItem $item, \DOMElement $element, $key)
    {
        // TODO read settings from mblock html settings

        $sortItem = $element->firstChild;
        if ($sortItem instanceof \DOMElement && $sortItem->getAttribute('class') == 'sortitem') { // sort item === mblock sort wrapper
            $nodes = $sortItem->childNodes;
            /** @var \DOMElement $node */
            foreach ($nodes as $node) {
                if ($node instanceof \DOMElement && $node->nodeName == 'div') { // first div in sort item === form wrapper
                    $nodes = $node->childNodes;
                    break;
                }
            }

            // create new DOM for mblock
            $form = new \DOMDocument();
            foreach($nodes as $node)
            {
                $newnode = $form->importNode($node, true);
                $form->appendChild($newnode);
            }
            // dump($newdoc->saveHTML());
            // remove mblock forms
            $element->removeChild($sortItem);

            $arrV = array();
            foreach ($item->getVal() as $val) {
                if (is_array($val)) {
                    $arrV[] = $val;
                }
            }
            $value = (isset($arrV[$key])) ? array('value' => array($this->id => $arrV[$key])) : array('value' => array());

            // TODO add settings from mblock html settings
            // add new nodes
            $subMblockHandler = new MBlockHandler($this->id, $form, array(), $value);

            // duplicate form elements by values
            $subMblockHandler->createItems();

            // remove data mblock flag
            $subMblockHandler->clearItems();

            // iterate items and create blocks
            $subMblockHandler->iterateItems($item->getItemId());

            // parse elements to mblock blocks
            $subMblockHandler->parseItemElements();

            // add blocks to element
            foreach ($subMblockHandler->items as $item) {
                $elementNode = self::createDom($item->getElement()->getOutput());
                $newnode = $element->ownerDocument->importNode($elementNode->firstChild, true);
                if ($newnode instanceof \DOMNode) {
                    $element->appendChild($newnode);
                }
            }

            // set mblock count data
            $element->setAttribute('data-mblock_count', rex_session('mblock_count'));
            // dump($this->innerHTML($element->parentNode));
        } else {
            // TODO error log
            // dump($key);
        }
    }

    /**
     * @param \DOMElement $element
     * @return string
     * @author Joachim Doerr
     */
    protected function innerHTML(\DOMElement $element)
    {
        $doc = $element->ownerDocument;
        $html = '';
        foreach ($element->childNodes as $node) {
            $html .= $doc->saveHTML($node);
        }
        return $html;
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
                    if ($item->getForm() instanceof \DOMDocument && $matches = $item->getForm()->getElementsByTagName($value)) {
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

        // set directly instance of DOMElement
        if ($form instanceof \DOMDocument) {
            $this->formDomDocument = $form;
            return $this;
        }

        // create dom document by form html
        $this->formDomDocument = self::createDom($form);
        return $this;
    }
}