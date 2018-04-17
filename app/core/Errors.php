<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

/**
 * Class Errors
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Errors
{

    /**
     * Stores errors with their id
     * @var array
     */
    public $errors = array();

    /**
     * Stores status of errors which says whether an error exist
     * @var boolean
     */
    public $status;

    /**
     * Stores boolean which says that whether errors should be showed
     * @var boolean
     */
    public $showErrors;

    /**
     * Stores information with their id
     * They can be showed even when there is no error
     * @var array
     */
    public $information = array();

    /**
     * Constructs Error Class
     */
    public function __construct()
    {
        $this->status = false;
        $this->showErrors = true;
    }

    /**
     * Creates new error message
     * @param string $message Error message
     * @param string $id Error id
     */
    public function newError($message, $id = "errorMsg")
    {
        // Add new error to errors array
        $this->errors[$id] = $message;
        // Show errors
        $this->status = true;
    }

    /**
     * Creates new information message
     * @param string $message Information Message
     * @param string $id Information id
     */
    public function newInfo($message, $id)
    {
        // Add new information to information array
        $this->information[$id] = $message;
    }

    /**
     * Checks an error exists
     * @param string $id Error id
     * @return boolean
     */
    public function checkErrorExist($id)
    {
        // Check the error exists and then return true if it is
        if (array_key_exists($id, $this->errors)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Removes an error
     * @param string $id Information id
     */
    public function removeError($id)
    {
        // Remove the error
        unset($this->errors[$id]);

        // Check if another error exists, otherwise change the status of error array
        if (empty($this->errors)) {
            $this->status = false;
        }
    }

    /**
     * Checks an information exists
     * @param string $id Information id
     */
    public function checkInformationExist($id)
    {
        // Check the information exists and then return true if it is
        if (array_key_exists($id, $this->information)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Removes an information
     * @param string $id Information id
     */
    public function removeInfo($id)
    {
        // Remove it
        unset($this->information[$id]);
    }

    /**
     * Changes status and appearance of errors
     * @param boolean $status Error array status
     * @param boolean $showErrors The value which specifies whether errors are showed
     */
    public function changeError($status = 0, $showErrors = true)
    {
        // Change status if it isn't 0, which avoids confusion
        if ($status != 0) {
            $this->status = ($status == false ? false : true);
        }

        // Change showErrors
        $this->showErrors = ($showErrors == false ? false : true);
    }

    /**
     * Prints all errors
     */
    public function printErrors()
    {
        // Bring together all errors and information rows in an array
        $result = array_merge_recursive($this->errors, $this->information);

        // Check the object of show_error to figure out whether errors are showed
        if ($this->showErrors == true) {
            // Add new parameter with json for showing or hiding errors
            $result["errorDetected"] = $this->status;
        }

        // echo json
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Returns ok and prints a success message
     * @param string $message
     */
    public function printSuccess($message = "")
    {
        // Create new variable for information
        $result = $this->information;

        // Check the object of show_error for template
        // These lines don't effect showing errors, but they might make changes in templates
        if ($this->showErrors == true) {
            // Add new parameter with json
            $result["errorDetected"] = false;
        }

        // Add a success message to array
        $result["success"] = $message;

        // echo json
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Prints errors without json
     */
    public function printErrorsSimp()
    {
        // Bring together all errors and information rows in an array
        $result = array_merge_recursive($this->errors, $this->information);

        // Echo error and information messages
        foreach ($result as $message) {
            echo $message;
        }
    }

    /**
     * Prints success without json
     * @param string $message
     */
    public function printSuccessSimp($message = "")
    {
        // Create new variable for information
        $result = $this->information;

        // Add a success message to array
        $result["success"] = $message;

        // Echo information messages and success message
        foreach ($result as $message) {
            echo $message;
        }
    }

    /**
     * Returns errors without json
     * @return string
     */
    public function getErrorsSimp()
    {
        // Bring together all errors and information rows in an array
        $result = array_merge_recursive($this->errors, $this->information);

        // Echo error and information messages
        $numItems = count($result);
        $i = 0;
        $allMessages = '';
        foreach ($result as $message) {
            $allMessages .= $message;
            if (++$i != $numItems) {
                $allMessages .= "<br/>";
            }
        }

        return $allMessages;
    }

    /**
     * Returns array of errors without json
     * @param string $loopName
     * @param string $errorName
     * @return array
     */
    public function getErrorsSimpInArray($loopName, $errorName)
    {
        // Bring together all errors and information rows in an array
        $result = array_merge_recursive($this->errors, $this->information);
        $numItems = count($result);
        $i = 1;
        $errorArray = [];
        foreach (array_reverse($result) as $message) {
            $errorArray[$loopName . '_' . $i] = [$errorName => $message];
            $i++;
        }
        return $errorArray;
    }

}
