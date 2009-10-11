<?php

namespace pear2\Templates\Savant;

class BasicFastCompiler implements FastCompilerInterface
{
    protected $compiledtemplatedir;

    function __construct($compiledtemplatedir)
    {
        $this->compiledtemplatedir = realpath($compiledtemplatedir);
        if (!$this->compiledtemplatedir && !is_writable($this->compiledtemplatedir)) {
            throw new UnexpectedValueException('Unable to compile templates into ' .
                                               $compiledtemplatedir . ', directory does not exist ' .
                                               'or is unwritable');
        }
        $this->compiledtemplatedir .= DIRECTORY_SEPARATOR;
    }

    function compile($name, $savant)
    {
        $cname = $this->compiledtemplatedir . md5($name);
        if (file_exists($cname)) {
            if (filemtime($name) == filemtime($cname)) {
                return $cname;
            }
        }
        $a = file_get_contents($name);
        $a = "<?php return '" . str_replace(array('<?php echo', '?>'), array('\' . ', ' . \''), $a) . "';";
        file_put_contents($cname, $a);
        touch($cname, filemtime($name));
        return $cname;
    }
}