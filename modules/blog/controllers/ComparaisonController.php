<?php

namespace blog\controllers;
use blog\models\ComparaisonModel;
use blog\views\ComparaisonView;
use geoPHP;

class ComparaisonController{

    private $comparaisonModel;
    private $view;

    public function __construct()
    {
        $this->comparaisonModel = new ComparaisonModel();
        $this->view = new ComparaisonView();
    }
    public function compare($geoJsonSimName, $geoJsonVerName){


        // Charger les GeoJSON depuis la base de données
        $geoJsonSim = $this->comparaisonModel->fetchGeoJson($geoJsonSimName);
        $geoJsonVer = $this->comparaisonModel->fetchGeoJson($geoJsonVerName);

        // Projeter les GeoJSON dans le même système de coordonnées
        $geoJsonSim = $this->comparaisonModel->projectGeoJson($geoJsonSim);
        $geoJsonVer = $this->comparaisonModel->projectGeoJson($geoJsonVer);

        // Calculer les statistiques pour chaque GeoJSON
        $valuesSim = $this->comparaisonModel->getAreasAndPerimeters(geoPHP::load($geoJsonSim));
        $valuesVer = $this->comparaisonModel->getAreasAndPerimeters(geoPHP::load($geoJsonVer));

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
        $this->view->showComparison($results);

    }


}