<?php
/**
 * FluentDOM
 *
 * @link https://thomas.weinert.info/FluentDOM/
 * @copyright Copyright 2009-2018 FluentDOM Contributors
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 */

namespace FluentDOM\DOM\Node {

  use FluentDOM\DOM\Document;
  use FluentDOM\DOM\Node;

  /**
   * Add a `evaluate()` method to execute an Xpath expression in the context of the node and
   * make the node a functor.
   *
   * @property Document $ownerDocument
   */
  trait Xpath {

    /**
     * Evaluate an xpath expression in the context of this
     * element.
     *
     * @param string $expression
     * @param \DOMNode $context
     * @return string|float|\DOMNodeList|Node[]
     */
    public function evaluate(string $expression, \DOMNode $context = NULL) {
      /** @var \DOMNode|Document $this */
      $document = $this instanceof Document
        ? $this
        : $this->ownerDocument;
      return $document->xpath()->evaluate(
        $expression, $context ?? $this
      );
    }

    /**
     * Allow to call evaluate() by using the node as object
     *
     * @param string $expression
     * @return string|float|\DOMNodeList|Node[]
     */
    public function __invoke(string $expression) {
      return $this->evaluate(
        $expression, $this instanceof \DOMNode ? $this : NULL
      );
    }
  }
}
