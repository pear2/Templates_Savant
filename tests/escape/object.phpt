--TEST--
\pear2\Templates\Savant\Main::addEscaoe() object variable escaping test
--FILE--
<?php
require dirname(__FILE__) . '/../test_framework.php.inc';
chdir(__DIR__);
$savant = new \pear2\Templates\Savant\Main();

$savant->addEscape('htmlspecialchars');

class Foo
{
    public $var1;
}

$object = new Foo();
$object->var1  = '<p></p>';

$test->assertEquals(htmlspecialchars($object->var1), $savant->render($object), 'render object with variable escaping');
?>
===DONE===
--EXPECT--
===DONE===