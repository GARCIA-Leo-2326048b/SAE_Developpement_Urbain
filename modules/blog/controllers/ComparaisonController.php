<?php

namespace blog\controllers;
use blog\models\ComparaisonModel;
use blog\models\GeoJsonModel;
use blog\models\SingletonModel;
use blog\models\UploadModel;
use blog\views\ComparaisonView;
use geoPHP;

class ComparaisonController{

    private $comparaisonModel;
    private $GeoJsonModel;
    private $view;
    private $db;

    public function __construct()
    {
        $this->db = SingletonModel::getInstance()->getConnection();
        $uploadModel = new UploadModel($this->db);
        $folders = $uploadModel->getFolderHierarchy($_SESSION['current_project_id'], $_SESSION['user_id']);
        $this->comparaisonModel = new ComparaisonModel();
        $this->view = new ComparaisonView($folders);
        $this->GeoJsonModel = new GeoJsonModel();
    }

    public function saveExperimentation()
    {
        // Récupérer les données JSON envoyées par l'AJAX
        $data = json_decode(file_get_contents('php://input'), true);

        // Récupérer les noms des fichiers GeoJSON pour la simulation et la vérité terrain
        $geoJsonSimName = $data['geoJsonSimName'] ?? 'default_simulation';
        $geoJsonVerName = $data['geoJsonVerName'] ?? 'default_verite';

        // Récupérer le nom, le dossier, et le projet depuis la requête AJAX
        $name = $data['name'] ?? 'Nom par défaut';
        $dossier = $data['dossier'] ?? 'Dossier par défaut';
        $project = $data['project'] ?? 'Projet par défaut';

        // Appeler la méthode `saveExperimentation` du modèle
        try {
            $this->comparaisonModel->saveExperimentation($data, $geoJsonSimName, $geoJsonVerName, $name, $dossier, $project);
            // Répondre à l'AJAX
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log('Erreur lors de la sauvegarde : ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function execute($geoJsonHouseSimName, $geoJsonHouseVerName,$geoJsonRoadSimName,$geoJsonRoadVerName,$experimentId = null){


        // Charger les GeoJSON depuis la base de données
        $geoJsonHouseSim = $this->GeoJsonModel->fetchGeoJson($geoJsonHouseSimName);
        $geoJsonHouseVer= $this->GeoJsonModel->fetchGeoJson($geoJsonHouseVerName);
        $geoJsonRoadSim = $this->GeoJsonModel->fetchGeoJson($geoJsonRoadSimName);
        $geoJsonRoadVer = $this->GeoJsonModel->fetchGeoJson($geoJsonRoadVerName);

        // Projeter les GeoJSON dans le même système de coordonnées
        $geoJsonSimProj = $this->comparaisonModel->projectGeoJson($geoJsonHouseSim);
        $geoJsonVerProj = $this->comparaisonModel->projectGeoJson($geoJsonHouseVer);

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

        // PHP : Gestion des redirections après soumission du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['geoJsonName'])) {
            $geoJsonName = htmlspecialchars($_POST['geoJsonName']);

            // Redirige vers une nouvelle page en utilisant la méthode GET
            header("Location: https://developpement-urbain.alwaysdata.net/index.php?action=affichage&house=$geoJsonName");
            exit;
        }
        if ($experimentId) {
            // Charger l'expérience si un ID est fourni
            $experimentData = $this->comparaisonModel->loadExperimentation($experimentId);
            // Passer l'expérience à la vue si nécessaire
            //$this->view->showComparisonWithExperiment($results, $geoJsonSim, $geoJsonVer, $geoJsonSimName, $geoJsonVerName, $experimentData);
        } else {
            // Si aucun ID n'est fourni, juste afficher les résultats
            $this->view->showComparison($results,  $geoJsonHouseSim,$geoJsonHouseVer,$geoJsonHouseSimName,$geoJsonHouseVerName, $geoJsonRoadSim,$geoJsonRoadVer);
        }

    }


}