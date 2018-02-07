<?php

class rex_yform_action_mblock_db_query extends rex_yform_action_abstract
{
    public function executeAction()
    {
        $query = trim($this->getElement(2));
        $labels = explode(',', $this->getElement(3));

        if ($query == '') {
            if ($this->params['debug']) {
                echo 'ActionQuery Error: no query';
            }
            return;
        }

        $sql = rex_sql::factory();
        if ($this->params['debug']) {
            $sql->setDebug();
        }

        $params = [];
        foreach ($labels as $label) {
            $label = trim($label);
            if (!isset($this->params['value_pool']['sql'][$label])) {
                $params[] = $this->params['value_pool']['sql'][$label];
            } else {
                $params[] = json_encode(dump(rex_request::post($label)));
            }
        }

        $sql->setQuery($query, $params);
    }

    public function getDescription()
    {
        return 'action|mblock_db_query|query|labels[name,email,id]';
    }
}
