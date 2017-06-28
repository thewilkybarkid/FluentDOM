<?php
require('../../../vendor/autoload.php');

$document = new FluentDOM\Document();
$document->loadXml(
  '<div>
    <ul>
      <li><a href="http://fluentdom.org">FluentDOM</a></li>
      <li><a href="http://www.php.net">PHP</a></li>
    </ul>
  </div>'
);

$_ = FluentDOM::create();
$_->formatOutput = TRUE;

echo $_('p', $document('//a'));
