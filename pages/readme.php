<?php
/**
 * MBlock README Seite
 */
$fragment = new rex_fragment();
$fragment->setVar('title', 'MBlock README', false);
$fragment->setVar('body', rex_file::get(rex_path::addon('mblock', 'README.md')), false);
echo $fragment->parse('core/page/section.php');
