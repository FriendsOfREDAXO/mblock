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

// Check and display package suggestions
$packageConfig = rex_file::getConfig($this->getPath('package.yml'));
if (isset($packageConfig['suggests']['packages']) && is_array($packageConfig['suggests']['packages'])) {
    $suggestions = [];
    foreach ($packageConfig['suggests']['packages'] as $package => $description) {
        $addon = rex_addon::get($package);
        if (!$addon->isAvailable()) {
            $suggestions[] = [
                'package' => $package,
                'description' => $description
            ];
        }
    }
    
    if (!empty($suggestions)) {
        $message = '<div class="alert alert-info">';
        $message .= '<h4>' . $this->i18n('suggests_headline') . '</h4>';
        $message .= '<p>' . $this->i18n('suggests_intro') . '</p>';
        $message .= '<ul>';
        foreach ($suggestions as $suggestion) {
            $message .= '<li><strong>' . htmlspecialchars($suggestion['package']) . '</strong>';
            if (!empty($suggestion['description'])) {
                $message .= ': ' . htmlspecialchars($suggestion['description']);
            }
            $message .= ' <em>(' . $this->i18n('suggests_not_available') . ')</em></li>';
        }
        $message .= '</ul>';
        $message .= '</div>';
        
        echo rex_view::info($message);
    }
}
