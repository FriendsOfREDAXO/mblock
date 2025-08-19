<?php
/**
 * @author https://github.com/FriendsOfREDAXO
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
if (!$this->hasConfig('mblock_delete_confirm')) {
    $this->setConfig('mblock_delete_confirm', 1);
}
// MBlock 4.0 - Copy/Paste Feature - Default aktiviert für neue und bestehende Installationen
if (!$this->hasConfig('mblock_copy_paste')) {
    $this->setConfig('mblock_copy_paste', 1); // Default: aktiviert
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

// copy data directory (für neue Installationen)
if (!is_dir($this->getDataPath())) {
    rex_dir::copy($this->getPath('data'), $this->getDataPath());
}

// delete all assets
rex_dir::deleteFiles($this->getAssetsPath(), true);
// copy assets
rex_dir::copy($this->getPath('assets'), $this->getAssetsPath());


// ensure demo table
rex_sql_table::get(rex::getTable('mblock_rexform_demo'))
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('status', 'int(1)', true, '1'))
    ->ensureColumn(new rex_sql_column('name', 'text'))
    ->ensureColumn(new rex_sql_column('mblock_field', 'text'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime', true))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime', true))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->setPrimaryKey('id')
    ->ensure();


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
