<?php

namespace blog\controllers;

use _assets\config\Database;
use blog\models\SingletonModel;
use blog\models\UploadModel;
use blog\views\PreparationSimulation;

class SimulationController
{
//    private $db;
//    private $uploadModel;
//    private $utilisateur;
//
//    public function __construct(){
//        // Utiliser SingletonModel pour obtenir la connexion à la base de données
//        $this->db = SingletonModel::getInstance()->getConnection();
//        $this->uploadModel = new UploadModel($this->db);
//        $this->utilisateur = $_SESSION['user_id'];
//
//    }
//    public function execute() : void {
//        $repertoires = $this->uploadModel->getUserFilesWithFolders($this->utilisateur);
//        (new PreparationSimulation($repertoires))->show();
//    }

}