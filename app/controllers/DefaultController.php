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
 * Class DefaultController
 * Default page
 *
 * @package AFN-PHP-FRAMEWORK
 */
class DefaultController extends Controller
{

    /**
     * Stores view class
     * @var object $view
     */
    public $view;

    public function __construct()
    {
        $this->view = new \AFN\App\Core\View();
        $this->secure = new \AFN\App\Core\Secure();
    }

    /**
     * Displays default page
     */
    public function display()
    {
        $this->view->viewFile = "main";
        $this->view->newEntry(1, ["navbar_home_active" => "active"]);
        echo $this->view->generateMarkup();
    }
}
