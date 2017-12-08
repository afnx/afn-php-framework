<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

use PDO;

/**
 * Mysql Database Operations
 *
 * @author alifuatnumanoglu
 */
class Model {

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
    public $recordcount;

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
    public function __construct($dbno = 1) {
        try {
            // Check if ROOT_DIR is defined then use it or add it manually for the config file path
            if(!defined(ROOT_DIR)) {
                $db_dir = realpath(__DIR__ . '/../..') . '/config/database.php';
            } else {
                $db_dir = ROOT_DIR . '/config/database.php';
            }

            // Include the database config file
            $database = require_once $db_dir;

            // Run database connection with PDO and pass it to the variable
            $this->conn = new PDO($database[$dbno]["DSN"], $database[$dbno]["USERNAME"], $database[$dbno]["PASSWORD"], [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="NO_BACKSLASH_ESCAPES"'
            ]);
        } catch(PDOException $e) {
            // If the process fails, throw an error
            echo $e->getMessage();
        }
    }

    /**
     * Passes the given value to the given property
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        $this->$property = $value;
    }

    /**
     * Returns value of the property if it is defined
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if(isset($this->$property)) {
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
    public function execute_on_query($query, array $params = [], $count = FALSE) {
        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            if($count == TRUE) {
                $this->recordcount = $stmt->rowCount();
            } else {
                $this->recordcount = 0;
            }
            return $stmt;
        } catch(PDOException $e) {
            echo $e->getMessage();
            return FALSE;
        }
    }

    /**
     * @param object $stmt
     * @return array
     */
    public function get_num_rows($stmt) {
        return (is_resource($stmt) ? $stmt->fetchColumn() : 0);
    }

    /**
     * Fetches all column names of the given table as an array
     * @param string $tableName
     * @return boolean or array
     */
    public function get_columns_name(string $tableName = "") {
        if(!empty($this->table) || !empty($tableName)) {
            $tname = isset($tableName) ? $tableName : $this->table;
            $query = "DESCRIBE " . $tname;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $tableFields = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return $tableFields;
        } else {
            return FALSE;
        }
    }

