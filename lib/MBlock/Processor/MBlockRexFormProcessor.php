<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockRexFormProcessor
{
    /**
     * @param $status
     * @param mblock_rex_form $form
     * @param array $post
     * @return mixed
     * @author Joachim Doerr
     * @throws rex_sql_exception
     */
    public static function postPostSaveAction($status, mblock_rex_form $form, array $post)
    {
        $redirect = false;
        $processIt = false;

        foreach ($post as $fieldRow => $fields) {
            if (strpos($fieldRow, '_save') !== false OR strpos($fieldRow, '_apply') !== false)
                $processIt = true;

            if (strpos($fieldRow, '_apply') !== false)
                $redirect = true;
        }

        if ($processIt)
            self::update($form, $post, $form->getSql()->getLastId());

        if ($redirect) {
            if (($result = $form->validate()) === true) {
                $form->setApplyUrl($form->getUrl(['func' => 'edit', 'redirected' => 1], false));
                $form->redirect(rex_i18n::msg('form_applied'));
            } else {
                $form->setApplyUrl($form->getUrl(['func' => 'edit', 'redirected' => 1, 'msg_is_warning' => 1], false));
                $form->redirect(rex_i18n::msg('form_save_error'));
            }
        }

        return $status;
    }

    /**
     * @param mblock_rex_form $form
     * @param array $post
     * @param null $id
     * @author Joachim Doerr
     * @throws rex_sql_exception
     */
    private static function update(mblock_rex_form $form, array $post, $id = null)
    {
        $sql = rex_sql::factory();
        $sql->setDebug(0);
        $sql->setTable($form->getTableName());

        if (!is_null($id) && $id != 0)
            $sql->setWhere('id = ' . $id);
        else
            $sql->setWhere($form->getWhereCondition());

        $updateValues = array();
        $rows = array();

        $result = $form->getSql()->getRow();

        if (is_array($result) && sizeof($result) > 0)
            foreach ($result as $row => $value)
                if(is_array(json_decode($value, true))) {
                    $newRow = explode('.', $row);
                    $rows[] = array_pop($newRow);
                }

        if (isset($post[$form->getName()])) {
            // Check for potential max_input_vars truncation
            $maxInputVars = ini_get('max_input_vars');
            $postVarCount = self::countPostVars($post);
            
            if ($postVarCount >= $maxInputVars * 0.9) { // Warn at 90% of limit
                error_log('MBlock Warning: Form POST data contains ' . $postVarCount . ' variables, approaching PHP max_input_vars limit of ' . $maxInputVars . '. Large MBlock datasets may be truncated. Consider increasing max_input_vars in PHP configuration.');
            }
            
            foreach ($post[$form->getName()] as $row => $field)
                if (is_array($field))
                    $updateValues[$row] = json_encode($field);
        }

        // is row not in update list?
        if (sizeof($rows) > 0)
            foreach ($rows as $row)
                if (!array_key_exists($row, $updateValues))
                    $updateValues[$row] = NULL;

        if (sizeof($updateValues) > 0)
            $sql->setValues($updateValues)->update();
    }

    /**
     * Recursively count variables in an array (like $_POST)
     * Used to detect max_input_vars truncation issues
     * @param array $array
     * @return int
     */
    private static function countPostVars($array)
    {
        $count = 0;
        foreach ($array as $key => $value) {
            $count++;
            if (is_array($value)) {
                $count += self::countPostVars($value);
            }
        }
        return $count;
    }
}