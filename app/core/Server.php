<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

use AFN\App\Controllers\p404Controller;

/**
 * Class Server
 * Server Activities
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Server
{

    /**
     * Sends a HTTP request
     * @param string $url
     * @param boolean $permanent
     */
    public static function redirect($url, $permanent = false)
    {

        if ($permanent) {
            header('HTTP/1.1 301 Moved Permanently');
        }
        echo '<META HTTP-EQUIV="Refresh" Content="0; URL=' . $url . '">';
        exit();
    }

    /**
     * Sends the previous HTTP request
     */
    public static function redirectBack()
    {

        session_write_close();
        echo '<META HTTP-EQUIV="Refresh" Content="0; URL=' . getenv("HTTP_REFERER") . '">';
        exit();
    }

    /**
     * Displays 404 Not Found Page
     */
    public static function p404()
    {
        $p404Controller = new p404Controller();
        $p404Controller->display();
    }

    /**
     * Displays Default Page
     */
    public static function defaultPage()
    {
        $main = new DefaultController();
        $main->display();
    }

    /**
     * Gets current time from database for a inserting operation
     * @return string
     */
    public static function getNow()
    {
        $run_sql = new \AFN\App\Core\Model();
        $query = "SELECT NOW() AS date";
        $now = $run_sql->fetch($query);
        return $now->date;
    }

    /**
     * Finds user ip
     * @return string
     */
    public function findIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            $_SERVER['REMOTE_ADDR'] = filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
        } else
        if (isset($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            $_SERVER['REMOTE_ADDR'] = filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
        } else {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            if (isset($_SERVER['HTTP_X_REAL_IP'])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
            }
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Finds user country with given ip
     * @param string $ip
     * @return string
     */
    public function findCountryWithIp($ip)
    {
        $numbers = preg_split("/\./", $ip);
        require_once STO_DIR . "/ip_files/" . $numbers[0] . ".php";
        $code = ($numbers[0] * 16777216) + ($numbers[1] * 65536) + ($numbers[2] * 256) + ($numbers[3]);
        foreach ($ranges as $key => $value) {
            if ($key <= $code) {
                if ($ranges[$key][0] >= $code) {
                    $country = $ranges[$key][1];
                    break;
                }
            }
        }
        if ($country == "") {
            $country = "unkown";
        }
        return $country;
    }

    /**
     * Finds user language
     * @return string
     */
    public static function detectLang()
    {
        $knownLangs = $GLOBALS["settings"]["languages"];

        if (!isset($_COOKIE['language'])) {
            $noCookie = 1;
        } else {
            $noCookie = 0;
        }
        if ($noCookie != 1) {
            if (in_array($_COOKIE['language'], $knownLangs)) {
                return $_COOKIE['language'];
            }
        } else {
            $userPrefLangs = !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) : array();
            $language = '';
            foreach ($userPrefLangs as $idx => $lang) {
                $lang = substr($lang, 0, 2);
                if (in_array($lang, $knownLangs)) {
                    $language = $lang;
                    break;
                }
            }

            if (empty($language)) {
                $language = $GLOBALS["settings"]["default_lang"];
            }

            return $language;
        }
    }

}
