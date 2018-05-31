<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class mblock_rex_form extends rex_form
{
    /**
     * Speichert das Formular.
     *
     * Übernimmt die Werte aus den FormElementen in die Datenbank.
     *
     * Gibt true zurück wenn alles ok war, false bei einem allgemeinen Fehler,
     * einen String mit einer Fehlermeldung oder den von der Datenbank gelieferten ErrorCode.
     *
     * @return bool
     */
    protected function save()
    {
        $sql = rex_sql::factory();
        $sql->setDebug($this->debug);
        $sql->setTable($this->tableName);

        $values = [];
        foreach ($this->getSaveElements() as $fieldsetName => $fieldsetElements) {
            /** @var rex_form_element $element */
            foreach ($fieldsetElements as $element) {
                // read-only-fields nicht speichern
                if (strpos($element->getAttribute('class'), 'form-control-static') !== false) {
                    continue;
                }

                // add by JD
                // must have for json array
                if (strpos($element->getFieldName(), '][') !== false) {
                    continue;
                }

                $fieldName = $element->getFieldName();
                $fieldValue = $element->getSaveValue();

                // Callback, um die Values vor dem Speichern noch beeinflussen zu können
                $fieldValue = $this->preSave($fieldsetName, $fieldName, $fieldValue, $sql);

                $values[$fieldName] = $fieldValue;
            }
        }

        try {
            if ($this->isEditMode()) {
                $sql->setValues($values);
                $sql->setWhere($this->whereCondition);
                $sql->update();
            } else {
                if (count($this->languageSupport)) {
                    foreach (rex_clang::getAllIds() as $clang_id) {
                        $sql->setTable($this->tableName);
                        $sql->addGlobalCreateFields();
                        $sql->addGlobalUpdateFields();
                        if (!isset($id)) {
                            $id = $sql->setNewId($this->languageSupport['id']);
                        } else {
                            $sql->setValue($this->languageSupport['id'], $id);
                        }
                        $sql->setValue($this->languageSupport['clang'], $clang_id);
                        $sql->setValues($values);
                        $sql->insert();
                    }
                } else {
                    $sql->setValues($values);
                    $sql->insert();
                }
            }
            $saved = true;
        } catch (rex_sql_exception $e) {
            $saved = false;
        }

        // ----- EXTENSION POINT
        if ($saved) {
            $saved = rex_extension::registerPoint(new rex_extension_point('REX_FORM_SAVED', $saved, ['form' => $this, 'sql' => $sql]));
        } else {
            $saved = $sql->getMysqlErrno();
        }

        return $saved;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function get()
    {
        if (rex_request::get('redirected', 'int', 0) == 1) {
            $list_name = rex_request::get('list', 'string');
            $message = rex_request::get($list_name . '_msg', 'string');

            if ($message)
                if (rex_request::get('msg_is_warning', 'int', 0) == 1)
                    $this->setWarning($message);
                else
                    $this->setMessage($message);
        }
        return parent::get();
    }

    /**
     * @return bool
     * @author Joachim Doerr
     */
    public function validate()
    {
        return parent::validate();
    }

    /**
     * @param string $listMessage
     * @param string $listWarning
     * @param array $params
     * @author Joachim Doerr
     */
    public function redirect($listMessage = '', $listWarning = '', array $params = [])
    {
        parent::redirect($listMessage, $listWarning, $params);
    }

    /**
     * @return bool
     * @author Joachim Doerr
     */
    public function isEditMode()
    {
        return parent::isEditMode();
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    public function getFieldsets()
    {
        return parent::getFieldsets();
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    public function getFieldsetElements()
    {
        return parent::getFieldsetElements();
    }

    /**
     * @param string $legend
     * @return string
     * @author Joachim Doerr
     */
    public function getElements($legend = '')
    {
        $i = 0;
        $addHeaders = true;
        $fieldsets = $this->getFieldsetElements();
        $last = count($fieldsets);
        $s = "\n";

        foreach ($fieldsets as $fieldsetName => $fieldsetElements) {
            $s .= '<fieldset>' . "\n";

            if ($legend != '' && $legend != $this->name) {
                $s .= '<legend>' . htmlspecialchars($legend) . '</legend>' . "\n";
            }

            if ($i == 0 && $addHeaders) {
                foreach ($this->getHeaderElements() as $element) {
                    // Callback
                    $element->setValue($this->preView($fieldsetName, $element->getFieldName(), $element->getValue()));
                    // HeaderElemente immer ohne <p>
                    $s .= $element->formatElement();
                }
                $addHeaders = false;
            }

            foreach ($fieldsetElements as $element) {
                // Callback
                $element->setValue($this->preView($fieldsetName, $element->getFieldName(), $element->getValue()));
                $s .= $element->get();
            }


            $s .= '</fieldset>' . "\n";

            ++$i;
        }
        return $s;
    }
}