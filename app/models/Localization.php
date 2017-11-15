<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Models;

use AFN\App\Core\Model;

/**
 * Localization Model
 * It provides all localization operations.
 *
 * @author alifuatnumanoglu
 */
class Localization extends Model {
    /*
     * variables from table
     */
    Public $ID;
    Public $identifier;
    Public $label;
    Public $lang;
    Public $isDeleted;

    /*
     * user site language
     * @var string
     */
    private $user_lang;

    public function __construct($ID = NULL, $db = 1) {
        parent::__construct($db);

        $this->ID = $ID;

        $this->user_lang = isset($_SESSION["lang"]) ? $_SESSION["lang"] : $GLOBALS["settings"]["default_lang"];

        $this->table = get_called_class();

        $this->refresh($ID);
    }

    public function __set($property, $value) {
        $this->$property = $value;
    }

    public function __get($property) {
        if(isset($this->$property)) {
            return $this->$property;
        }
    }

    public function translate($name, $language = NULL, $forced = TRUE) {

        $lastLang = (is_null($language) ? $this->user_lang : $language);
        $query = "SELECT label FROM Localization WHERE lang=:lang AND identifier=:identifier AND isDeleted<>1 LIMIT 1";
        $resource = $this->fetch($query, [":lang" => $lastLang, ":identifier" => $name], TRUE);

        if($this->recordcount != 1) {
            if($lastLang != $GLOBALS["settings"]["default_lang"] && $forced == TRUE) {
                $resource = $this->fetch($query, [":lang" => $GLOBALS["settings"]["default_lang"], ":identifier" => $name], TRUE);
                $returner = $resource["label"];
            } else {
                $returner = "@lang('" . $name . "')";
            }
        } else {
            $returner = $resource["label"];
        }

        return $returner;
    }

    public function ea(string $text, $language = NULL, $forced = TRUE) {
        if(preg_match_all('/{@+(.*?)}/', $text, $matches)) {
            foreach($matches[1] as $value) {
                $text = str_replace('{@' . $value . '}', self::get_label($value, $language, $forced), $text);
            }
        }

        return $text;
    }

}
