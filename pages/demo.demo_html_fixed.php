<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mblock_info'), false);
$fragment->setVar('body', '<div class="alert alert-warning">
    <h4><i class="fa fa-code"></i> HTML Legacy-Beispiele</h4>
    <p>'.rex_i18n::msg('mblock_example_description_html').'</p>
    
    <div class="alert alert-info" style="margin-top: 15px;">
        <strong>🚀 EMPFEHLUNG: Verwenden Sie MForm 8!</strong><br>
        <p>HTML-Beispiele sind nur für Legacy-Support gedacht. Für neue Projekte wird <strong>MForm 8</strong> empfohlen:</p>
        <ul>
            <li>✅ <strong>Typsicherheit</strong> - Automatische Validierung</li>
            <li>✅ <strong>Wartbarkeit</strong> - Sauberer, lesbarer Code</li>
            <li>✅ <strong>Moderne Syntax</strong> - <code>use FriendsOfRedaxo\\MForm;</code></li>
            <li>✅ <strong>Weniger Fehler</strong> - Kein manuelles HTML</li>
        </ul>
        <code>$mform = MForm::factory()->addTextField("1.0.title", ["label" => "Titel"]);</code>
    </div>
</div>', false);
echo $fragment->parse('core/page/section.php');

// HTML Tipps und Limitierungen
$fragment = new rex_fragment();
$fragment->setVar('title', '💡 HTML-Tipps für Legacy-Projekte', false);
$fragment->setVar('body', '<div class="alert alert-info">
    <h5>Falls Sie HTML verwenden müssen:</h5>
    
    <h6>✅ Wichtige Punkte beachten:</h6>
    <ul>
        <li><strong>Online/Offline Toggle:</strong> Hidden Field <code>mblock_offline</code> hinzufügen</li>
        <li><strong>Media-ID Konflikte:</strong> Eindeutige IDs zwischen MBlocks verwenden (1,2,3...)</li>
        <li><strong>Copy & Paste:</strong> Funktioniert automatisch ohne Konfiguration</li>
        <li><strong>XSS-Schutz:</strong> Immer <code>rex_escape()</code> in der Ausgabe verwenden</li>
    </ul>
    
    <h6>📋 HTML Template:</h6>
    <pre><code>\$form = &lt;&lt;&lt;EOT
&lt;fieldset class="form-horizontal"&gt;
    &lt;legend&gt;Titel&lt;/legend&gt;
    &lt;input type="text" name="REX_INPUT_VALUE[\$id][0][title]" class="form-control"&gt;
    &lt;!-- Hidden field für Online/Offline Toggle --&gt;
    &lt;input type="hidden" name="REX_INPUT_VALUE[\$id][0][mblock_offline]" value="0"&gt;
&lt;/fieldset&gt;
EOT;

echo MBlock::show(\$id, \$form);</code></pre>
</div>', false);
echo $fragment->parse('core/page/section.php');

// parse demo fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mblock_demo_html') . ' - Legacy-Beispiele', false);
$fragment->setVar('body', MBlockPageHelper::exchangeExamples('html'), false);
echo $fragment->parse('core/page/section.php');
