<?php
/**
 * @author https://github.com/FriendsOfREDAXO
 * @package redaxo5
 * @license MIT
 */

// set default template - always reset to standard on update
$this->setConfig('mblock_theme', 'standard');
if (!$this->hasConfig('mblock_delete')) {
    $this->setConfig('mblock_delete', 1);
}
if (!$this->hasConfig('mblock_scroll')) {
    $this->setConfig('mblock_scroll', 1);
}
if (!$this->hasConfig('mblock_delete_confirm')) {
    $this->setConfig('mblock_delete_confirm', 1);
}
// MBlock 4.0 - Copy/Paste Feature - Default aktiviert für neue und bestehende Installationen
if (!$this->hasConfig('mblock_copy_paste')) {
    $this->setConfig('mblock_copy_paste', 1); // Default: aktiviert
}
// Language Placeholder replacement - Default deaktiviert für neue und bestehende Installationen
if (!$this->hasConfig('mblock_replace_language_placeholders')) {
    $this->setConfig('mblock_replace_language_placeholders', 0); // Default: deaktiviert
}

// MBlock 4.0 - Template System Update
// Löscht alte Default-Templates aus dem data/ Ordner
$dataTemplatesPath = $this->getDataPath('templates/');

if (is_dir($dataTemplatesPath)) {
    // Liste der Default-Templates die gelöscht werden sollen
    $defaultTemplates = [
        'copy_theme',
        'default_theme', 
    ];
    
    $deletedCount = 0;
    foreach ($defaultTemplates as $templateName) {
        $templatePath = $dataTemplatesPath . $templateName;
        
        if (is_dir($templatePath)) {
            // Rekursiv löschen
            rex_dir::delete($templatePath);
            $deletedCount++;
        }
    }
    
    if ($deletedCount > 0) {
        // Prüfen ob templates Ordner leer ist
        $remainingFiles = glob($dataTemplatesPath . '*');
        if (empty($remainingFiles)) {
            // Leeren templates Ordner auch löschen
            rmdir($dataTemplatesPath);
        }
    }
}

// copy data directory (für neue Installationen) oder aktualisiere Templates (bei Updates)
if (!is_dir($this->getDataPath())) {
    rex_dir::copy($this->getPath('data'), $this->getDataPath());
}

// Bei jedem Update: Mitgelieferte Templates immer aktualisieren
$addonDataTemplatesPath = $this->getPath('data/templates/');
$userDataTemplatesPath = $this->getDataPath('templates/');

if (is_dir($addonDataTemplatesPath)) {
    // Erstelle templates Ordner im data-Verzeichnis falls nicht vorhanden
    if (!is_dir($userDataTemplatesPath)) {
        rex_dir::create($userDataTemplatesPath);
    }
    
    // Kopiere/aktualisiere mitgelieferte Templates - immer überschreiben bei Update
    $addonTemplates = glob($addonDataTemplatesPath . '*', GLOB_ONLYDIR);
    foreach ($addonTemplates as $templateDir) {
        $templateName = basename($templateDir);
        $targetDir = $userDataTemplatesPath . $templateName;
        
        // Überschreibe mitgelieferte Templates immer (für Updates)
        if (is_dir($templateDir)) {
            if (is_dir($targetDir)) {
                rex_dir::delete($targetDir);
            }
            rex_dir::copy($templateDir, $targetDir);
        }
    }
}

// delete all assets
rex_dir::deleteFiles($this->getAssetsPath(), true);
// copy assets
rex_dir::copy($this->getPath('assets'), $this->getAssetsPath());

// rex media and link updater
$values = array();

for ($i = 1; $i < 21; $i++) {
    $values[] = " value{$i} = REPLACE(value{$i}, 'REX_INPUT_L', 'REX_L')";
    $values[] = " value{$i} = REPLACE(value{$i}, 'REX_INPUT_M', 'REX_M')";
}

$values = implode(",\n\t", $values);
$prefix = rex::getTablePrefix();
$query= "UPDATE\n\t {$prefix}article_slice \nSET\n\t{$values};\n";

$sql = rex_sql::factory();
$sql->setDebug(false);
$sql->setQuery($query);
$rows = $sql->getRows();
