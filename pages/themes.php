<?php
/**
 * MBlock Theme System Documentation
 * 
 * @package redaxo\mblock\pages
 * @since 4.0.0
 */

$content = '
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-header">
                <div class="panel-title">🎨 MBlock Theme Customization</div>
            </div>
            <div class="panel-body">
                <p>MBlock 4.0 verwendet ein modernes CSS Custom Properties (CSS Variables) System, das einfache Theme-Anpassungen ermöglicht. Alle Farben, Größen und Animationen können über CSS-Variablen überschrieben werden.</p>
                
                <h3>🚀 Quick Start</h3>
                <p>Um ein eigenes Theme zu erstellen, überschreiben Sie einfach die gewünschten CSS-Variablen in Ihrem eigenen CSS:</p>
                
                <pre><code>/* Ihr eigenes Theme CSS */
:root {
    /* Block Container anpassen */
    --mblock-background: rgba(255, 240, 245, 0.1);
    --mblock-border-color: rgba(220, 20, 60, 0.3);
    --mblock-shadow: 0 5px 20px rgba(220, 20, 60, 0.15);
    
    /* Add Button in Pink */
    --mblock-add-background: #e91e63;
    --mblock-add-background-hover: #c2185b;
    
    /* Toggle Button Breite anpassen */
    --mblock-toggle-width: 80px;
}</code></pre>

                <div class="alert alert-success">
                    <strong>✅ Dark Mode Ready:</strong> MBlock 4.0 unterstützt automatisch REDAXO Dark Theme, Bootstrap 5 Dark Mode und System Dark Mode (prefers-color-scheme)!
                </div>

                <div class="alert alert-info">
                    <strong>💡 Tipp:</strong> Fügen Sie Ihr CSS nach dem MBlock CSS ein, damit Ihre Variablen Vorrang haben.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-header">
                <div class="panel-title">📦 Block Container Variablen</div>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Standard</th>
                            <th>Beschreibung</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>--mblock-background</code></td>
                            <td>rgba(255, 255, 255, 0.09)</td>
                            <td>Block Hintergrundfarbe</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-border-color</code></td>
                            <td>rgba(100, 100, 100, 0.20)</td>
                            <td>Block Rahmenfarbe</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-shadow</code></td>
                            <td>0 5px 15px rgba(0,0,0,.08)</td>
                            <td>Block Schatten</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-shadow-hover</code></td>
                            <td>0 2px 8px rgba(0,0,0,0.12)</td>
                            <td>Block Schatten beim Hover</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-border-radius</code></td>
                            <td>4px</td>
                            <td>Block Eckenradius</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-padding</code></td>
                            <td>40px 15px 20px 30px</td>
                            <td>Block Innenabstand</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-margin</code></td>
                            <td>0 0 10px 0</td>
                            <td>Block Außenabstand</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-transition</code></td>
                            <td>all 0.2s ease</td>
                            <td>Block Transition-Effekt</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-transform-hover</code></td>
                            <td>translateY(-1px)</td>
                            <td>Block Transform beim Hover</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-text-color</code></td>
                            <td>inherit</td>
                            <td>Block Textfarbe</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-header">
                <div class="panel-title">🎛️ Button Variablen</div>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Standard</th>
                            <th>Beschreibung</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>--mblock-add-background</code></td>
                            <td>#28a745</td>
                            <td>Add Button Hintergrund</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-add-background-hover</code></td>
                            <td>#218838</td>
                            <td>Add Button Hover</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-control-background</code></td>
                            <td>#f8f9fa</td>
                            <td>Control Buttons Hintergrund</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-control-background-hover</code></td>
                            <td>#e9ecef</td>
                            <td>Control Buttons Hover</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-button-border-radius</code></td>
                            <td>4px</td>
                            <td>Button Eckenradius</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-button-font-size</code></td>
                            <td>12px</td>
                            <td>Button Schriftgröße</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-header">
                <div class="panel-title">🔄 Toggle Button Variablen</div>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Standard</th>
                            <th>Beschreibung</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>--mblock-toggle-width</code></td>
                            <td>70px</td>
                            <td>Toggle Button Breite</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-toggle-font-size</code></td>
                            <td>10px</td>
                            <td>Toggle Text Größe</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-toggle-gap</code></td>
                            <td>3px</td>
                            <td>Abstand Icon-Text</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-online-background</code></td>
                            <td>linear-gradient(135deg, #28a745, #20c997)</td>
                            <td>Online Button Gradient</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-offline-background</code></td>
                            <td>linear-gradient(135deg, #6c757d, #495057)</td>
                            <td>Offline Button Gradient</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-header">
                <div class="panel-title">🌙 Dark Mode Variablen</div>
            </div>
            <div class="panel-body">
                <p>MBlock 4.0 unterstützt automatisch drei Dark Mode Systeme:</p>
                <ul>
                    <li><strong>REDAXO Dark Theme:</strong> <code>body.rex-theme-dark</code></li>
                    <li><strong>Bootstrap 5 Dark Mode:</strong> <code>html[data-bs-theme="dark"]</code></li>
                    <li><strong>System Dark Mode:</strong> <code>@media (prefers-color-scheme: dark)</code> mit <code>body:not(.rex-theme-light)</code></li>
                </ul>
                
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Standard</th>
                            <th>Beschreibung</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>--mblock-dark-background</code></td>
                            <td>rgba(255, 255, 255, 0.05)</td>
                            <td>Dark Mode Hintergrund</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-dark-border-color</code></td>
                            <td>rgba(255, 255, 255, 0.15)</td>
                            <td>Dark Mode Rahmenfarbe</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-dark-shadow</code></td>
                            <td>0 5px 15px rgba(0,0,0,.3)</td>
                            <td>Dark Mode Schatten</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-dark-text-color</code></td>
                            <td>#e9ecef</td>
                            <td>Dark Mode Textfarbe</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-dark-drag-color</code></td>
                            <td>#adb5bd</td>
                            <td>Dark Mode Drag Handle</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-dark-control-background</code></td>
                            <td>#495057</td>
                            <td>Dark Mode Control Buttons</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-dark-add-background</code></td>
                            <td>#198754</td>
                            <td>Dark Mode Add Button</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="alert alert-info">
                    <strong>💡 Custom Dark Mode:</strong> Sie können eigene Dark Mode Variablen definieren, die automatisch aktiviert werden.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-header">
                <div class="panel-title">✨ Animation & Effekt Variablen</div>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Standard</th>
                            <th>Beschreibung</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>--mblock-animation-fast</code></td>
                            <td>0.2s</td>
                            <td>Schnelle Animationen</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-animation-normal</code></td>
                            <td>0.3s</td>
                            <td>Normale Animationen</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-animation-glow</code></td>
                            <td>1.2s</td>
                            <td>Glow Effekt Dauer</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-transition</code></td>
                            <td>all 0.2s ease</td>
                            <td>Standard Transition</td>
                        </tr>
                        <tr>
                            <td><code>--mblock-transform-hover</code></td>
                            <td>translateY(-1px)</td>
                            <td>Hover Transform</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-header">
                <div class="panel-title">🌙 Dark Mode Unterstützung</div>
            </div>
            <div class="panel-body">
                <p>MBlock unterstützt automatisch Dark Mode über zwei verschiedene Mechanismen:</p>
                
                <h4>1. REDAXO Dark Theme (fest eingestellt)</h4>
                <pre><code>body.rex-theme-dark {
    --mblock-background: rgba(255, 255, 255, 0.05);
    --mblock-border-color: rgba(255, 255, 255, 0.15);
    /* Weitere Dark Mode Variablen */
}</code></pre>
                
                <h4>2. System Preference Dark Mode (Auto-Modus)</h4>
                <pre><code>@media (prefers-color-scheme: dark) {
    body.rex-has-theme:not(.rex-theme-light) {
        --mblock-background: rgba(255, 255, 255, 0.05);
        --mblock-border-color: rgba(255, 255, 255, 0.15);
        /* Weitere Dark Mode Variablen */
    }
}</code></pre>
                
                <div class="alert alert-success">
                    <strong>✅ Automatisch:</strong> Ihre Custom Theme Variablen werden automatisch in beiden Dark Mode Varianten angewendet!
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-header">
                <div class="panel-title">🎯 Beispiel Themes</div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-4">
                        <h4>🌸 Pink Theme</h4>
                        <pre><code>:root {
    --mblock-add-background: #e91e63;
    --mblock-add-background-hover: #c2185b;
    --mblock-online-background: linear-gradient(135deg, #e91e63, #f06292);
    --mblock-border-color: rgba(233, 30, 99, 0.2);
    --mblock-shadow: 0 5px 15px rgba(233, 30, 99, 0.1);
}</code></pre>
                    </div>
                    
                    <div class="col-lg-4">
                        <h4>💜 Purple Theme</h4>
                        <pre><code>:root {
    --mblock-add-background: #9c27b0;
    --mblock-add-background-hover: #7b1fa2;
    --mblock-online-background: linear-gradient(135deg, #9c27b0, #ba68c8);
    --mblock-border-color: rgba(156, 39, 176, 0.2);
    --mblock-shadow: 0 5px 15px rgba(156, 39, 176, 0.1);
}</code></pre>
                    </div>
                    
                    <div class="col-lg-4">
                        <h4>🧡 Orange Theme</h4>
                        <pre><code>:root {
                            --mblock-add-background: #ff5722;
    --mblock-add-background-hover: #e64a19;
    --mblock-online-background: linear-gradient(135deg, #ff5722, #ff8a65);
    --mblock-border-color: rgba(255, 87, 34, 0.2);
    --mblock-shadow: 0 5px 15px rgba(255, 87, 34, 0.1);
}</code></pre>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <strong>🔧 Integration:</strong> Fügen Sie Ihr Theme CSS in Ihr Template oder über ein separates Addon ein. Die Variablen überschreiben automatisch die MBlock Standard-Werte.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-header">
                <div class="panel-title">📋 Vollständige Variablen Liste</div>
            </div>
            <div class="panel-body">
                <p>Hier finden Sie alle verfügbaren CSS-Variablen für maximale Anpassbarkeit:</p>
                
                <details>
                    <summary><strong>Alle CSS Variablen anzeigen</strong></summary>
                    <pre><code>/* Block Container */
--mblock-border-color: rgba(100, 100, 100, 0.20);
--mblock-background: rgba(255, 255, 255, 0.09);
--mblock-shadow: 0 5px 15px rgba(0,0,0,.08);
--mblock-shadow-hover: 0 2px 8px rgba(0,0,0,0.12);
--mblock-border-radius: 4px;
--mblock-padding: 40px 15px 20px 30px;
--mblock-margin: 0 0 10px 0;
--mblock-transition: all 0.2s ease;
--mblock-transform-hover: translateY(-1px);

/* Drag Handle */
--mblock-drag-color: #6c757d;
--mblock-drag-color-hover: #495057;
--mblock-drag-opacity: 0.6;
--mblock-drag-opacity-hover: 1;
--mblock-drag-font-size: 14px;
--mblock-drag-position-top: 8px;
--mblock-drag-position-left: 8px;

/* Buttons Base */
--mblock-button-border-radius: 4px;
--mblock-button-font-size: 12px;
--mblock-button-padding: 4px 6px;
--mblock-button-min-size: 28px;
--mblock-button-transition: all 0.2s ease;
--mblock-button-shadow-hover: 0 2px 4px rgba(0,0,0,0.1);
--mblock-button-transform-hover: translateY(-1px);

/* Control Buttons */
--mblock-control-background: #f8f9fa;
--mblock-control-border: #dee2e6;
--mblock-control-color: #495057;
--mblock-control-background-hover: #e9ecef;
--mblock-control-border-hover: #adb5bd;
--mblock-control-background-disabled: #f8f9fa;
--mblock-control-color-disabled: #6c757d;

/* Add Button */
--mblock-add-background: #28a745;
--mblock-add-border: #28a745;
--mblock-add-color: white;
--mblock-add-background-hover: #218838;
--mblock-add-border-hover: #1e7e34;
--mblock-add-shadow-hover: 0 4px 12px rgba(40, 167, 69, 0.3);
--mblock-add-transform-hover: translateY(-2px);
--mblock-add-padding: 8px 16px;
--mblock-add-gap: 6px;

/* Online/Offline Toggle */
--mblock-toggle-width: 70px;
--mblock-toggle-padding: 4px 8px;
--mblock-toggle-font-size: 10px;
--mblock-toggle-border-radius: 12px;
--mblock-toggle-letter-spacing: 0.3px;
--mblock-toggle-gap: 3px;

/* Online State */
--mblock-online-background: linear-gradient(135deg, #28a745, #20c997);
--mblock-online-color: white;
--mblock-online-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
--mblock-online-background-hover: linear-gradient(135deg, #218838, #17a085);
--mblock-online-shadow-hover: 0 3px 8px rgba(40, 167, 69, 0.4);

/* Offline State */
--mblock-offline-background: linear-gradient(135deg, #6c757d, #495057);
--mblock-offline-color: white;
--mblock-offline-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
--mblock-offline-background-hover: linear-gradient(135deg, #5a6268, #343a40);
--mblock-offline-shadow-hover: 0 3px 8px rgba(108, 117, 125, 0.4);

/* Offline Block State */
--mblock-offline-block-opacity: 0.6;
--mblock-offline-block-background: rgba(248, 249, 250, 0.5);
--mblock-offline-block-border: #6c757d;
--mblock-offline-pattern: repeating-linear-gradient(45deg, transparent, transparent 8px, rgba(108, 117, 125, 0.1) 8px, rgba(108, 117, 125, 0.1) 16px);

/* Copy & Paste Toolbar */
--mblock-toolbar-background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
--mblock-toolbar-border: #dee2e6;
--mblock-toolbar-shadow: 0 2px 4px rgba(0,0,0,0.05);
--mblock-toolbar-shadow-hover: 0 4px 8px rgba(0,0,0,0.1);
--mblock-toolbar-padding: 10px 15px;
--mblock-toolbar-margin: 10px;
--mblock-toolbar-border-radius: 6px;
--mblock-toolbar-gap: 8px;

/* Sortable Ghost */
--mblock-ghost-opacity: 0.6;
--mblock-ghost-background: rgba(248, 249, 250, 0.9);
--mblock-ghost-border: 2px dashed #007bff;
--mblock-ghost-padding: 8px;
--mblock-ghost-margin: 4px 0;
--mblock-ghost-shimmer: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.1), transparent);

/* Animation Timings */
--mblock-animation-fast: 0.2s;
--mblock-animation-normal: 0.3s;
--mblock-animation-slow: 0.5s;
--mblock-animation-shimmer: 2s;
--mblock-animation-glow: 1.2s;

/* Glow Effects */
--mblock-paste-glow-color: rgba(40, 167, 69, 0.8);
--mblock-paste-glow-color-mid: rgba(40, 167, 69, 0.2);
--mblock-paste-glow-shadow: 0 0 30px rgba(40, 167, 69, 0.4);
--mblock-copy-glow-color: rgba(0, 123, 255, 0.8);
--mblock-copy-glow-color-mid: rgba(0, 123, 255, 0.25);
--mblock-copy-glow-shadow: 0 0 25px rgba(0, 123, 255, 0.4);</code></pre>
                </details>
                
                <h4>🌙 Dark Mode Anpassung</h4>
                <p>Erstellen Sie eigene Dark Mode Themes mit den Dark Mode Variablen:</p>
                
                <pre><code>/* Custom Purple Dark Theme */
:root {
    /* Light Mode Überschreibungen */
    --mblock-add-background: #9c27b0;
    --mblock-add-background-hover: #7b1fa2;
    
    /* Dark Mode Überschreibungen */
    --mblock-dark-background: rgba(156, 39, 176, 0.08);
    --mblock-dark-border-color: rgba(156, 39, 176, 0.3);
    --mblock-dark-add-background: #ab47bc;
    --mblock-dark-add-background-hover: #8e24aa;
}

/* Automatische Dark Mode Aktivierung */
body.rex-theme-dark .mblock_wrapper,
html[data-bs-theme="dark"] .mblock_wrapper {
    /* Verwendet automatisch die --mblock-dark-* Variablen */
}

@media (prefers-color-scheme: dark) {
    body:not(.rex-theme-light) .mblock_wrapper {
        /* System Dark Mode Support */
    }
}</code></pre>

                <div class="alert alert-success">
                    <strong>✅ Automatisch:</strong> Dark Mode Variablen werden automatisch in allen unterstützten Dark Mode Kontexten angewendet!
                </div>
                
                <div class="alert alert-warning mt-3">
                    <strong>⚠️ Hinweis:</strong> Das Theme System ist ab MBlock 4.0 verfügbar. Stellen Sie sicher, dass Sie die aktuelle Version verwenden.
                </div>
            </div>
        </div>
    </div>
</div>
';

echo $content;
