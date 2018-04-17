<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Controllers;

use AFN\App\Core\Controller;

/**
 * Class Search
 * Simple Search Engine
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Search extends Controller
{

    /**
     * Stores search text
     * @var string
     */
    public $search;

    /**
     * Stores view class
     * @var object
     */
    public $view;

    /**
     * Stores number of results in total
     * @var integer
     */
    public $totalResultNumber = 0;

    /**
     * Stores number of results per page
     * @var integer
     */
    public $limit = 10;

    /**
     * Stores search results page number
     * @var integer
     */
    public $pageNumber = 1;

    /**
     * Stores time of search process
     * @var integer
     */
    public $searchTime = 0;

    /**
     * Stores first row of search
     * @var integer
     */
    public $firstRow = 0;

    /**
     * Stores number of links in pagination
     * @var integer
     */
    public $links = 10;

    /**
     * Stores search results
     * @var array
     */
    public $results = array();

    public function __construct()
    {
        $this->view = new \AFN\App\Core\View();
        $this->secure = new \AFN\App\Core\Secure();
        $this->documentation = new \AFN\App\Models\Documentation();

        // Get search and page number from user
        $this->search = isset($_GET["search"]) ? $_GET["search"] : "";
        $this->pageNumber = isset($_GET["page_number"]) && $_GET["page_number"] > 0 ? $_GET["page_number"] : 1;

        // If page number is 1, make firstRow 0. Othewise calculate it.
        if ($this->pageNumber == 1) {
            $this->firstRow = 0;
        } else {
            $this->firstRow = $this->pageNumber * 10 - 10;
            $this->limit = $this->firstRow + 10;
        }

    }

    /**
     * Displays search page
     */
    public function display()
    {
        $this->view->viewFile = "search";
        if (!empty($this->search)) {
            $this->view->newEntry(1, [
                "search" => $_GET["search"],
            ]);

            // If there are search results, send results to template files
            if (!empty($this->results)) {
                $this->view->addFile([
                    'search_results_show' => 'search_results',
                ]);

                $i = 1;
                $searchArray = [];
                foreach ($this->results as $row) {
                    $row["title"] = strip_tags($row["title"]);
                    $row["description"] = strip_tags($row["description"]);
                    $row["description"] = strlen($row["description"]) > 200 ? substr($row["description"], 0, 200) . "..." : $row["description"];
                    $searchArray["loop_1_" . $i] = [
                        "search_name" => $row["title"],
                        "search_des" => $row["description"],
                        "search_link" => "docs#" . $row["tag"],
                    ];
                    $i++;
                }

                // Pagination
                $pages = [];

                $last = ceil($this->totalResultNumber / $this->limit);
                $start = (($this->pageNumber - $this->links) > 0) ? $this->pageNumber - $this->links : 1;
                $end = (($this->pageNumber + $this->links) < $last) ? $this->pageNumber + $this->links : $last;
                if ($start > 1) {
                    $pages["loop_2_1"] = [
                        "page_number" => 1,
                        "dots" => "...",
                    ];
                }

                for ($i = $start; $i < $this->pageNumber; $i++) {
                    $pages["loop_2_" . $i] = [
                        "page_number" => $i,
                    ];
                }

                for ($i = $this->pageNumber + 1; $i <= $end; $i++) {
                    $pages["loop_3_" . $i] = [
                        "page_number" => $i,
                    ];
                }

                if ($end < $last) {
                    $pages["loop_3_" . $last] = [
                        "page_number" => $last,
                        "dots" => "...",
                    ];
                }

                // Enter data
                $this->view->newEntry(1, $searchArray);
                $this->view->newEntry(1, $pages);
                $this->view->newEntry(1, [
                    "search_time" => $this->searchTime,
                    "page_number" => $this->pageNumber,
                    "search_result_number" => $this->totalResultNumber,
                    "tk" => $this->secure->calculateCsrfToken(),
                ]);
            }
        }
        echo $this->view->generateMarkup();
    }

    public function go()
    {
        if (empty($this->search)) {
            $this->display();
        } else {
            // Start counter
            $startSearch = $this->getmicrotime();

            // Seperate search keys
            $searchExploded = preg_split('/\s+/', $this->search);

            // Edit search keys
            foreach ($searchExploded as $key => $value) {
                if (strlen(trim($value)) == 0) {
                    unset($searchExploded[$key]);
                } else if (strlen($value) < 4 && count($searchExploded) > 2) {
                    unset($searchExploded[$key]);
                }
            }

            $possibleSearches = array_values($searchExploded);

            if (empty($possibleSearches)) {
                goto skip_process;
            }

            // Create possible searches for query
            $queryWhere = "";
            $numSearchPoss = count($possibleSearches);
            for ($i = 0; $i < $numSearchPoss; $i++) {
                if ($i + 1 == $numSearchPoss) {
                    $queryWhere .= "(title LIKE :title_" . $i . " OR description LIKE :description_" . $i . ")";
                } else {
                    $queryWhere .= "(title LIKE :title_" . $i . " OR description LIKE :description_" . $i . ") OR ";
                }

            }

            // Perepare parameters
            $params = array();
            $i = 0;
            foreach ($possibleSearches as $possibleSearch) {
                $params[":title_" . $i] = "%" . $possibleSearch . "%";
                $params[":description_" . $i] = "%" . $possibleSearch . "%";
                $i++;
            }

            // Add firstRow and limit to array
            $params[":first_row"] = $this->firstRow;
            $params[":limit_n"] = $this->limit;

            // Complete query and execute it
            $query = "SELECT * FROM Documentation WHERE " . $queryWhere . " LIMIT :first_row, :limit_n";
            $result = $this->documentation->fetchAll($query, $params, false);

            // Reset limit parameters
            $params[":first_row"] = 0;
            $params[":limit_n"] = -1;
            $this->documentation->fetchAll($query, $params, true);

            $this->results = $result;

            skip_process:

            // Stop counter
            $stopSearch = $this->getmicrotime();

            // Calculate number of results
            $this->totalResultNumber = isset($this->documentation->recordCount) ? $this->documentation->recordCount : 0;

            // Calculate search time
            $this->searchTime = number_format((float) ($stopSearch - $startSearch), 6, '.', '');

            // Display results
            $this->display();
        }
    }

    /**
     * Gets micro time
     */
    private function getmicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    /**
     * Crates similar forms of searches
     * @param string $char
     * @param integer $size
     * @param array $combinations
     * @return array
     */
    private function sampling($chars, $size, $combinations = array())
    {

        # if it's the first iteration, the first set
        # of combinations is the same as the set of characters
        if (empty($combinations)) {
            $combinations = $chars;
        }

        # we're done if we're at size 1
        if ($size == 1) {
            return $combinations;
        }

        # initialise array to put new values in
        $newCombinations = array();

        # loop through existing combinations and character set to create strings
        foreach ($combinations as $combination) {
            foreach ($chars as $char) {
                if ($combination != $char) {
                    $newCombinations[] = $combination . " " . $char;
                }
            }
        }

        # call same function again for the next iteration
        return $this->sampling($chars, $size - 1, $newCombinations);

    }
}
