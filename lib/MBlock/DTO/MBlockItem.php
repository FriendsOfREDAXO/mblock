<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\DTO;


class MBlockItem
{
    /**
     * @var string
     */
    public $formHtml;

    /**
     * @var array
     */
    public $result;

    /**
     * @var array
     */
    public $val;

    /**
     * @var string
     */
    public $plainId;

    /**
     * @var integer
     */
    public $itemId;

    /**
     * @var integer
     */
    public $subId;

    /**
     * @var integer
     */
    public $valueId;

    /**
     * @var integer
     */
    public $systemId;

    /**
     * @var string
     */
    public $systemName;

    /**
     * @var \DOMDocument
     */
    public $formDomDocument;

    /**
     * @var MBlockElement
     */
    public $element;

    /**
     * @var array
     */
    public $payload = array();

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getFormHtml()
    {
        return $this->formHtml;
    }

    /**
     * @param string $formHtml
     * @return $this
     * @author Joachim Doerr
     */
    public function setFormHtml($formHtml)
    {
        $this->formHtml = $formHtml;
        return $this;
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array $result
     * @return $this
     * @author Joachim Doerr
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    public function getVal()
    {
        return $this->val;
    }

    /**
     * @param array $val
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setVal($val)
    {
        $this->val = $val;
        return $this;
    }

    /**
     * @return int
     * @author Joachim Doerr
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param int $itemId
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
        return $this;
    }

    /**
     * @return int
     * @author Joachim Doerr
     */
    public function getSubId()
    {
        return $this->subId;
    }

    /**
     * @param int $subId
     * @return $this
     * @author Joachim Doerr
     */
    public function setSubId($subId)
    {
        $this->subId = $subId;
        return $this;
    }

    /**
     * @return int
     * @author Joachim Doerr
     */
    public function getValueId()
    {
        return $this->valueId;
    }

    /**
     * @param int $valueId
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setValueId($valueId)
    {
        $this->valueId = $valueId;
        return $this;
    }

    /**
     * @return int
     * @author Joachim Doerr
     */
    public function getSystemId()
    {
        return $this->systemId;
    }

    /**
     * @param int $systemId
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setSystemId($systemId)
    {
        $this->systemId = $systemId;
        return $this;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getSystemName()
    {
        return $this->systemName;
    }

    /**
     * @param string $systemName
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setSystemName($systemName)
    {
        $this->systemName = $systemName;
        return $this;
    }

    /**
     * @return MBlockElement
     * @author Joachim Doerr
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param MBlockElement $element
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setElement($element)
    {
        $this->element = $element;
        return $this;
    }

    /**
     * @return \DOMDocument
     * @author Joachim Doerr
     */
    public function getFormDomDocument()
    {
        return $this->formDomDocument;
    }

    /**
     * @param \DOMDocument $formDomDocument
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setFormDomDocument($formDomDocument)
    {
        $this->formDomDocument = $formDomDocument;
        return $this;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getPlainId()
    {
        return $this->plainId;
    }

    /**
     * @param string $plainId
     * @return $this
     * @author Joachim Doerr
     */
    public function setPlainId($plainId)
    {
        $this->plainId = $plainId;
        return $this;
    }

    /**
     * @param null $key
     * @return array
     * @author Joachim Doerr
     */
    public function getPayload($key = null)
    {
        if (!is_null($key) && array_key_exists($key, $this->payload)) {
            return $this->payload[$key];
        }
        return $this->payload;
    }

    /**
     * @param array $payload
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addPayload($key, $value)
    {
        $this->payload[$key] = $value;
        return $this;
    }
}