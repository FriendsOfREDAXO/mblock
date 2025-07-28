<?php
/**
 * MBlock Debug Helper - Vereinfacht das Debugging von MBlock-Modulen
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MBlockDebugHelper
{
    /**
     * Debug-Output für MBlock-Daten im Backend
     * @param int|string $id - MBlock ID
     * @param array $data - Optional: Spezifische Daten zum Debuggen
     */
    public static function dump($id, $data = null)
    {
        if (!rex::isBackend()) {
            return;
        }

        if ($data === null) {
            $data = rex_var::toArray("REX_VALUE[$id]");
        }

        echo '<div class="panel panel-info mblock-debug">';
        echo '<div class="panel-heading"><strong>🐛 MBlock Debug (ID: ' . $id . ')</strong></div>';
        echo '<div class="panel-body">';
        echo '<pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; max-height: 400px; overflow-y: auto;">';
        
        if (is_array($data) && !empty($data)) {
            echo "Anzahl Blöcke: " . count($data) . "\n\n";
            foreach ($data as $index => $block) {
                echo "Block #$index:\n";
                echo self::formatArray($block, 1);
                echo "\n" . str_repeat('-', 50) . "\n\n";
            }
        } else {
            echo "Keine MBlock-Daten gefunden oder leeres Array.\n";
            echo "Mögliche Ursachen:\n";
            echo "- MBlock wurde noch nicht gespeichert\n";
            echo "- Falsche ID verwendet\n";
            echo "- Daten sind nicht als Array gespeichert\n";
        }
        
        echo '</pre>';
        echo '</div></div>';
    }

    /**
     * Validiert MBlock-Daten und zeigt Probleme an
     * @param int|string $id - MBlock ID
     * @param array $requiredFields - Erforderliche Felder pro Block
     */
    public static function validate($id, $requiredFields = [])
    {
        if (!rex::isBackend()) {
            return true;
        }

        $data = rex_var::toArray("REX_VALUE[$id]");
        $hasErrors = false;
        $errors = [];

        echo '<div class="panel panel-warning mblock-validation">';
        echo '<div class="panel-heading"><strong>✅ MBlock Validierung (ID: ' . $id . ')</strong></div>';
        echo '<div class="panel-body">';

        if (!is_array($data) || empty($data)) {
            echo '<div class="alert alert-warning">⚠️ Keine Daten zum Validieren vorhanden.</div>';
            echo '</div></div>';
            return false;
        }

        foreach ($data as $index => $block) {
            $blockErrors = [];

            if (!is_array($block)) {
                $blockErrors[] = "Block ist kein Array";
                $hasErrors = true;
                continue;
            }

            // Erforderliche Felder prüfen
            foreach ($requiredFields as $field) {
                if (!isset($block[$field]) || empty($block[$field])) {
                    $blockErrors[] = "Feld '$field' fehlt oder ist leer";
                    $hasErrors = true;
                }
            }

            // Ergebnisse anzeigen
            if (!empty($blockErrors)) {
                echo '<div class="alert alert-danger">';
                echo "<strong>❌ Block #$index hat Probleme:</strong><ul>";
                foreach ($blockErrors as $error) {
                    echo "<li>$error</li>";
                }
                echo '</ul></div>';
            } else {
                echo '<div class="alert alert-success">✅ Block #' . $index . ' ist gültig</div>';
            }
        }

        if (!$hasErrors) {
            echo '<div class="alert alert-success"><strong>🎉 Alle Blöcke sind gültig!</strong></div>';
        }

        echo '</div></div>';
        return !$hasErrors;
    }

    /**
     * Zeigt Performance-Informationen für MBlock
     * @param int|string $id - MBlock ID
     */
    public static function performance($id)
    {
        if (!rex::isBackend()) {
            return;
        }

        $data = rex_var::toArray("REX_VALUE[$id]");
        $blockCount = is_array($data) ? count($data) : 0;
        
        echo '<div class="panel panel-info mblock-performance">';
        echo '<div class="panel-heading"><strong>⚡ MBlock Performance (ID: ' . $id . ')</strong></div>';
        echo '<div class="panel-body">';
        
        echo '<div class="row">';
        echo '<div class="col-md-3">';
        echo '<div class="well text-center">';
        echo '<h3>' . $blockCount . '</h3>';
        echo '<p>Anzahl Blöcke</p>';
        echo '</div>';
        echo '</div>';
        
        if ($blockCount > 0) {
            $totalSize = strlen(serialize($data));
            $avgSize = round($totalSize / $blockCount);
            
            echo '<div class="col-md-3">';
            echo '<div class="well text-center">';
            echo '<h3>' . self::formatBytes($totalSize) . '</h3>';
            echo '<p>Gesamt-Datensize</p>';
            echo '</div>';
            echo '</div>';
            
            echo '<div class="col-md-3">';
            echo '<div class="well text-center">';
            echo '<h3>' . self::formatBytes($avgSize) . '</h3>';
            echo '<p>⌀ Block-Size</p>';
            echo '</div>';
            echo '</div>';

            echo '<div class="col-md-3">';
            echo '<div class="well text-center">';
            if ($blockCount > 20) {
                echo '<h3 style="color: #d9534f;">⚠️</h3>';
                echo '<p>Viele Blöcke</p>';
            } elseif ($blockCount > 10) {
                echo '<h3 style="color: #f0ad4e;">📊</h3>';
                echo '<p>Moderate Anzahl</p>';
            } else {
                echo '<h3 style="color: #5cb85c;">✅</h3>';
                echo '<p>Optimal</p>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';

        // Empfehlungen
        if ($blockCount > 20) {
            echo '<div class="alert alert-warning">';
            echo '<strong>⚠️ Performance-Hinweis:</strong> Sehr viele Blöcke können die Performance beeinträchtigen. ';
            echo 'Erwägen Sie eine Aufteilung auf mehrere MBlocks oder Pagination.';
            echo '</div>';
        }

        if ($blockCount > 0) {
            $totalSize = strlen(serialize($data));
            if ($totalSize > 100000) { // > 100KB
                echo '<div class="alert alert-warning">';
                echo '<strong>⚠️ Datensize-Hinweis:</strong> Die MBlock-Daten sind sehr groß. ';
                echo 'Prüfen Sie, ob alle Daten wirklich benötigt werden.';
                echo '</div>';
            }
        }

        echo '</div></div>';
    }

    /**
     * Formatiert Array für Debug-Ausgabe
     * @param mixed $data - Zu formatierende Daten
     * @param int $level - Einrückungsebene
     * @return string - Formatierte Ausgabe
     */
    private static function formatArray($data, $level = 0)
    {
        $output = '';
        $indent = str_repeat('  ', $level);

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $output .= $indent . $key . ': ';
                if (is_array($value)) {
                    $output .= "\n" . self::formatArray($value, $level + 1);
                } elseif (is_string($value)) {
                    $output .= '"' . (strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value) . '"';
                } else {
                    $output .= var_export($value, true);
                }
                $output .= "\n";
            }
        } else {
            $output .= $indent . var_export($data, true) . "\n";
        }

        return $output;
    }

    /**
     * Formatiert Bytes in lesbare Einheiten
     * @param int $size - Size in Bytes
     * @return string - Formatierte Size
     */
    private static function formatBytes($size)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 1) . ' ' . $units[$unitIndex];
    }

    /**
     * Generiert ein Code-Snippet für das aktuelle MBlock
     * @param int|string $id - MBlock ID
     * @param string $type - Snippet-Typ ('input', 'output', 'both')
     */
    public static function generateSnippet($id, $type = 'both')
    {
        if (!rex::isBackend()) {
            return;
        }

        echo '<div class="panel panel-success mblock-snippet">';
        echo '<div class="panel-heading"><strong>📝 Code-Snippets für MBlock ' . $id . '</strong></div>';
        echo '<div class="panel-body">';

        if ($type === 'input' || $type === 'both') {
            echo '<h4>Input (für Modul-Input):</h4>';
            echo '<pre><code>';
            echo htmlspecialchars('<?php
// Einfacher MBlock
echo MBlockHelper::create(' . $id . ', $form);

// Mit Optionen
echo MBlockHelper::create(' . $id . ', $form, [
    \'min\' => 1,
    \'max\' => 10,
    \'delete_confirm\' => true
]);

// Vorgefertigtes Template
echo MBlockHelper::create(' . $id . ', 
    MBlockHelper::template(\'team\', [\'id\' => ' . $id . ']), 
    [\'max\' => 6]
);
?>');
            echo '</code></pre>';
        }

        if ($type === 'output' || $type === 'both') {
            echo '<h4>Output (für Modul-Output):</h4>';
            echo '<pre><code>';
            echo htmlspecialchars('<?php
// Einfacher Output mit Helper
echo MBlockHelper::output(' . $id . ', function($data, $index) {
    return \'<div class="block-item">
        <h3>\' . rex_escape($data[\'title\'] ?? \'\') . \'</h3>
        <p>\' . rex_escape($data[\'text\'] ?? \'\') . \'</p>
    </div>\';
});

// Klassischer Weg (falls gewünscht)
$mblock = rex_var::toArray("REX_VALUE[' . $id . ']");
if (is_array($mblock)) {
    foreach ($mblock as $item) {
        // Ihre Block-Ausgabe hier
    }
}
?>');
            echo '</code></pre>';
        }

        echo '</div></div>';
    }
}
