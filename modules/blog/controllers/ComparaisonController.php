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

        $geometrySimProjected = $this->model->projectGeoJson( $polygonSim);
        $geometryVerProjected = $this->model->projectGeoJson($polygonVer);

        // Récupérer les aires et périmètres pour simulation et vérité terrain
        $valuesSim = $this->getAreasAndPerimeters(geoPHP::load($geometrySimProjected));
        $valuesVer = $this->getAreasAndPerimeters(geoPHP::load($geometryVerProjected));

        // Calculer les statistiques pour les aires
         $areaStatsSim = $this->getStat($valuesSim['areas']);
        $areaStatsVer = $this->getStat($valuesVer['areas']);

        /*// Calculer les Shape Index pour simulation et vérité terrain
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
        $this->view->showComparison($results);*/


        $chemin = $this->createHistogram($areaStatsSim, $areaStatsVer);

        $this->view->showComparison([
            'sim' => $areaStatsSim,
            'ver' => $areaStatsVer,
            'path' => $chemin
        ]);
    }

    private function getAreasAndPerimeters($geometry,&$areas = [], &$perimeters = []){
        //on rentre les aires de tous les batiments dans un tableau
        $geometryType = $geometry->geometryType();
        switch ($geometryType){
            case 'MultiPolygon':
                foreach ($geometry->getComponents() as $component) {
                    $this->getAreasAndPerimeters($component,$areas,$perimeters);
                }
                break;
            case 'LineString':
                $perimeters[] = $geometry->length();
                break;
            case 'Polygon':
                $areas[] = $geometry->area();
                // Parcours des composants pour les contours et les trous (LineString)
                foreach ($geometry->getComponents() as $subComponent) {
                    if ($subComponent->geometryType() === 'LineString') {
                        // Calcul du périmètre de chaque contour
                        $perimeters[] = $subComponent->length();
                    }
                }
                break;
            default:
                echo "Type de géométrie non pris en charge : " . $geometryType . "\n";
                break;

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

    private function getHausdorffDistance($geometry1, $geometry2)
    {
        if (!$geometry1 || !$geometry2) {
            throw new InvalidArgumentException("Les géométries fournies sont invalides ou nulles.");
        }

        // Convertir les géométries en collections de points
        $points1 = $this->extractPoints($geometry1);
        $points2 = $this->extractPoints($geometry2);

        // Calculer la distance maximale minimale (Hausdorff)
        $maxMinDistance1 = $this->calculateMaxMinDistance($points1, $points2);
        $maxMinDistance2 = $this->calculateMaxMinDistance($points2, $points1);

        return max($maxMinDistance1, $maxMinDistance2);
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