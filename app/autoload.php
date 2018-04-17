<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

/**
 * Autoload
 *
 * @package AFN-PHP-FRAMEWORK
 */

// Determine ROOT_DIR if it is not defined
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', realpath(__DIR__ . '/..'));
}

// Define default settings as global variable
$GLOBALS['settings'] = require_once ROOT_DIR . '/config/app.php';
$GLOBALS['captchas'] = require_once ROOT_DIR . '/config/captchas.php';

spl_autoload_register(function ($class) {

    // Project-specific namespace prefix
    $prefix = 'AFN\\';

    // Check if ROOT_DIR is not defined
    // Base directory for the namespace prefix
    if (!defined('ROOT_DIR')) {
        $base_dir = realpath(__DIR__ . '/..') . '/';
    } else {
        $base_dir = ROOT_DIR . '/';
    }

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Get everything before the class name
    $non_class = substr($relative_class, 0, strrpos($relative_class, '\\'));

    // Make lowercase non-class string
    $non_class_lower = strtolower($non_class);

    // Get the plain class name
    $plain_class = substr($relative_class, strrpos($relative_class, '\\') + 1);

    // Compound the non-class string and the plain class
    $last_class = $non_class_lower . "/" . $plain_class;

    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $last_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
