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
 * Class Logout
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Logout extends Controller
{

    /**
     * Stores view class
     * @var object
     */
    public $view;

    /**
     * Stores Localization class
     * @var object
     */
    public $loc;

    public function __construct()
    {
        $this->view = new \AFN\App\Core\View();
        $this->server = new \AFN\App\Core\Server();
        $this->secure = new \AFN\App\Core\Secure();
        $this->loc = new \AFN\App\Models\Localization();
    }

    /**
     * Logout process
     */
    public function display()
    {
        $this->secure::afnSessionStop();
        $this->server->redirect("/");
    }
}
