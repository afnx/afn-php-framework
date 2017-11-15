<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Routers;

/**
 * Web Router
 *
 * @author alifuatnumanoglu
 */
class WebRouter {

    protected static $routes = array();

    public static function add_route($pattern, $tokens = array()) {
        self::$routes[] = array(
            "pattern" => $pattern,
            "tokens" => $tokens
        );
    }

    public function parse($url) {

        $tokens = array();

        foreach(self::$routes as $route) {
            preg_match("@^" . $route['pattern'] . "$@", $url, $matches);

            if($matches) {
                foreach($matches as $key => $match) {
                    if($key == 0) {
                        continue;
                    }
                    $match = preg_replace('{/$}', '', $match);
                    $tokens[$route['tokens'][$key - 1]] = $match;
                }

                return $tokens;
            }
        }

        return $tokens;
    }

}
