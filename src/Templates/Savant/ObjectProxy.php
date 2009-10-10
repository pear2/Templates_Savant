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
        $var = $this->object->$var;
        if (is_string($var)) {
            return $this->savant->escape($var);
        }
        return $var;
    }
    
    function __raw($var)
    {
        return $this->object->$var;
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