<?php

/**
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * @author AFN <afn@alifuatnumanoglu.com>
 * 
 * YOU CAN DELETE THIS FILE!
 */

namespace AFN\App\Controllers;

use AFN\App\Core\Controller;

/**
 * Class Docs
 * Documentation
 *
 * @package AFN-PHP-FRAMEWORK
 */
class Docs extends Controller
{

    /**
     * Stores view class
     * @var object $view
     */
    public $view;

    public function __construct()
    {
        $this->view = new \AFN\App\Core\View();
        $this->secure = new \AFN\App\Core\Secure();
        $this->documentation = new \AFN\App\Models\Documentation();
    }

    /**
     * Displays default page
     */
    public function display()
    {
        $this->view->viewFile = "docs";
        $data = $this->documentation->getAllDocumentation();

        $docsArray = array();
        $docsTitleArray = array();

        $i = 0;
        foreach ($data as $row) {
            if ($row["parent_id"] == 0) {
                $docsArray["loop_1_" . $row["id"]] = [
                    "documentation_id" => $row["id"],
                    "documentation_title" => $row["title"],
                    "documentation_description" => $row["description"],
                    "documentation_tag" => $row["tag"],
                ];

                $docsTitleArray["loop_11_" . $row["id"]] = [
                    "documentation_nav_id" => $row["id"],
                    "documentation_nav_title" => $row["title"],
                    "documentation_nav_tag" => $row["tag"],
                ];
            } else {
                $findParent = $this->findInArray($docsArray, "documentation_id", $row["parent_id"]);
                reset($findParent);
                $parentKey = key($findParent);
                preg_match('~loop_(\d+)~', $parentKey, $underscoreOutput);
                if (isset($underscoreOutput[1])) {
                    $loopId = $underscoreOutput[1];
                    $subLoopId = $loopId + 1;
                    $newKey = "loop_" . $subLoopId . "_" . $row["id"];

                    $parentTitleKey = "loop_" . $loopId . "$loopId" . "_" . $row["parent_id"];
                    $newTitleKey = "loop_" . $subLoopId . "$subLoopId" . "_" . $row["id"];

                    $docsArray[$parentKey][$newKey] = [
                        "documentation_id" => $row["id"],
                        "documentation_title" => $row["title"],
                        "documentation_description" => $row["description"],
                        "documentation_tag" => $row["tag"],
                    ];

                    $docsTitleArray[$parentTitleKey][$newTitleKey] = [
                        "documentation_nav_id" => $row["id"],
                        "documentation_nav_title" => $row["title"],
                        "documentation_nav_tag" => $row["tag"],
                    ];
                } else {
                    $docsArray["loop_1_" . $row["id"]] = [
                        "documentation_id" => $row["id"],
                        "documentation_title" => $row["title"],
                        "documentation_description" => $row["description"],
                        "documentation_tag" => $row["tag"],
                    ];

                    $docsTitleArray["loop_11_" . $row["id"]] = [
                        "documentation_nav_id" => $row["id"],
                        "documentation_nav_title" => $row["title"],
                        "documentation_nav_tag" => $row["tag"],
                    ];
                }
            }

            $i++;
        }

        $this->view->newEntry(1, [
            "navbar_doc_active" => "active",
        ]);

        $this->view->newEntry(4, $docsArray);
        $this->view->newEntry(1, $docsTitleArray);
        echo $this->view->generateMarkup();
    }

    /**
     * Finds an element in an array by using key and value of element.
     * @param array $array
     * @param string $searchKey
     * @param string $searchValue
     * @return array
     */
    private function findInArray($array, $searchKey, $searchValue)
    {
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $sub = $this->findInArray($value, $searchKey, $searchValue);
                if (!empty($sub)) {
                    if (count($array) > 1) {
                        $result[$key] = $value;
                    } else {
                        $result = array_merge($result, $array);
                    }
                    break;
                }
            } else {
                if ($key == $searchKey && $value == $searchValue) {
                    $result = array_merge($result, $array);
                    break;
                }
            }
        }
        return $result;
    }
}
