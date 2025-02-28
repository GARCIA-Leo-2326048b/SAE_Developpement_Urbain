<?php

namespace blog\controllers;
use blog\models\SingletonModel;
use blog\models\UploadModel;
use blog\views\HomepageView;

/**
 * Classe HomepageController
 *
 * Cette classe gère l'affichage de la page d'accueil.
 */
class HomepageController {

    /**
     * @var \PDO $db Connexion à la base de données
     */
    private $db; // Connexion à la base de données

    /**
     * @var UploadModel $uploadModel Modèle pour les uploads
     */
    private $uploadModel; // Modèle pour les uploads

    /**
     * @var int $utilisateur Identifiant de l'utilisateur
     */
    private $utilisateur; // Identifiant de l'utilisateur

    /**
     * Constructeur de la classe HomepageController
     *
     * Initialise la connexion à la base de données et le modèle d'upload.
     * Vérifie si l'utilisateur est connecté et récupère son identifiant.
     */
    public function __construct(){
        // Utiliser SingletonModel pour obtenir la connexion à la base de données
        $this->db = SingletonModel::getInstance()->getConnection();
        // Initialisation du modèle d'upload avec la connexion DB
        $this->uploadModel = new UploadModel($this->db);
        // Vérifier si l'identifiant de l'utilisateur est défini dans la session
        if(isset($_SESSION['user_id'])){
            // Récupération de l'identifiant de l'utilisateur depuis la session
            $this->utilisateur = $_SESSION['user_id'];
        }
    }

    /**
     * Exécuter l'affichage de la page d'accueil
     *
     * Vérifie si l'utilisateur est connecté, récupère ses projets et affiche la vue de la page d'accueil.
     *
     * @return void
     */
    public function execute() : void {
        // Vérifier si l'utilisateur est connecté
        if(isset($_SESSION['user_id'])){
            // Récupérer les projets de l'utilisateur
            $projets = $this->uploadModel->getUserProjects($this->utilisateur);
        } else {
            // Si l'utilisateur n'est pas connecté, définir les projets à null
            $projets = null;
        }
        // Afficher la vue de la page d'accueil avec les projets de l'utilisateur
        (new HomepageView($projets))->show();
    }
}