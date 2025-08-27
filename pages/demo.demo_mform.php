<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mblock_info'), false);
$fragment->setVar('body', '<div class="alert alert-info">
    <h4><i class="fa fa-lightbulb-o"></i> MBlock mit MForm</h4>
    <p>'.rex_i18n::msg('mblock_example_description_base').'</p>
    <p><strong>Moderne MBlock Features:</strong></p>
    <ul>
        <li><strong>Online/Offline Toggle:</strong> Items per hidden field <code>mblock_offline</code> steuern</li>
        <li><strong>Copy & Paste:</strong> Automatisch aktiv - keine Konfiguration nötig</li>
        <li><strong>Frontend API:</strong> Neue Methoden für Filterung, Sortierung und Gruppierung</li>
        <li><strong>MForm Integration:</strong> Moderne <code>use</code> Statements und Factory Pattern</li>
    </ul>
</div>', false);
echo $fragment->parse('core/page/section.php');

// parse demo fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mblock_demo_mform'), false);
$fragment->setVar('body', \FriendsOfRedaxo\MBlock\Utils\MBlockPageHelper::exchangeExamples('base'), false);
echo $fragment->parse('core/page/section.php');
