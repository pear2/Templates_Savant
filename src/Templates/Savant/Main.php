<?php
/**
 * pear2\Templates\Savant\Main
 *
 * PHP version 5
 *
 * @category  Yourcategory
 * @package   PEAR2_Templates_Savant
 * @author    Your Name <handle@php.net>
 * @copyright 2009 Your Name
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/repository/pear2/PEAR2_Templates_Savant
 */

/**
 * Main class for PEAR2_Templates_Savant
 *
 * @category  Yourcategory
 * @package   PEAR2_Templates_Savant
 * @author    Your Name <handle@php.net>
 * @copyright 2009 Your Name
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/repository/pear2/PEAR2_Templates_Savant
 */
namespace pear2\Templates\Savant;
class Main
{
    /**
    * 
    * Array of configuration parameters.
    * 
    * @access protected
    * 
    * @var array
    * 
    */
    
    protected $__config = array(
        'template_path' => array(''),
        'resource_path' => array(),
        'compiler'      => null,
        'filters'       => array(),
        'helpers'       => array(),
        'helper_conf'   => array(),
        'escape'        => array(),
    );
    
    protected $output_controller;
    
    /**
     * Default template mapping can be temporarily overridden by 
     * assigning a direct template name.
     * 
     * OutputController::$output_template['My_Class'] = 'My/Class_rss.tpl.php';
     * 
     * @var array
     */
    static $output_template       = array();
    
    /**
     * What character to use as a directory separator when mapping class names
     * to templates.
     * 
     * @var string
     */
    static $directory_separator   = '_';
    
    /**
     * Strip something out of class names before mapping them to templates.
     * 
     * This can be useful if your class names are very long, and you don't
     * want empty subdirectories within your templates directory.
     * 
     * @var string
     */
    static $classname_replacement = '';
    
    // -----------------------------------------------------------------
    //
    // Constructor and magic methods
    //
    // -----------------------------------------------------------------
    
    
    /**
    * 
    * Constructor.
    * 
    * @access public
    * 
    * @param array $config An associative array of configuration keys for
    * the Main object.  Any, or none, of the keys may be set.
    * 
    * @return pear2\Templates\Savant\Main A pear2\Templates\Savant\Main instance.
    * 
    */
    
    public function __construct($config = null)
    {
        // force the config to an array
        settype($config, 'array');
        
        // set the default template search path
        if (isset($config['template_path'])) {
            // user-defined dirs
            $this->setPath('template', $config['template_path']);
        } else {
            // no directories set, use the
            // default directory only
            $this->setPath('template', null);
        }
        
        // set the default resource search path
        if (isset($config['resource_path'])) {
            // user-defined dirs
            $this->setPath('resource', $config['resource_path']);
        } else {
            // no directories set, use the
            // default directory only
            $this->setPath('resource', null);
        }
        
        // set the output escaping callbacks
        if (isset($config['escape'])) {
            $this->setEscape($config['escape']);
        }
        
        // set the default helper configs
        if (isset($config['helper_conf']) && is_array($config['helper_conf'])) {
            foreach ($config['helper_conf'] as $name => $opts) {
                $this->setHelperConf($name, $opts);
            }
        }
        
        // set the default filter callbacks
        if (isset($config['filters'])) {
            $this->addFilters($config['filters']);
        }
        
        $savant =& $this;
        $this->output_controller = function($view, $template = null) use ($savant) {
                ob_start();
                if ($template == NULL) {
                    if ($view instanceof ObjectProxy) {
                        $class = $view->__getClass();
                    } else {
                        $class = get_class($view);
                    }
                    $template = $savant->getTemplateFilename($class);
                }
                $file = $savant->findFile('template', $template);
                if (!$file) {
                    echo 'Could not find template!';
                }
                include $file;
                return $savant->applyFilters(ob_get_clean());
            };
    }
    
    
    /**
    *
    * Executes a main helper method with arbitrary parameters.
    * 
    * @access public
    * 
    * @param string $func The plugin method name.
    *
    * @param array $args The parameters passed to the method.
    *
    * @return mixed The plugin output
    * 
    */
    
