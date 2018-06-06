<?php
dump($_POST);

$mform = new MForm();

$mform->addFieldset('Text Inputs');
$mform->addTextField("1.0.test1", array('label'=>'Input Text1')); // use string for x.0 json values
$mform->addTextField("1.0.test2", array('label'=>'Input Text2')); // use string for x.0 json values

$nmform = new MForm();
$nmform->addFieldset('Text Inputs a');
$nmform->addTextField("1.0.0.0.test1a", array('label'=>'Input Text1a')); // use string for x.0 json values
$nmform->addTextField("1.0.0.0.test2a", array('label'=>'Input Text2a')); // use string for x.0 json values

//dump(MBlock::show(false, $nmform->show()));
//dump(MBlock2::show(false, $nmform->show()));
$mform->addHtml(MBlock::show(false, $nmform->show()));
$mform->addTextField("1.0.test3", array('label'=>'Input Text3')); // use string for x.0 json values

echo MBlock::show('1', $mform->show());

// [{"test1":"asd","test2":"das","0":[{"test1a":"1233","test2a":"3211"},{"test1a":"123b3","test2a":"32c11"}]},{"test1":"fefe","test2":"4652","0":[{"test1a":"323","test2a":"113"},{"test1a":"rrr","test2a":"eee"}]},{"test1":"ghaj","test2":"5462","0":[{"test1a":"355633","test2a":"222"},{"test1a":"ffff","test2a":"fff"}]}]
