<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// set default template - always set to standard on install
$this->setConfig('mblock_theme', 'standard');
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
if (!$this->hasConfig('mblock_replace_language_placeholders')) {
    $this->setConfig('mblock_replace_language_placeholders', 0); // Default: deaktiviert
}

// copy data directory
rex_dir::copy($this->getPath('data'), $this->getDataPath());

// Sicherstellen, dass mitgelieferte Templates immer aktuell sind
$addonDataTemplatesPath = $this->getPath('data/templates/');
$userDataTemplatesPath = $this->getDataPath('templates/');

if (is_dir($addonDataTemplatesPath) && is_dir($userDataTemplatesPath)) {
    // Kopiere/aktualisiere mitgelieferte Templates auch bei Installation
    $addonTemplates = glob($addonDataTemplatesPath . '*', GLOB_ONLYDIR);
    foreach ($addonTemplates as $templateDir) {
        $templateName = basename($templateDir);
        $targetDir = $userDataTemplatesPath . $templateName;
        
        // Überschreibe mitgelieferte Templates (für konsistente Installation)
        if (is_dir($templateDir)) {
            if (is_dir($targetDir)) {
                rex_dir::delete($targetDir);
            }
            rex_dir::copy($templateDir, $targetDir);
        }
    }
}
