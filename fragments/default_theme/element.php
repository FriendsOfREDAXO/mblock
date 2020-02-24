<div class="sortitem" data-mblock-iterate-index="<?= $this->iterate_index; ?>" data-default-count="<?= $this->iterate_index-1; ?>">
    <span class="sorthandle"></span>
    <span class="removeadded">
        <span class="btn-default addme" title="duplicate"><i class="rex-icon rex-icon-add-module"></i></span>
        <span class="btn-duplicate copyme" title="copy"><i class="rex-icon fa-copy"></i></span>
        <span class="btn-delete removeme" title="delete"><i class="rex-icon rex-icon-delete"></i></span>
        <span class="btn-visibility visibility" title="visibility"><i class="rex-icon rex-icon-visible"></i></span>
        <span class="btn-move moveup" title="move up"><i class="rex-icon rex-icon-up"></i></span>
        <span class="btn-move movedown" title="move down"><i class="rex-icon rex-icon-down"></i></span>
    </span>
    <div class="mblock-sortitem-form"><input type="hidden" name="%%%_mblock_visibility_status_%%%" value="1"><?= $this->form; ?></div>
</div>