<?php
/**
 * Serialize a DOM to BadgerFish Json: http://badgerfish.ning.com/
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM\Serializer\Json {

  use FluentDOM\Serializer\Json;
  use FluentDOM\XPath;

  /**
   * Serialize a DOM to BadgerFish Json: http://badgerfish.ning.com/
   *
   * @license http://www.opensource.org/licenses/mit-license.php The MIT License
   * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
   */
  class BadgerFish extends Json {

    /**
     * @return \stdClass|NULL
     */
    public function jsonSerialize() {
      $result = new \stdClass();
      if (isset($this->_document->documentElement)) {
        $result->{$this->_document->documentElement->nodeName} =
          $this->getNodes($this->_document->documentElement);
        return $result;
      }
      return NULL;
    }

    /**
     * @param \DOMElement $node
     * @return \stdClass
     */
    private function getNodes(\DOMElement $node) {
      $result = new \stdClass();
      $xpath = new XPath($node->ownerDocument);
      foreach ($xpath->evaluate('*', $node) as $childNode) {
        $this->addElement($result, $childNode);
      }
      foreach ($xpath->evaluate('text()', $node) as $childNode) {
        $this->addText($result, $childNode);
      }
      $this->addAttributes($result, $node, $xpath);
      $this->addNamespaces($result, $node, $xpath);
      return $result;
    }

    /**
     * @param \stdClass $target
     * @param \DOMElement $node
     * @param XPath $xpath
     */
    private function addAttributes(\stdClass $target, \DOMElement $node, Xpath $xpath) {
      foreach ($xpath->evaluate('@*', $node) as $attribute) {
        $target->{'@'.$attribute->name} = $attribute->value;
      }
    }

    /**
     * @param \stdClass $target
     * @param \DOMElement $node
     * @param XPath $xpath
     */
    private function addNamespaces(\stdClass $target, \DOMElement $node, Xpath $xpath) {
      if ($node->namespaceURI != '' && $node->prefix == '') {
        if (!isset($target->{'@xmlns'})) {
          $target->{'@xmlns'} = new \stdClass();
        }
        $target->{'@xmlns'}->{'$'} = $node->namespaceURI;
      }
      foreach ($xpath->evaluate('namespace::*', $node) as $namespace) {
        if ($namespace->localName == 'xml' || $namespace->localName == 'xmlns') {
          continue;
        }
        if (!isset($target->{'@xmlns'})) {
          $target->{'@xmlns'} = new \stdClass();
        }
        if ($namespace->nodeName !== 'xmlns') {
          $target->{'@xmlns'}->{$namespace->localName} = $namespace->namespaceURI;
        }
      }
    }

    /**
     * @param \stdClass $target
     * @param \DOMElement $node
     */
    private function addElement(\stdClass $target, \DOMElement $node) {
      $nodeName = $node->nodeName;
      if (isset($target->$nodeName)) {
        if (!is_array($target->$nodeName)) {
          $target->{$nodeName} = [$target->{$nodeName}];
        }
        array_push($target->$nodeName, $this->getNodes($node));
      } else {
        $target->$nodeName = $this->getNodes($node);
      }
    }

    /**
     * @param \stdClass $target
     * @param \DOMNode $node
     */
    private function addText(\stdClass $target, \DOMNode $node) {
      if (!$node->isWhitespaceInElementContent()) {
        if (isset($target->{'$'})) {
          $target->{'$'} .= $node->textContent;
        } else {
          $target->{'$'} = $node->textContent;
        }
      }
    }
  }
}