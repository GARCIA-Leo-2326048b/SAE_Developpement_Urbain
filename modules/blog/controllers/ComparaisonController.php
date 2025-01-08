<?php

namespace blog\controllers;
use blog\models\ComparaisonModel;
use blog\models\GeoJsonModel;
use blog\views\ComparaisonView;
use geoPHP;

class ComparaisonController{

    private $comparaisonModel;
    private $GeoJsonModel;
    private $view;

    public function __construct()
    {
        $this->comparaisonModel = new ComparaisonModel();
        $this->view = new ComparaisonView();
        $this->GeoJsonModel = new GeoJsonModel();
    }
    public function compare($geoJsonSimName, $geoJsonVerName){


        // Charger les GeoJSON depuis la base de données
        $geoJsonSim = $this->GeoJsonModel->fetchGeoJson($geoJsonSimName);
        $geoJsonVer = $this->GeoJsonModel->fetchGeoJson($geoJsonVerName);

        // Projeter les GeoJSON dans le même système de coordonnées
        $geoJsonSimProj = $this->comparaisonModel->projectGeoJson($geoJsonSim);
        $geoJsonVerProj = $this->comparaisonModel->projectGeoJson($geoJsonVer);

        // Calculer les statistiques pour chaque GeoJSON
        $valuesSim = $this->comparaisonModel->getAreasAndPerimeters(geoPHP::load($geoJsonSimProj));
        $valuesVer = $this->comparaisonModel->getAreasAndPerimeters(geoPHP::load($geoJsonVerProj));

        $areaStatsSim = $this->comparaisonModel->getStat($valuesSim['areas']);
        $areaStatsVer = $this->comparaisonModel->getStat($valuesVer['areas']);

        // Calculer les Shape Index pour simulation et vérité terrain
        $shapeIndexesSim = $this->comparaisonModel->getShapeIndexStats(['areas' => $valuesSim['areas'], 'perimeters' => $valuesSim['perimeters']]);
        $shapeIndexesVer = $this->comparaisonModel->getShapeIndexStats(['areas' => $valuesVer['areas'], 'perimeters' => $valuesVer['perimeters']]);

        // Calculer les statistiques pour les Shape Index
        $shapeIndexStatsSim = $this->comparaisonModel->getStat($shapeIndexesSim);
        $shapeIndexStatsVer = $this->comparaisonModel->getStat($shapeIndexesVer);

        $results = $this->comparaisonModel->grapheDonnees($areaStatsSim,$areaStatsVer,$shapeIndexStatsSim,$shapeIndexStatsVer);

        /*$results = [
            'StatsSim'=>
                ['areaStatsSim' => $areaStatsSim,
                'areaStatsVer' => $areaStatsVer,],
            'StatsVer' =>
                ['shapeIndexStatsSim' => $shapeIndexStatsSim,
                'shapeIndexStatsVer' => $shapeIndexStatsVer,]


            //'graph' => $graph
        ];*/
        $this->view->showComparison($results,$geoJsonSim,$geoJsonVer,$geoJsonSimName,$geoJsonVerName);

    }


}