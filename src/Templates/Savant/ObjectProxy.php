<?php
/**
 * pear2\Templates\Savant\ObjectProxy
 *
 * PHP version 5
 *
 * @category  Templates
 * @package   PEAR2_Templates_Savant
 * @author    Brett Bieber <saltybeagle@php.net>
 * @copyright 2009 Brett Bieber
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/repository/pear2/PEAR2_Templates_Savant
 */

/**
 * ObjectProxy class for PEAR2_Templates_Savant
 * 
 * The ObjectProxy acts as an intermediary between an object and a template.
 * The $context variable will be an ObjectProxy which proxies member variable
 * access so escaping can be applied.
 *
 * @category  Templates
 * @package   PEAR2_Templates_Savant
 * @author    Brett Bieber <saltybeagle@php.net>
 * @copyright 2009 Brett Bieber
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/repository/pear2/PEAR2_Templates_Savant
 */
namespace pear2\Templates\Savant;
class ObjectProxy
{
    /**
     * The internal object
     * 
     * @var mixed
     */
    protected $object;
    
    /**
     * The savant templating system
     * 
     * @var pear2\Templates\Savant\Main
     */
    protected $savant;
    
    /**
     * Construct a new object proxy
     * 
     * @param mixed $object The object
     * @param Main  $savant The savant templating system
     */
    function __construct($object, $savant)
    {
        $this->object = $object;
        $this->savant = $savant;
    }
    
    /**
     * Magic method for retrieving data.
     * 
     * String data will be escaped with $savant->escape() before it is returned
     * 
     * @return mixed
     */
    function __get($var)
    {
        return $this->filterVar($this->object->$var);
    }
    
    /**
     * Returns a variable, after it has been filtered.
     * 
     * @param mixed $var
     * 
     * @return string|ObjectProxy
     */
    protected function filterVar($var)
    {
        switch(gettype($var)) {
        case 'object':
            return self::factory($var, $this->savant);
        case 'string':
        case 'int':
        case 'bool':
        case 'double':
            return $this->savant->escape($var);
        }
        return $var;
    }
    
    /**
     * Allows access to the raw member variables of the internal object.
     * 
     * @return mixed
     */
    function getRaw($var)
    {
        return $this->object->$var;
    }
    
    function __set($var, $value)
    {
        $this->object->$var = $value;
    }
    
    /**
     * Magic method for checking if a property is set.
     * 
     * @param string $var The var
     * 
     * @return bool
     */
    function __isset($var)
    {
        return isset($this->object->$var);
    }
    
    /**
     * Unset a property.
     * 
     * @param string $var The var
     * 
     * @return void
     */
    function __unset($var)
    {
        unset($this->object->$var);
    }
    
    /**
     * Magic method which will call methods on the object.
     * 
     * @return mixed
     */
    function __call($name, $arguments)
    {
        return call_user_func_array(array($this->object, $name), $arguments);
    }
    
    /**
     * Gets the class of the internal object
     * 
     * When using the ClassToTemplateMapper this method will be called to
     * determine the class of the object.
     * 
     * @return string
     */
    function __getClass()
    {
        return get_class($this->object);
    }
    
    /**
     * Constructs an ObjectProxy for the given object.
     * 
     * @param mixed $object The object to proxy
     * @param Main  $savant The main savant instance
     * 
     * @return ObjectProxy
     */
    public static function factory($object, $savant)
    {
        if ($object instanceof \Traversable) {
            return new ObjectProxy\Traversable($object, $savant);
        }
        if ($object instanceof \ArrayAccess) {
            return new ObjectProxy\ArrayAccess($object, $savant);
        }
        return new self($object, $savant);
    }
}