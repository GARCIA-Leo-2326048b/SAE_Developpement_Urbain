<?php

namespace blog\controllers;
use blog\views\AffichageView;
require_once 'modules/blog/views/AffichageView.php';

class AffichageController
{
    private $carte;

    public function __construct()
    {
        $this->carte = new AffichageView();
    }

    public function execute()
    {
        $house = '/_assets/utils/Household_3-2019.geojson';
        $road = '/_assets/utils/Road_3-2019.geojson';
        $vegetation = '/_assets/utils/Vegetation.geojson';

        $this->carte->show($house,$road,$vegetation);
    }
}