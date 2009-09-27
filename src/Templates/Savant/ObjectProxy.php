<?php

namespace pear2\Templates\Savant;
class ObjectProxy
{
    protected $object;
    
    protected $savant;
    
    function __construct($object, $savant)
    {
        $this->object = $object;
        $this->savant = $savant;
    }
    
    function __get($var)
    {
        return $this->savant->escape($this->object->$var);
    }
    
    function __set($var, $value)
    {
        
    }
    
    function __call($method, $args)
    {
        return $this->object->$method($args);
    }
    
    function __getClass()
    {
        return get_class($this->object);
    }
}