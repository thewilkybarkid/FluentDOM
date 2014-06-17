<?php
/**
 * Cast a DOMNode into a string
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM\Node {

  /**
   * Cast a DOMNode into a string
   */
  trait StringCast {

    /**
     * Casting the element node to string will returns its node value
     *
     * @return string
     */
    public function __toString() {
      return $this->nodeValue;
    }
  }
}