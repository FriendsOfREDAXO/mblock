<?php
/**
 * Author: Joachim Doerr
 * Date: 05.06.18
 * Time: 13:28
 */

namespace Mblock\Handler;


use MBlock\Decorator\MBlockDOMTrait;
use MBlock\Decorator\MBlockFormItemDecorator;
use MBlock\DTO\MBlockElement;
use MBlock\DTO\MBlockItem;
use MBlock\Parser\MBlockParser;
use MBlock\Provider\MBlockValueProvider;
use MBlock\Replacer\MBlockBootstrapReplacer;
use MBlock\Replacer\MBlockCheckboxReplacer;
use MBlock\Replacer\MBlockCountReplacer;
use MBlock\Replacer\MBlockSystemButtonReplacer;
use mblock_rex_form;
use MBlockSettingsHelper;
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
     * @return MBlockItem[]
     * @author Joachim Doerr
     */
    public function createItems()
    {
        if (!is_null($this->val)) {
            foreach ($this->val as $key => $value) {
                if (isset($this->val[$key])) {
                    $this->items[$key] = new MBlockItem();
                    $this->items[$key]->setItemId($key)
                        ->setValueId($this->id)
                        ->setVal($this->val[$key])
                        ->setForm(clone $this->formDomDocument);
                }
            }
        }
        // don't loaded?
        if (sizeof($this->items) <= 0) {
            // set plain item for base form
            $this->plain = true;
            $this->items[0] = new MBlockItem();
            $this->items[0]->setItemId(0)
                ->setValueId($this->id)
                ->setVal(array())
                ->setForm($this->formDomDocument);
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

                $this->executeItemManipulations($item, $count, $nestedCount);

                // parse form item
                $element = new MBlockElement();
                $element->setForm($item->getForm()->saveHTML())
                    ->setIndex(($count + 1))
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
                $this->elementParse($item->getElement(), $this->themeKey);
            }
        }

        return $this->items;
    }

    /**
     * @return mixed|string
     * @author Joachim Doerr
     */
    public function parseMBlockWrapper()
    {
        $output = '';
        if (sizeof($this->items) > 0) {
            // wrap parsed form items
            $wrapper = new MBlockElement();
            $wrapper->setOutput($this->getElementOutputs())
                ->setSettings(MBlockSettingsHelper::getSettings($this->settings));

            // return wrapped from elements
            $output = MBlockParser::parseElement($wrapper, 'wrapper', $this->themeKey);
        }
        return $output;
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
     */
    private function executeItemManipulations(MBlockItem $item, $count, $nestedCount = null)
    {
        // replace system button data
        MBlockSystemButtonReplacer::replaceSystemButtons($item, ($count + 1));
        MBlockCountReplacer::replaceCountKeys($item, ($count + 1));
        MBlockBootstrapReplacer::replaceTabIds($item, ($count + 1));
        MBlockBootstrapReplacer::replaceCollapseIds($item, ($count + 1));

        // decorate item form
        if ($item->getVal() or !is_null($nestedCount)) {
            MBlockFormItemDecorator::decorateFormItem($item, $nestedCount);
            // custom link hidden to text
            MBlockSystemButtonReplacer::replaceCustomLinkText($item);
        }

        // set only checkbox block holder
        MBlockCheckboxReplacer::replaceCheckboxesBlockHolder($item, ($count + 1));
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
        if ($sortItem->getAttribute('class') == 'sortitem') { // sort item === mblock sort wrapper
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
                $element->appendChild($newnode);
            }

            // set mblock count data
            $element->setAttribute('data-mblock_count', $_SESSION['mblock_count']);
            // dump($this->innerHTML($element->parentNode));
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