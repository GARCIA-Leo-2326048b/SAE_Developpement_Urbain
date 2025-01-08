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

    public function execute($fileId)
    {
       $file = $this->model->fetchGeoJson($fileId);
       $this->view->show($file,null,null,null);
    }

}