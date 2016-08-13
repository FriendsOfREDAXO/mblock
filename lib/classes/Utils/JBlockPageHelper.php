<?php

/**
 * Author Joachim Doerr
 * Date: 07.08.16
 * Time: 14:38
 */
class JBlockPageHelper
{
    public static function exchangeExamples($type)
    {
        $return = '';
        foreach (scandir(rex_path::addon('jblock', 'pages/examples')) as $file) {
            if (is_dir($file)) {
                continue;
            }
            if (strpos($file, $type) !== false && strpos($file, 'output') === false) {

                // add input
                $content = '<h3>'.rex_i18n::msg('jblock_modul_input').'</h3>' . rex_string::highlight(file_get_contents(rex_path::addon('jblock', 'pages/examples/' . $file)));

                if (file_exists(rex_path::addon('jblock', 'pages/examples/' . pathinfo($file, PATHINFO_FILENAME) . '_output.ini'))) {
                    // add output
                    $content .= '<h3>'.rex_i18n::msg('jblock_modul_output').'</h3>' . rex_string::highlight(file_get_contents(rex_path::addon('jblock', 'pages/examples/' . pathinfo($file, PATHINFO_FILENAME) . '_output.ini')));
                }

                // parse info fragment
                $fragment = new rex_fragment();
                $fragment->setVar('title', rex_i18n::msg('jblock_example_' . preg_replace('/\d+/u', '', pathinfo($file, PATHINFO_FILENAME))));
                $fragment->setVar('content', '<div class="span" style="padding: 0 20px 10px 20px">' . $content . '</div>', false);
                $fragment->setVar('collapse', true);
                $fragment->setVar('collapsed', true);
                $content = $fragment->parse('core/page/section.php');
                $return .= $content;
            }
        }
        return $return;
    }
}