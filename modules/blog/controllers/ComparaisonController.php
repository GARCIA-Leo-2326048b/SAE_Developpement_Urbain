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

        $chemin = $this->createHistogram($areaStatsSim, $areaStatsVer);

        $this->view->showComparison([
            'sim' => $areaStatsSim,
            'ver' => $areaStatsVer,
            'path' => $chemin
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

    private function createHistogram($statsSim, $statsVer)
    {
        //initialisation des données pour les barres
        $dataSim = [$statsSim['mean'], $statsSim['min'], $statsSim['max'], $statsSim['std']];
        $dataVer = [$statsVer['mean'], $statsVer['min'], $statsVer['max'], $statsVer['std']];
        $labels = ['Mean', 'Min', 'Max', 'Std'];

        //initialisation du graphique
        $graph = new Graph(800, 600);
        $graph->SetScale('textlin');

        //titre et légendes
        $graph->title->Set('Comparaison des données de simulation et de vérité terrain');
        $graph->xaxis->title->Set('Statistiques');
        $graph->xaxis->SetTickLabels($labels);
        $graph->yaxis->title->Set('Valeurs');

        //mise en forme des barres
        $barPlotSim = new BarPlot($dataSim);
        $barPlotSim->SetFillColor('blue');
        $barPlotSim->SetLegend('Simulation');

        $barPlotVer = new BarPlot($dataVer);
        $barPlotVer->SetFillColor('green');
        $barPlotVer->SetLegend('Vérité terrain');

        //groupement des barres
        $groupBarPlot = new GroupBarPlot([$barPlotSim, $barPlotVer]);

        $graph->Add($groupBarPlot);

        $imagePath = '_assets/images/graphe.png';
        $graph->StrokeStore($imagePath);

        return $imagePath;
    }
}