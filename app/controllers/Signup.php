<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Controllers;

use AFN\App\Core\Controller;
use AFN\App\Models\Users;

/**
 * Class Signup
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Signup extends Controller
{
    /**
     * Stores user full name
     * @var string
     */
    public $fullName;

    /**
     * Stores user email
     * @var string
     */
    public $email;

    /**
     * Stores username
     * @var string
     */
    public $username;

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
        $this->fullName = isset($_POST["full_name"]) ? $_POST["full_name"] : null;
        $this->email = isset($_POST["email"]) ? $_POST["email"] : null;
        $this->username = isset($_POST["username"]) ? $_POST["username"] : null;
        $this->password = isset($_POST["password"]) ? $_POST["password"] : null;
    }

    /**
     * Displays Sign up page
     */
    public function display()
    {
        $this->view->viewFile = "signup";
        if ($this->errors->status == true) {
            $this->view->addFile([
                'signup_alert' => 'components/alerts/signup_alert',
            ]);

            // Append error loop to view
            $this->view->newEntry(1, $this->errors->getErrorsSimpInArray("loop_1", "alert_text"));

            $this->view->newEntry(1, [
                'full_name' => (isset($this->fullName) ? $this->fullName : ""),
                'email' => (isset($this->email) ? $this->email : ""),
                'username' => (isset($this->username) ? $this->username : ""),
                'password' => (isset($this->password) ? $this->password : ""),
            ]);

        }

        $this->view->newEntry(1, [
            "navbar_login_active" => "active",
        ]);

        echo $this->view->generateMarkup();
    }

    /**
     * Executes sign up proccess for the user
     */
    public function go()
    {
        // Check user full name
        if (empty($this->fullName)) {
            $this->errors->newError($this->loc->translate("Enter your name please."), "full_name");
        } else if (strcspn($this->fullName, '0123456789') != strlen($this->fullName) || preg_match('/[\'^£$%&*()}{@#~?!><>,|=_+¬-]/', $this->fullName)) {
            $this->errors->newError($this->loc->translate("Your name cannot contain special characters."), "full_name");
        } else if (strlen($this->fullName) > 100) {
            $this->errors->newError($this->loc->translate("Your name is too long."), "full_name");
        } else if (strlen($this->fullName) < 3) {
            $this->errors->newError($this->loc->translate("Your name is too short."), "full_name");
        } else if (strlen(trim($this->fullName)) == 0) {
            $this->errors->newError($this->loc->translate("Your name cannot be blank."), "full_name");
        }

        // Check user email address
        $contUser = Users::findUserWithEmail($this->email);
        if (empty($this->email)) {
            $this->errors->newError($this->loc->translate("Enter your email address please."), "email");
        } else if (strlen($this->email) > 100) {
            $this->errors->newError($this->loc->translate("Your email address is too long."), "email");
        } else if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors->newError($this->loc->translate("Your email address is invalid."), "email");
        } else if ($contUser->id > 0) {
            $this->errors->newError($this->loc->translate("Your email address is used by another account."), "email");
        }

        // Check user password
        $pattern = '/^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/';
        if (empty($this->password)) {
            $this->errors->newError($this->loc->translate("Enter your password please."), "password");
        } else if (strlen($this->password) > 35) {
            $this->errors->newError($this->loc->translate("Your password is too long."), "password");
        } else if (strlen($this->password) < 8) {
            $this->errors->newError($this->loc->translate("Your password is too short."), "password");
        } else if (!(preg_match($pattern, $this->password))) {
            $this->errors->newError($this->loc->translate("Your password must contain at least one uppercase letter one lower case letter and one number."), "password");
        }

        // Check user username
        $this->username = filter_var($this->username, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

        $checkUsername = Users::findUserWithUsername($this->username);

        $dir = CR_DIR;
        $files = scandir($dir);
        $fileErr = 0;
        foreach ($files as $key => $value) {
            $value = basename($value, ".php");
            if ($value == strtolower($this->username)) {
                $fileErr = 1;
            }
        }

        $notAllowedUsernames = array(
            $GLOBALS["settings"]["name"],
            $GLOBALS["settings"]["site_url"],
            "index",
            "index.php",
            "index.html",
        );

        if (in_array(strtolower($this->username), $notAllowedUsernames)) {
            $fileErr = 1;
        }

        if (empty($this->username)) {
            $this->errors->newError($this->loc->translate("Write a creative username for yourself."), "username");
        } else if ($checkUsername->id > 0) {
            $this->errors->newError($this->loc->translate("Username youve have given is in use. Please try another one."), "username");
        } else if (preg_match('/[\'^£$%&*()}{@#~?!><>,.|=_+¬-]/', $this->username)) {
            $this->errors->newError($this->loc->translate("Username cannot contain special characters."), "username");
        } else if (strlen(trim($this->username)) == 0) {
            $this->errors->newError($this->loc->translate("Username cannot be blank."), "username");
        } else if (!preg_match('/^[a-zA-Z0-9]{3,}$/', $this->username)) {
            $this->errors->newError($this->loc->translate("Username must be at least 3 character and alphanumeric."), "username");
        } else if (preg_match('/\s/', $this->username)) {
            $this->errors->newError($this->loc->translate("Username cannot contain special characters."), "username");
        } else if (strlen($this->username) > 30) {
            $this->errors->newError($this->loc->translate("Your username too long."), "username");
        } else if ($fileErr == 1) {
            $this->errors->newError($this->loc->translate("Your username invalid."), "username");
        }

        if ($this->errors->status != true) {
            // Insert user information
            $newUser = new Users();
            $newUser->full_name = $this->fullName;
            $newUser->email = $this->email;
            $newUser->username = $this->username;
            $newUser->password = password_hash($this->password, PASSWORD_DEFAULT);
            $newUser->country = $this->server->findCountryWithIp($this->server->findIp());
            $newUser->language = $_SESSION["language"];
            $newUser->register_date = $this->server->getNow();
            $newUser->last_login = $this->server->getNow();
            $result = $newUser->save();

            // Set session for user
            $_SESSION["user_id"] = $result;
            $_SESSION["full_name"] = $this->fullName;
            $_SESSION["username"] = $this->username;

            // Save user ip
            $IPLogs = new \AFN\App\Models\IPLogs();
            $IPLogs->user_id = $result;
            $IPLogs->address = ip2long($this->server->findIp());
            $IPLogs->date = $this->server->getNow();
            $IPLogs->save();

            $this->success();
        } else {
            // Print all errors
            $this->display();
        }

    }

    private function success()
    {
        $this->view->__reset();
        $this->view->viewFile = "signup_success";
        $this->view->newEntry(1, [
            'full_name' => $this->fullName,
            'email' => $this->email,
        ]);
        echo $this->view->generateMarkup();
    }
}
