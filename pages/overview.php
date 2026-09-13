<?php
/**
 * MBlock Overview
 */

$mformAddon = rex_addon::get('mform');
$mformAvailable = $mformAddon->isAvailable();
$mform9 = $mformAvailable && version_compare($mformAddon->getVersion(), '9.0.0-beta1', '>=');

$fragment = new rex_fragment();
$fragment->setVar('title', 'MBlock – Übersicht', false);

ob_start();
?>
<div class="mblock-overview">

    <div class="alert alert-info">
        <strong>Mai 2026 – Maintenance-Status:</strong>
        MBlock (4.6+) wird weiterhin gepflegt, erhält aber keine neuen Features mehr.
        Fehlerbehebungen und wichtige Stabilitaetskorrekturen werden weiterhin umgesetzt.
    </div>

    <?php if ($mform9): ?>
    <div class="alert alert-success">
        <strong><i class="rex-icon fa-check-circle"></i> MForm <?= rex_escape($mformAddon->getVersion()) ?> erkannt.</strong>
        Die MForm-9-spezifischen Hinweise auf dieser Seite sind für deine Installation relevant.
    </div>
    <?php elseif ($mformAvailable): ?>
    <div class="alert alert-warning">
        <strong><i class="rex-icon fa-warning"></i> MForm <?= rex_escape($mformAddon->getVersion()) ?> erkannt.</strong>
        Einige Hinweise auf dieser Seite beziehen sich auf MForm &gt;= 9.0.0-beta1 und sind für deine Version noch nicht relevant.
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="rex-icon fa-magic"></i> Features</h3>
                </div>
                <div class="panel-body">
                    <h4>Inhaltsblöcke</h4>
                    <ul>
                        <li>Beliebig viele wiederholbare Datenblöcke pro Modul</li>
                        <li>Drag &amp; Drop Sortierung</li>
                        <li>Min/Max-Anzahl definierbar</li>
                        <li>Copy &amp; Paste von Blöcken (inkl. Modultyp-Validierung)</li>
                        <li>Online/Offline Toggle (<code>mblock_offline</code>-Feld)</li>
                    </ul>

                    <h4>Template-System</h4>
                    <ul>
                        <li>Standard-Templates direkt im AddOn</li>
                        <li>Custom Templates in <code>data/addons/mblock/templates/</code></li>
                        <li>Automatische Template-Priorität</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="rex-icon fa-cogs"></i> Frontend-API</h3>
                </div>
                <div class="panel-body">
                    <pre><code class="language-php">// Online-Items laden
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Filtern, sortieren, gruppieren
$news    = MBlock::filterByField($items, 'cat', 'news');
$sorted  = MBlock::sortByField($items, 'date', 'desc');
$grouped = MBlock::groupByField($items, 'year');
$paged   = MBlock::limitItems($items, 10, 0);

// Schema.org JSON-LD
echo MBlock::generateSchema($items, 'Article');</code></pre>
                </div>
            </div>
        </div>
    </div>

    <?php if ($mform9): ?>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="rex-icon fa-info-circle"></i> MForm 9 – Hinweise für MBlock-Module</h3>
        </div>
        <div class="panel-body">
            <h4>CustomLink &amp; Media in MBlock-Modulen</h4>
            <p>MForm 9 verwendet standardmäßig moderne Link-Widgets. In klassischen MBlock-Modulen (Modul-Input)
               muss <code>useCustomLinkForClassicWidgets(true)</code> gesetzt werden, damit die Link- und Media-Felder
               korrekt im MBlock-Kontext funktionieren:</p>
            <pre><code class="language-php">$mform = new MForm();
MForm::useCustomLinkForClassicWidgets(true); // Pflicht in MBlock-Modulen mit MForm 9

$mform->addMediaField(1, ['label' => 'Bild']);
$mform->addLinkField(2, ['label' => 'Link']);

echo MBlock::show(1, $mform->show());</code></pre>

            <h4>Neue Feldtypen (MForm 9)</h4>
            <ul>
                <li><code>addCustomLinkMultipleField()</code> – mehrere Links als JSON-Array</li>
                <li><code>addConditionalFieldsetArea()</code> – regelbasierte Feldanzeige, funktioniert in MBlock</li>
                <li><code>addToggleField()</code> – Online/Offline Toggle; <code>MBlock::decode()</code> beim Auslesen verwenden</li>
            </ul>

            <h4><code>decode()</code> beim Toggle</h4>
            <p>Wird ein Toggle-Feld (<code>__disabled</code>) verwendet, muss der Wert im Frontend über
               <code>MBlock::decode()</code> entpackt werden – <code>json_decode()</code> allein reicht dann nicht:</p>
            <pre><code class="language-php">$items = MBlock::decode(rex_var::toArray("REX_VALUE[1]"));</code></pre>
        </div>
    </div>
    <?php else: ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="rex-icon fa-info-circle"></i> MForm-Integration</h3>
        </div>
        <div class="panel-body">
            <p>MBlock arbeitet mit MForm zusammen. Alle MForm-Felder können innerhalb von MBlock-Modulen
               verwendet werden. Mit <strong>MForm &gt;= 9.0.0-beta1</strong> sind zusätzliche Hinweise zur
               Konfiguration zu beachten (wird angezeigt, sobald MForm 9 installiert ist).</p>
            <pre><code class="language-php">$mform = new MForm();
$mform->addTextField(1, ['label' => 'Titel']);
$mform->addMediaField(2, ['label' => 'Bild']);
echo MBlock::show(1, $mform->show());</code></pre>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="rex-icon fa-check"></i> Systemanforderungen</h3>
                </div>
                <div class="panel-body">
                    <ul>
                        <li><strong>REDAXO:</strong> 5.18+</li>
                        <li><strong>PHP:</strong> 8.1+</li>
                        <li><strong>MForm:</strong> 8+ (empfohlen), 9+ (für neue Projekte)</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="rex-icon fa-refresh"></i> Rückwärtskompatibilität</h3>
                </div>
                <div class="panel-body">
                    <ul>
                        <li>Bestehende MBlock-Module funktionieren ohne Änderungen</li>
                        <li>Neue Features sind opt-in</li>
                        <li>Legacy-API weiterhin unterstützt</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-default">
        <ul class="list-inline" style="margin-bottom:0">
            <li><a href="index.php?page=mblock/api" class="btn btn-default btn-sm">API-Dokumentation</a></li>
            <li><a href="index.php?page=mblock/help" class="btn btn-default btn-sm">README</a></li>
            <li><a href="index.php?page=mblock/demo" class="btn btn-default btn-sm">Demos &amp; Beispiele</a></li>
            <li><a href="index.php?page=mblock/settings" class="btn btn-default btn-sm">Konfiguration</a></li>
        </ul>
    </div>

</div>
<?php
$content = ob_get_clean();

$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');