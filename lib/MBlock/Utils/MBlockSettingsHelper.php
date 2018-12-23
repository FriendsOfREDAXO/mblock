<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MBlock\Utils;


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
        if (!array_key_exists('smooth-scroll', $settings)) {
            // set default
            $settings['smooth-scroll'] = $addon->getConfig('mblock_scroll');
        }
        if (!array_key_exists('delete-confirm', $settings)) {
            if ($addon->getConfig('mblock_delete_confirm')) {
                // set default
                $settings['delete-confirm'] = rex_i18n::msg('mblock_delete_confirm');
            }
        } else {
            if ($settings['delete-confirm'] === 1)
                $settings['delete-confirm'] = rex_i18n::msg('mblock_delete_confirm');

            if ($settings['delete-confirm'] === 0)
                unset($settings['delete-confirm']);
        }

        $settings['mblock-instance'] = rex_session('mblock_count');

        foreach ($settings as $key => $value) {
            if (!$value) {
                $value = 0;
            }
            $out .= " data-$key=\"$value\"";
        }

        return $out;
    }
}