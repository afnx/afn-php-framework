<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

use PDO;

/**
 * Class Model
 * Mysql Database Operations
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Model
{

    /**
     * Stores database id from config file
     * @var integer
     */
    private $dbno;

    /**
     * Stores database connection
     * @var object
     */
    private $conn;

    /**
     * Stores number of records after an operation
     * @var integer
     */
    public $recordCount;

    /**
     * Stores the name of table that will be called
     * @var string
     */
    public $table;

    /**
     * Makes a database connection by using the credential in the config file
     * The credential is chosen according to database id
     * @param integer $dbno database id
     */
    public function __construct($dbno = 1)
    {
        try {
            // Check if ROOT_DIR is defined then use it or add it manually for the config file path
            if (!defined(ROOT_DIR)) {
                $db_dir = realpath(__DIR__ . '/../..') . '/config/database.php';
            } else {
                $db_dir = ROOT_DIR . '/config/database.php';
            }

            // Include the database config file
            $database = require $db_dir;

            // Run database connection with PDO and pass it to the variable
            $this->conn = new PDO($database[$dbno]["DSN"], $database[$dbno]["USERNAME"], $database[$dbno]["PASSWORD"], [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="NO_BACKSLASH_ESCAPES"',
            ]);
        } catch (PDOException $e) {
            // If the process fails, throw an error
            die($e->getMessage());
        }
    }

    /**
     * Closes database connection as soon as there are no other references
     * to a particular object, or in any order during the shutdown sequence.
     */
    public function __destruct()
    {
        try {
            $this->conn = null; //Closes connection
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * Passes the given value to the given property
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * Returns value of the property if it is defined
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        if (isset($this->$property)) {
            return $this->$property;
        }
    }

    /**
     * Executes the given query with its parameters as an array
     * Besides, it calculates record count of the query and pass it to a public variable
     * @param string $query The query that will be executed
     * @param array $params The Parameters that will be inserted to the query
     * @param boolean $count The parameter that will decide as to whether or not to calculate record count
     * @return boolean
     */
    public function executeOnQuery($query, array $params = [], $count = false)
    {
        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            if ($count == true) {
                $this->recordCount = $stmt->rowCount();
            } else {
                $this->recordCount = 0;
            }
            return $stmt;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * @param object $stmt
     * @return array
     */
    public function getNumRows($stmt)
    {
        return (is_resource($stmt) ? $stmt->fetchColumn() : 0);
    }

    /**
     * Fetches all column names of the given table as an array
     * @param string $tableName
     * @return boolean or array
     */
    public function getColumnsName(string $tableName = "")
    {
        if (!empty($this->table) || !empty($tableName)) {
            $tname = !empty($tableName) ? $tableName : $this->table;
            $query = "DESCRIBE " . $tname;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $tableFields = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return $tableFields;
        } else {
            return false;
        }
    }

    /**
     * Prepares data according to JSON format
     * @param array $array
     * @return string
     */
    public function jsonTurkish(array $array)
    {
        foreach ($array as $record) {
            foreach ($record as $key => $og) {
                $colm[] = '"' . $key . '":"' . $og . '"';
            }
            $rec[] = '{' . implode(',', $colm) . '}';
            unset($colm);
        }
        $result = '[' . implode(',', $rec) . ']';

        return $result;
    }

    /**
     * Fetches data with given id from the database and passes it to properties
     * @param integer $id The row id of the expected content in the database
     * @return string The Error
     */
    public function refresh($id)
    {
        if (is_numeric($id)) {
            $query = "SELECT * FROM " . $this->table . " WHERE " . key($this) . " = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                return 'ERROR: ' . $error[2];
            } else {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (isset($result)) {
                    foreach ($result as $name => $value) {
                        $this->$name = $value;
                    }
                }
            }
        }
    }

    /**
     * Executes given query to fetch data and then passes data to properties
     * @param string $query The query that will be executed
     * @param array $params The Parameters that will be inserted to the query
     * @return string The error or nothing
     */
    public function refreshProcedure($query, array $params = [])
    {
        if ($query != "") {
            $stmt = $this->conn->prepare($query);
            if (!$stmt->execute($params)) {
                $error = $stmt->errorInfo();
                return 'ERROR: ' . $error[2];
            } else {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    foreach ($result as $name => $value) {
                        $this->$name = $value;
                    }
                }
            }
        }
    }

    /**
     * Returns property names and property values of child classes as JSON format
     * @return string The property names and property values as JSON format
     */
    public function toJson()
    {
        $classItems = get_object_vars($this);
        $json = "{";
        foreach ($classItems as $key => $val) {
            if ($key !== 'table' && $key !== 'conn' && !is_numeric($key)) {
                $json .= "\"" . $key . "\":\"" . $val . "\",";
            }
        }
        $json = substr($json, 0, strlen($json) - 1);
        $json .= "}";
        return $json;
    }

    /**
     * The save function
     * The function deals two different situation. If the row id not exists,the function insert
     * the given data by adding new row to the table. However, if it exists, the function updates the row
     * by using new data.
     * @return int
     */
    public function save()
    {
        // Get class properties and pass them
        $classitems = get_object_vars($this);

        // Get column names
        $columns = $this->getColumnsName();

        // Create needed arrays
        $dataFields = [];
        $dataNames = [];
        $params = [];

        // Push names and values of properties to arrays if property names does not match column names
        foreach ($classitems as $claskey => $clasval) {
            foreach ($columns as $colkey => $colval) {
                if ($claskey == $colval) {
                    $dataFields[] = "`" . $colval . "`";
                    $dataNames[] = ":" . $colval;
                    // Determine the form of NULL according to numericalness of $classval
                    if (is_numeric($clasval)) {
                        $params[":" . $colval] = str_replace("'on'", "True", str_replace("'NULL'", "NULL", (is_null($clasval) ? "NULL" : $clasval)));
                    } else {
                        $params[":" . $colval] = str_replace("'on'", "True", str_replace("'NULL'", "NULL", (is_null($clasval) ? null : $clasval)));
                    }
                }
            }
        }

        // Begin the transaction
        //$this->conn->beginTransaction();

        // If the id exists, the process is updating. However, if not, the process is inserting.
        if (!is_numeric($this->id) || $this->id == 0) {
            $sql = "INSERT INTO " . $this->table . " (" . implode(",", $dataFields) . ") VALUES (" . implode(',', $dataNames) . ")";
        } else {
            $sql = "UPDATE " . $this->table . " SET ";
            $numItems = count($dataFields);
            $i = 0;
            foreach ($dataFields as $fval) {
                foreach ($dataNames as $nval) {
                    if (str_replace("`", "", $fval) == str_replace(":", "", $nval) && str_replace("`", "", $fval) != "id") {
                        $sql .= $fval . " = " . $nval;
                        if (++$i != $numItems - 1) {
                            $sql .= ', ';
                        }
                    }
                }
            }
            $sql .= " WHERE id = :id";
        }

        // Prepare a statement for above execution
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($params);

            // Get last inserted id after inserting process or get the id of updated row
            if (substr($sql, 0, 6) == "INSERT") {
                $operation = 1;
                $lastInsertId = $this->conn->lastInsertId();
            } else {
                $operation = 2;
                $lastInsertId = $this->id;
            }

            // History operation will be here soon
            // $this->newHistory($lastInsert, $operation);

            return $lastInsertId;
        } catch (PDOException $e) {
            // If the execution fails, throw an error
            echo $e->getMessage();
            return 0;
        }

        // Send all to database
        $this->conn->commit();
    }

    /**
     * Generates a globally unique identifier
     * @return string
     */
    public function customCreateGuid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); // Optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = "" // "{"
             . substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12)
                . ""; // "}"
            return $uuid;
        }
    }

    /**
     * Deletes a record in the database according to the given id
     * @param type $force 0 permanent deleting, 1 recoverable deleting
     * @return boolean The result of operation
     */
    public function delete($force = 0)
    {
        // Check requested operation and run it
        if ($force == 1) {
            // Permanent deleting
            $sql = "DELETE FROM " . $this->table . " WHERE " . key($this) . "=:id";
        } else {
            // Recoverable deleting
            $sql = "UPDATE " . $this->table . " SET is_deleted=1 WHERE " . key($this) . "=:id";
        }

        // Prepare a statement for execution
        $stmt = $this->conn->prepare($sql);

        // Send id of record that will be deleted
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        try {
            $stmt->execute();
            // History operation will be here soon
            // $this->newHistory($this->id, 3);
            return true;
        } catch (PDOException $e) {
            // If the execution fails, throw an error
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Deletes all records in the given table
     * @return boolean
     */
    public function deleteAll()
    {
        $sql = "DELETE FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute();
            // history operation
            // $this->newHistory($this->id, 3);
            return true;
        } catch (PDOException $e) {
            // If the execution fails, throw an error
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the count of records in the given table
     * @return integer The count of records
     */
    public function tableRecordCount()
    {
        $query = "SELECT COUNT(*) FROM " . $this->table;
        $result = $this->conn->query($query)->fetchColumn();
        return $result;
    }

    /**
     * Fetches all remaining results with given query and counts it by using passed parameters
     * @param string $query
     * @param array $params
     * @param boolean $count
     * @return mixed
     */
    public function fetch($query, array $params = [], $count = false)
    {
        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            if ($count == true) {
                $this->recordCount = $stmt->rowCount();
            } else {
                $this->recordCount = 0;
            }
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Returns an array containing all of the result set rows with given query and counts it by using passed parameters
     * @param string $query
     * @param array $params
     * @param boolean $count
     * @return array
     */
    public function fetchAll($query, array $params = [], $count = false)
    {
        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            if ($count == true) {
                $this->recordCount = $stmt->rowCount();
            } else {
                $this->recordCount = 0;
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Executes the query passed parametes
     * @param string $query
     * @param array $params
     * @return boolean
     */
    public function query($query, array $params = [])
    {
        $stmt = $this->conn->prepare($query);
        try {
            return $stmt->execute($params);
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Returns a single column from the next row of a result set and counts it by using passed parameters
     * @param string $query
     * @param array $params
     * @param boolean $count
     * @return boolean
     */
    public function fetchColumn($query, array $params = [], $count = false)
    {
        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            if ($count == true) {
                $this->recordCount = $stmt->rowCount();
            } else {
                $this->recordCount = 0;
            }
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
