<?php

namespace blog\controllers;
use blog\models\GeoJsonModel;
use blog\views\ComparaisonView;
use geoPHP;
use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\BarPlot;
use Amenadiel\JpGraph\Plot\GroupBarPlot;

class ComparaisonController{

    private $view;
    private $model;
    public function __construct()
    {
        $this->view = new ComparaisonView();
        $this->model=new GeoJsonModel();
    }
    public function compare(){


        $polygonSim = $this->model->fetchGeoJson('Household_3-2019.geojson');
        $polygonVer = $this->model->fetchGeoJson('Buildings2019_ABM');

        $geometrySim = $this->loadGeoJson($polygonSim);
        $geometryVer = $this->loadGeoJson($polygonVer);

        $areaStatsSim = $this->getAreaStat($geometrySim);
        $areaStatsVer = $this->getAreaStat($geometryVer);

        $this->view->showComparison([
            'sim' => $areaStatsSim,
            'ver' => $areaStatsVer
        ]);
    }

//a
    private function loadGeoJson($geoJsonData) {
        $geometry = GeoPHP::load($geoJsonData, 'json');
        if (!$geometry) {
            throw new \Exception("The GeoJSON file could not be loaded.");
        }
        return $geometry;
    }


    private function getAreaStat($geometry) {
        $areas = [];
        //on rentre les aires de tous les batiments dans un tableau
        foreach ($geometry->getComponents() as $component) {
            if ($component->geometryType() === 'Polygon') {
                $areas[]=$component->area();
            }
        }
        if (count($areas) > 0) {
            $mean = array_sum($areas)/count($areas);//moyenne des aires
            $min = min($areas);//aire minimum
            $max = max($areas);//aire maximum
            $std = $this->calculateStandardDeviation($areas,$mean);//ecart-type
        } else {
            $mean =$max=$min=$std= 0;
        }
        return [
            'mean' => $mean,
            'min' => $min,
            'max' => $max,
            'std' => $std
        ];
    }

    private function calculateStandardDeviation($areas, $mean) {
        $sum = 0;
        foreach ($areas as $area) {
            $sum += pow($area - $mean, 2); // Calcul de l'écart à la moyenne au carré
        }
        $variance = $sum / count($areas);  // Calcul de la variance
        return sqrt($variance);            // Retourne l'écart-type (racine carrée de la variance)
    }

    function createHistogram($areaStatsSim, $areaStatsVer)
    {
        //initialisation des données pour les barres
        $dataSim = [$areaStatsSim['mean'], $areaStatsSim['min'], $areaStatsSim['max'], $areaStatsSim['std']];
        $dataVer = [$areaStatsVer['mean'], $areaStatsVer['min'], $areaStatsVer['max'], $areaStatsVer['std']];
        $labels = ['Mean', 'Min', 'Max', 'Std'];

        //initialisation du graphique
        $graph = new Graph(800, 600);
        $graph->SetScale('textlin');
    }
}