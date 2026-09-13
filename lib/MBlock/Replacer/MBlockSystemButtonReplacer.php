<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MBlock\Replacer;

use FriendsOfRedaxo\MBlock\Decorator\MBlockReplacerTrait;
use FriendsOfRedaxo\MBlock\Decorator\MBlockFormItemDecorator;
use FriendsOfRedaxo\MBlock\DTO\MBlockItem;
use FriendsOfRedaxo\MBlock\Utils\MBlockSessionHelper;
use DOMElement;
use rex_article;

/**
 * Schreibt die REDAXO-Kern-Widgets (Media, Medialist, Link, Linklist) und das MForm-Custom-Link
 * in einem Block-Formular um: eindeutige Widget-Ids, Feldnamen im MBlock-Format
 * (REX_INPUT_VALUE[id][0][REX_MEDIA_1]) und die gespeicherten Werte in Select-Listen bzw. Artikelnamen.
 */
class MBlockSystemButtonReplacer
{
    use MBlockReplacerTrait;

    /**
     * Widget-Typen: Input-Name, Praefix der Select-Id, Art der Werte-Ausgabe (media|link),
     * onclick-Funktion, deren numerische Id ersetzt wird, und Sonderfaelle.
     */
    private const WIDGETS = [
        'REX_MEDIA' => ['input' => 'REX_INPUT_MEDIA', 'select' => null, 'options' => null, 'onclick' => 'REXMedia', 'firstChildOnly' => true, 'hiddenOnly' => false, 'artName' => false],
        'REX_MEDIALIST' => ['input' => 'REX_INPUT_MEDIALIST', 'select' => 'REX_MEDIALIST_SELECT_', 'options' => 'media', 'onclick' => 'REXMedialist', 'firstChildOnly' => false, 'hiddenOnly' => false, 'artName' => false],
        'REX_LINK' => ['input' => 'REX_INPUT_LINK', 'select' => null, 'options' => null, 'onclick' => 'REXLink', 'firstChildOnly' => false, 'hiddenOnly' => false, 'artName' => true],
        'REX_LINKLIST' => ['input' => 'REX_INPUT_LINKLIST', 'select' => 'REX_LINKLIST_SELECT_', 'options' => 'link', 'onclick' => 'REXLinklist', 'firstChildOnly' => false, 'hiddenOnly' => true, 'artName' => false],
    ];

    /**
     * Custom-Link (MForm): sichtbares Textfeld mit dem gespeicherten Wert bzw. Artikelnamen fuellen.
     *
     * @return string
     */
    public static function replaceCustomLinkText(MBlockItem $item)
    {
        $dom = self::createDom($item->getForm());
        foreach (self::getElementsByClass($dom, 'div.custom-link') as $match) {
            $value = '';
            foreach ($match->getElementsByTagName('input') as $child) {
                if ('hidden' === $child->getAttribute('type')) {
                    $value = $child->getAttribute('value');
                    break;
                }
            }
            foreach ($match->getElementsByTagName('input') as $child) {
                if ('text' === $child->getAttribute('type')) {
                    $child->setAttribute('value', is_numeric($value) ? self::getLinkInfo($value)['art_name'] : $value);
                    break;
                }
            }
        }

        return self::saveHtml($dom);
    }

    /**
     * @param int $count
     * @return string
     */
    public static function replaceSystemButtons(MBlockItem $item, $count)
    {
        $dom = self::createDom($item->getForm());
        $item->addPayload('count-id', $count);
        foreach (self::getElementsByClass($dom, 'div.input-group') as $key => $match) {
            $item->addPayload('replace-id', $key);
            foreach ($match->getElementsByTagName('input') as $child) {
                $id = $child->getAttribute('id');
                $name = $child->getAttribute('name');
                $type = $child->getAttribute('type');

                if (str_contains($id, 'REX_MEDIA_') && 'text' === $type) {
                    self::processWidget($match, $item, 'REX_MEDIA');
                }
                if (str_contains($id, 'REX_MEDIALIST_')) {
                    self::processWidget($match, $item, 'REX_MEDIALIST');
                }
                if (str_contains($name, 'REX_LINK_') && 'text' === $type) {
                    if (str_contains($match->getAttribute('class'), 'custom-link')) {
                        self::processCustomLink($match, $item);
                    } else {
                        self::processWidget($match, $item, 'REX_LINK');
                    }
                }
                if (str_contains($id, 'REX_LINKLIST_')) {
                    self::processWidget($match, $item, 'REX_LINKLIST');
                }
            }
        }

        return self::saveHtml($dom);
    }

