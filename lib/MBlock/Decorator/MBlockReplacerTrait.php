<?php
/**
 * User: joachimdoerr
 * Date: 31.05.18
 * Time: 15:07
 */

namespace MBlock\Decorator;


use DOMDocument;
use DOMElement;

trait MBlockDOMTrait
{
    /**
     * @param $html
     * @return DOMDocument
     * @author Joachim Doerr
     */
    private static function createDom($html)
    {
        $dom = new DOMDocument();
        //replaces $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $html = preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function ($match) {
            $utf8 = $match[0];
            return '&#' . \IntlChar::ord($utf8) . ';';
        }, htmlentities($html, ENT_COMPAT, 'UTF-8'));
        $html = htmlspecialchars_decode($html,ENT_QUOTES);
        @$dom->loadHTML("<html xmlns=\"http://www.w3.org/1999/xhtml\"><body>$html</body></html>");
        $dom->preserveWhiteSpace = false;
        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @return string
     * @author Joachim Doerr
     */
    private static function saveHtml(DOMDocument $dom)
    {
        $html = $dom->saveHTML();
        if (strpos($html, '<body') !== false) {
            preg_match("/<body>(.*)<\/body>/ism", $html, $matches);
            if (isset($matches[1])) {
                $html = $matches[1];
            }
        }
        return $html;
    }

    /**
     * @param DOMDocument $dom
     * @param $element
     * @param $class
     * @return array
     * @author Joachim Doerr
     */
    private static function getElementsByClass(DOMDocument $dom, $element)
    {
        $elementClass= explode('.', $element);
        $element = $elementClass[0];
        $class = $elementClass[1];
        $nodeList = array();
        $elements = $dom->getElementsByTagName($element);
        if (sizeof($elements) > 0) {
            /** @var DOMElement $element */
            foreach ($elements as $element) {
                if (strpos($element->getAttribute('class'), $class) !== false) {
                    $nodeList[] = $element;
                }
            }
        }
        return $nodeList;
    }

    /**
     * @param DOMDocument $dom
     * @param $element
     * @return array
     * @author Joachim Doerr
     */
    private static function getElementsByData(DOMDocument $dom, $element)
    {
        preg_match('/^.(\[.*?\])$/m', $element, $matches);
        $element = str_replace($matches[1], '', $matches[0]);
        $data = str_replace(array('[',']','"'), '', $matches[1]);
        $data = explode('=', $data);
        $nodeList = array();
        $elements = $dom->getElementsByTagName($element);
        if (sizeof($elements) > 0) {
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
