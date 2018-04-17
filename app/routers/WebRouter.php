<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Routers;

/**
 * Class WebRouter
 *
 * @package AFN-PHP-FRAMEWORK
 */
class WebRouter
{

    /**
     * Stores routes
     * @var array
     */
    protected static $routes = array();

    /**
     * Adds new route
     * @param string $pattern
     * @param array $tokens
     */
    public static function addRoute($pattern, $tokens = array())
    {
        self::$routes[] = array(
            "pattern" => $pattern,
            "tokens" => $tokens,
        );
    }

    /**
     * Parses url with route
     * @param string $url
     * @return array
     */
    public function parseWithRoute($url)
    {

        $tokens = array();

        foreach (self::$routes as $route) {
            preg_match("@^" . $route['pattern'] . "$@", $url, $matches);

            if ($matches) {
                foreach ($matches as $key => $match) {
                    if ($key == 0) {
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

    /**
     * Parses url simply
     * @param string $url
     * @return array
     */
    public function parse($url)
    {
        // Split the URL into its constituent parts
        $parse = parse_url($url);

        // Remove the leading forward slash, if there is one
        $path = ltrim((isset($parse['path']) ? $parse['path'] : ""), '/');

        // Put each element into an array
        $elements = explode('/', $path);

        // Create a new empty array
        $args = array();

        // Create a new empty array for parameters
        $paramArgs = array();

        // Loop through each pair of elements
        for ($i = 0; $i < count($elements); $i++) {
            if ($i == 0 && !empty($elements[$i])) {
                $args["controller"] = $elements[$i] == "index.php" ? "" : $elements[$i];
            } else if ($i == 1 && !empty($elements[$i])) {
                $args["action"] = $elements[$i];
            } else if ($i > 1 && !empty($elements[$i])) {
                $paramArgs[$i] = $elements[$i];
            }

        }

        // Check if entries object is empty to start replacement process
        if (count(array_filter($paramArgs)) == 0) {
            goto skip_process;
        }

        // Push parameters into args array
        $args["params"] = $paramArgs;

        skip_process:
        return $args;
    }

}
