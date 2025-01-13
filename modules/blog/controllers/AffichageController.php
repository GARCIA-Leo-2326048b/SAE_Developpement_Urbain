<?php

namespace blog\controllers;
use blog\models\SingletonModel;
use blog\models\UploadModel;
use blog\views\AffichageView;
use blog\models\GeoJsonModel;

/**
 * Classe AffichageController
 *
 * Cette classe gère les opérations d'affichage des fichiers GeoJson et la hiérarchie des répertoires.
 */
class AffichageController
{
    /**
     * @var AffichageView $view Vue pour l'affichage
     */
    private $view;

    /**
     * @var GeoJsonModel $model Modèle pour les données GeoJson
     */
    private $model;

    /**
     * @var \PDO $db Connexion à la base de données
     */
    private $db;

    /**
     * @var UploadModel $uploadModel Modèle pour les uploads
     */
    private $uploadModel;

    /**
     * @var int $utilisateur Identifiant de l'utilisateur
     */
    private $utilisateur;

    /**
     * Constructeur de la classe AffichageController
     *
     * Initialise le modèle GeoJson, la connexion à la base de données, le modèle d'upload et l'utilisateur connecté.
     */
    public function __construct()
    {
        $this->model = new GeoJsonModel(); // Initialisation du modèle GeoJson
        // Utiliser SingletonModel pour obtenir la connexion à la base de données
        $this->db = SingletonModel::getInstance()->getConnection();
        $this->uploadModel = new UploadModel($this->db); // Initialisation du modèle d'upload avec la connexion DB
        $this->utilisateur = $_SESSION['user_id']; // Récupération de l'identifiant de l'utilisateur depuis la session
    }

    /**
     * Exécuter l'affichage
     *
     * Récupère la hiérarchie des répertoires pour le projet courant et l'utilisateur,
     * puis affiche les données GeoJson des fichiers spécifiés.
     *
     * @param array $files Les fichiers à afficher
     * @return void
     */
    public function execute($files = [])
    {
        // Récupérer la hiérarchie des répertoires pour le projet courant et l'utilisateur
        $repertoires = $this->uploadModel->getFolderHierarchy($_SESSION['current_project_id'], $this->utilisateur);

        $fileData = []; // Tableau pour stocker les données des fichiers
        foreach ($files as $file) {
            // Récupérer les données GeoJson pour chaque fichier
            $fileData[] = $this->model->fetchGeoJson($file);
        }

        // Afficher les données en utilisant la vue AffichageView
        (new AffichageView($repertoires))->show($fileData);
    }
}
?>