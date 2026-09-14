<?php

/**
 * JSON-Hilfsfunktionen fuer MBlock-Daten: robustes Kodieren und Dekodieren
 * mit REDAXO-typischen HTML-Entities in gespeicherten Werten.
 *
 * @author https://github.com/FriendsOfREDAXO
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MBlock\Utils;

use InvalidArgumentException;

class MBlockJsonHelper
{
    private const ENCODE_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    private const MAX_DEPTH = 512;

    /**
     * @param mixed $data
     * @return string|false JSON oder false, wenn die Daten nicht kodierbar sind (ohne Exception)
     */
    public static function encode($data, bool $throwOnError = false)
    {
        $json = json_encode($data, self::ENCODE_FLAGS, self::MAX_DEPTH);
        if (false === $json) {
            if ($throwOnError) {
                throw new InvalidArgumentException('JSON-Encoding-Fehler: ' . json_last_error_msg());
            }
            return false;
        }

        return $json;
    }

    /**
     * @return mixed Dekodierte Daten; bei Fehlern [] (assoziativ) bzw. null
     */
    public static function decode(string $json, bool $associative = true, bool $throwOnError = false)
    {
        $json = trim($json);
        if ('' === $json) {
            return $associative ? [] : null;
        }
        $decoded = json_decode($json, $associative, self::MAX_DEPTH, JSON_BIGINT_AS_STRING);
        if (JSON_ERROR_NONE !== json_last_error()) {
            if ($throwOnError) {
                throw new InvalidArgumentException('JSON-Decoding-Fehler: ' . json_last_error_msg());
            }
            return $associative ? [] : null;
        }

        return $decoded;
    }

    /**
     * Dekodiert JSON, das REDAXO mit HTML-Entities gespeichert hat.
     *
     * @return mixed
     */
    public static function decodeFromHtml(string $json, bool $associative = true, bool $throwOnError = false)
    {
        return self::decode(htmlspecialchars_decode($json, ENT_QUOTES | ENT_HTML5), $associative, $throwOnError);
    }

    public static function isValid(string $json): bool
    {
        if ('' === trim($json)) {
            return false;
        }
        json_decode($json);

        return JSON_ERROR_NONE === json_last_error();
    }

    /**
     * MBlock-Daten kodieren: nur Skalare, null und Arrays bleiben erhalten, Objekte und Ressourcen entfallen.
     * @param array<mixed> $data
     */
    public static function encodeMBlockData(array $data): string
    {
        $json = self::encode(self::cleanData($data));

        return false !== $json ? $json : '[]';
    }

    /**
     * MBlock-Daten aus dem gespeicherten Wert lesen; liefert immer ein Array.
     * @return array<mixed>
     */
    public static function decodeMBlockData(string $json): array
    {
        if ('' === $json || 'null' === $json) {
            return [];
        }
        $decoded = self::decodeFromHtml($json);

        return is_array($decoded) ? self::cleanData($decoded) : [];
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    private static function cleanData(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = self::cleanData($value);
            } elseif (is_scalar($value) || null === $value) {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }
}
