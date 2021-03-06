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

  /**
   * Cast a DOMNode into a string
   *
   * @property string $nodeValue
   */
  trait StringCast {

    /**
     * Casting the element node to string will returns its node value
     *
     * @return string
     */
    public function __toString(): string {
      return ($this instanceof \DOMNode) ? (string)$this->textContent : '';
    }
  }
}
