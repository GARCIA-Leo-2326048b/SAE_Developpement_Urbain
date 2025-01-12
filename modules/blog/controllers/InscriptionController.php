<?php
namespace blog\controllers;
use blog\models\InscriptionModel;
use blog\views\InscriptionView;

class InscriptionController
{
    private $userModel; // sert à stocker l'instance du modèle
    private $view; // stocke l'instance de la vue

    public function __construct() {
        $this->userModel = new InscriptionModel(); // Initialisation du modèle
        $this->view = new InscriptionView(); // Initialisation de la vue
    }
    public function execute() : void {
        (new \blog\views\InscriptionView())->show();
    }

    public function creationCompte() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $identifiant = $_POST['identifiant'];
            $password = $_POST['password'];

            // Authentification de l'utilisateur
            $user = $this->userModel->test_User($identifiant);

            if (!$user) {
                // Démarre une session si l'authentification réussit
                if($this->userModel->inscrire($identifiant,$password)){
                    session_start();
                    $_SESSION['suid'] = session_id();
                    $_SESSION['user_id'] = $identifiant;
                    header('Location: https://developpement-urbain.alwaysdata.net/index.php');
                }
                exit();
            } else {
                // Retourne un message d'erreur si l'inscription échoue
                $error = "Nom d'utilisateur déjà utilisé.";
                ob_start(); // Démarre la bufferisation
                $this->view->show($error); // Affiche la vue avec l'erreur
                echo ob_get_clean(); // Nettoie le tampon de sortie
            }
        } else {
            ob_start(); // Démarre la bufferisation
            $this->view->show(); // Affiche la vue
            echo ob_get_clean(); // Nettoie le tampon de sortie
        }
    }
}
