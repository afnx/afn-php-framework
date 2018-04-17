<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Controllers;

use AFN\App\Core\Controller;

/**
 * Class p404Controller
 * 404 Not Found Page
 *
 * @package AFN-PHP-FRAMEWORK
 */
class p404Controller extends Controller
{

    /**
     * Stores view class
     * @var object
     */
    public $view;

    /**
     * Calls necessary classes
     */
    public function __construct()
    {
        $this->view = new \AFN\App\Core\View();
    }

    /**
     * Displays 404 Not Found Page
     */
    public function display()
    {
        $this->view->viewFile = "404";
        echo $this->view->generateMarkup();
    }
}
