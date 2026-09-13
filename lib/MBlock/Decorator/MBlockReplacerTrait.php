<?php
/**
 * User: joachimdoerr
 * Date: 31.05.18
 * Time: 15:07
 */

namespace FriendsOfRedaxo\MBlock\Decorator;

use DOMDocument;
use DOMElement;

trait MBlockReplacerTrait
{
    /**
     * @param mixed $html HTML-String oder Objekt mit __toString()/show()
     * @return DOMDocument
     * @author Joachim Doerr
     */
    private static function createDom($html)
    {
        $dom = new DOMDocument();
        
        // Handle case where $html is an object (like MForm) instead of a string
        if (is_object($html)) {
            if (method_exists($html, '__toString')) {
                $html = (string) $html;
            } elseif (method_exists($html, 'show')) {
                // Try to get HTML from MForm, but catch any errors
                try {
                    $html = $html->show();
                } catch (\Throwable $e) {
                    throw new \InvalidArgumentException('MBlock: Could not render MForm object. Error: ' . $e->getMessage() . '. Please ensure MForm compatibility with MBlock.');
                }
            } else {
                throw new \InvalidArgumentException('MBlock: HTML parameter must be a string or an object with __toString() or show() method. Got: ' . get_class($html));
            }
        }
        
        if (!is_string($html)) {
            $html = (string) $html;
        }
        
        //replaces $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $html = preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function ($match) {
            $utf8 = $match[0];
            return '&#' . \IntlChar::ord($utf8) . ';';
        }, htmlentities($html, ENT_COMPAT, 'UTF-8'));
        $html = htmlspecialchars_decode((string) $html, ENT_QUOTES);
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
        $html = (string) $dom->saveHTML();
        if (strpos($html, '<body') !== false) {
            preg_match("/<body>(.*)<\/body>/ism", $html, $matches);
            if (isset($matches[1])) {
                $html = $matches[1];
            }
        }
        return $html;
    }

    /**
     * @param string $element Tag und Klasse, z. B. "div.form-group"
     * @return list<DOMElement>
     * @author Joachim Doerr
     */
    private static function getElementsByClass(DOMDocument $dom, string $element): array
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
     * @param string $element Tag und Data-Attribut, z. B. 'input[data-x="y"]'
     * @return list<DOMElement>
     * @author Joachim Doerr
     */
    private static function getElementsByData(DOMDocument $dom, string $element): array
    {
        if (!preg_match('/^.(\[.*?\])$/m', $element, $matches)) {
            return [];
        }
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
