<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

/**
 * Robuste JSON-Verarbeitung für MBlock mit umfassendem Error-Handling
 * 
 * Diese Klasse stellt sichere JSON-Operationen zur Verfügung und 
 * verhindert JSON-Parsing-Fehler und Datenverlust.
 */
class MBlockJsonHelper
{
    /** @var int JSON-Flags für Encoding */
    private const ENCODE_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    
    /** @var int JSON-Flags für Decoding */
    private const DECODE_FLAGS = JSON_BIGINT_AS_STRING;
    
    /** @var int Maximale JSON-Tiefe */
    private const MAX_DEPTH = 512;
    
    /** @var int Maximale JSON-String-Länge für Validierung */
    private const MAX_JSON_LENGTH = 1048576; // 1MB

    /**
     * Sichere JSON-Kodierung mit Error-Handling
     * 
     * @param mixed $data Die zu kodierenden Daten
     * @param bool $throwOnError Soll Exception bei Fehler geworfen werden
     * @return string|false JSON-String oder false bei Fehler
     * @throws InvalidArgumentException Bei JSON-Encoding-Fehlern
     */
    public static function encode($data, bool $throwOnError = false)
    {
        // Eingabe-Validierung
        if (!self::isValidForEncoding($data)) {
            if ($throwOnError) {
                throw new InvalidArgumentException('Daten können nicht als JSON kodiert werden');
            }
            return false;
        }

        // JSON-Encoding mit Error-Handling
        $json = json_encode($data, self::ENCODE_FLAGS, self::MAX_DEPTH);
        
        if ($json === false || json_last_error() !== JSON_ERROR_NONE) {
            $error = self::getLastJsonError();
            
            if ($throwOnError) {
                throw new InvalidArgumentException("JSON-Encoding-Fehler: {$error}");
            }
            
            // Fallback-Strategie: Leeres Array als JSON
            return '[]';
        }

        return $json;
    }

    /**
     * Sichere JSON-Dekodierung mit Error-Handling
     * 
     * @param string $json JSON-String
     * @param bool $associative Als Array zurückgeben
     * @param bool $throwOnError Soll Exception bei Fehler geworfen werden
     * @return mixed|null Dekodierte Daten oder null bei Fehler
     * @throws InvalidArgumentException Bei JSON-Decoding-Fehlern
     */
    public static function decode(string $json, bool $associative = true, bool $throwOnError = false)
    {
        // Eingabe-Validierung
        if (!self::isValidJsonString($json)) {
            if ($throwOnError) {
                throw new InvalidArgumentException('Ungültiger JSON-String');
            }
            return $associative ? [] : null;
        }

        // JSON-Decoding mit Error-Handling
        $decoded = json_decode($json, $associative, self::MAX_DEPTH, self::DECODE_FLAGS);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = self::getLastJsonError();
            
            if ($throwOnError) {
                throw new InvalidArgumentException("JSON-Decoding-Fehler: {$error}");
            }
            
            // Fallback-Strategie basierend auf erwarteter Rückgabe
            return $associative ? [] : null;
        }

