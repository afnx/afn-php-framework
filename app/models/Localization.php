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
 * Class Localization
 * Localization Model
 *
 * It provides all localization operations by fetching data from Localization table.
 * It bases on languages.
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Localization extends Model
{

    /**
     * Stores the row id of content from the table
     * @var integer
     */
    public $id;

    /**
     * Stores name of content from the table
     * @var string
     */
    public $identifier;

    /**
     * Stores original content from the table
     * @var string
     */
    public $label;

    /**
     * Stores language of content from the table
     * @var string
     */
    public $lang;

    /**
     * Stores delete status of content from the table
     * @var integer
     */
    public $is_deleted;

    /**
     * Stores user site language
     * @var string
     */
    private $userLang;

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

        // Find the user language and pass it
        $this->userLang = isset($_SESSION["language"]) ? $_SESSION["language"] : \AFN\App\Core\Server::detectLang();

        // Pass the table name to core model
        $this->table = (new \ReflectionClass($this))->getShortName();

        // Fetch data from database and pass it to properties
        $this->refresh($id);
    }

    /**
     * Translates identifiers to original content according to the given language
     * @param string $name Name of content from the table
     * @param string $language Original content from the table
     * @param boolean $forced 0 try to translate other languages, 1 translate only the given language
     * @return string The original content according to the given language or name of it
     */
    public function translate($name, string $language = null, bool $forced = true)
    {

        // Find a language
        $lastLang = (is_null($language) ? $this->userLang : $language);

        // Prepare the query
        $query = "SELECT label FROM Localization WHERE lang=:lang AND identifier=:identifier AND is_deleted<>1 LIMIT 1";

        // Fetch the content
        $resource = $this->fetch($query, [":lang" => $lastLang, ":identifier" => $name], true, __CLASS__);

        // If the process is not ok, try other languages when the parameter of $forced is TRUE
        if ($this->recordCount != 1) {
            if ($lastLang != $GLOBALS["settings"]["default_lang"] && $forced == true) {
                // Fetch it again for other languages
                $resource = $this->fetch($query, [":lang" => $GLOBALS["settings"]["default_lang"], ":identifier" => $name], true, __CLASS__);
                $returner = $resource->label;
            } else {
                // If the process fails, return only name of content
                $returner = "**" . $name . "**";
            }
        } else {
            $returner = $resource->label;
        }

        // Return original content or name of it
        return $returner;
    }

}
