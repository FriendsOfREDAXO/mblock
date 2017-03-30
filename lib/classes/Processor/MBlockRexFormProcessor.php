<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockRexFormProcessor
{
    /**
     * @param mblock_rex_form $form
     * @param array $post
     * @author Joachim Doerr
     * @return mblock_rex_form
     */
    public static function prePostSaveAction(mblock_rex_form $form, array $post)
    {
        // set process it flag
        $processIt = false;

        foreach ($post as $fieldRow => $fields)
            if (strpos($fieldRow, '_save') !== false OR strpos($fieldRow, '_apply') !== false)
                $processIt = true;

        if ($processIt)
            if ($form->isEditMode())
                self::update($form, $post);

        return $form;
    }

    /**
     * @param $status
     * @param mblock_rex_form $form
     * @param array $post
     * @return mixed
     * @author Joachim Doerr
     */
    public static function postPostSaveAction($status, mblock_rex_form $form, array $post)
    {
        self::update($form, $post, $form->getSql()->getLastId());
        return $status;
    }

    /**
     * @param mblock_rex_form $form
     * @param array $post
     * @param null $id
     * @author Joachim Doerr
     */
    private static function update(mblock_rex_form $form, array $post, $id = null)
    {
        $sql = rex_sql::factory();
        $sql->setTable($form->getTableName());

        if (!is_null($id))
            $sql->setWhere('id = ' . $id);
        else
            $sql->setWhere($form->getWhereCondition());

        $updateValues = array();

        if (isset($post[$form->getName()]))
            foreach ($post[$form->getName()] as $row => $field)
                if (is_array($field))
                    $updateValues[$row] = json_encode($field);

        if (count($updateValues) > 0)
            $sql->setValues($updateValues)->update();
    }
}