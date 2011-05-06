<?php

set_include_path(__DIR__ . '/src');

function autoload($class)
{
    require_once str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $class).'.php';
}


spl_autoload_register('autoload');
