<?php
/**
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('jblock_info'), false);
$fragment->setVar('body', '<p>'.rex_i18n::msg('jblock_example_description_base').'</p>', false);
echo $fragment->parse('core/page/section.php');

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('jblock_demo_base'), false);
$fragment->setVar('body', JBlockPageHelper::exchangeExamples('base'), false);
echo $fragment->parse('core/page/section.php');
