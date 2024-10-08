<?php

namespace blog\controllers;

use blog\views\HomepageView;

require 'modules/blog/views/HomepageView.php';


class HomepageController {
    public function execute() : void {
        (new HomepageView())->show();
    }
}