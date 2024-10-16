<?php

namespace blog\controllers;
use blog\views\HomepageView;

class HomepageController {
    public function execute() : void {
        (new HomepageView())->show();
    }
}