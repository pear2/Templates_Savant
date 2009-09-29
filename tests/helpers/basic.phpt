--TEST--
\pear2\Templates\Savant\Main helper
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
use pear2\Templates\Savant;
$savant = new Savant\Main();

$helper = $savant->getHelper('Form');
$test->assertTrue($helper instanceof Savant\Helper\Form, 'getHelper()');

?>
===DONE===
--EXPECT--
===DONE===