    public function __call($func, $args)
    {
        $helper = $this->getHelper($func);
        return call_user_func_array(array($helper, $func), $args);
    }
    
    
    /**
    * 
    * Reports the API version for this class.
    * 
    * @access public
    * 
    * @return string A PHP-standard version number.
    * 
    */
    
    public function apiVersion()
    {
        return '@package_version@';
    }
    
    
    /**
    * 
    * Returns an internal helper object; creates it as needed.
    * 
    * @access public
    * 
    * @param string $name The plugin name.  If this plugin has not
    * been created yet, this method creates it automatically.
    *
    * @return mixed The helper object.
    * 
    */
    
    public function getHelper($name)
    {
        // shorthand reference
        $helpers =& $this->__config['helper'];
        
        // is the plugin method object already instantiated?
        if (! array_key_exists($name, $helpers)) {
            
            // not already instantiated, so load it up.
            // set up the class name.
            $class = "pear2\\Templates\\Savant\\Helper\\$name";
            
            // get the default configuration for the plugin.
            $helper_conf =& $this->__config['helper_conf'];
            if (! empty($helper_conf[$name])) {
                $opts = $helper_conf[$name];
            } else {
                $opts = array();
            }
            
            // add the Savant reference
            $opts['savant'] = $this;
            
            // instantiate the plugin with its options.
            $helpers[$name] = new $class($opts);
        }
    
        // return the plugin object
        return $helpers[$name];
    }
    
    
    // -----------------------------------------------------------------
    //
    // Public configuration management (getters and setters).
    // 
    // -----------------------------------------------------------------
    
    
    /**
    *
    * Returns a copy of the Savant3 configuration parameters.
    *
    * @access public
    * 
    * @param string $key The specific configuration key to return.  If null,
    * returns the entire configuration array.
    * 
    * @return mixed A copy of the $this->__config array.
    * 
    */
    
    public function getConfig($key = null)
    {
        if (is_null($key)) {
            // no key requested, return the entire config array
            return $this->__config;
        } elseif (empty($this->__config[$key])) {
            // no such key
            return null;
        } else {
            // return the requested key
            return $this->__config[$key];
        }
    }
    
    
    /**
    * 
    * Sets a custom compiler/pre-processor callback for template sources.
    * 
    * By default, Savant3 does not use a compiler; use this to set your
    * own custom compiler (pre-processor) for template sources.
    * 
    * @access public
    * 
    * @param mixed $compiler A compiler callback value suitable for the
    * first parameter of call_user_func().  Set to null/false/empty to
    * use PHP itself as the template markup (i.e., no compiling).
    * 
    * @return void
    * 
    */
    
    public function setCompiler($compiler)
    {
        $this->__config['compiler'] = $compiler;
    }
    
    
    /**
    *
    * Sets config array for a helper.
    * 
    * @access public
    * 
    * @param string $helper The plugin to configure.
    * 
    * @param array $config The configuration array for the plugin.
    * 
    * @return void
    *
    */
    
    public function setHelperConf($helper, $config = null)
    {
        $this->__config['helper_conf'][$helper] = $config;
    }
    
    
    // -----------------------------------------------------------------
    //
    // Output escaping and management.
    //
    // -----------------------------------------------------------------
    
    
    /**
    * 
    * Clears then sets the callbacks to use when calling $this->escape().
    * 
    * Each parameter passed to this function is treated as a separate
    * callback.  For example:
    * 
    * <code>
    * $savant->setEscape(
    *     'stripslashes',
    *     'htmlspecialchars',
    *     array('StaticClass', 'method'),
    *     array($object, $method)
    * );
    * </code>
    * 
    * @access public
    *
    * @return void
    *
    */
    
