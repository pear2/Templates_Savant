<?php
namespace pear2\Templates\Savant;

class HelperToClassMapper implements MapperInterface
{
    function map($name)
    {
        return 'pear2\\Templates\\Savant\\Helper\\'.ucfirst($name);
    }
}
?>