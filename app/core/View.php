<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

/**
 * Viewing HTML files by rendering them using data
 *
 * @author alifuatnumanoglu
 */
class View {

    /**
     * Stores the location of view file
     * @var string
     */
    public $view_file;

    /**
     * Stores the contents of the view file
     * @var string
     */
    private $_view;

    /**
     * Stores all datas passed by a controller
     * @var array
     */
    public $entries = array();

    /**
     * Stores localization data from database
     * @var array
     */
    public $localization = array();

    /**
     * Construct View Class
     */
    public function __construct($view_file = NULL) {
        $this->view_file = $view_file;
    }

    /**
     * Generates markup by inserting entry data into the view file
     * @param array $extra Extra data for the header/footer
     * @return string The HTML with entry data inserted into the view
     */
    public function generate_markup(array $extra) {
        $this->_load_view();
        return $this->_parse_view($extra);
    }

    /**
     * Loads a view file with which markup should be formatted
     * @return string The contents of the view file
     */
    private function _load_view() {

        // Check if the provided file path contains '/views/' then remove it
        if(strpos($this->view_file, "/views/") !== FALSE) {
            $this->view_file = substr($this->view_file, strpos($this->view_file, '/views/'));
        }

        // Check if the provided file path contains '.php' then remove it
        if(strpos($this->view_file, ".php") !== FALSE) {
            $this->view_file = substr($this->view_file, strpos($this->view_file, '.php'));
        }

        // add root dir to beginning of the file path
        if(!defined(ROOT_DIR)) {
            $this->view_file = realpath(__DIR__ . '/../..') . '/resources/views/' . $this->view_file . ".php";
        } else {
            $this->view_file = ROOT_DIR . '/resources/views/' . $this->view_file . ".php";
        }

        // If the file does not exists or it is not readable, use default file after check default file being ok
        if(file_exists($this->view_file) && is_readable($this->view_file)) {
            $path = $this->view_file;
        } else if(file_exists($default_file = realpath(__DIR__ . '/../..') . '/resources/views/default.php') && is_readable($default_file)) {
            $path = $default_file;
        } else {
            throw new Exception("No default view found!");
        }

        // Pass the result to $_view
        $this->_view = file_get_contents($path);
    }

    /**
     * Separates the template into header, loop, and footer for parsing
     * @param array $extra
     *
     * Additional content for the header/footer
     * @return string The entry markup
     */
    private function _parse_view($extra = NULL) {
        // Create an alias of the template file property to save space
        $view = $this->_view;

        // Remove any PHP-style comments from the template
        $comment_pattern = array('#/\*.*?\*/#s', '#(?<!:)//.*#');
        $view = preg_replace($comment_pattern, NULL, $view);

        // Define a regex to match any view tag for data provided by database
        $tag_pattern = self::pattern_creator(1, "data");

        // Define regex to match language tag
        $tag_pattern = self::pattern_creator(1, "language");

        $markup = NULL;

        // Replace matched tag with entries
        $markup = $this->replace_loop($this->entries, $tag_pattern, $_view);

        // Language
        // $lang_pattern = self::pattern_creator(0, "lang");

        // Extract the main entry loop from the file
        $pattern = '#.*@loop{begin}(.*?)@loop{end}.*#is';
        $entry_view = preg_replace($pattern, "$1", $view);

        $markup .= $this->loop_handler($tag_pattern, $entry_view);

        // If extra data was passed to fill in the header/footer, parse it here
        /* if(is_object($extra)) {
          foreach($extra as $key => $props) {
          //$key = preg_replace_callback($tag_pattern, $callback(serialize($extra->$key)), $key);
          }
          } */

        // Return the formatted entries
        return $entry_view;
    }

    /**
     * Replaces view tags with the corresponding entry data
     * @param string $entry A serialized entry object
     * @param array $params Parameters for replacement
     * @param array $matches The match array from preg_replace_callback()
     * @return string The replaced template value
     */
    public static function replace_tags($key, $value, $tag_pattern, $_view) {
        $_view = str_replace(self::pattern_converter($tag_pattern, $key), $value, $_view);
        return $_view;
    }

    public static function replace_all_tags($key, $value, $tag_pattern, $_view) {
        $pattern = "/" . self::pattern_converter($tag_pattern, $key) . "/";
        $_view = preg_replace($pattern, $value, $_view);
        return $_view;
    }

    public function replace_loop($data, $tag_pattern, $_view) {
        foreach($data as $key => $value) {
            $_view = self::replace_all_tags($key, $value, $tag_pattern, $_view);
        }
    }

    /**
     * Creates new patterns according to given parameters
     * @param int $type 0 word-digit, 1 digit, 2 letters, 3 everything
     * @param string $_prefix The beginning of identifier
     * @param string $custom_s_definition Custom definition
     * @return string The pattern
     */
    public static function pattern_creator(int $type, string $_prefix = "", string $custom_s_definition = "") {
        // Prepare definition of string to find
        switch($type) {
            // Custom characters
            case 0 :
                $s_definition = $custom_s_definition;
                break;
            // Must contain only word and digit characters
            case 1 :
                $s_definition = '(\w+)';
                break;
            // Must contain only digit characters
            case 2 :
                $s_definition = '(\d+)';
                break;
            // Must contain only letters
            case 3 :
                $s_definition = '([a-z]+)';
                break;
            // May contain any characters
            case 4 :
                $s_definition = '(.*?)';
                break;
            // Must contain only word and digit characters as default
            default :
                $s_definition = '(\w+)';
                break;
        }

        // Complete pattern with its parts
        $pattern = '/@' . $_prefix . '{' . $s_definition . '}/is';

        // Return final state of the pattern
        return $pattern;
    }

    public static function pattern_converter($pattern, $key) {
        preg_match('~/(.*?)/~', $pattern, $slashes_output);
        preg_match('/{(.*?)}/', $slashes_output[1], $braces_output);
        $result = str_replace($braces_output[1], $key, $slashes_output[1]);
        return $result;
    }

    public function loop_handler($tag_pattern, $_view) {
        // Process each entry and insert its values into the loop
        $markup = NULL;
        for($i = 0, $c = count($this->entries); $i < $c; ++$i) {
            $markup .= preg_replace_callback($tag_pattern, function ($matches) use($i, $tag_pattern) {
                // Make sure the view tag has a matching array element
                if(array_key_exists($i, $this->entries) && !empty($this->entries[$i][$matches[1]])) {
                    // Grab the value from the Entry object
                    $value = $this->entries[$i][$matches[1]];
                    // Remove the entry from entries array after grabbibg
                    unset($this->entries[$i][$matches[1]]);
                    // Return the value
                    return $value;
                } else {
                    // Otherwise, simply return the tag as is
                    return self::pattern_converter($tag_pattern, $matches[1]);
                }
            }, $_view);
        }
        // Return the final statement
        return $markup;
    }

}