    /**
     * Kern-Widget nach der Tabelle WIDGETS umschreiben.
     */
    protected static function processWidget(DOMElement $group, MBlockItem $item, string $system): void
    {
        $cfg = self::WIDGETS[$system];
        $item->setSystemName($system);
        if (!$group->hasChildNodes()) {
            return;
        }
        $first = $group->firstChild;
        $hiddenName = '';

        if ($cfg['firstChildOnly']) {
            // Media: nur das erste Element (Textfeld) traegt Name und Id
            if ($first instanceof DOMElement) {
                if (str_contains($first->getAttribute('name'), $cfg['input'])) {
                    self::replaceName($first, $item, $cfg['input']);
                }
                self::replaceId($first, $item);
            }
        } else {
            foreach ($group->getElementsByTagName('input') as $child) {
                if ($cfg['hiddenOnly'] && 'hidden' !== $child->getAttribute('type')) {
                    continue;
                }
                if (str_contains($child->getAttribute('name'), $cfg['input'])) {
                    self::replaceName($child, $item, $cfg['input']);
                }
                if ('hidden' === $child->getAttribute('type')) {
                    $hiddenName = $child->getAttribute('name');
                }
                self::replaceId($child, $item);
            }
        }

        if (null !== $cfg['select']) {
            foreach ($group->getElementsByTagName('select') as $child) {
                if (str_contains($child->getAttribute('id'), $cfg['select'])) {
                    self::replaceSelectNameWithItemId($child, $item);
                    self::replaceId($child, $item);
                    self::addSelectOptions('media' === $cfg['options'] && $first instanceof DOMElement ? $first : $child, $item, $hiddenName, 'link' === $cfg['options']);
                }
            }
        }

        if ($cfg['artName'] && $first instanceof DOMElement) {
            $first->removeAttribute('name');
            self::addArtName($first, $item, $hiddenName);
        }

        self::replaceOnClickIds($group, $item, $cfg['onclick']);
    }

    /**
     * MForm-Custom-Link: eigene Id-Vergabe, Name des Hidden-Inputs, Artikelname im Textfeld.
     */
    protected static function processCustomLink(DOMElement $dom, MBlockItem $item): void
    {
        if ($dom->hasAttribute('data-id')) {
            $dom->setAttribute('data-id', self::widgetId($item));
        }
        $item->setSystemName('REX_LINK');
        $id = self::widgetId($item);
        if (!$dom->hasChildNodes()) {
            return;
        }
        foreach ($dom->getElementsByTagName('input') as $child) {
            $name = $child->getAttribute('name');
            if (str_contains($name, 'REX_INPUT_LINK')) {
                self::replaceName($child, $item, 'REX_INPUT_LINK');
            }
            // Media-Kompatibilitaet (MForm::useCustomLinkForClassicWidgets)
            if (str_contains($name, 'REX_INPUT_MEDIA')) {
                self::replaceName($child, $item, 'REX_INPUT_MEDIA');
            }
            $child->setAttribute('id', preg_replace('/\d+/', $id, $child->getAttribute('id')));
        }
        if ($dom->firstChild instanceof DOMElement) {
            $dom->firstChild->removeAttribute('name');
            self::addArtName($dom->firstChild, $item);
        }
        if (($parent = $dom->parentNode) instanceof DOMElement) {
            foreach ($parent->getElementsByTagName('a') as $child) {
                $child->setAttribute('id', preg_replace('/\d+/', $id, $child->getAttribute('id')));
            }
        }
    }

    /**
     * Eindeutige Widget-Id: Block-Nummer, MBlock-Zaehler, "00", Position im Formular.
     */
    private static function widgetId(MBlockItem $item): string
    {
        return $item->getPayload('count-id') . MBlockSessionHelper::getCurrentCount() . '00' . $item->getPayload('replace-id');
    }

    /**
     * Numerische Id im onclick der Widget-Buttons ersetzen: fn('1', ...), fn(1) und openLinkMap('REX_LINK_1', ...).
     */
    protected static function replaceOnClickIds(DOMElement $group, MBlockItem $item, string $function): void
    {
        $id = self::widgetId($item);
        foreach ($group->getElementsByTagName('a') as $child) {
            $onclick = $child->getAttribute('onclick');
            if ('' === $onclick || !str_contains($onclick, $function . '(')) {
                if ('' === $onclick || 'REXLink' !== $function || !str_contains($onclick, 'openLinkMap(')) {
                    continue;
                }
            }
            $onclick = preg_replace('/(' . preg_quote($function, '/') . '\(\'?)\d+(\'?[,)])/', '${1}' . $id . '${2}', $onclick);
            if ('REXLink' === $function) {
                $onclick = preg_replace('/(openLinkMap\(\'REX_LINK_)\d+(\')/', '${1}' . $id . '${2}', $onclick);
            }
            $child->setAttribute('onclick', $onclick);
        }
    }

