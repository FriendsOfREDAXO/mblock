<?php
/**
 * Author: Joachim Doerr
 * Date: 30.07.16
 * Time: 22:37
 */

class JBlockElement
{
    const KEY = "<jblock:%s/>";

    /**
     * @var string
     */
    public $output;

    /**
     * @var string
     */
    public $form;

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