    public function setEscape()
    {
        $this->__config['escape'] = (array) @func_get_args();
    }
    
    
    /**
    * 
    * Adds to the callbacks used when calling $this->escape().
    * 
    * Each parameter passed to this function is treated as a separate
    * callback.  For example:
    * 
    * <code>
    * $savant->addEscape(
    *     'stripslashes',
    *     'htmlspecialchars',
    *     array('StaticClass', 'method'),
    *     array($object, $method)
    * );
    * </code>
    * 
    * @access public
    *
    * @return void
    *
    */
    
    public function addEscape()
    {
        $args = (array) @func_get_args();
        $this->__config['escape'] = array_merge(
            $this->__config['escape'], $args
        );
    }
    
    
    /**
    *
    * Gets the array of output-escaping callbacks.
    *
    * @access public
    *
    * @return array The array of output-escaping callbacks.
    *
    */
    
    public function getEscape()
    {
        return $this->__config['escape'];
    }
    
    
    /**
    *
    * Applies escaping to a value.
    * 
    * You can override the predefined escaping callbacks by passing
    * added parameters as replacement callbacks.
    * 
    * <code>
    * // use predefined callbacks
    * $result = $savant->escape($value);
    * 
    * // use replacement callbacks
    * $result = $savant->escape(
    *     $value,
    *     'stripslashes',
    *     'htmlspecialchars',
    *     array('StaticClass', 'method'),
    *     array($object, $method)
    * );
    * </code>
    *
    * 
    * Unfortunately, a call to "echo htmlspecialchars()" is twice
    * as fast as a call to "echo $this->escape()" under the default
    * escaping (which is htmlspecialchars).  The benchmark showed
    * 0.007 seconds for htmlspecialchars(), and 0.014 seconds for
    * $this->escape(), on 300 calls each.
    * 
    * @access public
    * 
    * @param mixed $value The value to be escaped.
    * 
    * @return mixed
    *
    */
    
    public function escape($value)
    {
        // were custom callbacks passed?
        if (func_num_args() == 1) {
        
            // no, only a value was passed.
            // loop through the predefined callbacks.
            foreach ($this->__config['escape'] as $func) {
                // this if() shaves 0.001sec off of 300 calls.
                if (is_string($func)) {
                    $value = $func($value);
                } else {
                    $value = call_user_func($func, $value);
                }
            }
            
        } else {
        
            // yes, use the custom callbacks
            $callbacks = func_get_args();
            
            // drop $value
            array_shift($callbacks);
            
            // loop through custom callbacks.
            foreach ($callbacks as $func) {
                // this if() shaves 0.001sec off of 300 calls.
                if (is_string($func)) {
                    $value = $func($value);
                } else {
                    $value = call_user_func($func, $value);
                }
            }
            
        }
        
        return $value;
    }
    
    
    // -----------------------------------------------------------------
    //
    // File management
    //
    // -----------------------------------------------------------------
    
    
    /**
    *
    * Sets an entire array of search paths for templates or resources.
    *
    * @access public
    *
    * @param string $type The type of path to set, typically 'template'
    * or 'resource'.
    * 
    * @param string|array $path The new set of search paths.  If null or
    * false, resets to the current directory only.
    *
    * @return void
    *
    */
    
    public function setPath($type, $path)
    {
        // clear out the prior search dirs
        $this->__config[$type . '_path'] = array();
        
        // always add the fallback directories as last resort
        switch (strtolower($type)) {
        case 'template':
            // the current directory
            $this->addPath($type, '.');
            break;
        case 'resource':
            // the Savant3 distribution resources
            $this->addPath($type, dirname(__FILE__) . '/Savant3/resources/');
            break;
        }
        
        // actually add the user-specified directories
        $this->addPath($type, $path);
    }
    
    
    /**
    *
    * Adds to the search path for templates and resources.
    *
    * @access public
    *
    * @param string|array $path The directory or stream to search.
    *
    * @return void
    *
    */
    
