<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

use AFN\App\Models\Localization;

/**
 * Viewing HTML files by rendering them using data
 *
 * @author alifuatnumanoglu
 */
class View
{

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
     * Stores all additional template files by a controller
     * @var array
     */
    public $add_files = array();

    /**
     * Construct View Class
     */
    public function __construct($view_file = null)
    {
        $this->view_file = $view_file;
    }

    /**
     * Generates markup by inserting entry data into the view file
     * @return string The HTML with entry data inserted into the view
     */
    public function generate_markup()
    {
        // Load main file and assing its contents to view object
        $this->_view = $this->_load_file($this->view_file);

        // Assembles other template files to view
        $this->assamble_other_temp_files();

        // Parsing view and replacement process
        $this->_parse_view();

        // Localization process
        $this->language_governer();

        // Removes view file from tags by sifting after all processes completed
        $this->clear_all_tags();

        // Return view which is ready to use
        return $this->_view;
    }

    /**
     * Loads a file with which markup should be formatted
     * @param string $file The path of file
     * @return string The contents of the file
     */
    private function _load_file($file)
    {

        // Check if the provided file path contains '/views/' then remove it
        if (strpos($file, "/views/") !== false) {
            $file = substr($file, strpos($file, '/views/'));
        }

        // Check if the provided file path contains '.php' then remove it
        if (strpos($file, ".php") !== false) {
            $file = substr($file, strpos($file, '.php'));
        }

        // add root dir to beginning of the file path
        if (!defined(ROOT_DIR)) {
            $file = realpath(__DIR__ . '/../..') . '/resources/views/' . $file . ".php";
        } else {
            $file = ROOT_DIR . '/resources/views/' . $file . ".php";
        }

        // If the file does not exists or it is not readable, use default file after check default file being ok
        if (file_exists($file) && is_readable($file)) {
            $path = $file;
        } else if (file_exists($default_file = realpath(__DIR__ . '/../..') . '/resources/views/default.php') && is_readable($default_file) && empty($this->_view)) {
            $path = $default_file;
        } else {
            $path = null;
        }

        // Return the result
        if ($path == null) {
            return "";
        } else {
            return file_get_contents($path);
        }
    }

    /**
     * Starts the process of replacement tags with data for parsing
     * @return boolean True
     */
    private function _parse_view()
    {

        // Create an alias of the view file property to save space
        $view = $this->_view;

        // Remove any PHP-style comments from the template
        $comment_pattern = array('#/\*.*?\*/#s', '#(?<!:)//.*#');
        $view = preg_replace($comment_pattern, null, $view);

        // Check if entries object is empty to start replacement process
        if (count(array_filter($this->entries)) == 0) {
            goto skip_process;
        }

        // Replacement process
        foreach ($this->entries as $key => $value) {
            // Find the data type
            preg_match('~_array_(\d+)~', $key, $underscore_output);
            $data_type = $underscore_output[1];

            // Define a regex to match any view tag for data provided by database
            switch ($data_type) {
                case 1:
                    $tag_pattern = self::pattern_creator(4, "data");
                    break;
                case 2:
                    $tag_pattern = self::pattern_creator(4, "template");
                    break;
                case 3:
                    $tag_pattern = self::pattern_creator(1, "lang");
                    break;
                default:
                    $tag_pattern = self::pattern_creator(4, "data");
                    break;
            }

            // Replace matched tag with entries
            $view = $this->replace_loop($value, $tag_pattern, $view);

        }

        // Change view file with the formatted one
        $this->_view = $view;

        skip_process:
        return true;
    }

    /**
     * Adds new entry to replace tags with new data
     * @param int $data_type Stores type of data such as language, general, template...
     * @param array $data Stores data as an array
     */
    public function new_entry(int $data_type = 1, array $data)
    {
        // Check if entries object is empty before adding new entry
        if (count(array_filter($this->entries)) == 0) {
            // Add first entry
            $key = "0_array_" . $data_type;
        } else {
            // Find different key from others' for new entry
            $key = '';
            $i = 0;
            do {
                $key = $i . "_array_" . $data_type;
                $i++;
            } while (array_key_exists($key, $this->entries));
        }

        // Push the value to entries object
        $this->entries[$key] = $data;
    }

    /**
     * Replaces a particular view tag with corresponding entry data(just one time)
     * @param string $key The identifier for tag
     * @param string $value Parameter for replacement
     * @param string $tag_pattern The pattern of tags
     * @param string $view The contents of view file
     * @return string The replaced view value
     */
    public static function replace_tags($key, $value, $tag_pattern, $_view)
    {
        $result_view = str_replace(self::pattern_reconverter($tag_pattern, $key), $value, $_view);
        return $result_view;
    }

    /**
     * Replaces all view tags with corresponding entry data
     * @param string $key The identifier for tag
     * @param string $value Parameter for replacement
     * @param string $tag_pattern The pattern of tags
     * @param string $view The contents of view file
     * @return string The replaced view value
     */
    public static function replace_all_tags($key, $value, $tag_pattern, $_view)
    {
        $pattern = "/" . self::pattern_reconverter($tag_pattern, $key) . "/";
        $result_view = preg_replace($pattern, $value, $_view);
        return $result_view;
    }

