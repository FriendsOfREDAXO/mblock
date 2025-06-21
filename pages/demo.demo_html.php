<?php
/**
 * MBlock v4.0 - HTML Demo mit Input/Output Tabs
 * Demonstration der reinen HTML-Verwendung von MBlock basiert auf den Original-Examples
 */

$content = '<div class="rex-page-header">
    <h1 class="rex-page-title">MBlock v4.0 - HTML Demo</h1>
</div>';

$content .= '<div class="alert alert-info">
    <strong>HTML Demo:</strong> Diese Seite zeigt die korrekte HTML-Verwendung von MBlock basierend auf den Original-Examples. 
    Input- und Output-Code werden in separaten Tabs dargestellt.
</div>';

// ======================
// 1. EINFACHES BEISPIEL - Teammitglieder
// ======================
$content .= '<div class="panel panel-primary">
    <div class="panel-heading">
        <h4>1. Einfach - Teammitglieder (wie html1_teammember_example)</h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">';

// Echtes MBlock Beispiel basierend auf html1_teammember_example.ini
$id1 = 1;
$teamForm = '
<fieldset class="form-horizontal">
    <legend>Team member</legend>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label for="rv2_1_0_name">Name</label></div>
        <div class="col-sm-10">
            <input id="rv2_1_0_name" type="text" name="REX_INPUT_VALUE[' . $id1 . '][0][name]" value="" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Avatar</label></div>
        <div class="col-sm-10">
            REX_MEDIA[id="1" widget="1"]
        </div>
    </div>
</fieldset>';

$content .= MBlock::show($id1, $teamForm);

$content .= '</div>
            <div class="col-md-6">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#code-input-1" aria-controls="code-input-1" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-pencil"></i> Modul-Input
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#code-output-1" aria-controls="code-output-1" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-eye"></i> Modul-Output
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" style="border: 1px solid #ddd; border-top: none;">
                    <div role="tabpanel" class="tab-pane active" id="code-input-1">
                        <pre class="code-example">' . htmlentities('<?php
// Modul-Input (html1_teammember_example.ini)
$id = 1;

$form = <<<EOT
<fieldset class="form-horizontal">
    <legend>Team member</legend>
    <div class="form-group">
        <div class="col-sm-2 control-label">
            <label for="rv2_1_0_name">Name</label>
        </div>
        <div class="col-sm-10">
            <input id="rv2_1_0_name" type="text" 
                   name="REX_INPUT_VALUE[$id][0][name]" 
                   value="" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Avatar</label></div>
        <div class="col-sm-10">
            REX_MEDIA[id="1" widget="1"]
        </div>
    </div>
</fieldset>
EOT;

echo MBlock::show($id, $form);
?>') . '</pre>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="code-output-1">
                        <pre class="code-example">' . htmlentities('<?php
// Modul-Output (html1_teammember_example_output.ini)
echo \'<pre>\';
print_r(rex_var::toArray("REX_VALUE[1]"));
echo \'</pre>\';

