<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

declare(strict_types=1);

namespace FriendsOfRedaxo\MBlock\Processor;

use FriendsOfRedaxo\MBlock\Utils\MBlockJsonHelper;
use rex_i18n;
use rex_sql;
use rex_sql_exception;

class MBlockRexFormProcessor
{
    /**
     * @param mixed $status
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
            if (strpos($fieldRow, '_save') !== false || strpos($fieldRow, '_apply') !== false) {
                $processIt = true;
            }

            if (strpos($fieldRow, '_apply') !== false) {
                $redirect = true;
            }
        }

        if ($processIt) {
            self::update($form, $post, (int) $form->getSql()->getLastId());
        }

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
     * @param int|null $id
     * @author Joachim Doerr
     * @throws rex_sql_exception
     */
    private static function update(mblock_rex_form $form, array $post, ?int $id = null): void
    {
        $sql = rex_sql::factory();
        $sql->setDebug(false);
        $sql->setTable($form->getTableName());

        if ($id !== null && $id !== 0) {
            $sql->setWhere('id = ' . $id);
        } else {
            $sql->setWhere($form->getWhereCondition());
        }

        $updateValues = [];
        $rows = [];

        $result = $form->getSql()->getRow();

        if (is_array($result) && count($result) > 0) {
            foreach ($result as $row => $value) {
                if (MBlockJsonHelper::isValid($value)) {
                    $newRow = explode('.', $row);
                    $rows[] = array_pop($newRow);
                }
            }
        }

        if (isset($post[$form->getName()])) {
            foreach ($post[$form->getName()] as $row => $field) {
                if (is_array($field)) {
                    $updateValues[$row] = MBlockJsonHelper::encodeMBlockData($field);
                }
            }
        }

        // is row not in update list?
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                if (!array_key_exists($row, $updateValues)) {
                    $updateValues[$row] = null;
                }
            }
        }

        if (count($updateValues) > 0) {
            $sql->setValues($updateValues)->update();
        }
    }
}