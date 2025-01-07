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

    public function execute($fileId)
    {
        //var_dump($fileId);

       $file = $this->model->fetchGeoJson($fileId);
        var_dump($file);
       $this->view->show($file,null,null,null);
    }

}