<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

use AFN\App\Core\Secure;
use AFN\App\Routers\WebRouter;

/**
 * Class ControllerFactory
 * Controller Creator
 *
 * @package AFN-PHP-FRAMEWORK
 */
class ControllerFactory
{

    /**
     * Stores router
     * @var WebRouter
     */
    public $router;

    /**
     * Stores controller name
     * @var string
     */
    public $controllerName;

    /**
     * Stores action name
     * @var string
     */
    public $actionName;

    /**
     * Stores parameters
     * @var array
     */
    public $params;

    /**
     * Creates new controller from Router
     * @param WebRouter $router
     * @return object
     */
    public function createFromRouter(WebRouter $router)
    {

        // Parse the current uri
        $result = $router->parse($_SERVER['REQUEST_URI']);

        // Get parameters and check them
        $this->controllerName = isset($result["controller"]) ? Secure::controlVar($result["controller"]) : 'DefaultController';
        $this->actionName = isset($result["action"]) ? Secure::controlVar($result["action"]) : 'display';
        $this->params = isset($result["params"]) ? Secure::controlVar($result["params"]) : ['NULL'];

        // Check whether the class file exists
        if (file_exists($file = CR_DIR . "/{$this->controllerName}.php")) {
            // Add namespace to class name
            $this->controllerName = '\\AFN\\App\\Controllers\\' . $this->controllerName;
            // Check whether the class exists
            if (class_exists($this->controllerName)) {
                // Call the class and return it
                $NewController = $this->controllerName;
                return new $NewController();
            } else {
                return false;
            }
        }
    }

}
