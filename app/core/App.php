<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @authorAFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

use AFN\App\Core\Secure;
use AFN\App\Core\Server;
use AFN\App\Routers\WebRouter;

/**
 * Class App
 * First processes
 *
 * @package AFN-PHP-FRAMEWORK
 */
class App
{

    /**
     * Stores controller class which will call
     * @var object
     */
    private $controller;

    /**
     * Stores action to call function which run the process
     * @var string
     */
    private $action;

    /**
     * Stores parameters to send a function
     * @var array
     */
    private $params;

    /**
     * Prepares before running the app
     */
    public function __construct()
    {
        // Perfom control for all passed $_GET variables
        foreach ($_GET as $key => $value) {
            $key = isset($value) ? Secure::controlVar($value) : "";
        }

        // Perfom control for all passed $_POST variables
        foreach ($_POST as $key => $value) {
            $key = isset($value) ? Secure::controlVar($value) : "";
        }

        $router = new WebRouter();

        // Include web routes file
        require_once RE_DIR . '/web.php';

        $factory = new ControllerFactory();

        // Get Parameters from ControllerFactory
        $this->controller = $factory->createFromRouter($router);
        $this->action = $factory->actionName;
        $this->params = $factory->params;

        Secure::afnSessionStart();

        if (!isset($_SESSION["language"])) {
            $_SESSION["language"] = Server::detectLang();
        }

        if ($this->action != "display") {
            if (!((new Secure)->secureService())) {
                // Display 404 Not Found page
                Server::p404();
                exit();
            }
        }
    }

    /**
     * Runs the app
     */
    public function run()
    {
        // Check whether controller exist
        if ($this->controller != false) {
            // Check whether the demanded function exists in the class
            if (method_exists($this->controller, $this->action)) {
                // Check if the function is public
                $reflection = new \ReflectionMethod($this->controller, $this->action);
                if (!$reflection->isPublic()) {
                    // Display 404 Not Found page
                    Server::p404();
                } else {
                    // Call the function
                    call_user_func_array([$this->controller, $this->action], $this->params);
                }
            } else {
                // Display 404 Not Found page
                Server::p404();
            }
        } else {
            // Display 404 Not Found page
            Server::p404();
        }
    }

}
