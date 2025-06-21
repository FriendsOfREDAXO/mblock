<?php
/**
 * MBlock HTML Demo - Vereinfachte Demo mit verschiedenen Feldtypen
 * Zeigt eine einzige, klare Demo mit verschiedenen HTML-Elementen und Widget-Erklärungen
 */

$content = '';

echo rex_view::title('MBlock HTML Demo - Verschiedene Feldtypen');

$fragment = new rex_fragment();
$fragment->setVar('title', 'HTML Form Felder mit MBlock', false);
$fragment->setVar('body', '
<div class="alert alert-info">
    <strong>Diese Demo zeigt:</strong>
    <ul>
        <li>Verschiedene HTML-Feldtypen (Text, Select, Textarea, Checkbox, Radio)</li>
        <li>Fake-Widgets als Platzhalter für echte REDAXO-Widgets</li>
        <li>Wie MBlock mit verschiedenen Input-Elementen funktioniert</li>
    </ul>
</div>
', false);
echo $fragment->parse('core/page/section.php');

// Demo-Werte für Vorschau
$demo_data = [
    [
        'title' => 'Erster Eintrag',
        'type' => 'artikel',
        'description' => 'Dies ist eine Beispiel-Beschreibung für den ersten Eintrag.',
        'active' => '1',
        'priority' => 'hoch',
        'media_id' => '1',
        'link_id' => '5'
    ],
    [
        'title' => 'Zweiter Eintrag',
        'type' => 'news',
        'description' => 'Eine weitere Beschreibung für den zweiten Eintrag mit mehr Text.',
        'active' => '0',
        'priority' => 'mittel',
        'media_id' => '2',
        'link_id' => '10'
    ]
];

$mblock_id = 1;
$content .= '
<form>
    <div class="form-group">
        <label>Demo MBlock mit verschiedenen Feldtypen:</label>
        
        <!-- MBlock Container -->
        <div class="mblock" data-mblock-id="' . $mblock_id . '">
            
            <!-- MBlock Items -->';

foreach ($demo_data as $index => $item) {
    $content .= '
            <div class="mblock-item" data-mblock-id="' . $mblock_id . '" data-mblock-count="' . ($index + 1) . '">
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Text Input -->
                        <div class="form-group">
                            <label>Titel:</label>
                            <input type="text" 
                                   name="mblock[' . $mblock_id . '][' . ($index + 1) . '][title]" 
                                   value="' . htmlspecialchars($item['title']) . '"
                                   class="form-control"
                                   placeholder="Titel eingeben...">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Select Dropdown -->
                        <div class="form-group">
                            <label>Typ:</label>
                            <select name="mblock[' . $mblock_id . '][' . ($index + 1) . '][type]" class="form-control">
                                <option value="">-- Typ wählen --</option>
                                <option value="artikel"' . ($item['type'] == 'artikel' ? ' selected' : '') . '>Artikel</option>
                                <option value="news"' . ($item['type'] == 'news' ? ' selected' : '') . '>News</option>
                                <option value="event"' . ($item['type'] == 'event' ? ' selected' : '') . '>Event</option>
                                <option value="galerie"' . ($item['type'] == 'galerie' ? ' selected' : '') . '>Galerie</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <!-- Textarea -->
                        <div class="form-group">
                            <label>Beschreibung:</label>
                            <textarea name="mblock[' . $mblock_id . '][' . ($index + 1) . '][description]" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Beschreibung eingeben...">' . htmlspecialchars($item['description']) . '</textarea>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <!-- Checkbox -->
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" 
                                           name="mblock[' . $mblock_id . '][' . ($index + 1) . '][active]" 
                                           value="1"' . ($item['active'] ? ' checked' : '') . '>
                                    Aktiv
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Radio Buttons -->
                        <div class="form-group">
                            <label>Priorität:</label><br>
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" 
                                           name="mblock[' . $mblock_id . '][' . ($index + 1) . '][priority]" 
                                           value="hoch"' . ($item['priority'] == 'hoch' ? ' checked' : '') . '>
                                    Hoch
                                </label>
                            </div>
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" 
                                           name="mblock[' . $mblock_id . '][' . ($index + 1) . '][priority]" 
                                           value="mittel"' . ($item['priority'] == 'mittel' ? ' checked' : '') . '>
                                    Mittel
                                </label>
                            </div>
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" 
                                           name="mblock[' . $mblock_id . '][' . ($index + 1) . '][priority]" 
                                           value="niedrig"' . ($item['priority'] == 'niedrig' ? ' checked' : '') . '>
                                    Niedrig
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Hidden Input für Sortierung -->
                        <input type="hidden" 
                               name="mblock[' . $mblock_id . '][' . ($index + 1) . '][sort]" 
                               value="' . ($index + 1) . '">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Fake Media Widget -->
                        <div class="form-group">
                            <label>Bild (Fake Media Widget):</label>
                            <div class="input-group">
                                <input type="text" 
                                       name="mblock[' . $mblock_id . '][' . ($index + 1) . '][media_id]" 
                                       value="' . $item['media_id'] . '"
                                       class="form-control"
                                       placeholder="Media ID"
                                       readonly>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" disabled>
                                        <i class="fa fa-picture-o"></i> Media wählen
                                    </button>
                                </span>
                            </div>
                            <small class="help-block">
                                <strong>Hinweis:</strong> In echter Verwendung würde hier ein 
                                <code>REX_MEDIA_WIDGET</code> stehen.
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Fake Link Widget -->
                        <div class="form-group">
                            <label>Link (Fake Link Widget):</label>
                            <div class="input-group">
                                <input type="text" 
                                       name="mblock[' . $mblock_id . '][' . ($index + 1) . '][link_id]" 
                                       value="' . $item['link_id'] . '"
                                       class="form-control"
                                       placeholder="Artikel ID"
                                       readonly>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" disabled>
                                        <i class="fa fa-link"></i> Link wählen
                                    </button>
                                </span>
                            </div>
                            <small class="help-block">
                                <strong>Hinweis:</strong> In echter Verwendung würde hier ein 
                                <code>REX_LINK_WIDGET</code> stehen.
                            </small>
                        </div>
                    </div>
                </div>
                
            </div>';
}

$content .= '
            
            <!-- MBlock Template (versteckt) -->
            <div class="mblock-template" data-mblock-id="' . $mblock_id . '" style="display: none;">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Titel:</label>
                            <input type="text" 
                                   name="mblock[' . $mblock_id . '][%count%][title]" 
                                   value=""
                                   class="form-control"
                                   placeholder="Titel eingeben...">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Typ:</label>
                            <select name="mblock[' . $mblock_id . '][%count%][type]" class="form-control">
                                <option value="">-- Typ wählen --</option>
                                <option value="artikel">Artikel</option>
                                <option value="news">News</option>
                                <option value="event">Event</option>
                                <option value="galerie">Galerie</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Beschreibung:</label>
                            <textarea name="mblock[' . $mblock_id . '][%count%][description]" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Beschreibung eingeben..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" 
                                           name="mblock[' . $mblock_id . '][%count%][active]" 
                                           value="1">
                                    Aktiv
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Priorität:</label><br>
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" 
                                           name="mblock[' . $mblock_id . '][%count%][priority]" 
                                           value="hoch">
                                    Hoch
                                </label>
                            </div>
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" 
                                           name="mblock[' . $mblock_id . '][%count%][priority]" 
                                           value="mittel">
                                    Mittel
                                </label>
                            </div>
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" 
                                           name="mblock[' . $mblock_id . '][%count%][priority]" 
                                           value="niedrig">
                                    Niedrig
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <input type="hidden" 
                               name="mblock[' . $mblock_id . '][%count%][sort]" 
                               value="%count%">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Bild (Fake Media Widget):</label>
                            <div class="input-group">
                                <input type="text" 
                                       name="mblock[' . $mblock_id . '][%count%][media_id]" 
                                       value=""
                                       class="form-control"
                                       placeholder="Media ID"
                                       readonly>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" disabled>
                                        <i class="fa fa-picture-o"></i> Media wählen
                                    </button>
                                </span>
                            </div>
                            <small class="help-block">
                                <strong>Hinweis:</strong> In echter Verwendung würde hier ein 
                                <code>REX_MEDIA_WIDGET</code> stehen.
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Link (Fake Link Widget):</label>
                            <div class="input-group">
                                <input type="text" 
                                       name="mblock[' . $mblock_id . '][%count%][link_id]" 
                                       value=""
                                       class="form-control"
                                       placeholder="Artikel ID"
                                       readonly>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" disabled>
                                        <i class="fa fa-link"></i> Link wählen
                                    </button>
                                </span>
                            </div>
                            <small class="help-block">
                                <strong>Hinweis:</strong> In echter Verwendung würde hier ein 
                                <code>REX_LINK_WIDGET</code> stehen.
                            </small>
                        </div>
                    </div>
                </div>
                
            </div>
            
        </div>
        
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Speichern</button>
        <button type="button" class="btn btn-default">Abbrechen</button>
    </div>
</form>';

$fragment = new rex_fragment();
$fragment->setVar('title', 'Live Demo', false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

// Code-Beispiele
$code_examples = '
<h4>Verwendung in REDAXO Templates/Modulen:</h4>

<h5>1. Template-Code für echte Widgets:</h5>
<pre><code>&lt;?php
// Media Widget
echo \'&lt;div class="form-group"&gt;\';
echo \'&lt;label&gt;Bild:&lt;/label&gt;\';
echo REX_MEDIA_WIDGET::widget("mblock[1][%count%][media_id]", $media_id);
echo \'&lt;/div&gt;\';

// Link Widget  
echo \'&lt;div class="form-group"&gt;\';
echo \'&lt;label&gt;Link:&lt;/label&gt;\';
echo REX_LINK_WIDGET::widget("mblock[1][%count%][link_id]", $link_id);
echo \'&lt;/div&gt;\';
?&gt;</code></pre>

<h5>2. Auslesen der MBlock-Daten:</h5>
<pre><code>&lt;?php
// MBlock-Daten aus der Datenbank laden
$mblock_data = rex_var::toArray("REX_VALUE[1]");

// Durch alle Einträge iterieren
foreach ($mblock_data as $item) {
    echo \'&lt;h3&gt;\' . htmlspecialchars($item[\'title\']) . \'&lt;/h3&gt;\';
    echo \'&lt;p&gt;Typ: \' . htmlspecialchars($item[\'type\']) . \'&lt;/p&gt;\';
    echo \'&lt;p&gt;\' . nl2br(htmlspecialchars($item[\'description\'])) . \'&lt;/p&gt;\';
    
    if ($item[\'active\']) {
        echo \'&lt;span class="label label-success"&gt;Aktiv&lt;/span&gt;\';
    }
    
    echo \'&lt;p&gt;Priorität: \' . htmlspecialchars($item[\'priority\']) . \'&lt;/p&gt;\';
    
    // Media ausgeben
    if ($item[\'media_id\']) {
        $media = rex_media::get($item[\'media_id\']);
        if ($media) {
            echo \'&lt;img src="\' . $media-&gt;getUrl() . \'" alt=""&gt;\';
        }
    }
    
    // Link ausgeben
    if ($item[\'link_id\']) {
        $article = rex_article::get($item[\'link_id\']);
        if ($article) {
            echo \'&lt;a href="\' . $article-&gt;getUrl() . \'"&gt;Weiterlesen&lt;/a&gt;\';
        }
    }
}
?&gt;</code></pre>

<h5>3. Template-Integration:</h5>
<pre><code>// In Ihrem Modul-Input:
&lt;div class="mblock" data-mblock-id="1"&gt;
    &lt;!-- Ihre MBlock-Items hier --&gt;
&lt;/div&gt;

// Wichtige Attribute:
// data-mblock-id="1"     - Eindeutige ID für den MBlock
// data-mblock-count="%count%" - Platzhalter für die Zählnummer
// name="mblock[1][%count%][field]" - Namenskonvention für Felder</code></pre>

<div class="alert alert-warning">
    <strong>Wichtige Hinweise:</strong>
    <ul>
        <li>In dieser Demo sind die Widgets deaktiviert - sie dienen nur zur Veranschaulichung</li>
        <li>Echte REDAXO-Widgets funktionieren nur im Backend-Kontext</li>
        <li>Die <code>%count%</code> Platzhalter werden automatisch durch MBlock ersetzt</li>
        <li>MBlock verwaltet die Sortierung und das Hinzufügen/Entfernen automatisch</li>
    </ul>
</div>';

$fragment = new rex_fragment();
$fragment->setVar('title', 'Code-Beispiele & Integration', false);
$fragment->setVar('body', $code_examples, false);
echo $fragment->parse('core/page/section.php');
