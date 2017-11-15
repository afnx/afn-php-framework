<?php

/*
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

    private $db;
    private $conn;
    public $recordcount;
    public $table;

    public function __construct($dbno = 1) {
        try {
            if(!defined(ROOT_DIR)) {
                $db_dir = realpath(__DIR__ . '/../..') . '/config/database.php';
            } else {
                $db_dir = ROOT_DIR . '/config/database.php';
            }

            $database = require_once $db_dir;

            $this->conn = new PDO($database[$dbno]["DSN"], $database[$dbno]["USERNAME"], $database[$dbno]["PASSWORD"], [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="NO_BACKSLASH_ESCAPES"'
            ]);
        } catch(PDOException $e) {
            echo $e->getMessage();
        }
    }

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

    public function get_num_rows($stmt) {
        return (is_resource($stmt) ? $stmt->fetchColumn() : 0);
    }

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
                if($result) {
                    foreach($result as $name => $value) {
                        $this->$name = $value;
                    }
                }
            }
        }
    }

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

    public function save() {
        $classitems = get_object_vars($this);
        $columns = $this->get_columns_name();

        $data_fields = [];
        $data_names = [];
        $params = [];

        foreach($classitems as $claskey => $clasval) {
            foreach($columns as $colkey => $colval) {
                if($claskey == $colval) {
                    $data_fields[] = $colval;
                    $data_names[] = ":" . $colval;
                    if(is_numeric($clasval)) {
                        $params[":" . $colval] = str_replace("'on'", "True", str_replace("'NULL'", "NULL", is_null($clasval) ? "NULL" : $clasval));
                    } else {
                        $params[":" . $colval] = str_replace("'on'", "True", str_replace("'NULL'", "NULL", "'" . is_null($clasval) ? "NULL" : $clasval . "'"));
                    }
                }
            }
        }

        $this->conn->beginTransaction();

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

        $stmt = $this->conn->prepare($sql);

        try {

            $stmt->execute($params);

            if(substr($sql, 0, 6) == "INSERT") {
                $operation = 1;
                $last_insert = $this->conn->lastInsertId();
            } else {
                $operation = 2;
                $last_insert = $this->ID;
            }

            // history operation
            // $this->new_history($lastInsert, $operation);

            return $last_insert;
        } catch(PDOException $e) {
            echo $e->getMessage();
            return 0;
        }
        $this->conn->commit();
    }

    public function custom_create_guid() {
        if(function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); // optional for php 4.2.0 and up.
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

    public function delete($force = 0) {
        if($force == 1) {
            $sql = "DELETE FROM " . $this->table . " WHERE " . key($this) . "=:ID";
        } else {
            $sql = "UPDATE " . $this->table . " SET isDeleted=1 WHERE " . key($this) . "=:ID";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ID', $this->ID, PDO::PARAM_INT);

        try {
            $stmt->execute();
            // history operation
            // $this->new_history($this->ID, 3);
            return TRUE;
        } catch(PDOException $e) {
            echo $e->getMessage();
            return FALSE;
        }
    }

    public function delete_all() {
        $sql = "DELETE FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute();
            // history operation
            // $this->new_history($this->ID, 3);
            return TRUE;
        } catch(PDOException $e) {
            echo $e->getMessage();
            return FALSE;
        }
    }

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

}
