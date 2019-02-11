<div class="sortitem" data-mblock-iterate-index="<?= $this->iterate_index; ?>" data-default-count="<?= $this->iterate_index-1; ?>">
    <span class="sorthandle"></span>
    <span class="removeadded">
      <div class="btn-group btn-group-xs">
         <button type="button" class="btn btn-default addme" title="duplicate"><i class="rex-icon rex-icon-add-module"></i></button>
         <button type="button" class="btn btn-delete removeme" title="delete"><i class="rex-icon rex-icon-delete"></i></button>
      </div>
      <div class="btn-group btn-group-xs">
         <button type="button" class="btn btn-move moveup" title="move up"><i class="rex-icon rex-icon-up"></i></button>
         <button type="button" class="btn btn-move movedown" title="move down"><i class="rex-icon rex-icon-down"></i></button>
      </div>
    </span>
    <div class="mblock-sortitem-form"><?= $this->form; ?></div>
</div>