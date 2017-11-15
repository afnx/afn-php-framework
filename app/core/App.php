<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

use AFN\App\Core\Secure;
use AFN\App\Core\Server;
use AFN\App\Routers\WebRouter;

/**
 * First processes
 *
 * @author alifuatnumanoglu
 */
class App {

    private $controller;
    private $action;
    private $params;

    public function __construct() {

        foreach($_GET as $key => $value) {
            $key = Secure::control_var($value);
        }

        foreach($_POST as $key => $value) {
            $key = Secure::control_var($value);
        }

        $router = new WebRouter();

        require_once RE_DIR . '/web.php';

        $factory = new ControllerFactory();

        $this->controller = $factory->create_from_router($router);
        $this->action = $factory->actionName;
        $this->params = $factory->params;
    }

    public function run() {

        if($this->controller != FALSE) {
            if(method_exists($this->controller, $this->action)) {
                call_user_func_array([$this->controller, $this->action], $this->params);
            } else {
                Server::p404();
            }
        } else {
            Server::p404();
        }
    }

}
