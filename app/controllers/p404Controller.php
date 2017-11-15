<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

namespace AFN\App\Controllers;

use AFN\App\Core\Controller;
use AFN\App\Models\Localization;

/**
 * 404 Not Found Page
 *
 * @author alifuatnumanoglu
 */
class p404Controller extends Controller {
    public function show_404() {
        $layout = $this->get_layout('404.php');
        $loc = new Localization();
        echo $loc->translate($layout);
    }
}
