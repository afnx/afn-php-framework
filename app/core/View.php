<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Core;

/**
 * Description of view
 *
 * @author alifuatnumanoglu
 */
class View {
    /*
     * Stores the location of view file
     * @var string
     */

    public $view_file;

    /*
     * Stores the contents of the view file
     * @var string
     */
    private $_view;

    /*
     * Stores all datas passed by a controller
     * @var array
     */
    public $entries = array();

    /*
     * Stores localization data from database
     * @var array
     */
    public $localization = array();

    /*
     * Construct View Class
     */

    public function __construct($view_file = NULL) {
        $this->view_file = $view_file;
    }

    /*
     * Generates markup by inserting entry data into the view file
     * @param array $extra Extra data for the header/footer
     * @return string The HTML with entry data inserted into the view
     */

    public function generate_markup(array $extra) {
        $this->_load_view();
        return $this->_parse_view($extra);
    }

    /*
     * Loads a view file with which markup should be formatted
     * @return string The contents of the view file
     */

    private function _load_view() {
        if(strpos($this->view_file, "/views/") !== FALSE) {
            $this->view_file = substr($this->view_file, strpos($this->view_file, '/views/'));
        }

        if(!defined(ROOT_DIR)) {
            $this->view_file = realpath(__DIR__ . '/../..') . '/resources/views/' . $this->view_file;
        } else {
            $this->view_file = ROOT_DIR . '/resources/views/' . $this->view_file;
        }

        if(file_exists($this->view_file) && is_readable($this->view_file)) {
            $path = $this->view_file;
        } else if(file_exists($default_file = realpath(__DIR__ . '/../..') . '/resources/views/default.php') && is_readable($default_file)) {
            $path = $default_file;
        } else {
            throw new Exception("No default view found!");
        }

        $this->_view = file_get_contents($path);
    }

    /*
     * Separates the template into header, loop, and footer for parsing
     * @param array $extra
     *
     * Additional content for the header/footer
     * @return string The entry markup
     */

    private function _parse_view($extra = NULL) {

    }

    public function get_layout($layout) {

        if(strpos($layout, "/layouts/") !== FALSE) {
            $layout = substr($layout, strpos($layout, '/layouts/'));
        }

        if(!defined(ROOT_DIR)) {
            $layout_dir = realpath(__DIR__ . '/../..') . '/resources/layouts/' . $layout;
        } else {
            $layout_dir = ROOT_DIR . '/resources/layouts/' . $layout;
        }

        $result = file_get_contents($layout_dir);

        return $result;
    }

    public static function render($view, array $params = []) {
        if(file_exists($file = LT_DIR . "/{$view}.php")) {

            extract($params);

            ob_start();

            require $file;

            echo ob_get_clean();
        } else {

            app::p404();
        }
    }

}
