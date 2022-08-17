<?php

/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockValueHandler
{
    /**
     * @return array
     * @author Joachim Doerr
     * @throws rex_sql_exception
     */
    public static function loadRexVars()
    {
        $sliceId = rex_request('slice_id', 'int', false);
        $result = array();

        if (rex_get('function') == 'add') {
            return $result;
        }
       
        $prevent_action = false; 
        if (rex_addon::get('gridblock')->isAvailable())
        {
            if (rex_gridblock::isBackend())
            {
                $prevent_action = true; 
            }
        }
         // Get data from $_POST and ignore gridblock addon 
         // Should be chenged when https://github.com/redaxo/redaxo/issues/5298 is fixed. 
        if (rex_request('save', 'int') == 1 && $prevent_action == false) {
            $result = [];

            if (rex_request('REX_INPUT_VALUE', 'array')) {
                foreach (rex_request('REX_INPUT_VALUE') as $key => $value) {
                    $result['value'][$key] = $value;
                }
                return $result;
            }
        }

        if ($sliceId != false) {
            $table = rex::getTablePrefix() . 'article_slice';
            $fields = '*';
            $where = 'id="' . $_REQUEST['slice_id'] . '"';

            $sql = rex_sql::factory();
            $query = '
                SELECT ' . $fields . '
                FROM ' . $table . '
                WHERE ' . $where;

            $sql->setQuery($query);
            $rows = $sql->getRows();

            if ($rows > 0) {
                for ($i = 1; $i <= 20; $i++) {
                    $result['value'][$i] = $sql->getValue('value' . $i);

                    if ($i <= 10) {
                        $result['filelist'][$i] = $sql->getValue('medialist' . $i);
                        $result['linklist'][$i] = $sql->getValue('linklist' . $i);
                        $result['file'][$i] = $sql->getValue('media' . $i);
                        $result['link'][$i] = $sql->getValue('link' . $i);
                    }

                    $jsonResult = json_decode(htmlspecialchars_decode((string) $result['value'][$i]), true);

                    if (is_array($jsonResult))
                        $result['value'][$i] = $jsonResult;
                }
            }
        }
        return $result;
    }

    /**
     * @param $table
     * @param null|int $id
     * @return array
     * @author Joachim Doerr
     * @throws rex_sql_exception
     */
    public static function loadFromTable($table, $id = 0)
    {
        $tableName = str_replace('yform_', '', $table[0]);
        $columnName = $table[1];
        $attrType = (isset($table[2])) ? $table[2] : null;
        $id = ($id == 0 && isset($table[3])) ? $table[3] : $id;
        $idField = 'id';

        if (strpos($id, '>>') !== false) {
            $explodedId = explode('>>', $id);
            $idField = $explodedId[0];
            $id = $explodedId[1];
        }

        $result = array();

        $sql = rex_sql::factory();
        $sql->setQuery("SELECT * FROM $tableName WHERE $idField='$id' LIMIT 1");

        if ($sql->getRows() > 0) {
            if (array_key_exists($tableName . '.' . $columnName, $sql->getRow())) {
                $jsonResult = json_decode(htmlspecialchars_decode($sql->getRow()[$tableName . '.' . $columnName]), true);

                if (!is_null($attrType) && is_array($jsonResult) && array_key_exists($attrType, $jsonResult)) {
                    $jsonResult = $jsonResult[$attrType];
                }

                $tableKey = ($table[0] != $tableName) ? $table[0] : $tableName;

                if (is_array($jsonResult))
                    $result['value'][$tableKey . '::' . $columnName] = $jsonResult;
            }
        }

        return $result;
    }
}

