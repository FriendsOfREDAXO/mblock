<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mblock_info'), false);
$fragment->setVar('body', '<p>'.rex_i18n::msg('mblock_example_description_html').'</p>', false);
echo $fragment->parse('core/page/section.php');

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mblock_demo_html'), false);
$fragment->setVar('body', MBlockPageHelper::exchangeExamples('html'), false);
echo $fragment->parse('core/page/section.php');
