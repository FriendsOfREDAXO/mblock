<?php
/**
 * MBlock 4.0 - Best Practices
 * Professionelle Tipps und Empfehlungen
 */

$fragment = new rex_fragment();
$fragment->setVar('title', 'MBlock 4.0 Best Practices');

$content = '
<div class="mblock-best-practices">
    
    <div class="alert alert-success">
        <h4><i class="fa fa-star"></i> MBlock 4.0 Best Practices</h4>
        <p>Professionelle Tipps für die optimale Nutzung von MBlock in REDAXO-Projekten.</p>
    </div>
    
    <div class="row">
        
        <!-- Media-ID Management -->
        <div class="col-md-12">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-exclamation-triangle"></i> #1 Media-ID Konflikte vermeiden (KRITISCH!)</h3>
                </div>
                <div class="panel-body">
                    <div class="alert alert-danger">
                        <strong>Häufigstes Problem:</strong> "medialist im 2. block bleibt leer" 
                        → Ursache: <strong>Gleiche Media-IDs in verschiedenen MBlocks!</strong>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Falsch - Konflikte vorprogrammiert:</h5>
                            <pre><code class="php">// MBlock 1: Team-Member
$mform1->addMediaField("2", ["label" => "Profilbild"]);

// MBlock 2: News-Cards  
$mform2->addMediaField("2", ["label" => "News-Bild"]); // ← KONFLIKT!

// MBlock 3: Galerie
$mform3->addMedialistField("2", ["label" => "Bilder"]); 
$mform3->addMediaField("2", ["label" => "Titelbild"]); // ← KONFLIKT!</code></pre>
                        </div>
                        <div class="col-md-6">
                            <h5>Richtig - Eindeutige Media-IDs (1-10):</h5>
                            <pre><code class="php">// MBlock 1: Team-Member (Media-IDs 1,2)
$mform1->addMediaField("1", ["label" => "Profilbild"]);

// MBlock 2: News-Cards (Media-IDs 3,4)
$mform2->addMediaField("3", ["label" => "News-Bild"]);

// MBlock 3: Galerie (Media-IDs 5,6)
$mform3->addMedialistField("5", ["label" => "Bilder"]);
$mform3->addMediaField("6", ["label" => "Titelbild"]);</code></pre>
                        </div>
                    </div>
                    
                    <div class="alert alert-success">
                        <h5><i class="fa fa-lightbulb-o"></i> Empfohlenes Media-ID Schema (1-10 verfügbar):</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>MBlock 1:</strong> Media-IDs 1, 2<br>
                                <strong>MBlock 2:</strong> Media-IDs 3, 4<br>
                                <strong>MBlock 3:</strong> Media-IDs 5, 6
                            </div>
                            <div class="col-md-4">
                                <strong>MBlock 4:</strong> Media-IDs 7, 8<br>
                                <strong>MBlock 5:</strong> Media-IDs 9, 10<br>
                                <em>Maximal 5 MBlocks mit je 2 Media-Feldern</em>
                            </div>
                            <div class="col-md-4">
                                <strong>Warum nur 1-10?</strong><br>
                                - REDAXO Media-System Limit<br>
                                - REX_MEDIA[1] bis REX_MEDIA[10]<br>
                                - Sorgfältige Planung nötig!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <div class="row">
        
        <!-- Frontend Best Practices -->
        <div class="col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-eye"></i> #2 Frontend-Ausgabe optimieren</h3>
                </div>
                <div class="panel-body">
                    
                    <h5>🚀 MBlock 4.0 - Moderne Datenabfrage:</h5>
                    <pre><code class="php">// EMPFOHLEN: Nur Online-Items laden
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");

// LEGACY: Manuelle Filterung
$data = rex_var::toArray("REX_VALUE[1]");
$items = array_filter($data, function($item) {
    return $item["mblock_item_online"] == "1";
});</code></pre>
                    
                    <h5>🔒 Sichere Datenausgabe:</h5>
                    <pre><code class="php">foreach ($items as $item) {
    // IMMER escapen
    $title = rex_escape($item["title"] ?? "");
    $text = rex_escape($item["text"] ?? "");
    
    // Media-Existenz prüfen
    $mediaId = $item["REX_MEDIA_12"] ?? "";
    if ($mediaId && ($media = rex_media::get($mediaId))) {
        $imageUrl = rex_media_manager::getUrl(
            "rex_media_medium", 
            $media->getFileName()
        );
    }
}</code></pre>
                    
                    <h5>⚡ Performance-Optimierung:</h5>
                    <pre><code class="php">// 1. Filtern (reduziert Datenmenge)
