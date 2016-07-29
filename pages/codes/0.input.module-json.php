<fieldset>

  <div class="form-group">
    <label class="col-sm-2 control-label" for="value_1">VALUE 1:</label>
    <div class="col-sm-10">
      <input class="form-control" id="value_1" type="text" name="REX_INPUT_VALUE[1]" value="REX_VALUE[1]">
      <p class="help-block">Lorem Ipsum dolor</p>
    </div>
  </div>
    
  <div class="form-group">
    <label class="col-sm-2 control-label" for="value_2">VALUE 2:</label>
    <div class="col-sm-10">
      <input class="form-control" id="value_2" type="text" name="REX_INPUT_VALUE[2]" value="REX_VALUE[2]">
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label" for="value_3">VALUE 3:</label>
    <div class="col-sm-10 checkbox">
      <label for="value_3">
        <input type="checkbox" id="value_3" name="REX_INPUT_VALUE[3]" value="REX_VALUE[3]"> mehr Information
        <p class="help-block">Lorem Ipsum dolor</p>
      </label>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label" for="value_4">VALUE 4:</label>
    <div class="col-sm-10">
      <textarea class="form-control" id="value_4" name="REX_INPUT_VALUE[4]">REX_VALUE[4]</textarea>
    </div>
  </div>
</fieldset>

<br><br>

<fieldset>
  <legend>JSON-Values</legend>

  <?php $index = 0;foreach('REX_JSON_VALUE[5]' as $key => $data) {?>
  <div class="block" data-json="1">

    <ul class="icons">
      <li class="btn btn-move">
        <i class="rex-icon rex-icon-up"></i>
      </li>
      <li class="btn btn-move">
        <i class="rex-icon rex-icon-down"></i>
      </li>
    </ul>

    <div class="form-group">
      <label class="col-sm-2 control-label" for="json_value_5_<?=$index;?>_title">Titel:</label>
      <div class="col-sm-10">
        <input class="form-control" id="json_value_5_<?=$index;?>_title" type="text" name="REX_INPUT_VALUE[5][<?=$index;?>][title]" value="<?=$data['title'];?>">
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label" for="json_value_5_<?=$index;?>_eins">Eins:</label>
      <div class="col-sm-10">
        <input class="form-control" id="json_value_5_<?=$index;?>_eins" type="text" name="REX_INPUT_VALUE[5][<?=$index;?>][eins]" value="<?=$data['eins'];?>">
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label" for="json_value_5_<?=$index;?>_vier">Vier</label>
      <div class="col-sm-10">
        <?php 
        $Media = new rex_input_mediabutton();
        $Media->setButtonId(($index+1));
        $Media->setAttribute('name','MEDIA['.($index+1).']');
        if($Slice)
          $Media->setValue($Slice->getMedia(($index+1)));
        echo $Media->getHtml();
        ?>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label" for="json_value_5_<?=$index;?>_fuenf">Fünf</label>
      <div class="col-sm-10">
        <textarea class="form-control rredactorEditor-full" id="rredactor_json_value_5_<?=$index;?>_fuenf" name="REX_INPUT_VALUE[5][<?=$index;?>][fuenf]"><?=$data['fuenf'];?></textarea>
      </div>
    </div>

    <ul class="icons">
      <li class="btn btn-add btn-move">
        <i class="rex-icon rex-icon-add"></i>
      </li>
      <li class="btn btn-delete" data-confirm="Diesen Block wirklick entfernen?">
        <i class="rex-icon rex-icon-delete"></i>
      </li>
    </ul>
  </div>
  <?php $index++;}?>
</fieldset>

<fieldset>
  <legend>JSON-Values</legend>

  <?php $index = 0;foreach('REX_JSON_VALUE[5]' as $key => $data) {?>
  <div class="block" data-json="1">

    <ul class="icons">
      <li class="btn btn-move">
        <i class="rex-icon rex-icon-up"></i>
      </li>
      <li class="btn btn-move">
        <i class="rex-icon rex-icon-down"></i>
      </li>
    </ul>

    <div class="form-group">
      <label class="col-sm-2 control-label" for="json_value_5_<?=$index;?>_title">Titel:</label>
      <div class="col-sm-10">
        <input class="form-control" id="json_value_5_<?=$index;?>_title" type="text" name="REX_INPUT_VALUE[5][<?=$index;?>][title]" value="<?=$data['title'];?>">
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label" for="json_value_5_<?=$index;?>_eins">Eins:</label>
      <div class="col-sm-10">
        <input class="form-control" id="json_value_5_<?=$index;?>_eins" type="text" name="REX_INPUT_VALUE[5][<?=$index;?>][eins]" value="<?=$data['eins'];?>">
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label" for="json_value_5_<?=$index;?>_vier">Vier</label>
      <div class="col-sm-10">
        <?php 
        $Media = new rex_input_mediabutton();
        $Media->setButtonId(($index+1));
        $Media->setAttribute('name','MEDIA['.($index+1).']');
        if($Slice)
          $Media->setValue($Slice->getMedia(($index+1)));
        echo $Media->getHtml();
        ?>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label" for="json_value_5_<?=$index;?>_fuenf">Fünf</label>
      <div class="col-sm-10">
        <textarea class="form-control redactorEditor-full" id="redactor_json_value_5_<?=$index;?>_fuenf" name="REX_INPUT_VALUE[5][<?=$index;?>][fuenf]"><?=$data['fuenf'];?></textarea>
      </div>
    </div>

    <ul class="icons">
      <li class="btn btn-add btn-move">
        <i class="rex-icon rex-icon-add"></i>
      </li>
      <li class="btn btn-delete" data-confirm="Diesen Block wirklick entfernen?">
        <i class="rex-icon rex-icon-delete"></i>
      </li>
    </ul>
  </div>
  <?php $index++;}?>
</fieldset>
