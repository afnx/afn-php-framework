<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

use AFN\App\Models\Localization;

/**
 * Class View
 * Viewing HTML files by rendering them using data
 *
 * @package AFN-PHP-FRAMEWORK
 */
class View
{

    /**
     * Stores the location of view file
     * @var string
     */
    public $viewFile;

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
    public $files = array();

    /**
     * Construct View Class
     */
    public function __construct($viewFile = null)
    {
        $this->viewFile = $viewFile;
    }

    /**
     * Resets all properties in the class
     */
    public function __reset()
    {
        $blankInstance = new static;
        $reflBlankInstance = new \ReflectionClass($blankInstance);
        foreach ($reflBlankInstance->getProperties() as $prop) {
            $prop->setAccessible(true);
            $this->{$prop->name} = $prop->getValue($blankInstance);
        }
    }

    /**
     * Generates markup by inserting entry data into the view file
     * @return string The HTML with entry data inserted into the view
     */
    public function generateMarkup()
    {
        // Load main file and assing its contents to view object
        $this->_view = $this->loadFile($this->viewFile);

        // Logged in user Header
        if ((new \AFN\App\Core\Secure())->checkUserLogin() == true) {
            // Show header logout
            $this->addFile([
                'header_logout_part' => 'components/header/logout',
            ]);
            $this->newEntry(1, ["header_loggedin_username" => $_SESSION["username"]]);
        }

        // Assembles other template files to view
        $this->assambleOtherTempFiles();

        // Assembles other template second time
        $this->assambleOtherTempFiles();

        // Add new entry for site url
        $this->newEntry(1, [
            "global_site_url" => $GLOBALS["settings"]["site_url"],
            "global_site_name" => $GLOBALS["settings"]["name"],
            "csrf-token" => ((new \AFN\App\Core\Secure())->calculateCsrfToken()),
        ]);

        // Csrf Protection
        $this->addCsrfInput();

        // Parsing view and replacement process
        $this->parseView();

        // Localization process
        $this->languageGoverner();

        // Clean html codes in loops
        $this->loopCleaner();

        // Prevents codes in editor
        $this->codePreventer();

        // Remove view file from tags by sifting after all processes completed
        $this->clearAllTags();

        // Convert back some special characters which effects template engine
        $this->convertSpecialCharacters();

        // Return view which is ready to use
        return $this->_view;
    }