// Oder formatierte Ausgabe:
$blocks = rex_var::toArray("REX_VALUE[1]");
foreach($blocks as $block) {
    echo \'<div class="team-member">\';
    echo \'<h3>\' . $block[\'name\'] . \'</h3>\';
    
    // Avatar anzeigen
    if(!empty($block[\'REX_MEDIA_1\'])) {
        $media = rex_media::get($block[\'REX_MEDIA_1\']);
        if($media) {
            echo \'<img src="\' . $media->getUrl() . \'" alt="">\';
        }
    }
    echo \'</div>\';
}
?>') . '</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

// ======================
// 2. LINK BEISPIEL
// ======================
$content .= '<div class="panel panel-info">
    <div class="panel-heading">
        <h4>2. Links - Einzellinks und Listen (wie html2_link_example)</h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">';

// Link MBlock Beispiel basierend auf html2_link_example.ini
$id2 = 2;
$linkForm = '
<fieldset class="form-horizontal">
    <legend>Links</legend> 
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Link</label></div>
        <div class="col-sm-10">
            REX_LINK[id="1" widget="1"]
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Link list</label></div>
        <div class="col-sm-10">
            REX_LINKLIST[id="1" widget="1"]
        </div>
    </div>
</fieldset>';

$content .= MBlock::show($id2, $linkForm);

$content .= '</div>
            <div class="col-md-6">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#code-input-2" aria-controls="code-input-2" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-pencil"></i> Modul-Input
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#code-output-2" aria-controls="code-output-2" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-eye"></i> Modul-Output
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" style="border: 1px solid #ddd; border-top: none;">
                    <div role="tabpanel" class="tab-pane active" id="code-input-2">
                        <pre class="code-example">' . htmlentities('<?php
// Modul-Input (html2_link_example.ini)
$id = 2;

$form = <<<EOT
<fieldset class="form-horizontal">
    <legend>Links</legend> 
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Link</label></div>
        <div class="col-sm-10">
            REX_LINK[id="1" widget="1"]
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Link list</label></div>
        <div class="col-sm-10">
            REX_LINKLIST[id="1" widget="1"]
        </div>
    </div>
</fieldset>
EOT;

echo MBlock::show($id, $form);
?>') . '</pre>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="code-output-2">
                        <pre class="code-example">' . htmlentities('<?php
// Modul-Output für Link-Widgets
$blocks = rex_var::toArray("REX_VALUE[2]");
foreach($blocks as $block) {
    // Einzellink
    if(!empty($block[\'REX_LINK_1\'])) {
        $article = rex_article::get($block[\'REX_LINK_1\']);
        if($article) {
            echo \'<a href="\' . $article->getUrl() . \'">\';
            echo $article->getName();
            echo \'</a><br>\';
        }
    }
    
    // Link-Liste
    if(!empty($block[\'REX_LINKLIST_1\'])) {
        $linkList = explode(\',\', $block[\'REX_LINKLIST_1\']);
        echo \'<ul>\';
        foreach($linkList as $linkId) {
            $article = rex_article::get($linkId);
            if($article) {
                echo \'<li><a href="\' . $article->getUrl() . \'">\';
                echo $article->getName();
                echo \'</a></li>\';
            }
        }
        echo \'</ul>\';
    }
}
?>') . '</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

// ======================
// 3. MEDIA BEISPIEL
// ======================
$content .= '<div class="panel panel-success">
    <div class="panel-heading">
        <h4>3. Media - Einzelbilder und Listen (wie html3_media_example)</h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">';

// Media MBlock Beispiel basierend auf html3_media_example.ini
$id3 = 3;
$mediaForm = '
<fieldset class="form-horizontal">
    <legend>Media</legend> 
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Media</label></div>
        <div class="col-sm-10">
            REX_MEDIA[id="1" widget="1"]
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Media list</label></div>
        <div class="col-sm-10">
            REX_MEDIALIST[id="1" widget="1"]
        </div>
    </div>
</fieldset>';

$content .= MBlock::show($id3, $mediaForm);

$content .= '</div>
            <div class="col-md-6">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#code-input-3" aria-controls="code-input-3" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-pencil"></i> Modul-Input
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#code-output-3" aria-controls="code-output-3" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-eye"></i> Modul-Output
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" style="border: 1px solid #ddd; border-top: none;">
                    <div role="tabpanel" class="tab-pane active" id="code-input-3">
                        <pre class="code-example">' . htmlentities('<?php
// Modul-Input (html3_media_example.ini)
$id = 3;

$form = <<<EOT
<fieldset class="form-horizontal">
    <legend>Media</legend> 
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Media</label></div>
        <div class="col-sm-10">
            REX_MEDIA[id="1" widget="1"]
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Media list</label></div>
        <div class="col-sm-10">
            REX_MEDIALIST[id="1" widget="1"]
        </div>
    </div>
</fieldset>
EOT;

echo MBlock::show($id, $form);
?>') . '</pre>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="code-output-3">
                        <pre class="code-example">' . htmlentities('<?php
// Modul-Output für Media-Widgets
$blocks = rex_var::toArray("REX_VALUE[3]");
foreach($blocks as $block) {
    // Einzelbild
    if(!empty($block[\'REX_MEDIA_1\'])) {
        $media = rex_media::get($block[\'REX_MEDIA_1\']);
        if($media) {
            echo \'<img src="\' . $media->getUrl() . \'" alt="">\';
        }
    }
    
    // Medien-Liste
    if(!empty($block[\'REX_MEDIALIST_1\'])) {
        $mediaList = explode(\',\', $block[\'REX_MEDIALIST_1\']);
        echo \'<div class="media-gallery">\';
        foreach($mediaList as $mediaFile) {
            $media = rex_media::get($mediaFile);
            if($media) {
                echo \'<img src="\' . $media->getUrl() . \'" alt="">\';
            }
        }
        echo \'</div>\';
    }
}
?>') . '</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

// ======================
// 4. DEFAULTS BEISPIEL
// ======================
$content .= '<div class="panel panel-warning">
    <div class="panel-heading">
        <h4>4. Erweitert - Mit Standardwerten (wie html4_defaults_example)</h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">';

// Defaults MBlock Beispiel basierend auf html4_defaults_example.ini
$id4 = 4;
$defaultsForm = '
<fieldset class="form-horizontal">
    <legend>Name und Link</legend>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label for="rv2_4_0_name">Name</label></div>
        <div class="col-sm-10">
            <input id="rv2_4_0_name" type="text" name="REX_INPUT_VALUE[' . $id4 . '][0][name]" value="" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Link</label></div>
        <div class="col-sm-10">
            REX_LINK[id="1" widget="1"]
        </div>
    </div>
</fieldset>';

$content .= MBlock::show($id4, $defaultsForm, array(
    'label' => 'Erweiterte Blöcke',
    'min' => 1,
    'max' => 10
));

$content .= '</div>
            <div class="col-md-6">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#code-input-4" aria-controls="code-input-4" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-pencil"></i> Modul-Input
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#code-output-4" aria-controls="code-output-4" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-eye"></i> Modul-Output
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" style="border: 1px solid #ddd; border-top: none;">
                    <div role="tabpanel" class="tab-pane active" id="code-input-4">
                        <pre class="code-example">' . htmlentities('<?php
// Modul-Input (html4_defaults_example.ini)
$id = 4;

$form = <<<EOT
<fieldset class="form-horizontal">
    <legend>Name und Link</legend>
    <div class="form-group">
        <div class="col-sm-2 control-label">
            <label for="rv2_4_0_name">Name</label>
        </div>
        <div class="col-sm-10">
            <input id="rv2_4_0_name" type="text" 
                   name="REX_INPUT_VALUE[$id][0][name]" 
                   value="" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label>Link</label></div>
        <div class="col-sm-10">
            REX_LINK[id="1" widget="1"]
        </div>
    </div>
</fieldset>
EOT;

echo MBlock::show($id, $form, array(
    \'label\' => \'Erweiterte Blöcke\',
    \'min\' => 1,
    \'max\' => 10
));
?>') . '</pre>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="code-output-4">
                        <pre class="code-example">' . htmlentities('<?php
// Modul-Output (html4_defaults_example_output.ini)
echo \'<pre>\';
print_r(rex_var::toArray("REX_VALUE[4]"));
echo \'</pre>\';

// Oder formatierte Ausgabe:
$blocks = rex_var::toArray("REX_VALUE[4]");
foreach($blocks as $block) {
    echo \'<div class="name-link-block">\';
    echo \'<h3>\' . $block[\'name\'] . \'</h3>\';
    
    // Link anzeigen
    if(!empty($block[\'REX_LINK_1\'])) {
        $article = rex_article::get($block[\'REX_LINK_1\']);
        if($article) {
            echo \'<a href="\' . $article->getUrl() . \'">\';
            echo \'Zum Artikel: \' . $article->getName();
            echo \'</a>\';
        }
    }
    echo \'</div>\';
}
?>') . '</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

// Zusätzliche Informationen
$content .= '<div class="alert alert-success">
    <h4>Wichtige Syntax-Regeln für MBlock HTML:</h4>
    <ul>
        <li><strong>Input-Felder:</strong> <code>name="REX_INPUT_VALUE[ID][0][feldname]"</code></li>
        <li><strong>Output-Daten:</strong> <code>rex_var::toArray("REX_VALUE[ID]")</code></li>
        <li><strong>REDAXO-Widgets:</strong> <code>REX_MEDIA[id="1" widget="1"]</code>, <code>REX_LINK[id="1" widget="1"]</code>, <code>REX_MEDIALIST[id="1" widget="1"]</code>, <code>REX_LINKLIST[id="1" widget="1"]</code></li>
        <li><strong>Block-Index:</strong> Die <code>0</code> wird automatisch durch <code>0, 1, 2, ...</code> ersetzt</li>
        <li><strong>Widget-Ausgabe:</strong> Media-Widgets speichern in <code>REX_MEDIA_1</code>, Link-Widgets in <code>REX_LINK_1</code></li>
        <li><strong>Einstellungen:</strong> <code>min</code>, <code>max</code>, <code>label</code>, <code>initial_hidden</code></li>
        <li><strong>FALSCHE Variablen:</strong> <code>REX_MBLOCK_VALUE</code> und <code>REX_MBLOCK_ID</code> existieren NICHT!</li>
    </ul>
</div>';

// CSS für bessere Darstellung
$content .= '
<style>
.code-example {
    max-height: 350px;
    overflow-y: auto;
    margin: 0;
    padding: 15px;
    background: #f8f8f8;
    border: none;
    font-size: 13px;
    line-height: 1.5;
    font-family: "Courier New", Courier, monospace;
    white-space: pre-wrap;
}

.tab-content {
    min-height: 370px;
}

.nav-tabs {
    margin-bottom: 0;
}

.nav-tabs > li > a {
    padding: 8px 15px;
    font-size: 14px; /* Normal font size for tabs */
}

.tab-pane {
    padding: 0;
}

.panel {
    margin-bottom: 25px;
}

.panel-heading h4 {
    margin: 0;
    color: white;
}

.team-member,
.name-link-block {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 5px;
}

.media-gallery img {
    max-width: 100px;
    margin: 5px;
}

/* Bootstrap 3 Tab-Fix für bessere Darstellung */
.nav-tabs > li.active > a,
.nav-tabs > li.active > a:hover,
.nav-tabs > li.active > a:focus {
    background-color: #f8f8f8;
    border-bottom-color: #f8f8f8;
}
</style>';

echo $content;

// JavaScript um Widget-Platzhalter zu ersetzen
echo '<script>
$(document).ready(function() {
    // REX_MEDIA Widgets durch Fake-Widgets ersetzen
    $(".mblock_wrapper").each(function() {
        var $wrapper = $(this);
        var content = $wrapper.html();
        
        // REX_MEDIA Widget-Platzhalter
        content = content.replace(/REX_MEDIA\[id="(\d+)"\s+widget="1"\]/g, 
            \'<div class="rex-widget-demo">\' +
            \'<input type="text" class="form-control" placeholder="beispiel.jpg" readonly style="background: #f9f9f9; margin-bottom: 5px;">\' +
            \'<button type="button" class="btn btn-default btn-sm" disabled><i class="rex-icon fa-file-image-o"></i> Medienpool</button>\' +
            \'<div><small class="text-muted">Demo-Widget für: REX_MEDIA[id="$1" widget="1"]</small></div>\' +
            \'</div>\'
        );
        
        // REX_LINK Widget-Platzhalter  
        content = content.replace(/REX_LINK\[id="(\d+)"\s+widget="1"\]/g,
            \'<div class="rex-widget-demo">\' +
            \'<input type="text" class="form-control" placeholder="Artikel auswählen..." readonly style="background: #f9f9f9; margin-bottom: 5px;">\' +
            \'<button type="button" class="btn btn-default btn-sm" disabled><i class="rex-icon fa-link"></i> Artikel wählen</button>\' +
            \'<div><small class="text-muted">Demo-Widget für: REX_LINK[id="$1" widget="1"]</small></div>\' +
            \'</div>\'
        );
        
        // REX_MEDIALIST Widget-Platzhalter
        content = content.replace(/REX_MEDIALIST\[id="(\d+)"\s+widget="1"\]/g,
            \'<div class="rex-widget-demo">\' +
            \'<select class="form-control" disabled style="margin-bottom: 5px;">\' +
            \'<option>bild1.jpg</option><option>bild2.png</option><option>bild3.gif</option>\' +
            \'</select>\' +
            \'<button type="button" class="btn btn-default btn-sm" disabled><i class="rex-icon fa-plus"></i> Medien hinzufügen</button>\' +
            \'<div><small class="text-muted">Demo-Widget für: REX_MEDIALIST[id="$1" widget="1"]</small></div>\' +
            \'</div>\'
        );
        
        // REX_LINKLIST Widget-Platzhalter
        content = content.replace(/REX_LINKLIST\[id="(\d+)"\s+widget="1"\]/g,
            \'<div class="rex-widget-demo">\' +
            \'<select class="form-control" disabled style="margin-bottom: 5px;">\' +
            \'<option>Startseite</option><option>Über uns</option><option>Kontakt</option>\' +
            \'</select>\' +
            \'<button type="button" class="btn btn-default btn-sm" disabled><i class="rex-icon fa-plus"></i> Artikel hinzufügen</button>\' +
            \'<div><small class="text-muted">Demo-Widget für: REX_LINKLIST[id="$1" widget="1"]</small></div>\' +
            \'</div>\'
        );
        
        $wrapper.html(content);
    });
});
</script>

<style>
.rex-widget-demo {
    padding: 10px;
    background: #f8f8f8;
    border: 1px dashed #ccc;
    border-radius: 4px;
    margin: 5px 0;
}

.rex-widget-demo input,
.rex-widget-demo select {
    margin-bottom: 5px !important;
}

.rex-widget-demo .text-muted {
    font-style: italic;
    margin-top: 5px;
}
</style>';
