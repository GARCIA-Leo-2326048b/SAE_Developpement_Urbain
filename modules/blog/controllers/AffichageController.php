<?php

namespace blog\controllers;
use blog\models\SingletonModel;
use blog\models\UploadModel;
use blog\views\AffichageView;
use blog\models\GeoJsonModel;
class AffichageController
{

    private $view;
    private $model;
    private $db;
    private $uploadModel;
    private $utilisateur;

    public function __construct()
    {
        $this->model=new GeoJsonModel();
        // Utiliser SingletonModel pour obtenir la connexion à la base de données
        $this->db = SingletonModel::getInstance()->getConnection();
        $this->uploadModel = new UploadModel($this->db);
        $this->utilisateur = $_SESSION['user_id'];
    }

    public function setModel(GeoJsonModel $model)
    {
        $this->model = $model;
    }
    public function setView(AffichageView $view)
    {
        $this->view = $view;
    }

    public function execute($files = [])
    {
        $repertoires = $this->uploadModel->getFolderHierarchy($_SESSION['current_project_id'],$this->utilisateur);

        $fileData = [];
        foreach ($files as $file) {
            $fileData[] = $this->model->fetchGeoJson($file);

        }


        (new AffichageView($repertoires))->show($fileData);

    }

}