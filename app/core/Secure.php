<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

/**
 * Class Secure
 * Security and common processes
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Secure
{

    /**
     * Starts the session
     */
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->server = new \AFN\App\Core\Server();
    }

    /**
     * Checks a variable to make sure for security
     * @param string $var Variable
     * @return object
     */
    public static function controlVar($var)
    {
        // Use filter_var to control var
        if (filter_var($var, FILTER_SANITIZE_URL)) {
            return $var;
        } else {
            return false;
        }
    }

    /**
     * Creates CSRF tokens after session starts
     */
    private static function safeStart()
    {
        // Check if csrf token exists
        if (empty($_SESSION['csrf_token_2'])) {
            if (function_exists('openssl_random_pseudo_bytes')) {
                $_SESSION['csrf_token_2'] = bin2hex(openssl_random_pseudo_bytes(32, $cstrong));
            } else {
                $_SESSION['csrf_token_2'] = bin2hex(openssl_random_pseudo_bytes(32));
            }
        }
    }

    /**
     * Matches CSRF Tokens
     * @param string $token
     * @return boolean
     */
    public function secureService($token = null)
    {
        $error = 0;

        // Try all ways to check token exists
        if ($token != null) {
            $csToken = $token;
        } else if (isset($_POST["tk"])) {
            $csToken = $_POST["tk"];
        } else if (isset($_GET["tk"])) {
            $csToken = $_GET["tk"];
        } else {
            $headers = apache_request_headers();
            $csToken = (isset($headers["Csrf-Token"]) ? $headers["Csrf-Token"] : "");
        }

        // If token exists run it
        if (!empty($csToken)) {
            $calc = $this->calculateCsrfToken();
            if (hash_equals($calc, $csToken)) {
                // Continue...
            } else {
                // Log this as a warning and keep an eye on these attempts
                $error = 1;
            }
        } else {
            $error = 1;
        }

        // Check referer page
        if (isset($_SERVER['HTTP_REFERER'])) {
            $pos = strpos($_SERVER['HTTP_REFERER'], getenv('HTTP_HOST'));
            if ($pos === false) {
                $error = 1;
            }
        } else {
            $error = 1;
        }

        if ($error == 1) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Calculates CSRF Token
     * @return string
     */
    public function calculateCsrfToken()
    {
        self::safeStart();
        return hash_hmac('sha256', 'secureZoneAFN', $_SESSION['csrf_token_2']);
    }

    /**
     * Starts the session(AFN style)
     * @return boolean
     */
    public static function afnSessionStart()
    {
        //session_name($GLOBALS["settings"]["name"] . sha1(dirname(__FILE__) . '2k-]+*,3OzI!K^THTI'));
        //session_cache_limiter('nocache');

        if (@session_start()) {
            self::safeStart();
            return true;
        } else {
            die("Error:");
        }
    }

    /**
     * Stops the session(AFN style)
     * @return boolean
     */
    public static function afnSessionStop()
    {
        @session_unset();
        @session_destroy();
        return true;
    }

    /**
     * Returns true if user login
     * @return boolean
     */
    public function checkUserLogin()
    {
        if (isset($_SESSION["user_id"])) {
            $status = true;
        } else {
            $status = false;
        }
        return $status;
    }

    /**
     * Redirect page to login page if user didn't login
     * @return boolean
     */
    public function controlLogin()
    {
        if ($this->checkUserLogin() == false) {
            if (isset($_SERVER["REQUEST_URI"])) {
                $url = trim($_SERVER["REQUEST_URI"], '/');
                $this->server->redirect("/login?pre=" . $url);
            } else {
                $this->server->redirect("/login");
            }
        } else {
            return true;
        }
    }

}
