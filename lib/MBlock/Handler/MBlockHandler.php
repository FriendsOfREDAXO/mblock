<?php
/**
 * Author: Joachim Doerr
 * Date: 05.06.18
 * Time: 13:28
 */

namespace Mblock\Handler;


use MBlock\Decorator\MBlockDOMTrait;
use MBlock\DTO\MBlockItem;
use MBlock\Provider\ValueProvider;
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
     * @var \MBlockItem[]
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
     * MBlockHandler constructor.
     * @param mixed $id
     * @param mixed|string $form
     * @param array $settings
     * @param array $values
     * @author Joachim Doerr
     */
    public function __construct($id, $form, array $settings = array(), array $values = null)
    {
        $this->id = $id;
        $this->values = $values;
        $this->settings = $settings;
        $this->settings['id'] = $id;

        $this->setForm($form);

        if (is_null($this->values)) {
            $this->readValues($id);
        }

        $this->setValByValue();
    }

    /**
     * @return \MBlockItem[]
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

    public function iterateItems()
    {
        if (sizeof($this->items) > 0) {
            foreach ($this->items as $item) {
                // nested mblock?
                $mblockWrapper = self::getElementsByClass($item->getForm(), 'div.mblock_wrapper');
                if (sizeof($mblockWrapper) > 0) {
                    foreach ($mblockWrapper as $wrapper) {
                        $this->handleNestedMBlock($item, $wrapper);
                    }
                }

            }
        }
    }

    public function handleNestedMBlock(MBlockItem $item, \DOMElement $element)
    {
        $sortitem = $element->firstChild;
        if ($sortitem->getAttribute('class') == 'sortitem') { // sort item === mblock sort wrapper
            $nodes = $sortitem->childNodes;
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
            $element->removeChild($sortitem);

            // add new nodes
            $subMblockHandler = new MBlockHandler($this->id, $form, array(), $this->values);

            dump($subMblockHandler);
            die;
        }
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    private function setValByValue()
    {
        $this->val = (array_key_exists('value', $this->values) && isset($this->values['value'][$this->id])) ? $this->values['value'][$this->id] : null;
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
            $this->values = ValueProvider::loadRexVars();
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
                        $this->values = ValueProvider::loadFromTable($table);
                    }
                } else {
                    $this->values = rex_request::post($table[1]);
                }

            } else {
                // is table::column
                $table = explode('::', $id);
                $this->values = ValueProvider::loadFromTable($table, rex_request::get('id', 'int', 0));

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