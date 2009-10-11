<?php
/**
 * pear2\Templates\Savant\Main
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
 * Main class for PEAR2_Templates_Savant
 *
 * @category  Templates
 * @package   PEAR2_Templates_Savant
 * @author    Brett Bieber <saltybeagle@php.net>
 * @copyright 2009 Brett Bieber
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
        'compiler'      => null,
        'filters'       => array(),
        'escape'        => 'htmlspecialchars',
    );
    
    /**
     * Parameters for escaping.
     * @var array
     */
    protected $_escape = array(
        'quotes'  => ENT_COMPAT,
        'charset' => 'UTF-8',
        );
    
    /**
     * The output template to render using
     * @var string
     */
    protected $template;
    
    /**
     * An array of paths to look for template files in.
     * @var array
     */
    protected $template_path = array('./');

    /**
     * A list of output controllers.  One does no filtering, another does.  This
     * makes non-filtering controllers faster.
     * @var array
     */
    protected $output_controllers = array();

    /**
     * 
     * @var string
     */
    protected $selected_controller;
    
    /**
     * How class names are translated to templates
     * 
     * @var MapperInterface
     */
    protected $class_to_template;
    
    /**
     * How helpers are translated to class names
     * 
     * @var MapperInterface
     */
    protected $helper_to_class;
    
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
        $savant = $this;
        $this->output_controllers['basic'] = function($context, $file) use ($savant) {
                ob_start();
                include $file;
                return ob_get_clean();
            };
        $this->output_controllers['filter'] = function($context, $file) use ($savant) {
                ob_start();
                include $file;
                return $savant->applyFilters(ob_get_clean());
            };
        $this->selected_controller = 'basic';
        
        // set the default template search path
        if (isset($config['template_path'])) {
            // user-defined dirs
            $this->setTemplatePath($config['template_path']);
        }
        
        // set the output escaping callbacks
        if (isset($config['escape'])) {
            $this->setEscape($config['escape']);
        }
        
        // set the default filter callbacks
        if (isset($config['filters'])) {
            $this->addFilters($config['filters']);
        }
    }
    
    function getTemplate()
    {
        return $this->template;
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
    
    function setClassToTemplateMapper(MapperInterface $mapper)
    {
        $this->class_to_template = $mapper;
    }
    
    function getClassToTemplateMapper()
    {
        if (!isset($this->class_to_template)) {
            $this->setClassToTemplateMapper(new ClassToTemplateMapper());
        }
        return $this->class_to_template;
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
     * Escapes a value for output in a view script.
     *
     * If escaping mechanism is one of htmlspecialchars or htmlentities, uses
     * {@link $_encoding} setting.
     *
     * @param mixed $var The output to escape.
     * @return mixed The escaped value.
     */
    public function escape($var)
    {
        if (in_array($this->__config['escape'],
                array('htmlspecialchars', 'htmlentities'))) {
            return call_user_func($this->__config['escape'],
                                  $var,
                                  $this->_escape['quotes'],
                                  $this->_escape['charset']);
        }

        return call_user_func($this->__config['escape'], $var);
    }
    
    
    // -----------------------------------------------------------------
    //
    // File management
    //
    // -----------------------------------------------------------------
    
    function getTemplatePath()
    {
        return $this->template_path;
    }
    
    /**
    *
    * Sets an entire array of search paths for templates or resources.
    *
    * @access public
    * 
    * @param string|array $path The new set of search paths.  If null or
    * false, resets to the current directory only.
    *
    * @return void
    *
    */
    
    public function setTemplatePath($path = null)
    {
        // clear out the prior search dirs, add default
        $this->template_path = array('./');
        
        // actually add the user-specified directories
        $this->addTemplatePath($path);
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
    
    public function addTemplatePath($path)
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
                $this->template_path,
                $dir
            );
        }
    }
    
    
    /**
    * 
    * Searches the directory paths for a given file.
    * 
    * @param string $file The file name to look for.
    * 
    * @return string|bool The full path and file name for the target file,
    * or boolean false if the file is not found in any of the paths.
    *
    */
    
    public function findTemplateFile($file)
    {
        
        // start looping through the path set
        foreach ($this->template_path as $path) {
            
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
        $method = 'render'.gettype($mixed);
        return $this->$method($mixed, $template);
    }
    
    protected function renderResource($resouce, $template = null)
    {
        throw new UnexpectedValueException('No way to render a resource!');
    }
    
    protected function renderBoolean($bool, $template = null)
    {
        return $this->renderString((string)$bool, $template);
    }
    
    protected function renderDouble($double, $template = null)
    {
        return $this->renderString($double, $template);
    }
    
    protected function renderInteger($int, $template = null)
    {
        return $this->renderString($int, $template);
    }
    
    protected function renderString($string, $template = null)
    {
        if ($this->__config['escape']) {
            $string = $this->escape($string);
        }
        
        if ($template) {
            return $this->fetch($string, $template);
        }

        if (!$this->__config['filters']) {
            return $string;
        }
        return $this->applyFilters($string);
    }
    
    protected function renderArray($array, $template = null)
    {
        $savant = $this;
        $render = function($output, $mixed) use ($savant, $template) {
            return $output . $savant->render($mixed, $template);
        };
        return array_reduce($array, $render, '');
    }
    
    protected function renderObject($object, $template = null)
    {
        if ($this->__config['escape']) {
            $object = new ObjectProxy($object, $this);
        }
        return $this->fetch($object, $template);
    }
    
    protected function fetch($mixed, $template = null)
    {
        if ($template) {
            $this->template = $template;
        } else {
            if ($mixed instanceof ObjectProxy) {
                $class = $mixed->__getClass();
            } else {
                $class = get_class($mixed);
            }
            $this->template = $this->getClassToTemplateMapper()->map($class);
        }
        $file = $this->findTemplateFile($this->template);
        if (!$file) {
            throw new TemplateException('Could not find the template '.$this->template);
        }
        $outputcontroller = $this->output_controllers[$this->selected_controller];
        return $outputcontroller($mixed, $file);
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
        $file = $this->findTemplateFile($tpl);
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
        if (!$this->__config['filters']) {
            $this->selected_controller = 'basic';
        } else {
            $this->selected_controller = 'filter';
        }
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
            $this->selected_controller = 'filter';
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
            $buffer = call_user_func($callback, $buffer);
        }
        
        return $buffer;
    }
    
}
