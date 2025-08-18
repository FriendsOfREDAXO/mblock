<?php
/**
 * MBlock 4.0 Overview - Neue Features und Verbesserungen
 */

$fragment = new rex_fragment();
$fragment->setVar('title', 'MBlock 4.0 - Was ist neu?', false);

$content = '
<div class="mblock-overview-v4">
    <div class="alert alert-success">
        <h3><i class="rex-icon fa-rocket"></i> MBlock 4.0 - Komplette Modernisierung</h3>
        <p>Eine umfassende Überarbeitung des beliebten MBlock-Addons mit modernen Features, verbesserter Performance und erweiterten APIs für Entwickler.</p>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="rex-icon fa-magic"></i> Hauptfeatures</h3>
                </div>
                <div class="panel-body">
                    <h4>Bloecks Integration</h4>
                    <ul>
                        <li>Moderne <code>bloecks ^5.2.0</code> Abhängigkeit</li>
                        <li>Aktuelles Sortable.js für bessere Performance</li>
                        <li>Moderne Drag & Drop Funktionalität</li>
                        <li>Entfernung veralteter jQuery UI Fallbacks</li>
                    </ul>

                    <h4>Erweiterte Copy & Paste Funktionalität</h4>
                    <ul>
                        <li>Session/Local Storage Zwischenablage</li>
                        <li>Modultyp-Validierung verhindert Cross-Module-Paste</li>
                        <li>Komplexe Formulardaten-Erhaltung</li>
                        <li>Visuelles Feedback mit Copy-Status-Indikatoren</li>
                        <li>Automatische Toolbar-Sichtbarkeit</li>
                    </ul>

                    <h4>Online/Offline Toggle</h4>
                    <ul>
                        <li>Automatische <code>mblock_offline</code> Feld-Erkennung</li>
                        <li>Farbkodierte UI (grün=online, rot=offline)</li>
                        <li>Intelligente Feld-Erkennung ohne Hardcoding</li>
                        <li>Nahtlose Template-Integration</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="rex-icon fa-cogs"></i> Technische Verbesserungen</h3>
                </div>
                <div class="panel-body">
                    <h4>JavaScript-Komponenten</h4>
                    <ul>
                        <li>Bootstrap Selectpicker Reinitialisierung nach Copy/Paste</li>
                        <li>Verschachtelte bootstrap-select Wrapper-Behandlung</li>
                        <li>Intelligente Feld-Zuordnung für REDAXO</li>
                        <li>Optimierte rex:ready Events</li>
                    </ul>

                    <h4>Entwickler-API</h4>
                    <ul>
                        <li>Neue <code>MBlock::getDataArray()</code> Methode</li>
                        <li><code>getOnlineDataArray()</code> Helper</li>
                        <li>Filter & Sortier-Methoden</li>
                        <li>Schema.org JSON-LD Generierung</li>
                        <li>Rückwärtskompatible API</li>
                    </ul>

                    <h4>Template-System</h4>
                    <ul>
                        <li>Templates jetzt direkt im Addon</li>
                        <li>Custom Templates weiterhin in <code>data/</code></li>
                        <li>Automatische Template-Priorität</li>
                        <li>Mehrsprachige Template-Unterstützung</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="rex-icon fa-code"></i> Neue Frontend-API Methoden</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6">
                    <h4>Filter & Sortierung</h4>
                    <pre><code class="language-php">// Items nach Feldwert filtern
