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

        // Récupérer les aires et périmètres pour simulation et vérité terrain
        $valuesSim = $this->getAreasAndPerimeters($geometrySim);
        $valuesVer = $this->getAreasAndPerimeters($geometryVer);

        // Calculer les statistiques pour les aires
         $areaStatsSim = $this->getStat($valuesSim['areas']);
        $areaStatsVer = $this->getStat($valuesVer['areas']);

        // Calculer les Shape Index pour simulation et vérité terrain
        $shapeIndexesSim = $this->getShapeIndexStats(['areas' => $valuesSim['areas'], 'perimeters' => $valuesSim['perimeters']]);
        $shapeIndexesVer = $this->getShapeIndexStats(['areas' => $valuesVer['areas'], 'perimeters' => $valuesVer['perimeters']]);

        // Calculer les statistiques pour les Shape Index
        $shapeIndexStatsSim = $this->getStat($shapeIndexesSim);
        $shapeIndexStatsVer = $this->getStat($shapeIndexesVer);

        $results = [
            'areaStatsSim' => $areaStatsSim,
            'areaStatsVer' => $areaStatsVer,
            'shapeIndexStatsSim' => $shapeIndexStatsSim,
            'shapeIndexStatsVer' => $shapeIndexStatsVer
        ];
        $this->view->showComparison($results);
    }

    private function loadGeoJson($geoJsonData) {
        $geometry = GeoPHP::load($geoJsonData, 'json');
        if (!$geometry) {
            throw new \Exception("The GeoJSON file could not be loaded.");
        }
        return $geometry;
    }

    private function getAreasAndPerimeters($geometry){
        $areas = [];
        $perimeters = [];
        //on rentre les aires de tous les batiments dans un tableau
        foreach ($geometry->getComponents() as $component) {
            if ($component->geometryType() === 'Polygon') {
                $areas[] = $component->area();
                $perimeters[] = $component->length();
            }
        }
        return [
            'areas'=>$areas,
            'perimeters'=>$perimeters];
        }


    private function getShapeIndexStats($polygon)
    {
        $shapeIndexes = [];
        foreach ($polygon['areas'] as $i => $area) {
            if ($area > 0) {
                $shapeIndex = $polygon['perimeters'][$i] / (2 * sqrt(pi() * $area));
                $shapeIndexes[] = $shapeIndex;
            }

        }
        return $shapeIndexes;
    }
    private function getStat($values) {

        if (count($values) > 0) {
            $mean = array_sum($values)/count($values);//moyenne des aires
            $min = min($values);//aire minimum
            $max = max($values);//aire maximum
            $std = $this->calculateStandardDeviation($values,$mean);//ecart-type
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

        $graph->Stroke();
    }
}