    /**
     * Prepares data according to JSON format
     * @param array $array
     * @return string
     */
    public function json_turkish(array $array) {
        foreach($array as $record) {
            foreach($record as $key => $og) {
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
     * @param integer $ID The row id of the expected content in the database
     * @return string The Error
     */
    public function refresh($ID) {
        if(is_numeric($ID)) {
            $query = "SELECT * FROM " . $this->table . " WHERE " . key($this) . " = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $ID, PDO::PARAM_INT);

            if(!$stmt->execute()) {
                $error = $stmt->errorInfo();
                return 'ERROR: ' . $error[2];
            } else {
                $result = $stmt->fetch(PDO::FETCH_BOTH);
                if(isset($result)) {
                    foreach($result as $name => $value) {
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
    public function refresh_procedure($query, array $params = []) {
        if($query != "") {
            $stmt = $this->conn->prepare($query);
            if(!$stmt->execute($params)) {
                $error = $stmt->errorInfo();
                return 'ERROR: ' . $error[2];
            } else {
                $result = $stmt->fetch(PDO::FETCH_BOTH);
                if($result) {
                    foreach($result as $name => $value) {
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
    public function to_json() {
        $classItems = get_object_vars($this);
        $json = "{";
        foreach($classItems as $key => $val) {
            if($key !== 'table' && $key !== 'conn' && !is_numeric($key)) {
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
    public function save() {
        // Get class properties and pass them
        $classitems = get_object_vars($this);

        // Get column names
        $columns = $this->get_columns_name();

        // Create needed arrays
        $data_fields = [];
        $data_names = [];
        $params = [];

        // Push names and values of properties to arrays if property names does not match column names
        foreach($classitems as $claskey => $clasval) {
            foreach($columns as $colkey => $colval) {
                if($claskey == $colval) {
                    $data_fields[] = $colval;
                    $data_names[] = ":" . $colval;

                    // Determine the form of NULL according to numericalness of $classval
                    if(is_numeric($clasval)) {
                        $params[":" . $colval] = str_replace("'on'", "True", str_replace("'NULL'", "NULL", is_null($clasval) ? "NULL" : $clasval));
                    } else {
                        $params[":" . $colval] = str_replace("'on'", "True", str_replace("'NULL'", "NULL", "'" . is_null($clasval) ? "NULL" : $clasval . "'"));
                    }
                }
            }
        }

        // Begin the transaction
        $this->conn->beginTransaction();

        // If the id exists, the process is updating. However, if not, the process is inserting.
        if(!is_numeric($this->ID) || $this->ID == 0) {
            $sql = "INSERT INTO " . $this->table . " (" . implode(",", $data_fields) . ") VALUES " . implode(',', $data_names);
        } else {
            $sql = "UPDATE " . $this->table . " SET ";
            foreach($data_fields as $fval) {
                foreach($data_names as $nval) {
                    if($fval == str_replace(":", "", $nval) && $fval != "ID") {
                        $sql .= $fval . "=" . $nval;
                    }
                }
            }
            $sql .= " WHERE ID=:ID";
        }

        // Prepare a statement for above execution
        $stmt = $this->conn->prepare($sql);

        try {

            $stmt->execute($params);

            // Get last inserted id after inserting process or get the id of updated row
            if(substr($sql, 0, 6) == "INSERT") {
                $operation = 1;
                $last_insert_id = $this->conn->lastInsertId();
            } else {
                $operation = 2;
                $last_insert_id = $this->ID;
            }

            // History operation will be here soon
            // $this->new_history($lastInsert, $operation);

            return $last_insert_id;
        } catch(PDOException $e) {
            // If the execution fails, throw an error
            echo $e->getMessage();
            return 0;
        }

        // Send all to database
        $this->conn->commit();
    }

    /**
     * Generate a globally unique identifier
     * @return string
     */
    public function custom_create_guid() {
        if(function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); // Optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = ""// "{"
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
    public function delete($force = 0) {
        // Check requested operation and run it
        if($force == 1) {
            // Permanent deleting
            $sql = "DELETE FROM " . $this->table . " WHERE " . key($this) . "=:ID";
        } else {
            // Recoverable deleting
            $sql = "UPDATE " . $this->table . " SET isDeleted=1 WHERE " . key($this) . "=:ID";
        }

        // Prepare a statement for execution
        $stmt = $this->conn->prepare($sql);

        // Send id of record that will be deleted
        $stmt->bindParam(':ID', $this->ID, PDO::PARAM_INT);

        try {
            $stmt->execute();
            // History operation will be here soon
            // $this->new_history($this->ID, 3);
            return TRUE;
        } catch(PDOException $e) {
            // If the execution fails, throw an error
            echo $e->getMessage();
            return FALSE;
        }
    }

    /**
     * Deletes all records in the given table
     * @return boolean
     */
    public function delete_all() {
        $sql = "DELETE FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute();
            // history operation
            // $this->new_history($this->ID, 3);
            return TRUE;
        } catch(PDOException $e) {
            // If the execution fails, throw an error
            echo $e->getMessage();
            return FALSE;
        }
    }

    /**
     * Returns the count of records in the given table
     * @return integer The count of records
     */
    public function table_record_count() {
        $query = "SELECT COUNT(*) FROM " . $this->table;
        $result = $this->conn->query($query)->fetchColumn();
        return $result;
    }

    public function fetch($query, array $params = [], $count = FALSE) {
        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            if($count == TRUE) {
                $this->recordcount = $stmt->rowCount();
            } else {
                $this->recordcount = 0;
            }
            return $stmt->fetch();
        } catch(PDOException $e) {
            echo $e->getMessage();
            return FALSE;
        }
    }

    public function fetch_all($query, array $params = [], $count = FALSE) {
        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            if($count == TRUE) {
                $this->recordcount = $stmt->rowCount();
            } else {
                $this->recordcount = 0;
            }
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            echo $e->getMessage();
            return FALSE;
        }
    }

    public function query($query, array $params = []) {
        $stmt = $this->conn->prepare($query);
        try {
            return $stmt->execute($params);
        } catch(PDOException $e) {
            echo $e->getMessage();
            return FALSE;
        }
    }

    public function fetch_column($query, array $params = [], $count = FALSE) {
        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            if($count == TRUE) {
                $this->recordcount = $stmt->rowCount();
            } else {
                $this->recordcount = 0;
            }
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            echo $e->getMessage();
            return FALSE;
        }
    }

    /*
     * History operation will be in use soon
     * @param type $ID
     * @param type $operation
     * @param type $userID
     * @return boolean

      public function new_history($ID, $operation, $userID = 0) {
      //operation 1-New 2-Update 3-Delete
      $sql = "INSER INTO History (tableName,tableID,userID,operation,updated) VALUES (':table',:ID,:userID,:operation,NOW())";
      $stmt = $this->conn->prepare($sql);
      $stmt->bindParam(':table', $this->table, PDO::PARAM_STR);
      $stmt->bindParam(':ID', $ID, PDO::PARAM_INT);
      $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
      $stmt->bindParam(':operation', $operation, PDO::PARAM_INT);
      try {
      $stmt->execute();
      return TRUE;
      } catch(PDOException $e) {
      echo $e->getMessage();
      return FALSE;
      }
      }
     */
}
