<?php
/**
 * MBlock API-Dokumentation Seite
 */
$fragment = new rex_fragment();
$fragment->setVar('title', 'MBlock API-Dokumentation', false);
$fragment->setVar('body', rex_file::get(rex_path::addon('mblock', 'API.md')), false);
echo $fragment->parse('core/page/section.php');
