# MBlock Tutorial: MForm zuerst, HTML danach

Dieses Tutorial zeigt beide Wege fuer MBlock in einem Dokument:

1. MForm-Modul (empfohlen fuer neue Module)
2. Klassisches HTML-Modul (ohne MForm)

## Ziel

Du baust schrittweise:

1. MForm-Variante mit Link/Media und sicherer Ausgabe
2. MForm-9-spezifische Hinweise fuer MBlock
3. HTML-Variante mit denselben Feldern
4. optionale Erweiterungen

---

## Teil 1: MForm-Tutorial (empfohlen)

### Schritt 1: Moduleingabe mit MForm

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;
use FriendsOfRedaxo\MForm;

$id = 1;

// Bei MForm >= 9 in klassischen MBlock-Modulen aktivieren,
// damit Link/Media-Widgets robust geklont werden koennen.
MForm::useCustomLinkForClassicWidgets(true);

$mform = MForm::factory()
    ->addFieldsetArea('Eintrag', MForm::factory()
        ->addTextField("$id.0.title", ['label' => 'Titel'])
        ->addTextAreaField("$id.0.text", ['label' => 'Text'])
        ->addMediaField(1, ['label' => 'Medium'])
        ->addLinkField(1, ['label' => 'Link'])
        ->addLinklistField(1, ['label' => 'Linkliste'])
        ->addMedialistField(1, ['label' => 'Medienliste'])
        ->addHiddenField("$id.0.mblock_offline", '0')
    );

echo MBlock::show($id, $mform->show(), [
    'copy_paste' => true,
]);
```

### Schritt 2: Modulausgabe mit MForm-Daten

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getOnlineDataArray('REX_VALUE[1]');

foreach ($items as $item) {
    $title = (string) ($item['title'] ?? '');
    $text = (string) ($item['text'] ?? '');
    $linkId = (int) ($item['REX_LINK_1'] ?? 0);
    $mediaName = (string) ($item['REX_MEDIA_1'] ?? '');

    echo '<article class="teaser">';

    if ('' !== $title) {
        echo '<h3>' . rex_escape($title) . '</h3>';
    }
    if ('' !== $text) {
        echo '<p>' . nl2br(rex_escape($text)) . '</p>';
    }

    if ($linkId > 0 && ($article = rex_article::get($linkId))) {
        echo '<p><a href="' . rex_escape(rex_getUrl($linkId)) . '">' . rex_escape($article->getName()) . '</a></p>';
    }

    if ('' !== $mediaName && ($media = rex_media::get($mediaName))) {
        echo '<img src="' . rex_escape(rex_url::media($media->getFileName())) . '" alt="' . rex_escape($media->getTitle()) . '">';
    }

    echo '</article>';
}
```

### Schritt 3: Hinweise fuer MForm >= 9

- `useCustomLinkForClassicWidgets(true)` fuer MBlock-Module mit `addMediaField()`/`addLinkField()` setzen.
- Ruecksetzen auf `false` ist im normalen Modul-Input nicht noetig.
- Wenn Toggle-Felder mit `__disabled` im Spiel sind, mit `MBlock::decode()` arbeiten.

---

## Teil 2: HTML-Tutorial (ohne MForm)

### Schritt 1: Minimaler Start

Moduleingabe:

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$id = 1;

$form = <<<HTML
<fieldset>
    <legend>Eintrag</legend>
    <div class="form-group">
        <label>Titel</label>
        <input type="text" name="REX_INPUT_VALUE[$id][0][title]" value="" class="form-control">
    </div>
</fieldset>
HTML;

echo MBlock::show($id, $form);
```

Was passiert:

- `MBlock::show()` rendert den Repeat-Block im Modul-Input.
- Alle Feldwerte werden in `REX_VALUE[1]` als Array gespeichert.

### Schritt 2: Textfelder erweitern

```php
<?php
$form = <<<HTML
<fieldset>
    <legend>Eintrag</legend>

    <div class="form-group">
        <label>Titel</label>
        <input type="text" name="REX_INPUT_VALUE[$id][0][title]" value="" class="form-control">
    </div>

    <div class="form-group">
        <label>Untertitel</label>
        <input type="text" name="REX_INPUT_VALUE[$id][0][subtitle]" value="" class="form-control">
    </div>

    <div class="form-group">
        <label>Text</label>
        <textarea name="REX_INPUT_VALUE[$id][0][text]" class="form-control" rows="4"></textarea>
    </div>
