<?php

class rex_var_json_value extends rex_var {
  
  protected function getOutput() {
    $id = $this->getArg('id', 0, true);

    $value = json_decode($this->getContextData()->getValue('value' . $id),1);
    if(empty($value))
      $value = array(array());
    return var_export($value,1);
  }
}