$activeItems = MBlock::filterByField($items, "status", "active");

// 2. Sortieren (auf gefilterte Daten)
$sortedItems = MBlock::sortByField($activeItems, "date", "DESC", "date");

// 3. Limitieren (nur was gebraucht wird)  
$topItems = MBlock::limitItems($sortedItems, 5);

// 4. Ausgabe (minimale Schleife)
foreach ($topItems as $item) {
    // Ausgabe...
}</code></pre>
                </div>
            </div>
        </div>
        
        <!-- Backend Best Practices -->
        <div class="col-md-6">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-cogs"></i> #3 Backend-Konfiguration</h3>
                </div>
                <div class="panel-body">
                    
                    <h5>🎛️ MBlock-Optionen optimal nutzen:</h5>
                    <pre><code class="php">echo MBlock::show($id, $mform->show(), [
    // Basis-Einstellungen
    "min" => 1,                    // Mindestens 1 Item
    "max" => 10,                   // Maximal 10 Items
       
    // Erweiterte Optionen
    "settings" => [
        "mediapool_token_check" => false // Token-Check für Media-IDs 1-10
    ]
]);</code></pre>
                    
                    <h5>MBlock 4.0 - Online/Offline Funktionalität:</h5>
                    <pre><code>// Online/Offline Status über hidden field steuern
