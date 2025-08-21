<?php
/**
 * @author https://github.com/FriendsOfREDAXO
 * @package redaxo5
 * @license MIT
 */

use FriendsOfRedaxo\MBlock\Utils\TemplateManager;
use FriendsOfRedaxo\MBlock\Provider\TemplateProvider;

// rex request
$config = rex_post('config', array(
    array('mblock_scroll', 'boolean'),
    array('mblock_delete', 'boolean'),
    array('mblock_delete_confirm', 'boolean'),
    array('mblock_copy_paste', 'boolean'),
    array('mblock_theme', 'string'),
    array('submit', 'boolean')
));

// include info page
include rex_path::addon('mblock', 'pages/info.php');

//////////////////////////////////////////////////////////
// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mblock_help_subheadline_1'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

//////////////////////////////////////////////////////////
// init form
$form = '';

// if submit set config
if ($config['submit']) {
    // Handle template selection and CSS copying
    $templateChanged = false;
    $currentTemplate = $this->getConfig('mblock_theme', 'default_theme');
    
    if (isset($config['mblock_theme']) && $config['mblock_theme'] !== $currentTemplate) {
        // Validate template exists
        if (!TemplateProvider::templateExists($config['mblock_theme'])) {
            $form .= rex_view::error('Template "' . htmlspecialchars($config['mblock_theme']) . '" nicht gefunden. Bitte überprüfen Sie, ob alle benötigten Dateien vorhanden sind.');
        } else {
            $templateChanged = true;
            
            // Remove old template CSS if it exists
            if ($currentTemplate !== 'default_theme') {
                TemplateManager::removeTemplateCSS($currentTemplate);
            }
            
            // Set new template
            $this->setConfig('mblock_theme', $config['mblock_theme']);
            
            // Handle CSS for new template
            if ($config['mblock_theme'] === 'default_theme') {
                // Clean up all template CSS files when switching to default
                $removedCount = TemplateManager::cleanupAllTemplateCSS();
                if ($removedCount > 0) {
                    $form .= rex_view::info('Template-CSS Dateien wurden aufgeräumt (' . $removedCount . ' Dateien entfernt).');
                }
            } else {
                // Copy new template CSS if it exists (always overwrite)
                if (TemplateManager::hasTemplateCSS($config['mblock_theme'])) {
                    if (TemplateManager::copyTemplateCSS($config['mblock_theme'])) {
                        $form .= rex_view::success(rex_i18n::msg('mblock_theme_css_copied'));
                    } else {
                        $form .= rex_view::warning('Template-CSS konnte nicht kopiert werden.');
                    }
                }
            }
        }
    }
    
    // show is saved field
    $this->setConfig('mblock_delete', $config['mblock_delete']);
    $this->setConfig('mblock_scroll', $config['mblock_scroll']);
    $this->setConfig('mblock_delete_confirm', $config['mblock_delete_confirm']);
    $this->setConfig('mblock_copy_paste', $config['mblock_copy_paste']);
    
    $saveMessage = $templateChanged ? rex_i18n::msg('mblock_theme_saved') : rex_i18n::msg('mblock_config_saved');
    $form .= rex_view::info($saveMessage);
}

// open form
$form .= '
  <form action="' . rex_url::currentBackendPage() . '" method="post">
    <fieldset><legend class="middle">' . rex_i18n::msg('mblock_defaults') . '</legend>
';

// label
$formElements = array();
$elements = array();
$elements['label'] = '
  <label for="rex-mblock-config-scroll-label">' . rex_i18n::msg('mblock_scroll_label') . '</label>
';
// create select
$select = new rex_select;
$select->setId('rex-mblock-config-scroll-label');
$select->setSize(1);
$select->setAttribute('class', 'form-control');
$select->setName('config[mblock_scroll]');
// add options
$select->addOption(rex_i18n::msg('mblock_not_scroll'), 0);
$select->addOption(rex_i18n::msg('mblock_scroll'), 1);
$select->setSelected($this->getConfig('mblock_scroll'));
$elements['field'] = $select->get();
$formElements[] = $elements;
// parse select element
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$form .= $fragment->parse('core/form/form.php');

// label
$formElements = array();
$elements = array();
$elements['label'] = '
  <label for="rex-mblock-config-delete-confirm">' . rex_i18n::msg('mblock_delete_confirm_label') . '</label>
';
// create select
$select = new rex_select;
$select->setId('rex-mblock-config-delete-confirm');
$select->setSize(1);
$select->setAttribute('class', 'form-control');
$select->setName('config[mblock_delete_confirm]');
// add options
$select->addOption(rex_i18n::msg('mblock_not_delete_confirm'), 0);
$select->addOption(rex_i18n::msg('mblock_ok_delete_confirm'), 1);
$select->setSelected($this->getConfig('mblock_delete_confirm'));
$elements['field'] = $select->get();
$formElements[] = $elements;
// parse select element
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$form .= $fragment->parse('core/form/form.php');

// Copy/Paste configuration
$formElements = array();
$elements = array();
$elements['label'] = '
  <label for="rex-mblock-config-copy-paste">' . rex_i18n::msg('mblock_copy_paste') . '</label>
';
// create select
$select = new rex_select;
$select->setId('rex-mblock-config-copy-paste');
$select->setSize(1);
$select->setAttribute('class', 'form-control');
$select->setName('config[mblock_copy_paste]');
// add options
$select->addOption(rex_i18n::msg('mblock_disabled'), 0);
$select->addOption(rex_i18n::msg('mblock_enabled'), 1);
$select->setSelected($this->getConfig('mblock_copy_paste', 1)); // Default: enabled
$elements['field'] = $select->get();
$formElements[] = $elements;
// parse select element
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$form .= $fragment->parse('core/form/form.php');

// Template selection
$formElements = array();
$elements = array();
$elements['label'] = '
  <label for="rex-mblock-config-template">' . rex_i18n::msg('mblock_theme_label') . '</label>
';

// Get available templates
$availableTemplates = TemplateManager::getAvailableTemplates();

// create select
$select = new rex_select;
$select->setId('rex-mblock-config-template');
$select->setSize(1);
$select->setAttribute('class', 'form-control');
$select->setName('config[mblock_theme]');

// add options
foreach ($availableTemplates as $key => $label) {
    $select->addOption($label, $key);
}
$select->setSelected($this->getConfig('mblock_theme', 'default_theme'));

$elements['field'] = $select->get();
$elements['note'] = '<div class="help-block small">' . rex_i18n::msg('mblock_theme_info') . '</div>';
$formElements[] = $elements;

// parse select element
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$form .= $fragment->parse('core/form/form.php');

// create submit button
$formElements = array();
$elements = array();
$elements['field'] = '
  <input type="submit" class="btn btn-save rex-form-aligned" name="config[submit]" value="' . rex_i18n::msg('mblock_config_save') . '" ' . rex::getAccesskey(rex_i18n::msg('mblock_config_save'), 'save') . ' />
';
$formElements[] = $elements;

// parse submit element
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$form .= $fragment->parse('core/form/submit.php');

// close form
$form .= '
    </fieldset>
  </form>
';

//////////////////////////////////////////////////////////
// parse form fragment
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('mblock_config'));
$fragment->setVar('body', $form, false);
echo $fragment->parse('core/page/section.php');
