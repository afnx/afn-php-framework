<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

use AFN\App\Controllers\p404Controller;

/**
 * Server Activities
 *
 * @author alifuatnumanoglu
 */
class Server {

    public static function redirect($url, $permanent = false) {

        if($permanent) {
            header('HTTP/1.1 301 Moved Permanently');
        }
        echo '<META HTTP-EQUIV="Refresh" Content="0; URL=' . $url . '">';
        exit();
    }

    public static function redirect_back() {

        session_write_close();
        echo '<META HTTP-EQUIV="Refresh" Content="0; URL=' . getenv("HTTP_REFERER") . '">';
        exit();
    }

    public static function p404() {
        $p404Controller = new p404Controller();
        $p404Controller->show_404();
    }

}
