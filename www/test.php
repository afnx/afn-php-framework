<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
error_reporting(E_ALL);
ini_set("log_errors", 1);
ini_set("error_log", "../storage/logs/error_log");

use AFN\App\Core\View;

define('ROOT_DIR', realpath(__DIR__ . '/..'));
define('APP_DIR', ROOT_DIR . '/app');
define('CORE_DIR', APP_DIR . '/core');
define('CR_DIR', APP_DIR . '/controllers');
define('ML_DIR', APP_DIR . '/models');
define('VW_DIR', APP_DIR . '/views');
define('RR_DIR', APP_DIR . '/routers');
define('LT_DIR', ROOT_DIR . '/resources/layouts');
define('RE_DIR', ROOT_DIR . '/routes');

// Include autoload to call classes easier
require APP_DIR . '/autoload.php';

$view = new View();
$view->view_file = "test";

$view->entries = [
    0 => ['test' => 'This was inserted using template tags!', 'test2' => 'bok!'],
    1 => ['test' => 'salak!', 'test3' => 'salak2!']];

$extra = [
    'header' => [
        'header_stuff' => 'Some extra content.'
    ],
    'footer' => [
        'footerStuff' => 'More extra content.'
    ]
];

// Output the template markup
echo $view->generate_markup($extra);
