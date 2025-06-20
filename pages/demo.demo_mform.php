<?php
/**
 * MBlock v4.0 - MForm Demo mit Input/Output Tabs
 * Demonstration von MBlock mit MForm und Tab-Darstellung für Code-Beispiele
 */

use FriendsOfRedaxo\MForm;

// Handle form submission
if (rex_post('submit', 'bool')) {
    echo rex_view::success('Formulardaten gespeichert! (Demo-Modus)');
}

$content = '';

$content .= '<div class="rex-page-header">
    <h1 class="rex-page-title">MBlock v4.0 - MForm Demo</h1>
</div>';

$content .= '<div class="alert alert-info">
    <strong>MForm Demo:</strong> Diese Seite zeigt die Verwendung von MBlock mit MForm. 
    Input- und Output-Code werden in separaten Tabs dargestellt.
</div>';

$content .= '<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="rex-icon fa-cubes"></i> MBlock v4.0 - MForm Beispiele</h3>
    </div>
    <div class="panel-body">
        <p>Diese Demo zeigt die wichtigsten MBlock-Features mit 4 praktischen MForm-Beispielen:</p>
        <div class="row">
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li><i class="rex-icon fa-file-text-o text-primary"></i> <strong>Einfach:</strong> Grundlegendes Text-Block-System</li>
                    <li><i class="rex-icon fa-list-alt text-info"></i> <strong>Dynamisch:</strong> Verschiedene Content-Typen</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li><i class="rex-icon fa-puzzle-piece text-warning"></i> <strong>Komplex:</strong> Karten mit Meta-Daten</li>
                    <li><i class="rex-icon fa-eye-slash text-success"></i> <strong>Initial Button:</strong> Versteckte FAQ-Blöcke</li>
                </ul>
            </div>
        </div>
    </div>
</div>';

$content .= '<form method="post">';

// ======================
// 1. EINFACHES BEISPIEL
// ======================
$content .= '<div class="panel panel-default">
    <div class="panel-heading">
        <h4>1. Einfach - Text-Blöcke</h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">';

$mform1 = new MForm();
$mform1->addTextField("1.0.title", array('label' => 'Titel', 'placeholder' => 'z.B. Willkommen'));
$mform1->addTextAreaField("1.0.text", array('label' => 'Text'));

$content .= MBlock::show(1, $mform1->show(), array(
    'label' => 'Text-Blöcke',
    'min' => 1,
    'max' => 5
));

$content .= '</div>
            <div class="col-md-6">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#mform-input-1" aria-controls="mform-input-1" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-pencil"></i> Modul-Input
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#mform-output-1" aria-controls="mform-output-1" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-eye"></i> Modul-Output
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" style="border: 1px solid #ddd; border-top: none;">
                    <div role="tabpanel" class="tab-pane active" id="mform-input-1">
                        <pre class="code-example">' . htmlentities('<?php
use FriendsOfRedaxo\MForm;

