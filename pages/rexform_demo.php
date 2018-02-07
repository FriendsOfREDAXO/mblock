<?php

// include info page
include rex_path::addon('mblock', 'pages/info.php');


$func = rex_request::request('func', 'string');
$id = rex_request::request('id', 'int');
$start = rex_request::request('start', 'int', NULL);

$table = rex::getTablePrefix() . "sked_venues";
$message = '';

if ($func == '') {

    // create group and select by clang
    $group = array(40);
    $select = array('id');
    foreach (rex_clang::getAll() as $clang) {
        $group[] = '*';
        $select[] = 'name_' . $clang->getId();
    }
    // merge select with default
    $select = array_merge($select, array('status'));

    // instance list
    $list = rex_list::factory("SELECT * FROM rex_mblock_rexform_demo ORDER BY id");
    $list->addTableAttribute('class', 'table-striped');

    // merge group with default
    $group = array_merge($group, array(100, 200));

    $list->addTableColumnGroup($group);

    // Hide columns
    $list->removeColumn('id');

    // Column 1: Action (add/edit button)
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="'.rex_i18n::msg('sked_add_venue').'"><i class="rex-icon rex-icon-add-action"></i></a>';
    $tdIcon = '<i class="rex-icon fa-map-marker"></i>';

    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);


    // show
    $content = $list->get();
    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('sked_venues_title'));
    $fragment->setVar('content', $message . $content, false);
    echo $fragment->parse('core/page/section.php');

} elseif ($func == 'edit' || $func == 'add') {

    $id = rex_request('id', 'int');
    $form = mblock_rex_form::factory('rex_mblock_rexform_demo', '', 'id=' . $id);
    $form->addParam('start', $start);
    if ($func == 'edit') $form->addParam('id', $id);

    // add text.
    $field = $form->addTextField('name');
    $field->setLabel('name');

    $nf = mblock_rex_form::factory('rex_mblock_rexform_demo', '', 'id=' . $id);

    $element1 = $nf->addTextField('mblock_field][attr_type][0][test');
    $element1->setLabel('test11');
    $element2 = $nf->addTextField('mblock_field][attr_type][0][test2');
    $element2->setLabel('test11');


    $form->addRawField(mblock::show('rex_mblock_rexform_demo::mblock_field::attr_type', $nf->getElements()));

    // show
    $content = $form->get();
    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', ($func == 'edit') ? rex_i18n::msg('sked_venue_edit') : rex_i18n::msg('sked_venue_add'));
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}
