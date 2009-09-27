<?php
ini_set('display_errors',true);
error_reporting(E_STRICT);
require_once __DIR__.'/../../autoload.php';

// Set up a view object we'd like to display
$class = new stdClass();
$class->var1 = '<p>This is var1 inside a standard class</p>';

$savant = new \pear2\Templates\Savant\Main();
$savant->addPath('template','templates');
// Display a simple string
$savant->display('<h1>Welcome to the Savant Demo</h1>');

// Display a string, in a custom template
$savant->display('mystring', 'StringView.tpl.php');

// Display an array
$savant->display(array('<ul>', '<li>This is an array</li>', '</ul>'));

// Display an object using a default class name to template mapping function
$savant->display($class);

// Display the object using a specific template
$savant->display($class, 'MyTemplate.tpl.php');

$savant->display('<h2>Output Filtering</h2>');
$savant->addFilters('htmlspecialchars');

// Now show an entire template with htmlspecialchars
$savant->display($class);

// Ok, now remove the output filters
$savant->setFilters();

$savant->display('<h2>Variable Escaping</h2>');

// Turn on some template variable escaping
$savant->addEscape('htmlspecialchars');

// Display the standard class, now with all variables accessed being escaped
$savant->display($class);

highlight_file(__FILE__);