$mform = new MForm();
$mform->addTextField("1.0.title", array(\'label\' => \'Titel\'));
$mform->addTextAreaField("1.0.text", array(\'label\' => \'Text\'));

echo MBlock::show(1, $mform->show(), array(
    \'label\' => \'Text-Blöcke\',
    \'min\' => 1,
    \'max\' => 5
));
?>') . '</pre>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="mform-output-1">
                        <pre class="code-example">' . htmlentities('<?php
// Ausgabe (nur aktive Blöcke):
$blocks = MBlock::getBlocks(1);
foreach($blocks as $block) {
    echo \'<h2>\' . $block[\'title\'] . \'</h2>\';
    echo \'<p>\' . $block[\'text\'] . \'</p>\';
}
?>') . '</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

// ======================
// 2. DYNAMISCHES BEISPIEL  
// ======================
$content .= '<div class="panel panel-info">
    <div class="panel-heading">
        <h4>2. Dynamisch - Content-Typen</h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">';

$mform2 = new MForm();
$mform2->addSelectField("2.0.type", array('0' => 'Bitte wählen', 'text' => 'Text', 'image' => 'Bild', 'video' => 'Video'), array('label' => 'Content-Typ', 'data-toggle' => 'collapse'))
    ->setToggleOptions(array('text' => 'content-text', 'image' => 'content-image', 'video' => 'content-video'));
$mform2->addTextField("2.0.title", array('label' => 'Titel'));

// Bedingte Felder mit Collapse-Elementen
$mform2->addCollapseElement('',
    MForm::factory()
        ->addTextAreaField("2.0.text_content", array('label' => 'Text-Inhalt')),
    false, true, array('data-group-collapse-id' => 'content-text')
);

$mform2->addCollapseElement('',
    MForm::factory()
        ->addMediaField(1, array('label' => 'Bild auswählen'))
        ->addTextField("2.0.image_alt", array('label' => 'Alt-Text')),
    false, true, array('data-group-collapse-id' => 'content-image')
);

$mform2->addCollapseElement('',
    MForm::factory()
        ->addTextField("2.0.video_url", array('label' => 'Video URL'))
        ->addCheckboxField("2.0.video_autoplay", array('1' => 'Autoplay')),
    false, true, array('data-group-collapse-id' => 'content-video')
);

$content .= MBlock::show(2, $mform2->show(), array(
    'label' => 'Content-Blöcke',
    'min' => 0,
    'max' => 8
));

$content .= '</div>
            <div class="col-md-6">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#mform-input-2" aria-controls="mform-input-2" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-pencil"></i> Modul-Input
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#mform-output-2" aria-controls="mform-output-2" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-eye"></i> Modul-Output
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" style="border: 1px solid #ddd; border-top: none;">
                    <div role="tabpanel" class="tab-pane active" id="mform-input-2">
                        <pre class="code-example">' . htmlentities('<?php
use FriendsOfRedaxo\MForm;

$mform = new MForm();
$mform->addSelectField("2.0.type", array(
    \'0\' => \'Bitte wählen\',
    \'text\' => \'Text\', 
    \'image\' => \'Bild\', 
    \'video\' => \'Video\'
), array(\'label\' => \'Content-Typ\', \'data-toggle\' => \'collapse\'))
->setToggleOptions(array(
    \'text\' => \'content-text\', 
    \'image\' => \'content-image\', 
    \'video\' => \'content-video\'
));

$mform->addTextField("2.0.title", array(\'label\' => \'Titel\'));

// Bedingte Felder mit Collapse-Elementen
$mform->addCollapseElement(\'\',
    MForm::factory()->addTextAreaField("2.0.text_content"),
    false, true, array(\'data-group-collapse-id\' => \'content-text\')
);

$mform->addCollapseElement(\'\',
    MForm::factory()
        ->addMediaField(1, array(\'label\' => \'Bild\'))
        ->addTextField("2.0.image_alt", array(\'label\' => \'Alt-Text\')),
    false, true, array(\'data-group-collapse-id\' => \'content-image\')
);

echo MBlock::show(2, $mform->show(), array(
    \'label\' => \'Content-Blöcke\',
    \'min\' => 0,
    \'max\' => 8
));
?>') . '</pre>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="mform-output-2">
                        <pre class="code-example">' . htmlentities('<?php
// Ausgabe mit Typ-Unterscheidung:
$blocks = MBlock::getBlocks(2);
foreach($blocks as $block) {
    switch($block[\'type\']) {
        case \'image\':
            if(!empty($block[\'REX_MEDIA_1\'])) {
                $media = rex_media::get($block[\'REX_MEDIA_1\']);
                if($media) {
                    echo \'<img src="\' . $media->getUrl() . \'" alt="\' . $block[\'image_alt\'] . \'">\';
                }
            }
            break;
        case \'video\':
            echo \'<video src="\' . $block[\'video_url\'] . \'"></video>\';
            break;
        default:
            echo \'<h3>\' . $block[\'title\'] . \'</h3>\';
            echo \'<p>\' . $block[\'text_content\'] . \'</p>\';
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
// 3. KOMPLEXES BEISPIEL
// ======================
$content .= '<div class="panel panel-warning">
    <div class="panel-heading">
        <h4>3. Komplex - Karten mit Meta-Daten</h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">';

$mform3 = new MForm();
$mform3->addTextField("3.0.title", array('label' => 'Karten-Titel', 'placeholder' => 'z.B. Unser Service'));
$mform3->addTextAreaField("3.0.description", array('label' => 'Beschreibung'));
$mform3->addMediaField(1, array('label' => 'Bild', 'types' => 'jpg,png,gif'));
$mform3->addTextField("3.0.link_url", array('label' => 'Link URL'));
$mform3->addTextField("3.0.link_text", array('label' => 'Link Text', 'default' => 'Mehr erfahren'));
$mform3->addCheckboxField("3.0.featured", array(1 => 'Featured Karte'), array('label' => 'Optionen'));

$content .= MBlock::show(3, $mform3->show(), array(
    'label' => 'Karten-System',
    'min' => 1,
    'max' => 6
));

$content .= '</div>
            <div class="col-md-6">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#mform-input-3" aria-controls="mform-input-3" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-pencil"></i> Modul-Input
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#mform-output-3" aria-controls="mform-output-3" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-eye"></i> Modul-Output
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" style="border: 1px solid #ddd; border-top: none;">
                    <div role="tabpanel" class="tab-pane active" id="mform-input-3">
                        <pre class="code-example">' . htmlentities('<?php
use FriendsOfRedaxo\MForm;

$mform = new MForm();
$mform->addTextField("3.0.title", array(\'label\' => \'Karten-Titel\'));
$mform->addTextAreaField("3.0.description", array(\'label\' => \'Beschreibung\'));
$mform->addMediaField(1, array(\'label\' => \'Bild\', \'types\' => \'jpg,png,gif\'));
$mform->addTextField("3.0.link_url", array(\'label\' => \'Link URL\'));
$mform->addTextField("3.0.link_text", array(\'label\' => \'Link Text\', \'default\' => \'Mehr erfahren\'));
$mform->addCheckboxField("3.0.featured", array(1 => \'Featured Karte\'));

echo MBlock::show(3, $mform->show(), array(
    \'label\' => \'Karten-System\',
    \'min\' => 1,
    \'max\' => 6
));
?>') . '</pre>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="mform-output-3">
                        <pre class="code-example">' . htmlentities('<?php
// Ausgabe mit CSS-Klassen:
$blocks = MBlock::getBlocks(3);
foreach($blocks as $block) {
    $featuredClass = !empty($block[\'featured\']) ? \' featured\' : \'\';
    echo \'<div class="card\' . $featuredClass . \'">\';
    
    // Bild
    if(!empty($block[\'REX_MEDIA_1\'])) {
        $media = rex_media::get($block[\'REX_MEDIA_1\']);
        if($media) {
            echo \'<img src="\' . $media->getUrl() . \'" alt="">\';
        }
    }
    
    echo \'<h3>\' . $block[\'title\'] . \'</h3>\';
    echo \'<p>\' . $block[\'description\'] . \'</p>\';
    
    if(!empty($block[\'link_url\'])) {
        echo \'<a href="\' . $block[\'link_url\'] . \'" class="btn">\';
        echo $block[\'link_text\'] ?: \'Mehr erfahren\';
        echo \'</a>\';
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
// 4. INITIAL HIDDEN BEISPIEL
// ======================
$content .= '<div class="panel panel-success">
    <div class="panel-heading">
        <h4>4. Initial Button - FAQ mit versteckten Blöcken</h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">';

$mform4 = new MForm();
$mform4->addTextField("4.0.question", array('label' => 'Frage', 'placeholder' => 'z.B. Wie funktioniert das?'));
$mform4->addTextAreaField("4.0.answer", array('label' => 'Antwort'));
$mform4->addSelectField("4.0.category", array('allgemein' => 'Allgemein', 'technik' => 'Technik', 'preise' => 'Preise'), array('label' => 'Kategorie'));

$content .= MBlock::show(4, $mform4->show(), array(
    'label' => 'FAQ Einträge',
    'min' => 0,
    'max' => 10,
    'initial_hidden' => 1
));

$content .= '</div>
            <div class="col-md-6">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#mform-input-4" aria-controls="mform-input-4" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-pencil"></i> Modul-Input
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#mform-output-4" aria-controls="mform-output-4" role="tab" data-toggle="tab">
                            <i class="rex-icon fa-eye"></i> Modul-Output
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" style="border: 1px solid #ddd; border-top: none;">
                    <div role="tabpanel" class="tab-pane active" id="mform-input-4">
                        <pre class="code-example">' . htmlentities('<?php
use FriendsOfRedaxo\MForm;

$mform = new MForm();
$mform->addTextField("4.0.question", array(\'label\' => \'Frage\'));
$mform->addTextAreaField("4.0.answer", array(\'label\' => \'Antwort\'));
$mform->addSelectField("4.0.category", array(
    \'allgemein\' => \'Allgemein\',
    \'technik\' => \'Technik\',
    \'preise\' => \'Preise\'
), array(\'label\' => \'Kategorie\'));

echo MBlock::show(4, $mform->show(), array(
    \'label\' => \'FAQ Einträge\',
    \'min\' => 0,
    \'max\' => 10,
    \'initial_hidden\' => 1    // Versteckte Startblöcke!
));
?>') . '</pre>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="mform-output-4">
                        <pre class="code-example">' . htmlentities('<?php
// Ausgabe gruppiert nach Kategorien:
$blocks = MBlock::getBlocks(4);
$categories = array();

// Gruppierung
foreach($blocks as $block) {
    $cat = $block[\'category\'] ?: \'allgemein\';
    $categories[$cat][] = $block;
}

// Ausgabe
foreach($categories as $catName => $faqItems) {
    echo \'<h3>\' . ucfirst($catName) . \'</h3>\';
    echo \'<div class="faq-section">\';
    
    foreach($faqItems as $item) {
        echo \'<div class="faq-item">\';
        echo \'<h4>\' . $item[\'question\'] . \'</h4>\';
        echo \'<p>\' . $item[\'answer\'] . \'</p>\';
        echo \'</div>\';
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

$content .= '</form>';

// Zusätzliche Informationen über MBlock v3.5
$content .= '<div class="panel panel-default">
    <div class="panel-heading">
        <h4><i class="rex-icon fa-lightbulb-o"></i> MBlock v3.5 Features</h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li><i class="rex-icon fa-toggle-on text-primary"></i> <strong>Toggle:</strong> Ein-/Ausblenden von Blöcken</li>
                    <li><i class="rex-icon fa-arrows text-warning"></i> <strong>Sortierung:</strong> Drag & Drop Reihenfolge</li>
                    <li><i class="rex-icon fa-copy text-info"></i> <strong>Duplikation:</strong> Blöcke kopieren</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li><i class="rex-icon fa-code text-success"></i> <strong>API:</strong> <code>getBlocks()</code> nur aktive Blöcke</li>
                    <li><i class="rex-icon fa-plus-circle text-danger"></i> <strong>Initial Hidden:</strong> Versteckte Startblöcke</li>
                    <li><i class="rex-icon fa-language text-muted"></i> <strong>i18n:</strong> Mehrsprachige Tooltips</li>
                </ul>
            </div>
        </div>
    </div>
</div>';

// CSS für Tab-Darstellung
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

/* Bootstrap 3 Tab-Fix für bessere Darstellung */
.nav-tabs > li.active > a,
.nav-tabs > li.active > a:hover,
.nav-tabs > li.active > a:focus {
    background-color: #f8f8f8;
    border-bottom-color: #f8f8f8;
}
</style>';

$fragment = new rex_fragment();
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
