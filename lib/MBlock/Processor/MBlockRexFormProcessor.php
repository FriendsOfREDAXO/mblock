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

        if (isset($post[$form->getName()]))
            foreach ($post[$form->getName()] as $row => $field)
                if (is_array($field))
                    $updateValues[$row] = json_encode($field);

        // is row not in update list?
        if (sizeof($rows) > 0)
            foreach ($rows as $row)
                if (!array_key_exists($row, $updateValues))
                    $updateValues[$row] = NULL;

        if (sizeof($updateValues) > 0)
            $sql->setValues($updateValues)->update();
    }
}