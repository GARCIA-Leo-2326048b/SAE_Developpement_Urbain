<?php
namespace blog\controllers;
use blog\models\AuthentificationModel;
use blog\views\AuthentificationView;
require_once 'modules/blog/models/AuthentificationModel.php';
require_once 'modules/blog/views/AuthentificationView.php';
class AuthentificationController {
    private $userModel; // sert à stocker l'instance du modèle
    private $view; // stocke l'instance de la vue

    public function __construct() {
        $this->userModel = new AuthentificationModel(); // Initialisation du modèle
        $this->view = new AuthentificationView(); // Initialisation de la vue
    }

    public function execute() : void {
        (new \blog\views\AuthentificationView())->show();
    }

    public function connexion() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $identifiant = $_POST['identifiant'];
            $password = $_POST['password'];

            // Authentification de l'utilisateur
            $user = $this->userModel->test_Pass($identifiant, $password);

            if ($user) {
                // Démarre une session si l'authentification réussit
                session_start();
                $_SESSION['suid'] = session_id();
                header('Location: https://developpement-urbain.alwaysdata.net/index.php');
                exit();
            } else {
                // Retourne un message d'erreur si la connexion échoue
                $error = "Nom d'utilisateur ou mot de passe incorrect.";
                ob_start(); // Démarre la bufferisation
                $this->view->show($error); // Affiche la vue avec l'erreur
                echo ob_get_clean(); // Nettoie le tampon de sortie
                //                require 'modules/blog/views/AuthentificationView.php';
            }
        } else {
//            require 'modules/blog/views/AuthentificationView.php';
            ob_start(); // Démarre la bufferisation
            $this->view->show(); // Affiche la vue
            echo ob_get_clean(); // Nettoie le tampon de sortie
        }
    }

    public function deconnexion() {
        // Déconnecte l'utilisateur
        session_start();
        session_destroy();
        header('Location: https://developpement-urbain.alwaysdata.net/index.php');
        exit();
    }
}
?>