<?php

/**
* 
* Abstract Savant Heler class.
* 
* @package Savant
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Plugin.php,v 1.5 2005/04/29 16:23:50 pmjones Exp $
*
*/

/**
* 
* Abstract Savant Helper class.
*
* You have to extend this class for it to be useful; e.g., "class
* Savant\Helper\example extends pear2\Templates\Savant\Helper".
* 
* @package Savant
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/
namespace pear2\Templates\Savant;
abstract class HelperAbstract
{
    
    /**
    * 
    * Reference to the calling Savant object.
    * 
    * @access protected
    * 
    * @var object
    * 
    */
    
    protected $savant = null;
    
    
    function setSavant(Main $savant)
    {
        $this->savant = $savant;
    }
}
?>