\$fieldName = \$id . \'.0.mblock_offline\';
\$mform->addHiddenField(\$fieldName, \'0\');

// Im Frontend: Nur Online-Items laden  
\$items = rex_var::toArray("REX_MBLOCK[1]");
foreach ($items as $item) {
    // Skip offline items
    if (!empty($item["mblock_offline"]) && $item["mblock_offline"] == "1") {
        continue;
    }
    // ... Item ausgeben
}</code></pre>

                    <h5>📋 Copy & Paste Funktion:</h5>
                    <div class="alert alert-info">
                        <strong>💡 Copy & Paste ist automatisch aktiviert!</strong><br>
                        Keine Konfiguration nötig - Funktion ist standardmäßig verfügbar.
                    </div>
                    <pre><code class="php">                    <h5>📝 Feldnamen-Konvention:</h5>
                    <pre><code class="php">// Sprechende Feldnamen verwenden
$mform->addTextField("$id.0.title", ["label" => "Titel"]);
$mform->addTextField("$id.0.subtitle", ["label" => "Untertitel"]);
$mform->addMediaField("1", ["label" => "Hauptbild"]); // Media-ID 1-10!

// Kryptische Namen vermeiden
$mform->addTextField("$id.0.f1", ["label" => "Titel"]);
$mform->addTextField("$id.0.f2", ["label" => "Untertitel"]);</code></pre></code></pre>
                    
                    <h5>🎨 MForm Best Practices:</h5>
                    <pre><code class="php">// Fieldsets für bessere Struktur
$mform->addFieldsetArea("Basis-Informationen");
$mform->addTextField("$id.0.title", ["label" => "Titel"]);

$mform->addFieldsetArea("Media & Layout");
$mform->addMediaField("1", ["label" => "Bild", "preview" => true]); // Media-ID 1-10!

// Kategorien für Media-Felder
$mform->addMediaField("2", [
    "label" => "Bild",
    "category" => "1",  // Bilder-Kategorie
    "preview" => true
]);</code></pre>
                </div>
            </div>
        </div>
        
    </div>
    
    <div class="row">
        
        <!-- Projektorganisation -->
        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-folder-open"></i> #4 Projekt-Organisation</h3>
                </div>
                <div class="panel-body">
                    
                    <h5>📋 Media-ID Management dokumentieren:</h5>
                    <pre><code>// In Projekt-Dokumentation festhalten:

Media-ID Schema (1-10 verfügbar):
─────────────────────────────────
MBlock 1 (Team-Member):     Media-IDs 1, 2
MBlock 2 (News-Cards):      Media-IDs 3, 4
MBlock 3 (Galerie):         Media-IDs 5, 6
MBlock 4 (Produkte):        Media-IDs 7, 8
MBlock 5 (Testimonials):    Media-IDs 9, 10

REX_VALUE Zuordnung:
───────────────────
REX_VALUE[1] = Team-Member
REX_VALUE[2] = News-Cards
REX_VALUE[3] = Galerie
REX_VALUE[4] = Produkte

⚠️ WICHTIG: Maximal 5 MBlocks mit je 2 Media-Feldern möglich!</code></pre>
                    
                    <h5>📁 Template-Organisation:</h5>
                    <pre><code>redaxo/data/addons/mblock/templates/
├── project_theme/
│   ├── mblock_wrapper.ini     # Container-Template
│   ├── mblock_element.ini     # Item-Template  
│   └── theme.css             # Projekt-Styling
└── fallback_theme/
    ├── mblock_wrapper.ini     # Fallback-Container
    └── mblock_element.ini     # Fallback-Item</code></pre>
                    
                    <h5>🧪 Testing-Strategie:</h5>
                    <ul>
                        <li><strong>Media-IDs testen:</strong> Mehrere MBlocks gleichzeitig verwenden</li>
                        <li><strong>Online/Offline:</strong> Toggle-Verhalten prüfen</li>
                        <li><strong>Copy & Paste:</strong> Zwischen MBlocks testen</li>
                        <li><strong>Performance:</strong> Mit vielen Items testen (20+)</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Debugging & Troubleshooting -->
        <div class="col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-bug"></i> #5 Debugging & Troubleshooting</h3>
                </div>
                <div class="panel-body">
                    
                    <h5>🔍 Debug-Toolkit:</h5>
                    <pre><code class="php">// 1. Alle REX_VALUES prüfen
for ($i = 1; $i <= 20; $i++) {
    $data = MBlock::getDataArray("REX_VALUE[$i]");
    if (!empty($data)) {
        echo "REX_VALUE[$i]: " . count($data) . " Items";
    }
}

// 2. Media-IDs analysieren
$items = MBlock::getDataArray("REX_VALUE[1]");
foreach ($items as $index => $item) {
    foreach ($item as $key => $value) {
        if (strpos($key, "REX_MEDIA") === 0) {
            echo "Item $index: $key = $value";
        }
    }
}

// 3. Online/Offline Status checken
foreach ($items as $index => $item) {
    $status = MBlock::isOnline($item) ? "ONLINE" : "OFFLINE";
    echo "Item $index: $status";
}</code></pre>
                    
                    <h5>⚠️ Häufige Probleme & Lösungen:</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Problem</th>
                                    <th>Ursache</th>
                                    <th>Lösung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Media-Felder leer</td>
                                    <td>ID-Konflikt</td>
                                    <td>Eindeutige Media-IDs verwenden</td>
                                </tr>
                                <tr>
                                    <td>Items nicht sortierbar</td>
                                    <td>bloecks addon fehlt</td>
                                    <td>bloecks ^5.2.0 installieren</td>
                                </tr>
                                <tr>
                                    <td>Online/Offline fehlt</td>
                                    <td>Hidden field fehlt</td>
                                    <td><code>mblock_offline</code> hidden field hinzufügen</td>
                                </tr>
                                <tr>
                                    <td>Copy & Paste fehlt</td>
                                    <td>-</td>
                                    <td>Automatisch verfügbar - keine Konfiguration nötig</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <h5>🚨 Browser-Debugging:</h5>
                    <ul>
                        <li><strong>F12 → Console:</strong> JavaScript-Fehler prüfen</li>
                        <li><strong>Network-Tab:</strong> AJAX-Requests analysieren</li>
                        <li><strong>Elements-Tab:</strong> DOM-Struktur untersuchen</li>
                    </ul>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Performance & SEO -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-tachometer"></i> #6 Performance & SEO Optimierung</h3>
        </div>
        <div class="panel-body">
            
            <div class="row">
                <div class="col-md-4">
                    <h5>⚡ Performance-Tipps:</h5>
                    <ul>
                        <li><strong>Früh filtern:</strong> <code>getOnlineDataArray()</code> verwenden</li>
                        <li><strong>Media cachen:</strong> <code>rex_media::get()</code> Ergebnisse speichern</li>
                        <li><strong>Limitieren:</strong> Nicht alle Items gleichzeitig ausgeben</li>
                        <li><strong>Lazy Loading:</strong> Bilder bei Bedarf laden</li>
                    </ul>
                    
                    <pre><code class="php">// Performance-optimiert
$items = MBlock::getOnlineDataArray("REX_VALUE[1]");
$filteredItems = MBlock::filterByField($items, "category", "news");
$topNews = MBlock::limitItems($filteredItems, 5);</code></pre>
                </div>
                
                <div class="col-md-4">
                    <h5>🔍 SEO-Optimierung:</h5>
                    <ul>
                        <li><strong>Schema.org:</strong> Strukturierte Daten generieren</li>
                        <li><strong>Alt-Tags:</strong> Bei allen Bildern setzen</li>
                        <li><strong>Semantic HTML:</strong> Korrekte HTML-Struktur</li>
                        <li><strong>Meta-Daten:</strong> Aus MBlock-Daten generieren</li>
                    </ul>
                    
                    <pre><code class="php">// SEO Schema generieren
$schema = MBlock::generateSchema($items, "Person", [
    "name" => "name",
    "jobTitle" => "position", 
    "image" => "REX_MEDIA_12"
]);
echo \'&lt;script type="application/ld+json"&gt;\';
echo json_encode($schema);
echo \'&lt;/script&gt;\';</code></pre>
                </div>
                
                <div class="col-md-4">
                    <h5>📱 Responsive Design:</h5>
                    <ul>
                        <li><strong>Mobile First:</strong> Templates responsive gestalten</li>
                        <li><strong>Breakpoints:</strong> Grid-System nutzen</li>
                        <li><strong>Touch-friendly:</strong> Drag & Drop auf Mobile</li>
                        <li><strong>Lazy Images:</strong> Große Bilder optimieren</li>
                    </ul>
                    
                    <pre><code class="php">// Responsive Media Manager
$media = rex_media::get($mediaId);
if ($media) {
    echo \'&lt;picture&gt;\';
    echo \'&lt;source media="(max-width: 768px)" 
           srcset="\' . rex_media_manager::getUrl("rex_media_small", $media->getFileName()) . \'"&gt;\';
    echo \'&lt;img src="\' . rex_media_manager::getUrl("rex_media_medium", $media->getFileName()) . \'" 
           alt="\' . rex_escape($media->getTitle()) . \'" /&gt;\';
    echo \'&lt;/picture&gt;\';
}</code></pre>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Reference -->
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-rocket"></i> Quick Reference - Die wichtigsten Punkte</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <h5>🆔 Media-IDs:</h5>
                    <ul class="list-unstyled">
                        <li>MBlock 1: IDs 1, 2</li>
                        <li>MBlock 2: IDs 3, 4</li>
                        <li>MBlock 3: IDs 5, 6</li>
                        <li>Niemals gleiche IDs!</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>📝 Frontend:</h5>
                    <ul class="list-unstyled">
                        <li><code>getOnlineDataArray()</code></li>
                        <li><code>rex_escape()</code></li>
                        <li>Media-Existenz prüfen</li>
                        <li>Performance optimieren</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>⚙️ Backend:</h5>
                    <ul class="list-unstyled">
                        <li>Hidden field <code>mblock_offline</code></li>
                        <li>Copy & Paste automatisch aktiv</li>
                        <li>Min/Max Limits setzen</li>
                        <li>Sprechende Feldnamen</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>🐛 Debug:</h5>
                    <ul class="list-unstyled">
                        <li>REX_VALUES prüfen</li>
                        <li>Media-IDs analysieren</li>
                        <li>Browser-Konsole checken</li>
                        <li>Online-Status testen</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
.mblock-best-practices .panel-title i {
    margin-right: 8px;
}

.mblock-best-practices pre {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
}

.mblock-best-practices .table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
}

/* Dark Mode Support für Tabellen */
body.rex-theme-dark .mblock-best-practices .table th,
html[data-bs-theme="dark"] .mblock-best-practices .table th {
    background-color: #495057;
    color: #fff;
}

@media (prefers-color-scheme: dark) {
    body.rex-has-theme:not(.rex-theme-light) .mblock-best-practices .table th {
        background-color: #495057;
        color: #fff;
    }
}

.mblock-best-practices .alert h5 {
    margin-top: 0;
}

.mblock-best-practices .row {
    margin-bottom: 20px;
}
</style>
';

$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
