<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

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
    public $index;

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
     * @param string $output
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
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
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param int $index
     * @return $this
     * @author Joachim Doerr
     */
    public function setIndex($index)
    {
        $this->index = $index;
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