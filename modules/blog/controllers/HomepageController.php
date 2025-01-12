<?php

namespace blog\controllers;
use blog\models\SingletonModel;
use blog\models\UploadModel;
use blog\views\HomepageView;

class HomepageController {

    private $db;
    private $uploadModel;
    private $utilisateur;

    public function __construct(){
        // Utiliser SingletonModel pour obtenir la connexion à la base de données
        $this->db = SingletonModel::getInstance()->getConnection();
        $this->uploadModel = new UploadModel($this->db);
        if(isset($_SESSION['user_id'])){
            $this->utilisateur = $_SESSION['user_id'];
        }

    }
    public function execute() : void {
        if(isset($_SESSION['user_id'])){
            $projets = $this->uploadModel->getUserProjects($this->utilisateur);
        }else{
            $projets= null;
        }
        (new HomepageView($projets))->show();
    }
}
