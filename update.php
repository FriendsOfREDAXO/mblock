<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// set default template
if (!$this->hasConfig()) {
    $this->setConfig('mblock_theme', 'default_theme');
    $this->setConfig('mblock_delete', 1);
    $this->setConfig('mblock_scroll', 1);
}

// copy data directory
rex_dir::copy($this->getPath('data'), $this->getDataPath());
