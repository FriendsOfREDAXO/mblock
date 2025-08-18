<?php

/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */



namespace FriendsOfRedaxo\MBlock\Handler;

use rex;
use rex_addon;
use rex_get;
use rex_request;
use rex_sql;
use rex_sql_exception;

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
                $inputValues = rex_request('REX_INPUT_VALUE', 'array');
                
                // Sichere Validierung der Input-Werte
                foreach ($inputValues as $key => $value) {
                    // Schlüssel-Validierung: Nur numerische und String-Schlüssel erlauben
                    if (!is_string($key) && !is_numeric($key)) {
                        continue; // Ungültige Schlüssel überspringen
                    }
                    
                    // Wert-Validierung: SQL-Injection-Schutz und Datentypprüfung
                    if (is_array($value)) {
                        // Arrays rekursiv validieren und bereinigen
                        $result['value'][$key] = self::sanitizeArrayValue($value);
                    } elseif (is_scalar($value) || is_null($value)) {
                        // Skalare Werte sind erlaubt
                        $result['value'][$key] = $value;
                    } else {
                        // Objekte und Ressourcen überspringen
                        continue;
                    }
                }
                return $result;
            }
        }

        if ($sliceId !== false && is_numeric($sliceId) && $sliceId > 0) {
            $table = rex::getTablePrefix() . 'article_slice';
            $fields = '*';
            $sliceId = (int) $sliceId; // Explizite Typisierung für SQL-Sicherheit

            $sql = rex_sql::factory();
            $sql->setTable($table);
            $sql->setWhere(['id' => $sliceId]); // Sichere Parameter-Bindung
            $sql->select($fields);
            
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

                    // Robuste JSON-Dekodierung mit MBlockJsonHelper
                    $valueString = (string) $result['value'][$i];
                    if (!empty($valueString)) {
                        $jsonResult = Utils\MBlockJsonHelper::decodeFromHtml($valueString, true, false);
                        
                        // Nur gültige Arrays als Ergebnis verwenden
                        if (is_array($jsonResult) && !empty($jsonResult)) {
                            $result['value'][$i] = $jsonResult;
                        }
                        // Bei Fehlern bleibt der ursprüngliche Wert erhalten
                    }
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
                $result = array();

        // Input-Validierung für SQL-Injection-Schutz
        if (!is_array($table) || count($table) < 2) {
            return $result;
        }

        // Sichere Tabellen- und Spaltennamen-Validierung
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $table[0]);
        $columnName = preg_replace('/[^a-zA-Z0-9_]/', '', $table[1]);
        $idField = 'id';
        $attrType = (sizeof($table) > 3) ? preg_replace('/[^a-zA-Z0-9_]/', '', $table[3]) : null;

        // Sichere ID-Validierung
        if (!is_numeric($id) || $id <= 0) {
            return $result;
        }
        $id = (int) $id;

        if (strpos($table[0], '>>') !== false) {
            $explodedId = explode('>>', $table[0]);
            $idField = preg_replace('/[^a-zA-Z0-9_]/', '', $explodedId[0]);
            $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $explodedId[1]);
        }

        $sql = rex_sql::factory();
        $sql->setTable($tableName);
        $sql->setWhere([$idField => $id]);
        $sql->select('*');
        // rex_sql select hat bereits ein implizites LIMIT wenn setWhere mit einem einzelnen ID verwendet wird

        if ($sql->getRows() > 0) {
            $row = $sql->getRow();
            $fullColumnName = $tableName . '.' . $columnName;
            
            if (array_key_exists($fullColumnName, $row)) {
                $jsonString = $row[$fullColumnName];
                $jsonResult = Utils\MBlockJsonHelper::decodeFromHtml($jsonString, true, false);

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

    /**
     * Bereinigt Array-Werte rekursiv für sicherere Verarbeitung
     * @param array $array - Zu bereinigendes Array
     * @return array - Bereinigtes Array
     * @author Joachim Doerr
     */
    private static function sanitizeArrayValue($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $sanitized = [];
        foreach ($array as $key => $value) {
            // Schlüssel-Validierung
            if (!is_string($key) && !is_numeric($key)) {
                continue;
            }

            // Wert-Validierung
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArrayValue($value); // Rekursiver Aufruf
            } elseif (is_scalar($value) || is_null($value)) {
                $sanitized[$key] = $value;
            }
            // Objekte und Ressourcen werden übersprungen
        }

        return $sanitized;
    }
}

