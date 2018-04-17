<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

use AFN\App\Routers\WebRouter;

/**
 * Web Routes
 * All web routes is added below.
 *
 * @package AFN-PHP-FRAMEWORK
 */

WebRouter::addRoute("/(.*)/(.*)/([0-9]{1,6})", array("controller", "action", "params"));
WebRouter::addRoute("/(.*)/(.*)", array("controller", "action"));
WebRouter::addRoute("/(.*)", array("catchall"));
