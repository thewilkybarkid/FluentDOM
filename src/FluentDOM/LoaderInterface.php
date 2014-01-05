<?php
/**
 * FluentDOM\LoaderInterface describes an interface for loader objects that can be used to load
 * a data source into a DOM document.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM {

  /**
   * FluentDOM\LoaderInterface describes an interface for loader objects that can be used to load
   * a data source into a DOM document.
   *
   * The class can be attached to a FluentDOM\Query objects using the FluentDOM\Loaders class.
   */
  interface LoaderInterface {

    /**
     * Validate if the loader supports the given content type
     *
     * @param $contentType
     * @return boolean
     */
    function supports($contentType);

    /**
     * Load the data source and return the new DOM document. Return NULL if
     * the data source could not be loaded.
     *
     * @param mixed $source
     * @param string $contentType
     * @return NULL|\DOMDocument
     */
    function load($source, $contentType);
  }
}