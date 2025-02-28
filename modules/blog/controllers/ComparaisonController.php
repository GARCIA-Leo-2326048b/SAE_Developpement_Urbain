<?php

namespace blog\controllers;

use blog\models\ComparaisonModel;
use blog\models\GeoJsonModel;
use blog\models\SingletonModel;
use blog\models\UploadModel;
use blog\views\ComparaisonView;
use geoPHP;

/**
 * Contrôleur pour la gestion des comparaisons de fichiers GeoJSON et des expérimentations.
 */
class ComparaisonController
{
    /**
     * @var ComparaisonModel $comparaisonModel Modèle pour gérer les données de comparaison.
     */
    private $comparaisonModel;

    /**
     * @var GeoJsonModel $geoJsonModel Modèle pour manipuler les fichiers GeoJSON.
     */
    private $geoJsonModel;

    /**
     * @var ComparaisonView $view Vue associée aux comparaisons.
     */
    private $view;

    /**
     * @var \PDO $db Instance de connexion à la base de données.
     */
    private $db;

    /**
     * Constructeur : Initialise les modèles, la vue et récupère la connexion à la base de données.
     */
    public function __construct()
    {
        $this->db = SingletonModel::getInstance()->getConnection();
        $uploadModel = new UploadModel($this->db);
        $folders = $uploadModel->getFolderHierarchy($_SESSION['current_project_id'], $_SESSION['user_id']);
        $this->comparaisonModel = new ComparaisonModel();
        $this->view = new ComparaisonView($folders);
        $this->geoJsonModel = new GeoJsonModel();
    }

    /**
     * Sauvegarde une expérimentation avec les données fournies par une requête AJAX.
     *
     * @return void
     */
    public function saveExperimentation()
    {
        // Récupérer les données JSON envoyées par AJAX
        $data = json_decode(file_get_contents('php://input'), true);

        // Récupérer les noms des fichiers GeoJSON pour la simulation et la vérité terrain
        $geoJsonSimNames = $data['geoJsonSimName'] ?? [];
        $geoJsonVerNames = $data['geoJsonVerName'] ?? [];

        // Récupérer le nom, le dossier, et le projet depuis la requête AJAX
        $name = $data['name'] ?? 'Nom par défaut';
        $dossier = $data['folder'] ?? 'root';
        $project = $_SESSION['current_project_id'];

        try {
            $this->comparaisonModel->saveExperimentationM($data, $geoJsonSimNames, $geoJsonVerNames, $name, $dossier, $project);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log('Erreur lors de la sauvegarde : ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Supprime une expérimentation basée sur le nom de fichier fourni dans la requête GET.
     *
     * @return void
     */
    public function deleteExp()
    {
        $fileName = htmlspecialchars(filter_input(INPUT_GET, 'fileName', FILTER_SANITIZE_SPECIAL_CHARS));

        if ($this->comparaisonModel->deleteFileExp($fileName, $_SESSION['current_project_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    /**
     * Exécute la logique de comparaison ou charge une expérimentation existante.
     *
     * @param array|null $filesSimName Noms des fichiers de simulation GeoJSON.
     * @param array|null $filesVerName Noms des fichiers de vérité terrain GeoJSON.
     * @param int|null $experimentId Identifiant de l'expérimentation à charger (facultatif).
     *
     * @return void
     */
    public function execute($filesSimName, $filesVerName, $experimentId = null)
    {
        if ($experimentId) {
            // Charger l'expérience si un ID est fourni
            $experimentData = $this->comparaisonModel->loadExperimentation($experimentId);

            $geoJsonSimName = $experimentData['geoJsonSimName'] ?? [];
            $geoJsonVerName = $experimentData['geoJsonVerName'] ?? [];
            $charts = $experimentData['charts'] ?? null;
            $tableData = $experimentData['tableData'] ?? null;

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
            $this->view->setId($experimentId);
//            $this->view->showComparison($formattedData, $geoJsonSim,$geoJsonVer,$geoJsonSimName,$geoJsonVerName,$charts);
        } else {
            $fileDataSim = [];
            foreach ($filesSimName as $file) {
                $fileDataSim[] = $this->geoJsonModel->fetchGeoJson($file);
            }
            $fileDataVer = [];
            foreach ($filesVerName as $file) {
                $fileDataVer[] = $this->geoJsonModel->fetchGeoJson($file);
            }

            $geoJsonSimProj = $this->comparaisonModel->projectGeoJson($fileDataSim[0]);
            $geoJsonVerProj = $this->comparaisonModel->projectGeoJson($fileDataVer[0]);

            $valuesSim = $this->comparaisonModel->getAreasAndPerimeters(geoPHP::load($geoJsonSimProj));
            $valuesVer = $this->comparaisonModel->getAreasAndPerimeters(geoPHP::load($geoJsonVerProj));

            $areaStatsSim = $this->comparaisonModel->getStat($valuesSim['areas']);
            $areaStatsVer = $this->comparaisonModel->getStat($valuesVer['areas']);

            $shapeIndexesSim = $this->comparaisonModel->getShapeIndexStats(['areas' => $valuesSim['areas'], 'perimeters' => $valuesSim['perimeters']]);
            $shapeIndexesVer = $this->comparaisonModel->getShapeIndexStats(['areas' => $valuesVer['areas'], 'perimeters' => $valuesVer['perimeters']]);

            $shapeIndexStatsSim = $this->comparaisonModel->getStat($shapeIndexesSim);
            $shapeIndexStatsVer = $this->comparaisonModel->getStat($shapeIndexesVer);

            $results = $this->comparaisonModel->grapheDonnees($areaStatsSim, $areaStatsVer, $shapeIndexStatsSim, $shapeIndexStatsVer);

            $this->view->showComparison($results, $filesSimName, $filesVerName, $fileDataSim, $fileDataVer);
        }
    }

    /**
     * Met à jour les graphiques d'une expérimentation spécifique.
     *
     * @param array $data Données comprenant l'ID de l'expérimentation et les graphiques à mettre à jour.
     *
     * @return void
     */
    public function updateExperimentationCharts($data)
    {
        $id = $data['id'] ?? null;

        if (!$id || !isset($data['charts'])) {
            echo json_encode(['success' => false, 'message' => 'ID ou graphiques manquants']);
            return;
        }

        try {
            $success = $this->comparaisonModel->updateExperimentationChartsM($id, $data['charts']);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            error_log('Erreur lors de la mise à jour : ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