    public function addPath($type, $path)
    {
        // convert from path string to array of directories
        if (is_string($path) && ! strpos($path, '://')) {
        
            // the path config is a string, and it's not a stream
            // identifier (the "://" piece). add it as a path string.
            $path = explode(PATH_SEPARATOR, $path);
            
            // typically in path strings, the first one is expected
            // to be searched first. however, Savant3 uses a stack,
            // so the first would be last.  reverse the path string
            // so that it behaves as expected with path strings.
            $path = array_reverse($path);
            
        } else {
        
            // just force to array
            settype($path, 'array');
            
        }
        
        // loop through the path directories
        foreach ($path as $dir) {
        
            // no surrounding spaces allowed!
            $dir = trim($dir);
            
            // add trailing separators as needed
            if (strpos($dir, '://') && substr($dir, -1) != '/') {
                // stream
                $dir .= '/';
            } elseif (substr($dir, -1) != DIRECTORY_SEPARATOR) {
                // directory
                $dir .= DIRECTORY_SEPARATOR;
            }
            
            // add to the top of the search dirs
            array_unshift(
                $this->__config[$type . '_path'],
                $dir
            );
        }
    }
    
    
    /**
    * 
    * Searches the directory paths for a given file.
    * 
    * @param array $type The type of path to search (template or resource).
    * 
    * @param string $file The file name to look for.
    * 
    * @return string|bool The full path and file name for the target file,
    * or boolean false if the file is not found in any of the paths.
    *
    */
    
    public function findFile($type, $file)
    {
        // get the set of paths
        $set = $this->__config[$type . '_path'];
        
        // start looping through the path set
        foreach ($set as $path) {
            
            // get the path to the file
            $fullname = $path . $file;
            
            // is the path based on a stream?
            if (strpos($path, '://') === false) {
                // not a stream, so do a realpath() to avoid
                // directory traversal attempts on the local file
                // system. Suggested by Ian Eure, initially
                // rejected, but then adopted when the secure
                // compiler was added.
                $path = realpath($path); // needed for substr() later
                $fullname = realpath($fullname);
            }
            
            // the substr() check added by Ian Eure to make sure
            // that the realpath() results in a directory registered
            // with Savant so that non-registered directores are not
            // accessible via directory traversal attempts.
            if (file_exists($fullname) && is_readable($fullname) &&
                substr($fullname, 0, strlen($path)) == $path) {
                return $fullname;
            }
        }
        
        // could not find the file in the set of paths
        return false;
    }
    
    
    // -----------------------------------------------------------------
    //
    // Template processing
    //
    // -----------------------------------------------------------------
    
    
    function render($mixed = null, $template = null)
    {
        if (is_array($mixed)) {
            return $this->renderArray($mixed, $template);
        }
        
        if (is_object($mixed)) {
            return $this->renderObject($mixed, $template);
        }
        
        return $this->renderString((string) $mixed, $template);
    }
    
    protected function renderString($string, $template = null)
    {
        if ($template) {
            return $this->fetch($string, $template);
        }
        
        return $this->applyFilters($string);
    }
    
    protected function renderArray($mixed, $template = null)
    {
        $output = '';
        foreach ($mixed as $m) {
            $output .= $this->render($m, $template);
        }
        
        return $output;
    }
    
    protected function renderObject($object, $template = null)
    {
        if ($object instanceof Cacheable) {
            $key = $object->getCacheKey();
            if ($key !== false && $data = $this->cache->get($key)) {
                // Tell the object we have cached data and will output that.
                $object->preRun(true);
            } else {
                // Content should be cached, but none could be found.
                $object->preRun(false);
                $object->run();
                
                $data = $this->fetch($object);
                
                if ($key !== false) {
                    $this->cache->save($data, $key);
                }
            }
            return $data;
        }
        return $this->fetch($object, $template);
    }
    
    protected function fetch($mixed, $template = null)
    {
        $outputcontroller = $this->output_controller;
        if (is_object($mixed)
            && count($this->__config['escape'])) {
            $mixed = new ObjectProxy($mixed, $this);
        }
        return $outputcontroller($mixed, $template);
    }
    
