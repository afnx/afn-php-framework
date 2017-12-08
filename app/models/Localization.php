<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Models;

use AFN\App\Core\Model;

/**
 * Localization Model
 *
 * It provides all localization operations by fetching data from Localization table.
 * It bases on languages.
 *
 * @author alifuatnumanoglu
 */
class Localization extends Model {

    /**
     * Stores the row id of content from the table
     * @var integer
     */
    Public $ID;

    /**
     * Stores name of content from the table
     * @var string
     */
    Public $identifier;

    /**
     * Stores original content from the table
     * @var string
     */
    Public $label;

    /**
     * Stores language of content from the table
     * @var string
     */
    Public $lang;

    /**
     * Stores delete status of content from the table
     * @var integer
     */
    Public $isDeleted;

    /**
     * Stores user site language
     * @var string
     */
    private $user_lang;

    /**
     * Construct the class by carrying out basic operations
     * @param integer $ID The row id of the expected content in the database
     * @param integer $dbno The database id
     */
    public function __construct($ID = NULL, $dbno = 1) {

        // Make db connection
        parent::__construct($dbno);

        // If an id is received from outside, pass it
        $this->ID = $ID;

        // Find the user language and pass it
        $this->user_lang = isset($_SESSION["lang"]) ? $_SESSION["lang"] : $GLOBALS["settings"]["default_lang"];

        // Pass the table name to core model
        $this->table = get_called_class();

        // Fetch data from database and pass it to properties
        $this->refresh($ID);
    }

    /**
     * Translates identifiers to original content according to the given language
     * @param string $name Name of content from the table
     * @param string $language Original content from the table
     * @param boolean $forced 0 try to translate other languages, 1 translate only the given language
     * @return string The original content according to the given language or name of it
     */
    public function translate(string $name, string $language = NULL, bool $forced = TRUE) {

        // Find a language
        $lastLang = (is_null($language) ? $this->user_lang : $language);

        // Prepare the query
        $query = "SELECT label FROM Localization WHERE lang=:lang AND identifier=:identifier AND isDeleted<>1 LIMIT 1";

        // Fetch the content
        $resource = $this->fetch($query, [":lang" => $lastLang, ":identifier" => $name], TRUE);

        // If the process is not ok, try other languages when the parameter of $forced is TRUE
        if($this->recordcount != 1) {
            if($lastLang != $GLOBALS["settings"]["default_lang"] && $forced == TRUE) {
                // Fetch it again for other languages
                $resource = $this->fetch($query, [":lang" => $GLOBALS["settings"]["default_lang"], ":identifier" => $name], TRUE);
                $returner = $resource["label"];
            } else {
                // If the process fails, return only name of content
                $returner = "@lang{'" . $name . "'}";
            }
        } else {
            $returner = $resource["label"];
        }

        // Return original content or name of it
        return $returner;
    }

}
