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

    public function execute($house = null, $road = null, $tiff = null)
    {
        $repertoires = $this->uploadModel->getFolderHierarchy($_SESSION['current_project_id'],$this->utilisateur);

        $houseData = $this->model->fetchGeoJson($house);
        $roadData = $this->model->fetchGeoJson($road);

        (new AffichageView($repertoires))->show($houseData,$roadData,null,null);
//        $this->view->show($houseData,$roadData,null,null);
    }

}