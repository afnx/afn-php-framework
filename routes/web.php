<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

use AFN\App\Routers\WebRouter;

/*
 * All web routes is added below.
 */


WebRouter::add_route("/(.*)/(.*)/([0-9]{1,6})", array("controller", "action", "params"));
WebRouter::add_route("/(.*)/(.*)", array("controller", "action"));
WebRouter::add_route("/(.*)", array("catchall"));
