<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

/**
 * Security and common processes
 *
 * @author alifuatnumanoglu
 */
class Secure {

    public function __construct() {
        if(session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function control_var($var) {
        if (filter_var($var, FILTER_VALIDATE_URL)) {
            return $var;
        } else {
            return false;
        }
    }

    private function safe_start() {
        if(empty($_SESSION['csrf_token_2'])) {
            if(function_exists('mcrypt_create_iv')) {
                $_SESSION['csrf_token_2'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
            } else {
                $_SESSION['csrf_token_2'] = bin2hex(openssl_random_pseudo_bytes(32));
            }
        }
    }

    public function secure_service($token = NULL) {
        $error = 0;

        //try all ways to check token exists
        if($token != NULL) {
            $csToken = $token;
        } else if(isset($_POST["csrf_token"])) {
            $csToken = $_POST["csrf_token"];
        } else if(isset($_GET["csrf_token"])) {
            $csToken = $_GET["csrf_token"];
        } else {
            $headers = apache_request_headers();
            $csToken = (isset($headers["Csrf-Token"]) ? $headers["Csrf-Token"] : "");
        }

        //if token exists run it
        if(!empty($csToken)) {
            $calc = $this->calc_csrf_token();
            if(hash_equals($calc, $csToken)) {
                // Continue...
            } else {
                // Log this as a warning and keep an eye on these attempts
                $error = 1;
            }
        } else {
            $error = 1;
        }

        //check referer page
        if(isset($_SERVER['HTTP_REFERER'])) {
            $pos = strpos($_SERVER['HTTP_REFERER'], getenv('HTTP_HOST'));
            if($pos === false) {
                $error = 1;
            }
        } else {
            $error = 1;
        }

        if($error == 1) {
            return false;
        } else {
            return true;
        }
    }

    public function calc_csrf_token() {
        $this->safe_start();
        return hash_hmac('sha256', 'secureZoneAFN', $_SESSION['csrf_token_2']);
    }

    public function afn_session_start() {
        session_name(settings::name . sha1(dirname(__FILE__) . '2k-]+*,3OzI!K^THTI'));
        session_cache_limiter('nocache');

        if(@session_start()) {
            $this->safe_start();
            return true;
        } else {
            die("Error:");
        }
    }

    public function afn_session_stop() {
        @session_unset();
        @session_destroy();
        return true;
    }

}