$news = MBlock::filterByField($items, \'category\', \'news\');

// Items sortieren 
$sorted = MBlock::sortByField($items, \'date\', \'desc\');

// Items gruppieren
$grouped = MBlock::groupByField($items, \'year\');

// Pagination
$page = MBlock::limitItems($items, 10, 0);</code></pre>
                </div>
                <div class="col-sm-6">
                    <h4>SEO & Schema.org</h4>
                    <pre><code class="language-php">// JSON-LD Schema generieren
$schema = MBlock::generateSchema($items, \'Article\');

// Product Schema
$products = MBlock::generateSchema(
    $items, 
    \'Product\',
    [\'name\' => [\'product_title\']]
);

// Event Schema  
$events = MBlock::generateSchema($items, \'Event\');</code></pre>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="rex-icon fa-check"></i> Systemanforderungen</h3>
                </div>
                <div class="panel-body">
                    <ul>
                        <li><strong>REDAXO:</strong> 5.18+</li>
                        <li><strong>bloecks:</strong> ^5.2.0</li>
                        <li><strong>MForm:</strong> 8+ (empfohlen)</li>
                        <li><strong>PHP:</strong> 8.1+</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-sm-4">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="rex-icon fa-refresh"></i> Migration</h3>
                </div>
                <div class="panel-body">
                    <p><strong>Vollständig rückwärtskompatibel!</strong></p>
                    <ul>
                        <li>Bestehende MBlocks funktionieren weiterhin</li>
                        <li>Neue Features sind opt-in</li>
                        <li>Templates automatisch migriert</li>
                        <li>Legacy-API weiterhin unterstützt</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-sm-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="rex-icon fa-bug"></i> Bugfixes</h3>
                </div>
                <div class="panel-body">
                    <ul>
                        <li>Bootstrap Selectpicker nach Copy/Paste</li>
                        <li>Verschachtelte bootstrap-select Wrapper</li>
                        <li>Übermäßige rex:ready Events</li>
                        <li>REDAXO Feld-Namen-Matching</li>
                        <li>Template-Pfad-Prioritäten</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="rex-icon fa-lightbulb-o"></i> Praktische Beispiele</h3>
        </div>
        <div class="panel-body">
            <h4>News-System mit neuer API</h4>
            <pre><code class="language-php">// Alle Online-Items laden
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Nach Kategorie filtern
$news = MBlock::filterByField($items, \'category\', \'news\');

// Nach Datum sortieren (neueste zuerst)
$latest = MBlock::sortByField($news, \'date\', \'desc\');

// Nur erste 5 für Teaser
$teaser = MBlock::limitItems($latest, 5);

// Schema.org für SEO
echo MBlock::generateSchema($teaser, \'Article\');

// Archiv nach Jahren gruppieren
$archive = MBlock::groupByField($news, \'year\');</code></pre>

            <h4>Copy & Paste Workflow</h4>
            <ol>
                <li>Element in MBlock A kopieren</li>
                <li>Zu MBlock B wechseln (anderes Modul/Tab)</li>
                <li>Element einfügen - automatische Modultyp-Prüfung</li>
                <li>Komplexe Formularelemente bleiben erhalten</li>
                <li>Bootstrap Components werden neu initialisiert</li>
            </ol>

            <h4>Online/Offline Content-Management</h4>
            <pre><code class="language-php">// Backend: Alle Items anzeigen
$allItems = MBlock::getDataArray("REX_VALUE[1]");

// Frontend: Nur Online-Items
$publicItems = MBlock::getOnlineDataArray("REX_VALUE[1]");

// Preview: Nur Offline-Items  
$draftItems = MBlock::getOfflineDataArray("REX_VALUE[1]");</code></pre>
        </div>
    </div>

    <div class="alert alert-info">
        <h4><i class="rex-icon fa-graduation-cap"></i> Weitere Informationen</h4>
        <ul class="list-inline">
            <li><a href="index.php?page=mblock/api" class="btn btn-primary btn-sm">API-Dokumentation</a></li>
            <li><a href="index.php?page=mblock/help" class="btn btn-info btn-sm">README</a></li>
            <li><a href="index.php?page=mblock/demo" class="btn btn-success btn-sm">Demos & Beispiele</a></li>
            <li><a href="index.php?page=mblock/config" class="btn btn-warning btn-sm">Konfiguration</a></li>
        </ul>
    </div>
</div>

<style>
.mblock-overview-v4 h4 {
    color: #2c5aa0;
    margin-top: 15px;
    margin-bottom: 10px;
}

.mblock-overview-v4 .panel-title i {
    margin-right: 8px;
}

.mblock-overview-v4 pre {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    font-size: 12px;
    max-height: 300px;
    overflow-y: auto;
}

.mblock-overview-v4 .alert-success h3 {
    margin-top: 0;
    color: #3c763d;
}

.mblock-overview-v4 .alert-success h3 i {
    margin-right: 10px;
}

.mblock-overview-v4 .alert-info h4 {
    margin-top: 0;
    color: #31708f;
}

.mblock-overview-v4 .list-inline li {
    margin-right: 10px;
}
</style>
';

$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');