    /**
     * Loads a file with which markup should be formatted
     * @param string $file The path of file
     * @return string The contents of the file
     */
    private function loadFile($file)
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
        } else if (file_exists($defaultFile = realpath(__DIR__ . '/../..') . '/resources/views/default.php') && is_readable($defaultFile) && empty($this->_view)) {
            $path = $defaultFile;
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
    private function parseView()
    {

        // Create an alias of the view file property to save space
        $view = $this->_view;

        // Remove any PHP-style comments from the template
        $commentPattern = array('#/\*.*?\*/#s', '#(?<!:)//.*#');
        $view = preg_replace($commentPattern, null, $view);

        // Check if entries object is empty to start replacement process
        if (count(array_filter($this->entries)) == 0) {
            goto skip_process;
        }

        // Replacement process
        foreach ($this->entries as $key => $value) {
            // Find the data type
            preg_match('~_array_(\d+)~', $key, $underscoreOutput);
            $dataType = $underscoreOutput[1];

            // Define a regex to match any view tag for data provided by database
            switch ($dataType) {
                case 1:
                    $tagPattern = self::patternCreator(4, "data");
                    break;
                case 2:
                    $tagPattern = self::patternCreator(4, "template");
                    break;
                case 3:
                    $tagPattern = self::patternCreator(1, "lang");
                    break;
                case 4:
                    $tagPattern = self::patternCreator(1, "data_soft");
                    break;
                default:
                    $tagPattern = self::patternCreator(4, "data");
                    break;
            }

            // Replace matched tag with entries
            $view = $this->replaceLoop($value, $tagPattern, $view);

        }

        // Change view file with the formatted one
        $this->_view = $view;

        skip_process:
        return true;
    }

    /**
     * Adds new entry to replace tags with new data
     * @param int $dataType Stores type of data such as language, general, template...
     * @param array $data Stores data as an array
     */
    public function newEntry(int $dataType = 1, array $data)
    {
        // Check if entries object is empty before adding new entry
        if (count(array_filter($this->entries)) == 0) {
            // Add first entry
            $key = "0_array_" . $dataType;
        } else {
            // Find different key from others' for new entry
            $key = '';
            $i = 0;
            do {
                $key = $i . "_array_" . $dataType;
                $i++;
            } while (array_key_exists($key, $this->entries));
        }

        // Push the value to entries object
        $this->entries[$key] = $data;
    }

    /**
     * Adds new file to view file
     * @param array $file
     */
    public function addFile(array $files)
    {
        foreach ($files as $key => $value) {
            $this->files[$key] = $value;
        }
    }

    /**
     * Replaces a particular view tag with corresponding entry data(just one time)
     * @param string $key The identifier for tag
     * @param string $value Parameter for replacement
     * @param string $tagPattern The pattern of tags
     * @param string $view The contents of view file
     * @return string The replaced view value
     */
    public static function replaceTags($key, $value, $tagPattern, $_view)
    {
        $resultView = str_replace(self::patternReconverter($tagPattern, $key), $value, $_view);

        if (is_array($value)) {
            die("Error->Template Engine: Data value cannot be array! See Documentation: www.alifuatnumanoglu.com/framework.");
            exit();
        } else {
            // If data contains html and js codes by site owner, skip XSS protection
            if (strpos($tagPattern, 'data_soft') !== false) {
                goto skip_process;
            }
            // XSS Protection
            // Use fucntion of htmlspecialchars() to prevent html and js codes
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        skip_process:
        return $resultView;
    }

    /**
     * Replaces all view tags with corresponding entry data
     * @param string $key The identifier for tag
     * @param string $value Parameter for replacement
     * @param string $tagPattern The pattern of tags
     * @param string $view The contents of view file
     * @return string The replaced view value
     */
    public static function replaceAllTags($key, $value, $tagPattern, $_view)
    {
        $pattern = "/" . self::patternReconverter($tagPattern, $key) . "/";

        if (is_array($value)) {
            die("Error->Template Engine: Data value cannot be array! See Documentation: www.alifuatnumanoglu.com/framework.");
            exit();
        } else {
            // If data contains html and js codes by site owner, skip XSS protection
            if (strpos($tagPattern, 'data_soft') !== false) {
                goto skip_process;
            }
            // XSS Protection
            // Use fucntion of htmlspecialchars() to prevent html and js codes
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        skip_process:
        $resultView = preg_replace($pattern, $value, $_view);
        return $resultView;
    }

    /**
     * Replace views tags with the corresponding entry data for loops
     * @param array $data The data that is replaced tags
     * @param string $tagPattern The pattern of tags
     * @param string $view The contents of view file
     * @return string The replaced view value
     */
    public function replaceLoop($data, $tagPattern, $_view, $removeTags = true)
    {

        // Reverse array
        $data = array_reverse($data);

        foreach ($data as $key => $value) {
            if (substr($key, 0, 5) == "loop_") {

                // Reverse sub=arrays
                $value = array_reverse($value);

                // Find the loop id
                preg_match('~loop_(\d+)~', $key, $underscoreOutput);
                if (!isset($underscoreOutput[1])) {
                    die("Error->Template Engine: Loop names must be numeric! See Documentation: www.alifuatnumanoglu.com/framework.");
                    exit();
                } else {
                    $loopId = $underscoreOutput[1];
                }

                // Extract the loop pattern
                $loopStringEx = $this->getStringBtw($_view, "@loop{" . $loopId . "}{start}", "@loop{" . $loopId . "}{end}");
                $loopPattern = '@loop{' . $loopId . '}{start}' . $loopStringEx . '@loop{' . $loopId . '}{end}';

                // Replace tags with data
                $loopString = $loopStringEx;
                foreach ($value as $subKey => $subValue) {
                    // Check if there is another array, then send array to the function again
                    if (substr($subKey, 0, 5) == "loop_") {
                        $tempDataArray = array();
                        $tempDataArray[$subKey] = $subValue;
                        $loopString = $this->replaceLoop($tempDataArray, $tagPattern, $loopString, false);
                    } else {
                        // Change @ with hidden @ in data in order to prevent confusion
                        $subValue = str_replace("@", "/1#~#@#~#1/", $subValue);

                        $loopString = self::replaceAllTags("loop_" . $loopId . "_" . $subKey, $subValue, $tagPattern, $loopString);
                    }
                }

                // Append the entry data to the loop pattern
                $_view = str_replace($loopPattern, $loopPattern . $loopString, $_view);

                // Remove the loop element from the array in order to prevent confusion
                unset($data[$key]);

                // Check if there is a loop element that was not processed yet
                $removeLoop = 1;
                foreach ($data as $contKey => $contValue) {
                    if (strpos($contKey, "loop_" . $loopId) !== false) {
                        $removeLoop = 0;
                    }
                }
                // If all elements of the loop were processed, remove loop pattern
                if ($removeLoop == 1 && $removeTags == true) {
                    $_view = str_replace($loopPattern, '', $_view);
                }

            } else {
                // Change @ with hidden @ in data in order to prevent confusion
                $value = str_replace("@", "/1#~#@#~#1/", $value);

                // Replace tags with entry data
                $_view = self::replaceAllTags($key, $value, $tagPattern, $_view);

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
    private function getStringBtw(String $string, $start, $end)
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
    private function clearAllTags()
    {
        $this->_view = preg_replace('~@(\w+){(.*)}~', '', $this->_view);
        $this->_view = preg_replace('~@(\w+){(.*)}{(.*)}~', '', $this->_view);
        $this->_view = preg_replace('~@(\w+){(.*)}{(.*)}~', '', $this->_view);
    }

    /**
     * Creates new patterns according to given parameters
     * @param int $type 0 word-digit, 1 digit, 2 letters, 3 everything
     * @param string $_prefix The beginning of identifier
     * @param string $customSDefinition Custom definition
     * @return string The pattern
     */
    public static function patternCreator(int $type, string $_prefix = "", string $customSDefinition = "")
    {
        // Prepare definition of string to find
        switch ($type) {
            // Custom characters
            case 0:
                $sDefinition = $customSDefinition;
                break;
            // Must contain only word and digit characters
            case 1:
                $sDefinition = '(\w+)';
                break;
            // Must contain only digit characters
            case 2:
                $sDefinition = '(\d+)';
                break;
            // Must contain only letters
            case 3:
                $sDefinition = '([a-z]+)';
                break;
            // May contain any characters
            case 4:
                $sDefinition = '(.*?)';
                break;
            // Must contain only word and digit characters as default
            default:
                $sDefinition = '(\w+)';
                break;
        }

        // Complete pattern with its parts
        $pattern = '/@' . $_prefix . '{' . $sDefinition . '}/is';

        // Return final state of the pattern
        return $pattern;
    }

    /**
     * Re-converts a pattern to achieve initial state
     * @param string $pattern The pattern
     * @param string $key The key to push it into the pattern
     * @return string
     */
    public static function patternReconverter($pattern, $key)
    {
        // Parse pattern
        preg_match('~/(.*?)/~', $pattern, $slashesOutput);
        preg_match('/{(.*?)}/', $slashesOutput[1], $bracesOutput);

        // Push the key into the pattern
        $result = str_replace($bracesOutput[1], $key, $slashesOutput[1]);

        // Return the result
        return $result;
    }

    /**
     * Replaces language tas with localization data
     * @return boolean
     */
    private function languageGoverner()
    {
        // Create an alias of the view file property to save space
        $view = $this->_view;

        // Call localization class
        $loc = new Localization();

        // Define regex to match language tag
        $tagPattern = self::patternCreator(1, "lang");

        // Find all language tags
        preg_match_all($tagPattern, $view, $matches);

        // Check if there is a match before start the replacement process
        if (count(array_filter($matches)) == 0) {
            goto skip_process;
        }

        // Replace all language tags with localization data
        foreach ($matches[1] as $langId) {
            $lang = $loc->translate($langId);
            $view = $this->replaceTags($langId, $lang, $tagPattern, $view);
        }

        // Pass the result to view class object
        $this->_view = $view;

        skip_process:
        return true;
    }

    /**
     * Assables other template files with view file according to given parameters
     * @return boolean
     */
    private function assambleOtherTempFiles()
    {
        // Create an alias of the view file property to save space
        $view = $this->_view;

        // Define regex to match temp file tag
        $tagPattern = self::patternCreator(4, "temp_file");

        // Check if additional files array is empty before start to update
        if (count(array_filter($this->files)) > 0) {
            // Update all template paths according to given array
            foreach ($this->files as $key => $value) {
                // Replace old path with new one
                $view = $this->replaceTags($key, self::patternReconverter($tagPattern, $value), $tagPattern, $view);
            }
        }

        // Find all temp file tags
        preg_match_all($tagPattern, $view, $matches);

        // Check if there is a match before start the replacement process
        if (count(array_filter($matches)) == 0) {
            goto skip_process;
        }

        // Push all template files into view
        foreach ($matches[1] as $filePath) {
            // Load template file and get contents
            $file = $this->loadFile($filePath);
            // Replace tags with template contents
            $view = $this->replaceTags($filePath, $file, $tagPattern, $view);
        }

        // Pass the result to view class object
        $this->_view = $view;

        skip_process:
        return true;
    }

    /**
     * Appends a csrf input to forms
     */
    private function addCsrfInput()
    {
        // Create an alias of the view file property to save space
        $view = $this->_view;

        // Create csrf token input
        $input = '<input type="hidden" name="tk" value="' . ((new \AFN\App\Core\Secure())->calculateCsrfToken()) . '" />';

        // Append the input to all form tags
        $view = preg_replace('~</form>~', $input . '</form>', $view);

        // Pass the result to view class object
        $this->_view = $view;
    }

    /**
     * Cleans unnecessary html codes up
     */
    private function loopCleaner()
    {
        preg_match_all('~@loop{(.*)}{start}~', $this->_view, $search);
        foreach ($search[1] as $loopId) {
            $loopStringEx = $this->getStringBtw($this->_view, "@loop{" . $loopId . "}{start}", "@loop{" . $loopId . "}{end}");
            $loopPattern = '@loop{' . $loopId . '}{start}' . $loopStringEx . '@loop{' . $loopId . '}{end}';
            $this->_view = str_replace($loopPattern, '', $this->_view);
        }
    }

    /**
     * Prevents to process codes in editor
     */
    private function codePreventer()
    {
        preg_match_all("#<code.*?>(.*?)</code>#s", $this->_view, $code);
        foreach ($code[1] as $value) {
            $this->_view = str_replace($value, nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8')), $this->_view);
        }
    }

    /**
     * Converts special characters
     */
    public function convertSpecialCharacters()
    {
        $this->_view = str_replace("/1#~#@#~#1/", "@", $this->_view);
    }

}
