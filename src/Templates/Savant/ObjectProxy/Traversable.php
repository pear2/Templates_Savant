<?php
namespace pear2\Templates\Savant\ObjectProxy;
use pear2\Templates\Savant\ObjectProxy;
class Traversable extends ObjectProxy implements \Iterator
{

    function next()
    {
        $this->object->next();
    }

    function key()
    {
        return $this->object->key();
    }

    function valid()
    {
        return $this->object->valid();
    }

    function rewind()
    {
        $this->object->rewind();
    }

    function current()
    {
        return $this->filterVar($this->object->current());
    }
}