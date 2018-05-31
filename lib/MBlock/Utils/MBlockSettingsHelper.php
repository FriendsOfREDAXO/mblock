<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

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
        if (isset($_SESSION['mblock_count'])) {
            $settings['mblock_count'] = $_SESSION['mblock_count'];
        } else {
            $settings['mblock_count'] = 0;
        }

        foreach ($settings as $key => $value) {
            if (!$value) {
                $value = 0;
            }
            $out .= " data-$key=\"$value\"";
        }

        return $out;
    }
}