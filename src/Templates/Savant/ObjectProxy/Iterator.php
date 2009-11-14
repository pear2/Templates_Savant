<?php
namespace pear2\Templates\Savant\ObjectProxy;
use pear2\Templates\Savant\ObjectProxy;
class Iterator extends ObjectProxy implements \Iterator
{
    function current()
    {
        return $this->object->current();
    }
    
    function next()
    {
        return $this->object->next();
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
        return $this->object->rewind();
    }
}