    /**
     * @return string neue Id
     */
    protected static function replaceId(DOMElement $dom, MBlockItem $item)
    {
        $dom->setAttribute('id', preg_replace('/\_\d+/', '_' . self::widgetId($item), $dom->getAttribute('id')));

        return $dom->getAttribute('id');
    }

    /**
     * Name des Kern-Widgets (REX_INPUT_MEDIA[1]) in das MBlock-Format REX_INPUT_VALUE[id][0][REX_MEDIA_1] umschreiben.
     *
     * @param string $name Input-Name des Kern-Widgets, z. B. REX_INPUT_MEDIA
     */
    protected static function replaceName(DOMElement $dom, MBlockItem $item, $name)
    {
        $matches = MBlockFormItemDecorator::getName($dom);
        if ($matches) {
            $item->setSystemId($matches[1]);
            $replaceName = str_replace('_INPUT', '', $name);
            $dom->setAttribute('name', str_replace(
                [$name, '[' . $item->getSystemId() . ']'],
                ['REX_INPUT_VALUE', '[' . $item->getValueId() . '][0][' . $replaceName . '_' . $item->getSystemId() . ']'],
                $dom->getAttribute('name'),
            ));
        }
    }

    /**
     * Gespeicherter Wert des Widgets aus dem Ergebnis (Schluessel REX_MEDIA_1 oder rex_media_1), sonst null.
     */
    private static function resultValue(MBlockItem $item, string $name): ?string
    {
        self::setSystemIdByName($name, $item);
        $result = $item->getResult();
        if (!is_array($result)) {
            return null;
        }
        foreach ([$item->getSystemName() . '_' . $item->getSystemId(), strtolower($item->getSystemName()) . '_' . $item->getSystemId()] as $key) {
            if (array_key_exists($key, $result)) {
                return (string) $result[$key];
            }
        }

        return null;
    }

    /**
     * Select-Liste (Medialist/Linklist) mit den gespeicherten Werten fuellen; bei Links steht der Artikelname im Text.
     */
    protected static function addSelectOptions(DOMElement $dom, MBlockItem $item, string $name, bool $isLink): void
    {
        $value = self::resultValue($item, $name);
        if (null === $value) {
            return;
        }
        foreach (explode(',', $value) as $resultItem) {
            if ('' !== $resultItem) {
                $dom->appendChild(new DOMElement('option', $resultItem));
            }
        }
        foreach ($dom->childNodes as $child) {
            if ('option' !== $child->nodeName || !$child instanceof DOMElement) { // Patch xampp gegen ooops
                continue;
            }
            $child->setAttribute('value', $child->nodeValue);
            if ($isLink) {
                $child->nodeValue = htmlentities(self::getLinkInfo($child->getAttribute('value'))['art_name']);
            }
            $child->removeAttribute('selected');
        }
    }

    /**
     * Artikelname eines gespeicherten Links in das Textfeld schreiben.
     *
     * @param string $name
     */
    protected static function addArtName(DOMElement $dom, MBlockItem $item, $name = '')
    {
        $value = self::resultValue($item, $name);
        if (null !== $value) {
            $dom->setAttribute('value', self::getLinkInfo($value)['art_name']);
        }
    }

    /**
     * @param string $name
     */
    private static function setSystemIdByName($name, MBlockItem $item): void
    {
        if ('' !== $name && preg_match('/\_\d+/', $name, $matches)) {
            $item->setSystemId(str_replace('_', '', $matches[0]));
        }
    }

    /**
     * System-Identifier im Select-Namen durch die Item-Id ersetzen.
     */
    private static function replaceSelectNameWithItemId(DOMElement $dom, MBlockItem $item): void
    {
        $name = $dom->getAttribute('name');
        $systemId = $item->getSystemId();
        if (null === $systemId || '' === (string) $systemId) {
            $systemId = preg_match('/\d+/', $name, $matches) ? $matches[0] : '';
        }
        if ('' === (string) $systemId) {
            return;
        }
        $dom->setAttribute('name', str_replace((string) $systemId, (string) $item->getId(), $name));
    }

    /**
     * @param int|string $id
     * @return array{art_name: string, category_id: int}
     */
    private static function getLinkInfo($id)
    {
        $art = rex_article::get((int) $id);

        return ['art_name' => $art instanceof rex_article ? $art->getName() : '', 'category_id' => $art instanceof rex_article ? $art->getCategoryId() : 0];
    }
}
