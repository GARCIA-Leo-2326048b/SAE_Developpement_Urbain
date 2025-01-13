<?php

namespace blog\controllers;

use _assets\config\Database;
use blog\models\SingletonModel;
use blog\models\UploadModel;
use blog\views\MesSimulationView;
use blog\views\PreparationSimulation;

/**
 * Classe WorkSpaceController
 *
 * Cette classe gère les opérations liées à l'espace de travail de l'utilisateur.
 */
class WorkSpaceController
{
    /**
     * @var \PDO $db Connexion à la base de données
     */
    private $db;

    /**
     * @var UploadModel $uploadModel Modèle pour les opérations d'upload
     */
    private $uploadModel;

    /**
     * @var int $utilisateur ID de l'utilisateur connecté
     */
    private $utilisateur;

    /**
     * Constructeur de la classe WorkSpaceController
     *
     * Initialise la connexion à la base de données, le modèle d'upload et l'utilisateur connecté.
     */
    public function __construct(){
        // Utiliser SingletonModel pour obtenir la connexion à la base de données
        $this->db = SingletonModel::getInstance()->getConnection();
        $this->uploadModel = new UploadModel($this->db);
        $this->utilisateur = $_SESSION['user_id'];

    }

    /**
     * Exécuter l'affichage de l'espace de travail
     *
     * Vérifie si un projet est sélectionné, récupère la hiérarchie des dossiers et affiche la vue de l'espace de travail.
     *
     * @return void
     */
    public function execute() : void {

        if (!isset($_SESSION['current_project_id'])) {
            $_SESSION['current_project_id'] = null; // Initialisez à `null` si non défini
        }
        $repertoires = $this->uploadModel->getFolderHierarchy($_SESSION['current_project_id'],$this->utilisateur);
        (new PreparationSimulation($repertoires))->show();
    }

    /**
     * Gérer le projet courant
     *
     * Vérifie si un projet est sélectionné, récupère la hiérarchie des dossiers et affiche la vue du projet.
     *
     * @return void
     */
    public function project() : void
    {
        if (!isset($_SESSION['current_project_id'])) {
            $_SESSION['current_project_id'] = null; // Initialisez à `null` si non défini
        }

        $project = $this->uploadModel->getFolderHierarchy($_SESSION['current_project_id'],$this->utilisateur);
        $experimentation= $this->uploadModel->getExperimentation($this->utilisateur,$_SESSION['current_project_id']);
        (new MesSimulationView($project,$experimentation))->show();
    }

}
