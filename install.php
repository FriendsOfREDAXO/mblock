<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// set default template
if (!$this->hasConfig()) {
    $this->setConfig('mblock_template', 'default_theme');
}

// copy data directory
rex_dir::copy($this->getPath('data'), $this->getDataPath());