<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Models;

use AFN\App\Core\Model;

/**
 * Class Users
 * Users Model
 *
 * It provides all users information by fetching data from Users table.
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Users extends Model
{

    /**
     * Stores the row id of content
     * This id can be used as user id
     * @var integer
     */
    public $id;

    /**
     * Stores users e-mail address
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
     * Stores user full name
     * @var string
     */
    public $full_name;

    /**
     * Stores user language
     * @var string
     */
    public $language;

    /**
     * Stores user country
     * @var string
     */
    public $country;

    /**
     * Stores number of user login attempt
     * @var integer
     */
    public $login_attempt;

    /**
     * Stores time of user last login attempt in unix
     * @var integer
     */
    public $login_attempt_time;

    /**
     * Stores register date from the table
     * @var string
     */
    public $register_date;

    /**
     * Store user last login time
     * @var date
     */
    public $last_login;

    /**
     * Stores delete status of content from the table
     * @var integer
     */
    public $is_deleted;

    /**
     * Construct the class by carrying out basic operations
     * @param integer $id The row id of the expected content in the database
     * @param integer $dbno The database id
     */
    public function __construct($id = null, $dbno = 1)
    {

        // Make db connection
        parent::__construct($dbno);

        // If an id is received from outside, pass it
        $this->id = $id;

        // Pass the table name to core model
        $this->table = (new \ReflectionClass($this))->getShortName();

        // Fetch data from database and pass it to properties
        $this->refresh($id);
    }

    /**
     * Finds user by using email or username
     * @param string $emailOrUsn
     * @return object
     */
    public static function findUserWithEmailOrUsn($emailOrUsn)
    {
        $intc = new self();
        $query = "SELECT * FROM Users WHERE is_deleted<>1 AND email=:email OR username=:usn";
        $intc->refreshProcedure($query, [':email' => $emailOrUsn, ':usn' => $emailOrUsn]);
        return $intc;

    }

    /**
     * Finds user by using username
     * @param string $emailOrUsn
     * @return object
     */
    public static function findUserWithUsername($username)
    {
        $intc = new self();
        $query = "SELECT * FROM Users WHERE is_deleted<>1 AND username=:usn LIMIT 1";
        $intc->refreshProcedure($query, [':usn' => $username]);
        return $intc;
    }

    /**
     * Finds user by using email address
     * @param string $emailOrUsn
     * @return object
     */
    public static function findUserWithEmail($email)
    {
        $intc = new self();
        $query = "SELECT * FROM Users WHERE is_deleted<>1 AND email=:email LIMIT 1";
        $intc->refreshProcedure($query, [':email' => $email]);
        return $intc;
    }
}
