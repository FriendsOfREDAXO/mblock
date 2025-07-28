<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

/**
 * Sichere Session-Verwaltung für MBlock mit REDAXO Session API
 * 
 * Diese Klasse stellt robuste Session-Operationen zur Verfügung und 
 * verhindert Race-Conditions und Session-Inkonsistenzen.
 */
class MBlockSessionHelper
{
    /** @var string Session-Key für MBlock Count */
    private const MBLOCK_COUNT_KEY = 'mblock_count';
    
    /** @var int Default-Wert für MBlock Count */
    private const DEFAULT_COUNT = 0;
    
    /** @var int Maximum erlaubter Count-Wert */
    private const MAX_COUNT = 999999;

    /**
     * Sichere Initialisierung der MBlock Session mit Validierung
     * 
     * @return void
     */
    public static function initializeSession(): void
    {
        if (!self::isSessionActive()) {
            return;
        }

        $currentCount = self::getCurrentCount();
        
        // Validierung und Reset bei ungültigen Werten
        if (!self::isValidCount($currentCount)) {
            self::resetCount();
        }
    }

    /**
     * Sicheres Incrementieren des MBlock Counters
     * 
     * @return int Der neue Count-Wert
     */
    public static function incrementCount(): int
    {
        if (!self::isSessionActive()) {
            return self::DEFAULT_COUNT;
        }

        $currentCount = self::getCurrentCount();
        
        // Overflow-Protection
        if ($currentCount >= self::MAX_COUNT) {
            self::resetCount();
            return self::DEFAULT_COUNT;
        }

        $newCount = $currentCount + 1;
        rex_set_session(self::MBLOCK_COUNT_KEY, $newCount);
        
        return $newCount;
    }

    /**
     * Sichere Rückgabe des aktuellen MBlock Counters
     * 
     * @return int Der aktuelle Count-Wert
     */
    public static function getCurrentCount(): int
    {
        if (!self::isSessionActive()) {
            return self::DEFAULT_COUNT;
        }

        $count = rex_session(self::MBLOCK_COUNT_KEY, 'int', self::DEFAULT_COUNT);
        
        // Zusätzliche Validierung
        return self::isValidCount($count) ? $count : self::DEFAULT_COUNT;
    }

    /**
     * Sicheres Zurücksetzen des MBlock Counters
     * 
     * @return void
     */
    public static function resetCount(): void
    {
        if (self::isSessionActive()) {
            rex_set_session(self::MBLOCK_COUNT_KEY, self::DEFAULT_COUNT);
        }
    }

    /**
     * Atomisches Reset nur wenn notwendig (Performance-Optimierung)
     * 
     * @return bool True wenn Reset durchgeführt wurde
     */
    public static function resetCountIfNeeded(): bool
    {
        if (!self::isSessionActive()) {
            return false;
        }

        $currentCount = self::getCurrentCount();
        
        if ($currentCount !== self::DEFAULT_COUNT) {
            self::resetCount();
            return true;
        }
        
        return false;
    }

    /**
     * Überprüft ob Session aktiv und Backend-Kontext vorliegt
     * 
     * @return bool
     */
    private static function isSessionActive(): bool
    {
        // REDAXO-spezifische Session-Prüfung
        if (!rex::isBackend() || !is_object(rex::getUser())) {
            return false;
        }
        
        // Zusätzliche Session-Status-Prüfung
        return session_status() === PHP_SESSION_ACTIVE && session_id() !== '';
    }

    /**
     * Validiert Count-Werte
     * 
     * @param mixed $count Der zu validierende Wert
     * @return bool
     */
    private static function isValidCount($count): bool
    {
        return is_int($count) && $count >= 0 && $count <= self::MAX_COUNT;
    }

    /**
     * Debug-Information für Session-Status
     * 
     * @return array
     */
    public static function getDebugInfo(): array
    {
        return [
            'session_active' => self::isSessionActive(),
            'current_count' => self::getCurrentCount(),
            'session_status' => session_status(),
            'is_backend' => rex::isBackend()
        ];
    }
}
