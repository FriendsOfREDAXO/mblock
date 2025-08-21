<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// set default template
if (!$this->hasConfig('mblock_theme')) {
    $this->setConfig('mblock_theme', 'default_theme');
}
if (!$this->hasConfig('mblock_delete')) {
    $this->setConfig('mblock_delete', 1);
}
if (!$this->hasConfig('mblock_scroll')) {
    $this->setConfig('mblock_scroll', 1);
}
// MBlock 4.0 - Standard-Konfigurationen für neue Installation
if (!$this->hasConfig('mblock_delete_confirm')) {
    $this->setConfig('mblock_delete_confirm', 1);
}
if (!$this->hasConfig('mblock_copy_paste')) {
    $this->setConfig('mblock_copy_paste', 1); // Default: aktiviert
}

// copy data directory
rex_dir::copy($this->getPath('data'), $this->getDataPath());
