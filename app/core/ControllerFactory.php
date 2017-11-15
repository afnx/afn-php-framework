<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

use AFN\App\Core\Secure;
use AFN\App\Routers\WebRouter;

/**
 * Controller Creator
 *
 * @author alifuatnumanoglu
 */
class ControllerFactory {

    public $router;
    public $controllerName;
    public $actionName;
    public $params;

    public function create_from_router(WebRouter $router) {

        $result = $router->parse($_SERVER['REQUEST_URI']);

        $this->controllerName = isset($result["controller"]) ? Secure::control_var($result["controller"]) : 'DefaultController';
        $this->actionName = isset($result["action"]) ? Secure::control_var($result["action"]) : 'index';
        $this->params = isset($result["params"]) ? Secure::control_var($result["params"]) : '';

        if(file_exists($file = CR_DIR . "/{$this->controllerName}.php")) {
            if(class_exists($this->controllerName)) {
                $NewController = $this->controllerName;
                return new $NewController();
            } else {
                return FALSE;
            }
        }
    }

}
