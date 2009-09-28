<?php
namespace pear2\Templates\Savant;

class ClassToTemplateMapper implements MapperInterface
{
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
    
    /**
     * The file extension to use
     * 
     * @var string
     */
    static $template_extension = '.tpl.php';
    
    function map($class)
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
    
}
?>