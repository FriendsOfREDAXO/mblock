<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\DTO;


class MBlockElement
{
    const KEY = "<mblock:%s/>";

    /**
     * @var
     */
    public $settings;

    /**
     * @var string
     */
    public $output;

    /**
     * @var string
     */
    public $form;

    /**
     * @var int
     */
    public $iterateIndex;

    /**
     * @return mixed
     * @author Joachim Doerr
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param mixed $settings
     * @return MBlockElement
     * @author Joachim Doerr
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param string $output
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;
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
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return int
     * @author Joachim Doerr
     */
    public function getIterateIndex()
    {
        return $this->iterateIndex;
    }

    /**
     * @param int $iterateIndex
     * @return $this
     * @author Joachim Doerr
     */
    public function setIterateIndex($iterateIndex)
    {
        $this->iterateIndex = $iterateIndex;
        return $this;
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    public function getKeys()
    {
        $keys = array();
        foreach (get_object_vars($this) as $f => $v) {
            $keys[] = sprintf(self::KEY, $f);
        }
        return $keys;
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    public function getValues()
    {
        $values = array();
        foreach (get_object_vars($this) as $f => $v) {
            $values[] = $v;
        }
        return $values;
    }
}