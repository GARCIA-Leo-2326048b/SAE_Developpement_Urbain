<?php

namespace blog\controllers;
use blog\views\AffichageView;
use blog\models\GeoJsonModel;
class AffichageController
{

    private $view;
    private $model;
    public function __construct()
    {
        $this->view = new AffichageView();
        $this->model=new GeoJsonModel();
    }

    public function execute()
    {

        $house = $this->model->fetchGeoJson('1');

        $road = $this->model->fetchGeoJson('2');
        $vegetation = $this->model->fetchGeoJson('3');

        $this->view->show($house,$road,$vegetation);
    }
}