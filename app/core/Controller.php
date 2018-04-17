<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

/**
 * Class Controller
 * Fundamental of Controllers
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Controller
{
    /**
     * Loads model class by checking for existence of it
     * @param string $modelName
     * @return object
     */
    public function model($modelName)
    {

        if (file_exists($file = ML_DIR . "/{$modelName}.php")) {
            require_once $file;
            if (class_exists($modelName)) {
                return new $modelName;
            } else {
                Server::p404();
            }
        } else {
            Server::p404();
        }
    }

}
