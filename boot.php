<?php

if (rex_addon::get('assets')->isAvailable()) {
    rex_extension::register('BE_ASSETS', function ($ep) {
        $Subject = $ep->getSubject() ? $ep->getSubject() : [];
        $Subject[$this->getPackageId()] = [
            'files' => [
                $this->getPath('assets/styles.less'),
                $this->getPath('assets/slice_skin.js'),
            ],
            'addon' => $this->getPackageId(),
        ];
        return $Subject;
    }, rex_extension::EARLY);
} elseif (rex::isBackend()) {
    rex_view::addCssFile($this->getAssetsUrl('styles.less.min.css'));
    rex_view::addJsFile($this->getAssetsUrl('slice_skin.jsmin.min.js'));
    rex_view::addJsFile($this->getAssetsUrl('jquery.fn.sortable.min.js'));
    rex_view::addJsFile($this->getAssetsUrl('jblock.js'));
    rex_view::addCssFile($this->getAssetsUrl('jblock.css'));
}

if (rex::isBackend() && is_object(rex::getUser()))
    rex_perm::register('slice_ui[json]', null, rex_perm::OPTIONS);

