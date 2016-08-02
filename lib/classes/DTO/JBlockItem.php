<?php
/**
 * Created by PhpStorm.
 * User: joachimdoerr
 * Date: 31.07.16
 * Time: 08:34
 */

class JBlockItem
{
    /**
     * @var array
     */
    public $result;

    /**
     * @var integer
     */
    public $id;

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
    public $form;

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getValueId()
    {
        return $this->valueId;
    }

    /**
     * @param int $valueId
     * @return $this
     */
    public function setValueId($valueId)
    {
        $this->valueId = $valueId;
        return $this;
    }

    /**
     * @return int
     */
    public function getSystemId()
    {
        return $this->systemId;
    }

    /**
     * @param int $systemId
     */
    public function setSystemId($systemId)
    {
        $this->systemId = $systemId;
    }

    /**
     * @return string
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param string $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }
}