    /**
     * Replace views tags with the corresponding entry data for loops
     * @param array $data The data that is replaced tags
     * @param string $tag_pattern The pattern of tags
     * @param string $view The contents of view file
     * @return string The replaced view value
     */
    public function replace_loop($data, $tag_pattern, $_view)
    {
        foreach ($data as $key => $value) {
            if (substr($key, 0, 5) == "loop_") {
                // Find the loop id
                preg_match('~loop_(\d+)~', $key, $underscore_output);
                $loop_id = $underscore_output[1];

                // Extract the loop pattern
                $loop_string_ex = $this->get_string_btw($_view, "@loop{" . $loop_id . "}{start}", "@loop{" . $loop_id . "}{end}");
                $loop_pattern = '@loop{' . $loop_id . '}{start}' . $loop_string_ex . '@loop{' . $loop_id . '}{end}';

                // Replace tags with data
                $loop_string = $loop_string_ex;
                foreach ($value as $sub_key => $sub_value) {
                    $loop_string = self::replace_all_tags("loop_" . $loop_id . "_" . $sub_key, $sub_value, $tag_pattern, $loop_string);
                }

                // Append the entry data to the loop pattern
                $_view = str_replace($loop_pattern, $loop_pattern . $loop_string, $_view);

                // Remove the loop element from the array in order to prevent confusion
                unset($data[$key]);

                // Check if there is a loop element that was not processed yet
                $remove_loop = 1;
                foreach ($data as $cont_key => $cont_value) {
                    if (strpos($cont_key, "loop_" . $loop_id) !== false) {
                        $remove_loop = 0;
                    }
                }
                // If all elements of the loop were processed, remove loop pattern
                if ($remove_loop == 1) {
                    $_view = str_replace($loop_pattern, '', $_view);
                }

            } else {
                // Replace tags with entry data
                $_view = self::replace_all_tags($key, $value, $tag_pattern, $_view);

                // Remove the loop element from the array in order to prevent confusion
                unset($data[$key]);
            }
        }

        return $_view;
    }

    /**
     * Gets a string between two other strings or characters
     * @param string $string String to be searched
     * @param $start First indicator to split the string
     * @param $end Second indicator to finish the process
     * @return string The intended string
     */
    private function get_string_btw(String $string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return ' ';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * Clears all tags out of view such as languages, loops ...
     */
    private function clear_all_tags()
    {
        $this->_view = preg_replace('~@(.*){(.*)}~', '', $this->_view);
        $this->_view = preg_replace('~@(.*){(.*)}{(.*)}~', '', $this->_view);
        $this->_view = preg_replace('~@(.*){(.*)}{(.*)}~', '', $this->_view);
    }

    /**
     * Creates new patterns according to given parameters
     * @param int $type 0 word-digit, 1 digit, 2 letters, 3 everything
     * @param string $_prefix The beginning of identifier
     * @param string $custom_s_definition Custom definition
     * @return string The pattern
     */
    public static function pattern_creator(int $type, string $_prefix = "", string $custom_s_definition = "")
    {
        // Prepare definition of string to find
        switch ($type) {
            // Custom characters
            case 0:
                $s_definition = $custom_s_definition;
                break;
            // Must contain only word and digit characters
            case 1:
                $s_definition = '(\w+)';
                break;
            // Must contain only digit characters
            case 2:
                $s_definition = '(\d+)';
                break;
            // Must contain only letters
            case 3:
                $s_definition = '([a-z]+)';
                break;
            // May contain any characters
            case 4:
                $s_definition = '(.*?)';
                break;
            // Must contain only word and digit characters as default
            default:
                $s_definition = '(\w+)';
                break;
        }

        // Complete pattern with its parts
        $pattern = '/@' . $_prefix . '{' . $s_definition . '}/is';

        // Return final state of the pattern
        return $pattern;
    }

    /**
     * Re-converts a pattern to achieve initial state
     * @param string $pattern The pattern
     * @param string $key The key to push it into the pattern
     */
    public static function pattern_reconverter($pattern, $key)
    {
        // Parse pattern
        preg_match('~/(.*?)/~', $pattern, $slashes_output);
        preg_match('/{(.*?)}/', $slashes_output[1], $braces_output);

        // Push the key into the pattern
        $result = str_replace($braces_output[1], $key, $slashes_output[1]);

        // Return the result
        return $result;
    }

    /**
     * Replaces language tas with localization data
     */
    private function language_governer()
    {
        // Create an alias of the view file property to save space
        $view = $this->_view;

        // Call localization class
        $loc = new localization();

        // Define regex to match language tag
        $tag_pattern = self::pattern_creator(1, "lang");

        // Find all language tags
        preg_match_all($tag_pattern, $view, $matches);

        // Check if there is a match before start the replacement process
        if (count(array_filter($matches)) == 0) {
            goto skip_process;
        }

        // Replace all language tags with localization data
        foreach ($matches[1] as $langID) {
            $lang = $loc->translate($langID);
            $view = $this->replace_tags($langID, $lang, $tag_pattern, $view);
        }

        // Pass the result to view class object
        $this->_view = $view;

        skip_process:
        return true;
    }

    /**
     * Assables other template files with view file according to given parameters
     */
    private function assamble_other_temp_files()
    {
        // Create an alias of the view file property to save space
        $view = $this->_view;

        // Define regex to match temp file tag
        $tag_pattern = self::pattern_creator(4, "temp_file");

        // Check if additional files array is empty before start to update
        if (count(array_filter($this->add_files)) > 0) {
            // Update all template paths according to given array
            foreach ($this->add_files as $key => $value) {
                // Replace old path with new one
                $view = $this->replace_tags($key, self::pattern_reconverter($tag_pattern, $value), $tag_pattern, $view);
            }
        }

        // Find all temp file tags
        preg_match_all($tag_pattern, $view, $matches);

        // Check if there is a match before start the replacement process
        if (count(array_filter($matches)) == 0) {
            goto skip_process;
        }

        // Push all template files into view
        foreach ($matches[1] as $file_path) {
            // Load template file and get contents
            $file = $this->_load_file($file_path);
            // Replace tags with template contents
            $view = $this->replace_tags($file_path, $file, $tag_pattern, $view);
        }

        // Pass the result to view class object
        $this->_view = $view;

        skip_process:
        return true;
    }

}