</fieldset>
HTML;
```

Wichtig:

- Die Schluessel kommen aus den Namen wie `title`, `subtitle`, `text`.
- In der Ausgabe greifst du spaeter ueber `$item['title']`, `$item['subtitle']` usw. zu.

### Schritt 3: Link, Medium, Linkliste, Medienliste

```php
<?php
$form = <<<HTML
<fieldset>
    <legend>Eintrag</legend>

    <div class="form-group">
        <label>Titel</label>
        <input type="text" name="REX_INPUT_VALUE[$id][0][title]" value="" class="form-control">
    </div>

    <div class="form-group">
        <label>Link</label>
        REX_LINK[id="1" widget="1"]
    </div>

    <div class="form-group">
        <label>Medium</label>
        REX_MEDIA[id="1" widget="1"]
    </div>

    <div class="form-group">
        <label>Linkliste</label>
        REX_LINKLIST[id="1" widget="1"]
    </div>

    <div class="form-group">
        <label>Medienliste</label>
        REX_MEDIALIST[id="1" widget="1"]
    </div>
</fieldset>
HTML;

echo MBlock::show($id, $form, [
    'copy_paste' => true,
]);
```

Die Ausgabe-Schluessel in `$item`:

- `REX_LINK_1`
- `REX_MEDIA_1`
- `REX_LINKLIST_1`
- `REX_MEDIALIST_1`

### Schritt 4: Vollstaendige Modulausgabe

Moduleausgabe:

```php
<?php
use FriendsOfRedaxo\MBlock\MBlock;

$items = MBlock::getOnlineDataArray('REX_VALUE[1]');

foreach ($items as $item) {
    $title = (string) ($item['title'] ?? '');
    $linkId = (int) ($item['REX_LINK_1'] ?? 0);
    $mediaName = (string) ($item['REX_MEDIA_1'] ?? '');
    $linkListRaw = (string) ($item['REX_LINKLIST_1'] ?? '');
    $mediaListRaw = (string) ($item['REX_MEDIALIST_1'] ?? '');

    echo '<article class="teaser">';

    if ('' !== $title) {
        echo '<h3>' . rex_escape($title) . '</h3>';
    }

    if ($linkId > 0 && ($article = rex_article::get($linkId))) {
        echo '<p><a href="' . rex_escape(rex_getUrl($linkId)) . '">'
            . rex_escape($article->getName())
            . '</a></p>';
    }

    if ('' !== $mediaName && ($media = rex_media::get($mediaName))) {
        echo '<img src="' . rex_escape(rex_url::media($media->getFileName())) . '" alt="' . rex_escape($media->getTitle()) . '">';
    }

    $linkIds = array_filter(array_map('trim', explode(',', $linkListRaw)), static fn ($value): bool => '' !== $value);
    if ([] !== $linkIds) {
        echo '<ul>';
        foreach ($linkIds as $rawId) {
            $listId = (int) $rawId;
            if ($listId > 0 && ($listArticle = rex_article::get($listId))) {
                echo '<li><a href="' . rex_escape(rex_getUrl($listId)) . '">' . rex_escape($listArticle->getName()) . '</a></li>';
            }
        }
        echo '</ul>';
    }

    $mediaNames = array_filter(array_map('trim', explode(',', $mediaListRaw)), static fn ($value): bool => '' !== $value);
    if ([] !== $mediaNames) {
        echo '<ul>';
        foreach ($mediaNames as $listMediaName) {
            if ($listMedia = rex_media::get($listMediaName)) {
                echo '<li><a href="' . rex_escape(rex_url::media($listMedia->getFileName())) . '">' . rex_escape($listMedia->getFileName()) . '</a></li>';
            }
        }
        echo '</ul>';
    }

    echo '</article>';
}
```

### Schritt 5: Optional erweitern

```php
<?php
echo MBlock::show($id, $form, [
    'min'            => 1,
    'max'            => 10,
    'copy_paste'     => true,
    'delete_confirm' => 1,
]);
```

Tipps:

- Der Online/Offline-Schalter erscheint automatisch, sobald das Formular ein Hidden-Feld `mblock_offline` enthaelt und die Option in den MBlock-Einstellungen aktiv ist.
- Mit `min` und `max` begrenzt du die Anzahl der Eintraege.
- Fuer reine HTML-Module bleibt MBlock die beste Wahl.

## Weiterfuehrend

- README: `index.php?page=mblock/help`
- API: `index.php?page=mblock/api`
- HTML-Demos: `index.php?page=mblock/demo.demo_html`
- MForm-Demos: `index.php?page=mblock/demo.demo_mform`
