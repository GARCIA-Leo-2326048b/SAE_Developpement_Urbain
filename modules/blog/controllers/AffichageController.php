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

        $house = $this->model->fetchGeoJson('Household_3-2019.geojson');
        $road = $this->model->fetchGeoJson('Road_3-2019.geojson');
        $vegetation = $this->model->fetchGeoJson('3');
        $tiffPath = '/_assets/utils/valenicina_17_08_19_dtm.tif';

        $this->view->show($house,$road, $vegetation,$tiffPath);
    }
}
