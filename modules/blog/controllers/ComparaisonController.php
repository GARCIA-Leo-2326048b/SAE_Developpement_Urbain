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

        // Calculer la distribution des surfaces pour la simulation et la vérité terrain
        $distributionSim = $this->calculateAreaDistribution($areaStatsSim['areas']);
        $distributionVer = $this->calculateAreaDistribution($areaStatsVer['areas']);

        // Générer les histogrammes basés sur les distributions
        $distributionSimPath = $this->createHistogramFromDistribution($distributionSim, 'Distribution des surfaces - Simulation');
        $distributionVerPath = $this->createHistogramFromDistribution($distributionVer, 'Distribution des surfaces - Vérité terrain');

        // Calcul de la distance de Hausdorff
        $hausdorffDistance = $this->calculateHausdorffDistance($geometrySim, $geometryVer);

        // Affichage des résultats via la vue
        $this->view->showComparison([
            'sim' => $areaStatsSim,
            'ver' => $areaStatsVer,
            'path' => $chemin,
            'distributionSimPath' => $distributionSimPath,
            'distributionVerPath' => $distributionVerPath,
            'hausdorff' => $hausdorffDistance,
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
            'areas' => $areas,
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



    // Méthode pour calculer la distance d'Hausdorff entre deux géométries
    private function calculateHausdorffDistance($geometryA, $geometryB) {
        $maxDistAtoB = $this->calculateDirectedHausdorffDistance($geometryA, $geometryB);
        $maxDistBtoA = $this->calculateDirectedHausdorffDistance($geometryB, $geometryA);

        // La distance d'Hausdorff est le maximum des deux directions
        return max($maxDistAtoB, $maxDistBtoA);
    }

    // Calcul de la distance dirigée (de A vers B)
    private function calculateDirectedHausdorffDistance($geometryA, $geometryB) {
        $maxDist = 0;

        foreach ($geometryA->getComponents() as $componentA) {
            foreach ($componentA->getPoints() as $pointA) {
                // Calcul de la distance minimale entre un point de A et tous les points de B
                $minDist = INF;
                foreach ($geometryB->getComponents() as $componentB) {
                    foreach ($componentB->getPoints() as $pointB) {
                        $dist = $this->calculateDistanceBetweenPoints($pointA, $pointB);
                        if ($dist < $minDist) {
                            $minDist = $dist;
                        }
                    }
                }
                // Met à jour la distance maximale pour ce point
                if ($minDist > $maxDist) {
                    $maxDist = $minDist;
                }
            }
        }

        return $maxDist;
    }

    // Méthode pour calculer la distance entre deux points
    private function calculateDistanceBetweenPoints($pointA, $pointB) {
        $dx = $pointA->x() - $pointB->x();
        $dy = $pointA->y() - $pointB->y();
        return sqrt($dx * $dx + $dy * $dy); // Distance euclidienne
    }

    private function calculateAreaDistribution($areas, $numBins = null) {
        // Déterminer le nombre de bins en fonction de la règle de Sturges
        if ($numBins === null) {
            $numBins = (int) (1 + 3.322 * log10(count($areas)));  // Règle de Sturges
        }

        // Calcul des bornes des classes (bin) basées sur les données
        $minArea = min($areas);
        $maxArea = max($areas);
        $binWidth = ($maxArea - $minArea) / $numBins;

        // Initialisation des classes
        $distribution = array_fill(0, $numBins, 0);

        // Répartition des bâtiments dans les classes
        foreach ($areas as $area) {
            $binIndex = min((int)(($area - $minArea) / $binWidth), $numBins - 1); // Assigner chaque surface à une classe
            $distribution[$binIndex]++;
        }

        return [
            'distribution' => $distribution,
            'minArea' => $minArea,
            'maxArea' => $maxArea,
            'binWidth' => $binWidth,
            'numBins' => $numBins
        ];
    }

    private function createHistogramFromDistribution($distributionData, $title) {
        $distribution = $distributionData['distribution'];
        $minArea = $distributionData['minArea'];
        $maxArea = $distributionData['maxArea'];
        $binWidth = $distributionData['binWidth'];
        $numBins = count($distribution);

        // Labels pour les classes de surface
        $labels = [];
        for ($i = 0; $i < $numBins; $i++) {
            $rangeStart = $minArea + $i * $binWidth;
            $rangeEnd = $rangeStart + $binWidth;
            $labels[] = sprintf("%.0f - %.0f m²", $rangeStart, $rangeEnd);
        }

        // Création du graphique
        $graph = new Graph(800, 600);
        $graph->SetScale('textlin');

        $graph->title->Set($title);
        $graph->xaxis->title->Set('Classes de surfaces (m²)');
        $graph->xaxis->SetTickLabels($labels);
        $graph->yaxis->title->Set('Nombre de bâtiments');

        // Création des barres
        $barPlot = new BarPlot($distribution);
        $barPlot->SetFillColor('blue');
        $graph->Add($barPlot);

        // Sauvegarde de l'image
        $imagePath = 'C:\Users\t22018451\PhpstormProjects\SAE_Developpement_Urbain\_assets\images';
        $graph->StrokeStore($imagePath);

        return $imagePath;
    }



}