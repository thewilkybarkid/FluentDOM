<?php
/**
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2009-2017 FluentDOM Contributors
*/
require __DIR__.'/../../vendor/autoload.php';

header('Content-type: text/plain');

$html = <<<HTML
<html>
  <head>
    <title>Examples: FluentDOM\Query::parent()</title>
  </head>
  <body>
    <div>div,
      <span>span, </span>
      <b>b </b>
    </div>
    <p>p,
      <span>span,
        <em>em </em>
      </span>
    </p>
    <div>div,
      <strong>strong,
        <span>span, </span>
        <em>em,
          <b>b, </b>
        </em>
      </strong>
      <b>b </b>
    </div>
  </body>
</html>
HTML;

echo FluentDOM($html)
  ->find('//body//*')
  ->each('callback');


function callback($node) {
  $fluentNode = FluentDOM($node);
  $fluentNode->prepend(
    $fluentNode->document->createTextNode(
      $fluentNode->parent()->item(0)->tagName.' > '
    )
  );
}
