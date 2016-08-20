<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockItem
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
    public $systemName;

    /**
     * @var string
     */
    public $form;

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
}