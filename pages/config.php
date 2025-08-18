<?php
/**
 * MBlock Konfiguration
 */

// Titel der Seite anzeigen
echo rex_view::title(rex_i18n::msg('mblock_title') . ': ' . rex_i18n::msg('mblock_configuration'));

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
<p>MBlock unterstützt individuelle Themes. Standard-Theme-Dateien befinden sich in:</p>
<ul>
<li><code>data/templates/default_theme/</code></li>
<li><code>templates/default_theme/</code></li>
</ul>
<p>Eigene Theme-Dateien können in <code>data/templates/</code> abgelegt werden.</p>
', false);

echo $fragment->parse('core/page/section.php');
