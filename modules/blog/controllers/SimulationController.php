<?php

namespace blog\controllers;

use _assets\config\Database;
use blog\models\UploadModel;
use blog\views\PreparationSimulation;

class SimulationController
{
    private $db;
    private $uploadModel;
    private $files;
    private $utilisateur;

    public function __construct(){
        $database = new Database();
        $this->db = $database->getConnection();
        $this->uploadModel = new UploadModel($this->db);
        $this->utilisateur = $_SESSION['user_id'];

    }
    public function execute() : void {
        $this->files = $this->uploadModel->getAllUploadsByUser($this->utilisateur);
        $repertoires = $this->uploadModel->getUserFilesWithFolders($this->utilisateur);
        (new PreparationSimulation($repertoires))->show();
    }

}