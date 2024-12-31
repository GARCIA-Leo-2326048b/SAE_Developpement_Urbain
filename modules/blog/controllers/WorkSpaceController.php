<?php

namespace blog\controllers;

use _assets\config\Database;
use blog\models\UploadModel;
use blog\views\PreparationSimulation;

class WorkSpaceController
{
    private $db;
    private $uploadModel;
    private $utilisateur;

    public function __construct(){
        $database = new Database();
        $this->db = $database->getConnection();
        $this->uploadModel = new UploadModel($this->db);
        $this->utilisateur = $_SESSION['user_id'];

    }
    public function execute() : void {
        $repertoires = $this->uploadModel->getUserFilesWithFolders($this->utilisateur);
        (new PreparationSimulation($repertoires))->show();
    }

}