    /**
     * This function maps a class name to a template filename.
     * 
     * My_Class => My/Class.tpl.php
     * 
     * @see OutputController::$classname_replacment
     * @see OutputController::$directory_separator
     * @see OutputController::$output_template
     * 
     * @param string $class The class to get template filename for.
     * 
     * @return string
     */
    function getTemplateFilename($class)
    {
        if (isset(static::$output_template[$class])) {
            $class = static::$output_template[$class];
        }
        
        $class = str_replace(array(static::$classname_replacement,
                                   static::$directory_separator,
                                   '\\'),
                             array('',
                                   DIRECTORY_SEPARATOR,
                                   DIRECTORY_SEPARATOR),
                             $class);
        
        $templatefile = $class . '.tpl.php';
        
        return $templatefile;
    }
    
    /**
    *
    * Compiles a template and returns path to compiled script.
    * 
    * By default, Savant does not compile templates, it uses PHP as the
    * markup language, so the "compiled" template is the same as the source
    * template.
    * 
    * Used inside a template script like so:
    * 
    * <code>
    * include $this->template($tpl);
    * </code>
    * 
    * @access protected
    *
    * @param string $tpl The template source name to look for.
    * 
    * @return string The full path to the compiled template script.
    * 
    * @throws object An error object with a 'ERR_TEMPLATE' code.
    * 
    */
    
    protected function template($tpl = null)
    {
        // find the template source.
        $file = $this->findFile('template', $tpl);
        if (! $file) {
            throw new TemplateException('Template error. The template, '.$tpl.', was not found.');
        }
        
        // are we compiling source into a script?
        if ($this->__config['compiler']) {
            // compile the template source and get the path to the
            // compiled script (will be returned instead of the
            // source path)
            $result = call_user_func(
                array($this->__config['compiler'], 'compile'),
                $file
            );
        } else {
            // no compiling requested, use the source path
            $result = $file;
        }
        
        // is there a script from the compiler?
        if (!$result) {
            // return an error, along with any error info
            // generated by the compiler.
            throw new Exception('Compiler error for template '.$tpl.'. '.$result );
            
        } else {
            // no errors, the result is a path to a script
            return $result;
        }
    }
    
    
    // -----------------------------------------------------------------
    //
    // Filter management and processing
    //
    // -----------------------------------------------------------------
    
    
    /**
    * 
    * Resets the filter stack to the provided list of callbacks.
    * 
    * @access protected
    * 
    * @param array An array of filter callbacks.
    * 
    * @return void
    * 
    */
    
    public function setFilters()
    {
        $this->__config['filters'] = (array) @func_get_args();
    }
    
    
    /**
    * 
    * Adds filter callbacks to the stack of filters.
    * 
    * @access protected
    * 
    * @param array An array of filter callbacks.
    * 
    * @return void
    * 
    */
    
    public function addFilters()
    {
        // add the new filters to the static config variable
        // via the reference
        foreach ((array) @func_get_args() as $callback) {
            $this->__config['filters'][] = $callback;
        }
    }
    
    
    /**
    * 
    * Runs all filter callbacks on buffered output.
    * 
    * @access protected
    * 
    * @param string The template output.
    * 
    * @return void
    * 
    */
    
    public function applyFilters($buffer)
    {
        foreach ($this->__config['filters'] as $callback) {
        
            // if the callback is a static Savant3_Filter method,
            // and not already loaded, try to auto-load it.
            if (is_array($callback) &&
                is_string($callback[0]) &&
                substr($callback[0], 0, 15) == '\\pear2\\Templates\\Savant\\Filter\\' &&
                ! class_exists($callback[0])) {
                
                // load the Savant3_Filter_*.php resource
                $file = $callback[0] . '.php';
                $result = $this->findFile('resource', $file);
                if ($result) {
                    include_once $result;
                }
            }
            
            // can't pass a third $this param, it chokes the OB system.
            $buffer = call_user_func($callback, $buffer);
        }
        
        return $buffer;
    }
    
}
