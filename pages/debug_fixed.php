<?php
/**
 * MBlock v3.5 - Debug & Test Tools (nur für Admins)
 */

// Admin-Check
if (!rex::getUser()->isAdmin()) {
    echo rex_view::error('Diese Seite ist nur für Administratoren zugänglich.');
    return;
}

$content = '';

$content .= '<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="rex-icon fa-wrench"></i> MBlock v3.5 - Debug & Test Tools</h3>
    </div>
    <div class="panel-body">
        <p><strong>Diese Seite dient zum Testen und Debuggen der MBlock v3.5 Features:</strong></p>
        <ul>
            <li>Initial Hidden Button Tests</li>
            <li>Move Button (↑ ↓) Funktionalität</li>
            <li>Event-Handler Debugging</li>
            <li>Button-State Management</li>
        </ul>
        <p><small><i class="rex-icon fa-info-circle"></i> Diese Seite ist nur für Administratoren sichtbar.</small></p>
    </div>
</div>';

$content .= '<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Test-MBlock (initial_hidden)</h3>
            </div>
            <div class="panel-body">
                <form method="post">
                    <div class="form-group">
                        <label>Debug Test MBlock</label>
                        <div class="mblock_wrapper" id="REX_MBLOCK_1" 
                             data-min="0" 
                             data-max="5"
                             data-mblock-single-add=\'<div class="mblock-single-add"><span class="singleadded"><button type="button" class="btn btn-success mblock-add-btn" title="duplicate"><i class="rex-icon rex-icon-add-module"></i> Ersten Block hinzufügen</button></span></div>\'
                             data-mblock-plain-sortitem=\'<div class="sortitem" data-mblock_index="{{count}}"><div class="form-group"><label>Titel</label><input type="text" name="REX_INPUT_VALUE[1][{{count}}][title]" class="form-control" placeholder="Titel eingeben..." /></div><div class="form-group"><label>Text</label><textarea name="REX_INPUT_VALUE[1][{{count}}][text]" class="form-control" placeholder="Text eingeben..."></textarea></div></div>\'>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="rex-icon fa-save"></i> Speichern (Test)
                        </button>
                        <button type="button" class="btn btn-info" id="debug-move-buttons">
                            <i class="rex-icon fa-bug"></i> Debug Move-Buttons
                        </button>
                        <button type="button" class="btn btn-warning" id="test-move-manually">
                            <i class="rex-icon fa-cogs"></i> Test Move Manuell
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="rex-icon fa-terminal"></i> Debug Console</h3>
            </div>
            <div class="panel-body">
                <div id="debug-console" style="height: 400px; overflow-y: auto; background: #f8f8f8; border: 1px solid #ddd; padding: 10px; font-family: monospace; font-size: 12px;">
                    <p><strong>Debug-Log wird hier angezeigt...</strong></p>
                </div>
                <div class="text-center" style="margin-top: 10px;">
                    <button type="button" class="btn btn-xs btn-default" onclick="$(\'#debug-console\').empty().html(\'<p><strong>Debug-Log geleert...</strong></p>\');">
                        <i class="rex-icon fa-trash"></i> Console leeren
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>';

$fragment = new rex_fragment();
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

?>

<script>
$(document).ready(function() {
    const $debugConsole = $('#debug-console');
    
    function debugLog(message, data = null) {
        const timestamp = new Date().toLocaleTimeString();
        let logEntry = '<div style="border-bottom: 1px solid #ccc; padding: 3px 0;"><strong>' + timestamp + ':</strong> ' + message;
        
        if (data) {
            logEntry += '<br><code>' + JSON.stringify(data, null, 2) + '</code>';
        }
        logEntry += '</div>';
        
        console.log('MBlock Debug:', message, data || '');
        $debugConsole.append(logEntry);
        $debugConsole.scrollTop($debugConsole[0].scrollHeight);
    }
    
    debugLog('=== MBlock v3.5 Debug Tools gestartet ===');
    
    // Test ob Funktionen existieren
    let functionsFound = 0;
    const requiredFunctions = ['mblock_add_item', 'mblock_moveup', 'mblock_movedown', 'mblock_init'];
    
    requiredFunctions.forEach(function(funcName) {
        if (typeof window[funcName] === 'function') {
            debugLog('✓ ' + funcName + ' gefunden');
            functionsFound++;
        } else {
            debugLog('✗ ' + funcName + ' NICHT gefunden!');
        }
    });
    
    debugLog('Status: ' + functionsFound + '/' + requiredFunctions.length + ' Funktionen verfügbar');
    
    if (functionsFound === requiredFunctions.length) {
        debugLog('🎉 Alle MBlock-Funktionen sind verfügbar!');
    } else {
        debugLog('⚠️ Einige MBlock-Funktionen fehlen - möglicherweise nicht korrekt geladen.');
    }
    
    // Move-Button Tests
    $(document).on('click', '.mblock-move-up', function(e) {
        debugLog('✓ Move Up geklickt!', {
            disabled: $(this).prop('disabled'),
            blockIndex: $(this).closest('.sortitem').index()
        });
    });
    
    $(document).on('click', '.mblock-move-down', function(e) {
        debugLog('✓ Move Down geklickt!', {
            disabled: $(this).prop('disabled'),
            blockIndex: $(this).closest('.sortitem').index()
        });
    });
    
    debugLog('Debug-Test initialisiert');
});
</script>
