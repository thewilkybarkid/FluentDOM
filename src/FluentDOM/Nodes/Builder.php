<?php
/**
 * Create list of nodes for a FluentDOM\Nodes object from different values
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2017 FluentDOM Contributors
 */

namespace FluentDOM\Nodes {

  use FluentDOM\Exceptions;
  use FluentDOM\Nodes;
  use FluentDOM\Utility\Constraints;

  /**
    * Create list of nodes for a FluentDOM\Nodes object from different values
    */
  class Builder {

    /**
     * @var Nodes
     */
    private $_nodes;

    /**
     * @param Nodes $nodes
     */
    public function __construct(Nodes $nodes) {
      $this->_nodes = $nodes;
    }

    /**
     * @return Nodes
     */
    public function getOwner(): Nodes {
      return $this->_nodes;
    }

    /**
     * @param mixed $content
     * @param bool $includeTextNodes
     * @param int $limit
     * @return array|\Traversable|NULL
     * @throws \InvalidArgumentException
     */
    private function getNodeList(
      $content,
      bool $includeTextNodes = TRUE,
      int $limit = -1
    ) {
      if ($callback = Constraints::filterCallable($content)) {
        $content = $callback();
      }
      if ($content instanceof \DOMElement) {
        return [$content];
      } elseif ($includeTextNodes && Constraints::filterNode($content)) {
        return [$content];
      } elseif (Constraints::filterNodeList($content)) {
        return $this->getLimitedArray($content, $limit);
      }
      return NULL;
    }

    /**
     * Match selector against context and return matched elements.
     *
     * @param mixed $selector
     * @param \DOMNode $context optional, default value NULL
     * @throws \InvalidArgumentException
     * @return array
     */
    public function getTargetNodes($selector, \DOMNode $context = NULL) {
      if ($nodes = $this->getNodeList($selector)) {
        return $nodes;
      } elseif (is_string($selector)) {
        $result = $this->getOwner()->xpath(
          $this->getOwner()->prepareSelector(
            $selector,
            Nodes::CONTEXT_SELF
          ),
          $context
        );
        if (!($result instanceof \Traversable)) {
          throw new \InvalidArgumentException('Given selector did not return an node list.');
        }
        return iterator_to_array($result);
      }
      throw new \InvalidArgumentException('Invalid selector');
    }

    /**
     * Convert a given content into and array of nodes
     *
     * @param mixed $content
     * @param bool $includeTextNodes
     * @param int $limit
     * @throws Exceptions\LoadingError\EmptyResult
     * @return array
     */
    public function getContentNodes($content, bool $includeTextNodes = TRUE, int $limit = -1) {
      $result = FALSE;
      if ($nodes = $this->getNodeList($content, $includeTextNodes, $limit)) {
        $result = $nodes;
      } elseif (is_string($content)) {
        $result = $this->getFragment($content, $this->getOwner()->contentType, $includeTextNodes, $limit);
      }
      if (!is_array($result) || empty($result)) {
        throw new Exceptions\LoadingError\EmptyResult();
      } else {
        //if a node is not in the current document import it
        $document = $this->getOwner()->getDocument();
        foreach ($result as $index => $node) {
          if ($node->ownerDocument !== $document) {
            $result[$index] = $document->importNode($node, TRUE);
          }
        }
      }
      return $result;
    }

    /**
     * Convert $content to a DOMElement. If $content contains several elements use the first.
     *
     * @param mixed $content
     * @return \DOMElement
     */
    public function getContentElement($content): \DOMElement {
      $contentNodes = $this->getContentNodes($content, FALSE, 1);
      return $contentNodes[0];
    }

    /**
     * Convert a given content string into and array of nodes
     *
     * @param mixed $xml
     * @param string $contentType
     * @param bool $includeTextNodes
     * @param int $limit
     * @throws Exceptions\InvalidFragmentLoader
     * @return array
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function getFragment(
      $xml, $contentType = 'text/xml', bool $includeTextNodes = TRUE, int $limit = -1
    ): array {
      $xml = $this->getContentAsString($xml);
      $loader = $this->getOwner()->loaders();
      if (!$loader->supports($contentType)) {
        throw new Exceptions\InvalidFragmentLoader(get_class($loader));
      }
      if (!$xml) {
        return [];
      }
      $result = [];
      $fragment = $loader->loadFragment(
        $xml, $contentType, $this->getOwner()->getLoadingOptions($contentType)
      );
      if ($fragment) {
        $fragment = $this->getOwner()->document->importNode($fragment, TRUE);
        for ($i = $fragment->childNodes->length - 1; $i >= 0; $i--) {
          $element = $fragment->childNodes->item($i);
          if ($element instanceof \DOMElement ||
            ($includeTextNodes && Constraints::filterNode($element))) {
            array_unshift($result, $element);
            $element->parentNode->removeChild($element);
          }
        }
      }
      return $this->getLimitedArray($result, $limit);
    }

    /**
     * @param mixed $content
     * @return string|bool
     * @throws \UnexpectedValueException
     */
    private function getContentAsString($content) {
      if (is_scalar($content) || method_exists($content, '__toString')) {
        $content = (string)$content;
        return ($content === '') ? FALSE : $content;
      }
      throw new Exceptions\LoadingError\EmptySource();
    }

    /**
     * Get the inner xml of a given node or in other words the xml of all children.
     *
     * @param \DOMNode $context
     * @return string
     */
    public function getInnerXml(\DOMNode $context): string {
      $result = '';
      $document = $this->getOwner()->getDocument();
      $nodes = $this->getOwner()->xpath(
        '*|text()[normalize-space(.) != ""]|self::text()[normalize-space(.) != ""]',
        $context
      );
      foreach ($nodes as $child) {
        $result .= $document->saveXML($child);
      }
      return $result;
    }

    /**
     * Get the inner and outer wrapper nodes. Simple means that they are the
     * same nodes.
     *
     * @param \DOMElement $template
     * @param bool $simple
     * @return \DOMElement[]
     */
    public function getWrapperNodes(\DOMElement $template, bool &$simple) {
      $wrapper = $template->cloneNode(TRUE);
      $targets = NULL;
      $target = NULL;
      if (!$simple) {
        // get the first element without child elements.
        $targets = $this->getOwner()->xpath('.//*[count(*) = 0]', $wrapper);
      }
      if ($simple || $targets->length === 0) {
        $target = $wrapper;
        $simple = TRUE;
      } elseif ($targets instanceof \DOMNodeList) {
        $target = $targets->item(0);
      }
      return [$target, $wrapper];
    }

    /**
     * @param array|\Traversable $nodes
     * @param int $limit
     * @return array
     */
    private function getLimitedArray($nodes, int $limit = -1): array {
      if ($limit > 0) {
        if (is_array($nodes)) {
          return array_slice($nodes, 0, $limit);
        } else {
          return iterator_to_array(
            new \LimitIterator(
              new \IteratorIterator($nodes), 0, $limit
            ),
            FALSE
          );
        }
      }
      return is_array($nodes) ? $nodes : iterator_to_array($nodes, FALSE);
    }
  }
}