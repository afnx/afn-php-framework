<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

/**
 * Index
 *
 * @package AFN-PHP-FRAMEWORK
 */

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
error_reporting(E_ALL);
ini_set("log_errors", 1);
ini_set("error_log", "../storage/logs/error_log");

use AFN\App\Core\App;

define('ROOT_DIR', realpath(__DIR__ . '/..'));
define('APP_DIR', ROOT_DIR . '/app');
define('CORE_DIR', APP_DIR . '/core');
define('CR_DIR', APP_DIR . '/controllers');
define('ML_DIR', APP_DIR . '/models');
define('VW_DIR', APP_DIR . '/views');
define('RR_DIR', APP_DIR . '/routers');
define('LT_DIR', ROOT_DIR . '/resources/layouts');
define('RE_DIR', ROOT_DIR . '/routes');
define('STO_DIR', ROOT_DIR . '/storage');
define('LIB_DIR', ROOT_DIR . '/library');

// Include autoload to call classes easier
require APP_DIR . '/autoload.php';

// Run the app.
$app = new App;
$app->run();
