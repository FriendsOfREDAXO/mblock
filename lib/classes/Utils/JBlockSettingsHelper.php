<?php

/**
 * Author Joachim Doerr
 * Date: 18.08.16
 * Time: 07:12
 */
class JBlockSettingsHelper
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
            $settings['input_delete'] = rex_addon::get('jblock')->getConfig('jblock_delete');
        }

        echo '<pre>';
        print_r($settings);
        echo '</pre>';


        foreach ($settings as $key => $value) {
            if (!$value) {
                $value = 'false';
            }
            $out .= " data-$key=\"$value\"";
        }

        return $out;
    }
}