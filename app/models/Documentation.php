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
 * Class Documentation
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Documentation extends Model
{

    /**
     * Stores the row id of content
     * @var integer
     */
    public $id;

    /**
     * Stores title
     * @var string
     */
    public $title;

    /**
     * Stores description
     * @var string
     */
    public $description;

    /**
     * Stores tag
     * @var string
     */
    public $tag;

    /**
     * Stores parent id
     * @var string
     */
    public $parent_id;

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
     * Fetches all documents data from table
     * @return array
     */
    public function getAllDocumentation()
    {
        $query = "SELECT * FROM Documentation WHERE is_deleted<>1";
        return $this->fetchAll($query, [], true);
    }

}
