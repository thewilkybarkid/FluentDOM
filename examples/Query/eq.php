<?php
/**
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2009-2017 FluentDOM Contributors
*/
header('Content-type: text/plain');
require_once('../../vendor/autoload.php');

$html = <<<HTML
<html>
  <head>
    <title>Examples: FluentDOM\Query::eq()</title>
  </head>
  <body>
    <div/>
    <div/>
    <div/>
    <div/>
    <div/>
    <div/>
  </body>
</html>
HTML;

echo FluentDOM($html, 'text/html')
  ->find('//div')
  ->eq(2)
  ->addClass('emphased');
