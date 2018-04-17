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
 * Class IPLogs
 * Users Model
 *
 * @package AFN-PHP-FRAMEWORK
 */
class IPLogs extends Model
{

    /**
     * Stores the row id of content
     * @var integer
     */
    public $id;

    /**
     * Stores user id
     * @var string
     */
    public $user_id;

    /**
     * Stores guest or user ip address
     * @var string
     */
    public $address;

    /**
     * Stores inserting date
     * @var string
     */
    public $date;

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
     * Finds ip records with user id
     * @param integer $user_id
     * @return array
     */
    public function findIpRecordWithUserId($user_id)
    {
        $query = "SELECT * FROM IPLogs WHERE user_id=:user_id ORDER BY id DESC LIMIT 1";
        return $this->fetch($query, ['user_id' => $user_id]);
    }

}
