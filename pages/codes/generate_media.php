<?php
  $index = 5;
  $Slice = OOArticleSlice::getArticleSliceById($this->slice_id);

  $Media = new rex_input_mediabutton();
  $Media->setButtonId($index);
  $Media->setAttribute('name','MEDIA['.$index.']');
  if($Slice)
    $Media->setValue($Slice->getMedia($index));
  echo $Media->getHtml();
?>