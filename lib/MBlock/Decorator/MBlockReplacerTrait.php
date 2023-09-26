<?php
/**
 * User: joachimdoerr
 * Date: 31.05.18
 * Time: 15:07.
 */

namespace MBlock\Decorator;

use DOMDocument;
use DOMElement;
use IntlChar;

use function count;

trait MBlockDOMTrait
{
    /**
     * @return DOMDocument
     * @author Joachim Doerr
     */
    private static function createDom($html)
    {
        $dom = new DOMDocument();
        // replaces $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $html = preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', static function ($match) {
            $utf8 = $match[0];
            return '&#' . IntlChar::ord($utf8) . ';';
        }, htmlentities($html, ENT_COMPAT, 'UTF-8'));
        $html = htmlspecialchars_decode($html, ENT_QUOTES);
        @$dom->loadHTML("<html xmlns=\"http://www.w3.org/1999/xhtml\"><body>$html</body></html>");
        $dom->preserveWhiteSpace = false;
        return $dom;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    private static function saveHtml(DOMDocument $dom)
    {
        $html = $dom->saveHTML();
        if (str_contains($html, '<body')) {
            preg_match('/<body>(.*)<\\/body>/ism', $html, $matches);
            if (isset($matches[1])) {
                $html = $matches[1];
            }
        }
        return $html;
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    private static function getElementsByClass(DOMDocument $dom, $element)
    {
        $elementClass = explode('.', $element);
        $element = $elementClass[0];
        $class = $elementClass[1];
        $nodeList = [];
        $elements = $dom->getElementsByTagName($element);
        if (count($elements) > 0) {
            /** @var DOMElement $element */
            foreach ($elements as $element) {
                if (str_contains($element->getAttribute('class'), $class)) {
                    $nodeList[] = $element;
                }
            }
        }
        return $nodeList;
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    private static function getElementsByData(DOMDocument $dom, $element)
    {
        preg_match('/^.(\[.*?\])$/m', $element, $matches);
        $element = str_replace($matches[1], '', $matches[0]);
        $data = str_replace(['[', ']', '"'], '', $matches[1]);
        $data = explode('=', $data);
        $nodeList = [];
        $elements = $dom->getElementsByTagName($element);
        if (count($elements) > 0) {
            /** @var DOMElement $element */
            foreach ($elements as $element) {
                if ($element->hasAttribute($data[0]) && $element->getAttribute($data[0]) == $data[1]) {
                    $nodeList[] = $element;
                }
            }
        }
        return $nodeList;
    }
}
