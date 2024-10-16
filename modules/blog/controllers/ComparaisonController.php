<?php

namespace blog\controllers;
use blog\models\GeoJsonModel;
use blog\views\ComparaisonView;
use geoPHP;

class ComparaisonController{

    private $view;
    private $model;
    public function __construct()
    {
        $this->view = new ComparaisonView();
        $this->model=new GeoJsonModel();
    }
    public function compare(){


        $polygonSim = $this->model->fetchGeoJson(1);
        $polygonVer = $this->model->fetchGeoJson(3);

        $geometrySim = $this->loadGeoJson($polygonSim);
        $geometryVer = $this->loadGeoJson($polygonVer);

        $avgAreaSim = $this->getAverageArea($geometrySim);
        $avgAreaVer = $this->getAverageArea($geometryVer);

        $this->view->showComparison([
            'avgAreaSim' => $avgAreaSim,
            'avgAreaVer' => $avgAreaVer
        ]);
    }


    private function loadGeoJson($geoJsonData) {
        $geometry = GeoPHP::load($geoJsonData, 'json');
        if (!$geometry) {
            throw new \Exception("The GeoJSON file could not be loaded.");
        }
        return $geometry;
    }


    private function getAverageArea($geometry) {
        $totalArea = 0;
        $numHouses = 0;
        foreach ($geometry->getComponents() as $component) {
            if ($component->geometryType() === 'Polygon') {
                $totalArea += $component->area();
                $numHouses++;
            }
        }
        if ($numHouses > 0) {
            $averageArea = $totalArea / $numHouses;
        } else {
            $averageArea = 0;
        }
        return $averageArea;
    }
}