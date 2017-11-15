<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

// determine ROOT_DIR if it is not defined
if(!defined('ROOT_DIR')) {
    define('ROOT_DIR', realpath(__DIR__ . '/..'));
}

// define default settings as global variable
$GLOBALS['settings'] = require_once ROOT_DIR . '/config/app.php';

spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'AFN\\';

    // check if ROOT_DIR is not defined
    // base directory for the namespace prefix
    if(!defined('ROOT_DIR')) {
        $base_dir = realpath(__DIR__ . '/..') . '/';
    } else {
        $base_dir = ROOT_DIR . '/';
    }

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if(strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // get everything before the class name
    $non_class = substr($relative_class, 0, strrpos($relative_class, '\\'));

    // make lowercase non-class string
    $non_class_lower = strtolower($non_class);

    // get the plain class name
    $plain_class = substr($relative_class, strrpos($relative_class, '\\') + 1);

    // compound the non-class string and the plain class
    $last_class = $non_class_lower . "/" . $plain_class;

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $last_class) . '.php';

    // if the file exists, require it
    if(file_exists($file)) {
        require $file;
    }
});
