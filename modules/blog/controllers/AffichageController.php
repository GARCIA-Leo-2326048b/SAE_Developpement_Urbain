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

    public function setModel(GeoJsonModel $model)
    {
        $this->model = $model;
    }
    public function setView(AffichageView $view)
    {
        $this->view = $view;
    }

    public function execute($house = null, $road = null, $tiff = null)
    {
       $houseData = $this->model->fetchGeoJson($house);
       $roadData = $this->model->fetchGeoJson($road);

       $this->view->show($houseData,$roadData,null,null);
    }

}