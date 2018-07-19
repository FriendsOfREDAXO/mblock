<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

echo rex_view::title(rex_i18n::msg('mblock_title') . ': ' . rex_i18n::msg('mblock_'.rex_be_controller::getCurrentPagePart(2)));

rex_be_controller::includeCurrentPageSubPath();
