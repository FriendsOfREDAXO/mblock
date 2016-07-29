<?php

$fragment = new rex_fragment();
$content = $fragment->parse('json_help.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', $this->i18n('slice_json_block_json_help'), false);
$fragment->setVar('body', $content.'<code>'.highlight_file('codes/0.input.module-json.php',1).'</code>', false);
echo $fragment->parse('core/page/section.php');


$fragment = new rex_fragment();
$content = $fragment->parse('json_value.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', $this->i18n('slice_json_block_json_help'), false);
$fragment->setVar('body', $content.'<code>'.highlight_file('codes/rex_json_value.php',1).'</code>', false);
echo $fragment->parse('core/page/section.php');
