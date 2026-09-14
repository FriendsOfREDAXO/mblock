<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */



namespace FriendsOfRedaxo\MBlock\DTO;

class MBlockItem
{
    /**
     * @var array<string, mixed>
     */
    public $result = array();

    /**
     * @var integer
     */
    public $id;

    /**
     * @var int|string
     */
    public $valueId;

    /**
     * @var int|string|null
     */
    public $systemId;

    /**
     * @var string
     */
    public $systemName;

    /**
     * @var string
     */
    public $form;

    /**
     * @var array<string, mixed>
     */
    public $payload = array();

    /**
     * @return array<string, mixed>
     * @author Joachim Doerr
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array<string, mixed> $result
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return int
     * @author Joachim Doerr
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int|string
     * @author Joachim Doerr
     */
    public function getValueId()
    {
        return $this->valueId;
    }

    /**
     * @param int|string $valueId
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setValueId($valueId)
    {
        $this->valueId = $valueId;
        return $this;
    }

    /**
     * @return int|string|null
     * @author Joachim Doerr
     */
    public function getSystemId()
    {
        return $this->systemId;
    }

    /**
     * @param int|string $systemId
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
     * @return string
     * @author Joachim Doerr
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param string $form
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @param string|null $key
     * @return mixed Wert des Schluessels, ohne Schluessel das ganze Payload-Array
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
     * @param array<string, mixed> $payload
     * @return MBlockItem
     * @author Joachim Doerr
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addPayload($key, $value)
    {
        $this->payload[$key] = $value;
        return $this;
    }
}