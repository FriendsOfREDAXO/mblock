<?php
/**
 * User: joachimdoerr
 * Date: 31.05.18
 * Time: 15:07
 */

namespace MBlock\DOM;


use DOMDocument;
use DOMElement;
use DOMNode;

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
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        @$dom->loadHTML("<html xmlns=\"http://www.w3.org/1999/xhtml\"><body>$html</body></html>", XML_HTML_DOCUMENT_NODE);
        $dom->preserveWhiteSpace = false;
        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param null $node
     * @return string
     * @author Joachim Doerr
     */
    private static function saveHtml(DOMDocument $dom, $node = null)
    {
        $html = $dom->saveHTML($node);
        if (strpos($html, '<body') !== false) {
            preg_match("/<body>(.*)<\/body>/ism", $html, $matches);
            if (isset($matches[1])) {
                $html = $matches[1];
            }
        }
        return $html;
    }

    /**
     * @param DOMNode $dom
     * @param $element
     * @param $class
     * @return array
     * @author Joachim Doerr
     */
    private static function getElementsByClass(DOMNode $dom, $element)
    {
        $elementClass= explode('.', $element);
        $element = $elementClass[0];
        $class = $elementClass[1];
        $nodeList = array();
        if ($dom instanceof DOMElement or $dom instanceof DOMDocument) {
            $elements = $dom->getElementsByTagName($element);
            if (sizeof($elements) > 0) {
                /** @var DOMElement $element */
                foreach ($elements as $element) {
                    if (strpos($element->getAttribute('class'), $class) !== false) {
                        $nodeList[] = $element;
                    }
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

    /**
     * @param \DOMElement $element
     * @return string
     * @author Joachim Doerr
     */
    private static function innerHTML(\DOMElement $element)
    {
        $html = '';
        foreach ($element->childNodes as $node) {
            $html .= self::saveHtml($element->ownerDocument, $node);
        }
        return $html;
    }

    /**
     * @param DOMNode $dom
     * @param $source
     * @return DOMNode
     * @author Joachim Doerr
     */
    private static function appendHtml(DOMNode $dom, $source) {
        $form = self::createDom($source);
        foreach ($form->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $node = $dom->ownerDocument->importNode($node, true);
            $dom->appendChild($node);
        }
        return $dom;
    }
}