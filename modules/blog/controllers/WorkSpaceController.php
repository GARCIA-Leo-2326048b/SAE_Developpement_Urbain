<?php

namespace blog\controllers;

use _assets\config\Database;
use blog\models\SingletonModel;
use blog\models\UploadModel;
use blog\views\MesSimulationView;
use blog\views\PreparationSimulation;

class WorkSpaceController
{
    private $db;
    private $uploadModel;
    private $utilisateur;

    public function __construct(){
        // Utiliser SingletonModel pour obtenir la connexion à la base de données
        $this->db = SingletonModel::getInstance()->getConnection();
        $this->uploadModel = new UploadModel($this->db);
        $this->utilisateur = $_SESSION['user_id'];

    }
    public function execute() : void {

        if (!isset($_SESSION['current_project_id'])) {
            $_SESSION['current_project_id'] = null; // Initialisez à `null` si non défini
        }
        $repertoires = $this->uploadModel->getFolderHierarchy($_SESSION['current_project_id'],$this->utilisateur);
        (new PreparationSimulation($repertoires))->show();
    }

    public function project()
    {
        if (!isset($_SESSION['current_project_id'])) {
            $_SESSION['current_project_id'] = null; // Initialisez à `null` si non défini
        }

        $project = $this->uploadModel->getFolderHierarchy($_SESSION['current_project_id'],$this->utilisateur);
        (new MesSimulationView($project))->show();
    }

}
