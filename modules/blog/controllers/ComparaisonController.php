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
    private $geoJsonModel;
    private $view;
    private $db;

    public function __construct()
    {
        $this->db = SingletonModel::getInstance()->getConnection();
        $uploadModel = new UploadModel($this->db);
        $folders = $uploadModel->getFolderHierarchy($_SESSION['current_project_id'], $_SESSION['user_id']);
        $this->comparaisonModel = new ComparaisonModel();
        $this->view = new ComparaisonView($folders);
        $this->geoJsonModel = new GeoJsonModel();
    }

    public function saveExperimentation()
    {
        // Récupérer les données JSON envoyées par l'AJAX
        $data = json_decode(file_get_contents('php://input'), true);

        // Récupérer les noms des fichiers GeoJSON pour la simulation et la vérité terrain
        $geoJsonSimNames = $data['geoJsonSimNames'] ?? [];
        $geoJsonVerNames = $data['geoJsonVerNames'] ?? [];

        // Récupérer le nom, le dossier, et le projet depuis la requête AJAX
        $name = $data['name'] ?? 'Nom par défaut';
        $dossier = $data['folder'] ?? 'root';
        $project = $_SESSION['current_project_id'];

        // Appeler la méthode `saveExperimentation` du modèle
        try {
            $this->comparaisonModel->saveExperimentation($data, $geoJsonSimNames, $geoJsonVerNames, $name, $dossier, $project);
            // Répondre à l'AJAX
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log('Erreur lors de la sauvegarde : ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function deleteExp()
    {
        $fileName = htmlspecialchars(filter_input(INPUT_GET, 'fileName', FILTER_SANITIZE_SPECIAL_CHARS));

        if ($this->comparaisonModel->deleteFileExp($fileName,$_SESSION['current_project_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }


    public function execute($filesSimName, $filesVerName,$experimentId = null){


        if ($experimentId) {
            // Charger l'expérience si un ID est fourni
            $experimentData = $this->comparaisonModel->loadExperimentation($experimentId);

            // Extraire les données du tableau associatif
            $nomXp = $experimentData['nom_xp'] ?? null;
            $geoJsonSimName = $experimentData['geoJsonSimName'] ?? [];
            $geoJsonVerName = $experimentData['geoJsonVerName'] ?? [];
            $charts = $experimentData['charts'] ?? null;
            $tableData = $experimentData['tableData'] ?? null;
            // Reformater les données pour les passer à la vue
            $formattedData = $this->comparaisonModel->reformaterDonnees($tableData);

            // Désimbriquer les données GeoJSON si nécessaire
            $geoJsonSim = isset($experimentData['geoJsonSim']) ? array_map(function ($item) {
                return is_array($item) && isset($item['file_data']) ? $item['file_data'] : $item;
            }, $experimentData['geoJsonSim']) : [];

            $geoJsonVer = isset($experimentData['geoJsonVer']) ? array_map(function ($item) {
                return is_array($item) && isset($item['file_data']) ? $item['file_data'] : $item;
            }, $experimentData['geoJsonVer']) : [];


            // Passer chaque donnée individuellement à la vue
            $this->view->showComparison($formattedData, $geoJsonSimName,$geoJsonVerName,$geoJsonSim,$geoJsonVer,$charts);
        } else {
            // Charger les GeoJSON depuis la base de données
            $fileDataSim = [];
            foreach ($filesSimName as $file) {
                $fileDataSim[] = $this->geoJsonModel->fetchGeoJson($file);
            }
            $fileDataVer = [];
            foreach ($filesVerName as $file) {
                $fileDataVer[] = $this->geoJsonModel->fetchGeoJson($file);
            }


            // Projeter les GeoJSON dans le même système de coordonnées
            $geoJsonSimProj = $this->comparaisonModel->projectGeoJson($fileDataSim[0]);
            $geoJsonVerProj = $this->comparaisonModel->projectGeoJson($fileDataVer[0]);

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
            // Si aucun ID n'est fourni, juste afficher les résultats
            $this->view->showComparison($results, $filesSimName,$filesVerName,$fileDataSim,$fileDataVer);
        }

    }


}
