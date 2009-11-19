<?php
namespace pear2\Templates\Savant\ObjectProxy;
use pear2\Templates\Savant\ObjectProxy;
class Traversable extends ObjectProxy implements \IteratorAggregate
{
    function getIterator()
    {
        return $this->object;
    }
}