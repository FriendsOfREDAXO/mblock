<?php
/**
 * @author mail[at]joachim-doerr[dot]        // Copy/Paste-Konfiguration hinzufügen
        if (!array_key_exists('copy_paste', $settings)) {
            // Use addon->getConfig to get the copy_paste setting from the settings system
            $copyPasteEnabled = $addon->getConfig('mblock_copy_paste', 1); // Default: enabled
            $settings['copy_paste'] = (bool) $copyPasteEnabled;
        }achim Doerr
 * @package redaxo5
 * @license MIT
 */



namespace FriendsOfRedaxo\MBlock\Utils;

use FriendsOfRedaxo\MBlock\Utils\MBlockSessionHelper;
use rex_addon;
use rex_i18n;

class MBlockSettingsHelper
{
    /**
     * @param array $settings
     * @return string
     * @author Joachim Doerr
     */
    public static function getSettings(array $settings)
    {
        $addon = rex_addon::get('mblock');
        $out = '';
        if (!array_key_exists('input_delete', $settings)) {
            // set default
            $settings['input_delete'] = $addon->getConfig('mblock_delete');
        }
        if (!array_key_exists('smooth_scroll', $settings)) {
            // set default
            $settings['smooth_scroll'] = $addon->getConfig('mblock_scroll');
        }
        if (!array_key_exists('delete_confirm', $settings)) {
            if ($addon->getConfig('mblock_delete_confirm')) {
                // set default
                $settings['delete_confirm'] = rex_i18n::msg('mblock_delete_confirm');
            }
        } else {
            if ($settings['delete_confirm'] === 1)
                $settings['delete_confirm'] = rex_i18n::msg('mblock_delete_confirm');

            if ($settings['delete_confirm'] === 0)
                unset($settings['delete_confirm']);
        }

                // Copy/Paste-Konfiguration hinzufügen
                if (!array_key_exists('copy_paste', $settings)) {
                    // Use addon->getConfig to get the copy_paste setting from the settings system
                    $copyPasteEnabled = $addon->getConfig('mblock_copy_paste', 1); // Default: enabled
                    $settings['copy_paste'] = (bool) $copyPasteEnabled;
                }

                // Min/Max Unterstützung
                if (!array_key_exists('min', $settings)) {
                    $min = (int)$addon->getConfig('mblock_min', 0);
                    $settings['min'] = max(0, $min);
                } else {
                    $settings['min'] = max(0, (int)$settings['min']);
                }

                if (!array_key_exists('max', $settings)) {
                    $max = $addon->getConfig('mblock_max', 0);
                    $settings['max'] = max(0, (int)$max);
                } else {
                    $settings['max'] = max(0, (int)$settings['max']);
                }
        
        // Sichere Session-basierte mblock_count mit MBlockSessionHelper
        $settings['mblock_count'] = MBlockSessionHelper::getCurrentCount();

        foreach ($settings as $key => $value) {
            if (!$value) {
                $value = 0;
            }
            $out .= " data-$key=\"$value\"";
        }

        return $out;
    }
}