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
        var_dump($repertoires);
        if (isset($repertoires[0]) && is_array($repertoires[0])) {
            $rootFolder = $repertoires[0];
            $folders = $rootFolder['children'] ?? []; // Si 'children' n'existe pas, renvoyer un tableau vide
        } else {
            $folders = []; // Structure invalide ou vide
        }
        (new PreparationSimulation($folders))->show();
    }

}