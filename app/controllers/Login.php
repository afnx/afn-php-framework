<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Controllers;

use AFN\App\Core\Controller;
use \AFN\App\Models\Users;

/**
 * Class Login
 * Login page and processes
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Login extends Controller
{
    /**
     * Stores user email or username
     * @var string
     */
    public $emailOrUsn;

    /**
     * Stores user password
     * @var string
     */
    public $password;

    /**
     * Stores view class
     * @var object
     */
    public $view;

    /**
     * Stores Localization class
     * @var object
     */
    public $loc;

    /**
     * Stores error class
     * @var object
     */
    public $errors;

    /**
     * Stores whether captcha is active or not
     * @var boolean
     */
    public $captchaStatus = false;

    public function __construct()
    {
        $this->secure = new \AFN\App\Core\Secure();
        $this->server = new \AFN\App\Core\Server();

        if ($this->secure->checkUserLogin() == true) {
            $this->server->redirect("/");
            exit();
        }

        $this->view = new \AFN\App\Core\View();
        $this->errors = new \AFN\App\Core\Errors();
        $this->loc = new \AFN\App\Models\Localization();
        $this->emailOrUsn = isset($_POST["emailOrUsername"]) ? $_POST["emailOrUsername"] : null;
        $this->password = isset($_POST["password"]) ? $_POST["password"] : null;
        $this->captcha = $GLOBALS['captchas'];
    }

    /**
     * Displays Login page
     */
    public function display()
    {
        $this->view->viewFile = "login";
        if ($this->errors->status == true) {
            $this->view->addFile([
                'login_alert' => 'components/alerts/login_alert',
            ]);

            // Append error loop to view
            $this->view->newEntry(1, $this->errors->getErrorsSimpInArray("loop_1", "alert_text"));

            if ($this->captchaStatus == true) {
                $this->view->addFile([
                    'captcha' => 'components/captcha/normal',
                ]);
                $this->view->newEntry(1, [
                    'captcha_site_key' => $this->captcha["login"]["site_key"],
                ]);
            }
        }

        if (isset($this->emailOrUsn)) {
            $emailOrUsnVal = $this->emailOrUsn;
        } else if (isset($_COOKIE["emailOrUsn"]) && $this->errors->status != true) {
            $emailOrUsnVal = $_COOKIE["emailOrUsn"];
        } else {
            $emailOrUsnVal = "";
        }

        $this->view->newEntry(1, [
            'email_or_username' => $emailOrUsnVal,
            "navbar_login_active" => "active",
        ]);

        echo $this->view->generateMarkup();
    }

    /**
     * Executes login proccess for the user
     */
    public function go()
    {
        // Check whether username is empty or not, then add the error
        if (empty($this->emailOrUsn)) {
            $this->errors->newError($this->loc->translate("Please enter your username or email and your password"), "username");
        } else if (empty($this->password)) {
            // Check whether password is empty or not, then add the error
            $this->errors->newError($this->loc->translate("Please enter your username or email and your password"), "password");
        }

        // Check there is any error
        if ($this->errors->status != true) {
            // Find the user on table with email or username given by the user
            $checkUserP = Users::findUserWithEmailOrUsn($this->emailOrUsn);

            // Calculate elapsed time since the user's last attempt for login
            $timeDifference = time() - $checkUserP->login_attempt_time;

            // If the elapsed time is longer than 86400(24 hours), reset attempt columns on table
            if ($timeDifference >= 86400 && $checkUserP->login_attempt >= 3) {
                $checkUserP->login_attempt = 0;
                $checkUserP->login_attempt_time = 0;
                $checkUserP->save();
            }

            /*
             * If login attempts are more than three times and the elapsed time shorter than 600(10 minutes),
             * show captcha to check whether it is a robot or human.
             */
            if ($checkUserP->login_attempt >= 3 && $timeDifference < 600) {
                // Check whether captcha exists, then add the error
                if (empty($_POST['g-recaptcha-response'])) {
                    $this->errors->newError($this->loc->translate("Please verify that you are not a robot."));
                    $this->captchaStatus = true;
                } else {

                    // Define Google Captcha
                    $recaptcha = $_POST['g-recaptcha-response'];
                    $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $this->captcha["login"]["secret_key"] . "&response=" . $recaptcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']), true);

                    // Check whether captcha is ok, then add the error
                    if ($response['success'] == false) {
                        $this->errors->newError($this->loc->translate("Sorry. We do not verify that you are not a robot. You cannot sign up."));
                        $this->captchaStatus = true;
                    } else {
                        // Match passwords
                        if (!password_verify($this->password, $checkUserP->password)) {
                            $this->errors->newError($this->loc->translate("Password failed"), "password_match");
                            $this->captchaStatus = true;
                        }
                    }
                }
            } else {
                // Match Passwords
                if (!password_verify($this->password, $checkUserP->password)) {
                    // Increase login attempt number
                    $nowloginAttempt = $checkUserP->login_attempt + 1;
                    // If login attempt greater than 3, make it 3 otherwise increase just one time
                    if ($nowloginAttempt >= 3) {
                        $checkUserP->login_attempt = 3;
                        $checkUserP->login_attempt_time = time();
                    } else {
                        $checkUserP->login_attempt = $nowloginAttempt;
                        $checkUserP->login_attempt_time = 0;
                    }

                    // Save the changes
                    $checkUserP->save();

                    // ADd a error
                    $this->errors->newError($this->loc->translate("You have entered invalid username or password."));
                }
            }
        }

        if ($this->errors->status != true) {
            $user = new Users($checkUserP->id);

            // Process server session works
            $_SESSION["user_id"] = $user->id;
            $_SESSION["full_name"] = $user->full_name;
            $_SESSION["username"] = $user->username;
            $_SESSION['language'] = $user->language;

            // Save user ip
            $ipResult = (new \AFN\App\Models\IPLogs())->findIpRecordWithUserId($checkUserP->id);
            if ($ipResult == null or ip2long($this->server->findIp()) != $ipResult['address']) {
                $ipLogs = new \AFN\App\Models\IPLogs();
                $ipLogs->user_id = $checkUserP->id;
                $ipLogs->address = ip2long($this->server->findIp());
                $ipLogs->date = $this->server::getNow();
                $ipLogs->save();
            }

            // Reset login attempts
            if ($user->login_attempt != 0 || $user->login_attempt_time != 0) {
                $user->login_attempt = 0;
                $user->login_attempt_time = 0;
            }

            // Refresh last login date
            $user->last_login = $this->server::getNow();

            // Save all changes
            $user->save();

            // Set cookies
            setcookie("emailOrUsn", $this->emailOrUsn, time() + 84600, "/", $GLOBALS['settings']['site_url_without_http'], true, true);

            // Redirect profile page
            $this->server->redirect("/");
        } else {
            // Print all errors
            $this->display();
        }

    }
}
