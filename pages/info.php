<?php
/**
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */

if (rex_addon::isInstalled('mform') !== true) {
    $content = '
        <h3>JBlock</h3>
        <p>Dieses Addon ermöglicht es Nested-Slices zu erstellen.</p>
    ';
} else {
    $headline = '<h3>'. rex_i18n::msg('jblock_help_subheadline_1') .'</h3>';
    $content = '
        <p>'.  rex_i18n::msg('jblock_help_infotext_1') .'</p>
        <p>'.  rex_i18n::msg('jblock_help_infotext_2') .'</p>
        <a href="https://github.com/FriendsOfREDAXO/jblock/" target="_blank">'. rex_i18n::msg('jblock_github') .'</a>
    ';
}