        return $decoded;
    }

    /**
     * Sichere JSON-Dekodierung mit HTML-Entity-Decoding (für REDAXO-Kontext)
     * 
     * @param string $json HTML-encoded JSON-String
     * @param bool $associative Als Array zurückgeben
     * @param bool $throwOnError Soll Exception bei Fehler geworfen werden
     * @return mixed|null Dekodierte Daten oder null bei Fehler
     */
    public static function decodeFromHtml(string $json, bool $associative = true, bool $throwOnError = false)
    {
        // HTML-Entities dekodieren (REDAXO-spezifisch)
        $decodedJson = htmlspecialchars_decode($json, ENT_QUOTES | ENT_HTML5);
        
        return self::decode($decodedJson, $associative, $throwOnError);
    }

    /**
     * Validiert ob JSON-String gültig ist
     * 
     * @param string $json JSON-String
     * @return bool
     */
    public static function isValid(string $json): bool
    {
        if (!self::isValidJsonString($json)) {
            return false;
        }

        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Sichere Array-zu-JSON-Konvertierung für MBlock-Daten
     * 
     * @param array $data MBlock-Datenarray
     * @return string JSON-String
     */
    public static function encodeMBlockData(array $data): string
    {
        // Spezielle Behandlung für MBlock-Datenstrukturen
        $cleanedData = self::sanitizeMBlockData($data);
        
        $json = self::encode($cleanedData);
        
        // Bei Fehler: leeres MBlock-Array zurückgeben
        return $json !== false ? $json : '[]';
    }

    /**
     * Sichere JSON-zu-Array-Konvertierung für MBlock-Daten
     * 
     * @param string $json JSON-String
     * @return array MBlock-Datenarray
     */
    public static function decodeMBlockData(string $json): array
    {
        if (empty($json) || $json === 'null') {
            return [];
        }

        $decoded = self::decodeFromHtml($json, true, false);
        
        // Sicherstellen dass Ergebnis ein Array ist
        if (!is_array($decoded)) {
            return [];
        }

        return self::validateMBlockData($decoded);
    }

    /**
     * Bereinigt MBlock-Daten für JSON-Encoding
     * 
     * @param array $data MBlock-Daten
     * @return array Bereinigte Daten
     */
    private static function sanitizeMBlockData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            // Nur gültige Schlüssel und Werte beibehalten
            if (is_string($key) || is_int($key)) {
                if (is_array($value)) {
                    $sanitized[$key] = self::sanitizeMBlockData($value);
                } elseif (is_scalar($value) || is_null($value)) {
                    $sanitized[$key] = $value;
                }
                // Objekte und Ressourcen werden ignoriert
            }
        }
        
        return $sanitized;
    }

    /**
     * Validiert MBlock-Datenstruktur
     * 
     * @param array $data MBlock-Daten
     * @return array Validierte Daten
     */
    private static function validateMBlockData(array $data): array
    {
        $validated = [];
        
        foreach ($data as $key => $value) {
            // Sicherstellen dass Schlüssel gültig sind
            if (is_string($key) || is_int($key)) {
                if (is_array($value)) {
                    $validated[$key] = self::validateMBlockData($value);
                } elseif (is_scalar($value) || is_null($value)) {
                    $validated[$key] = $value;
                }
                // Ungültige Datentypen werden ignoriert
            }
        }
        
        return $validated;
    }

    /**
     * Überprüft ob Daten für JSON-Encoding geeignet sind
     * 
     * @param mixed $data Zu prüfende Daten
     * @return bool
     */
    private static function isValidForEncoding($data): bool
    {
        // Ressourcen können nicht kodiert werden
        if (is_resource($data)) {
            return false;
        }

        // Rekursive Prüfung für Arrays und Objekte
        if (is_array($data) || is_object($data)) {
            foreach ($data as $value) {
                if (!self::isValidForEncoding($value)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validiert JSON-String-Eingabe
     * 
     * @param string $json JSON-String
     * @return bool
     */
    private static function isValidJsonString(string $json): bool
    {
        // Leere Strings sind ungültig
        if (trim($json) === '') {
            return false;
        }

        // Größenbeschränkung
        if (strlen($json) > self::MAX_JSON_LENGTH) {
            return false;
        }

        // Grundlegende JSON-Struktur-Prüfung
        $firstChar = trim($json)[0] ?? '';
        return in_array($firstChar, ['{', '[', '"'], true) || is_numeric($firstChar) || in_array(trim($json), ['true', 'false', 'null'], true);
    }

    /**
     * Gibt benutzerfreundliche JSON-Fehlermeldung zurück
     * 
     * @return string Fehlermeldung
     */
    private static function getLastJsonError(): string
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return 'Kein Fehler';
            case JSON_ERROR_DEPTH:
                return 'Maximale Tiefe überschritten';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Ungültiger oder fehlerhafter JSON';
            case JSON_ERROR_CTRL_CHAR:
                return 'Steuerzeichen-Fehler';
            case JSON_ERROR_SYNTAX:
                return 'Syntax-Fehler';
            case JSON_ERROR_UTF8:
                return 'UTF-8-Zeichen-Fehler';
            case JSON_ERROR_RECURSION:
                return 'Rekursive Referenzen';
            case JSON_ERROR_INF_OR_NAN:
                return 'INF oder NAN Werte';
            case JSON_ERROR_UNSUPPORTED_TYPE:
                return 'Nicht unterstützter Datentyp';
            default:
                return 'Unbekannter JSON-Fehler';
        }
    }

    /**
     * Debug-Information für JSON-Operationen
     * 
     * @param string $json JSON-String
     * @return array Debug-Info
     */
    public static function getDebugInfo(string $json): array
    {
        return [
            'is_valid' => self::isValid($json),
            'length' => strlen($json),
            'first_char' => $json[0] ?? '',
            'last_error' => self::getLastJsonError(),
            'memory_usage' => memory_get_usage(true)
        ];
    }
}
