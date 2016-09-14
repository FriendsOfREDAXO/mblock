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
        $out = '';
        if (!array_key_exists('input_delete', $settings)) {
            // set default
            $settings['input_delete'] = rex_addon::get('mblock')->getConfig('mblock_delete');
        }
        if (!array_key_exists('smooth_scroll', $settings)) {
            // set default
            $settings['smooth_scroll'] = rex_addon::get('mblock')->getConfig('mblock_scroll');
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