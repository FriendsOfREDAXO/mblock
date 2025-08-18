<?php
/**
 * MBlock Konfiguration
 */

// Titel der Seite anzeigen

// Konfigurationsformular
$form = rex_config_form::factory('mblock');

$field = $form->addSelectField('theme', array(
    'default_theme' => rex_i18n::msg('mblock_theme_default'),
    'custom' => rex_i18n::msg('mblock_theme_custom')
));
$field->setLabel(rex_i18n::msg('mblock_theme'));

$field = $form->addSelectField('smooth_scroll', array(
    '0' => rex_i18n::msg('mblock_disabled'),
    '1' => rex_i18n::msg('mblock_enabled')
));
$field->setLabel(rex_i18n::msg('mblock_smooth_scroll'));

$field = $form->addSelectField('nested_support', array(
    '0' => rex_i18n::msg('mblock_disabled'),
    '1' => rex_i18n::msg('mblock_enabled')
));
$field->setLabel(rex_i18n::msg('mblock_nested_support'));

$field = $form->addSelectField('copy_paste', array(
    '0' => rex_i18n::msg('mblock_disabled'),
    '1' => rex_i18n::msg('mblock_enabled')
));
$field->setLabel(rex_i18n::msg('mblock_copy_paste'));

$field = $form->addSelectField('offline_toggle', array(
    '0' => rex_i18n::msg('mblock_disabled'),
    '1' => rex_i18n::msg('mblock_enabled')
));
$field->setLabel(rex_i18n::msg('mblock_offline_toggle'));

$form->setApplyUrl(rex_url::currentBackendPage());

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('mblock_configuration'), false);
$fragment->setVar('body', $form->get(), false);

echo $fragment->parse('core/page/section.php');

// Theme-Einstellungen Info
$fragment = new rex_fragment();
$fragment->setVar('title', 'Theme-Einstellungen', false);
$fragment->setVar('body', '
<p>MBlock unterstützt individuelle Themes mit automatischer Prioritätsverwaltung:</p>
<h4>Template-Priorität (in dieser Reihenfolge):</h4>
<ol>
<li><code>redaxo/data/addons/mblock/templates/custom_theme/</code> <strong>(Custom Templates - höchste Priorität)</strong></li>
<li><code>redaxo/src/addons/mblock/templates/default_theme/</code> <em>(Addon Default Templates)</em></li>
</ol>
<p><strong>Empfehlung:</strong> Eigene Templates immer im <code>data/</code> Ordner ablegen. Diese überschreiben automatisch die Default Templates.</p>
', false);

echo $fragment->parse('core/page/section.php');
