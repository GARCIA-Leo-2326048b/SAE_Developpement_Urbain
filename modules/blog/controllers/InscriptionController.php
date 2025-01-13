<?php
namespace blog\controllers;
use blog\models\InscriptionModel;
use blog\views\InscriptionView;

/**
 * Classe InscriptionController
 *
 * Cette classe gère l'inscription des utilisateurs.
 */
class InscriptionController
{
    /**
     * @var InscriptionModel $userModel Instance du modèle d'inscription
     */
    private $userModel; // sert à stocker l'instance du modèle

    /**
     * @var InscriptionView $view Instance de la vue d'inscription
     */
    private $view; // stocke l'instance de la vue

    /**
     * Constructeur de la classe InscriptionController
     *
     * Initialise le modèle et la vue d'inscription.
     */
    public function __construct() {
        $this->userModel = new InscriptionModel(); // Initialisation du modèle
        $this->view = new InscriptionView(); // Initialisation de la vue
    }

    /**
     * Exécuter l'affichage de la vue d'inscription
     *
     * Affiche la vue d'inscription.
     *
     * @return void
     */
    public function execute() : void {
        (new \blog\views\InscriptionView())->show(); // Affiche la vue d'inscription
    }

    /**
     * Gérer la création de compte
     *
     * Vérifie si la méthode de la requête est POST. Si oui, récupère les informations du formulaire,
     * vérifie si l'utilisateur existe déjà, et si ce n'est pas le cas, inscrit l'utilisateur et démarre une session.
     * Sinon, affiche un message d'erreur. Si la méthode de la requête n'est pas POST, affiche la vue d'inscription.
     *
     * @return void
     */
    public function creationCompte() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Vérifie si la méthode de la requête est POST
            $identifiant = $_POST['identifiant']; // Récupère l'identifiant de l'utilisateur depuis le formulaire
            $password = $_POST['password']; // Récupère le mot de passe de l'utilisateur depuis le formulaire

            // Authentification de l'utilisateur
            $user = $this->userModel->test_User($identifiant); // Vérifie si l'utilisateur existe déjà

            if (!$user) { // Si l'utilisateur n'existe pas
                // Démarre une session si l'authentification réussit
                if($this->userModel->inscrire($identifiant,$password)){ // Inscrit l'utilisateur
                    session_start(); // Démarre une session
                    $_SESSION['suid'] = session_id(); // Stocke l'ID de session
                    $_SESSION['user_id'] = $identifiant; // Stocke l'identifiant de l'utilisateur dans la session
                    header('Location: https://developpement-urbain.alwaysdata.net/index.php'); // Redirige vers la page d'accueil
                }
                exit(); // Termine le script
            } else { // Si l'utilisateur existe déjà
                // Retourne un message d'erreur si l'inscription échoue
                $error = "Nom d'utilisateur déjà utilisé."; // Message d'erreur
                ob_start(); // Démarre la bufferisation
                $this->view->show($error); // Affiche la vue avec l'erreur
                echo ob_get_clean(); // Nettoie le tampon de sortie
            }
        } else { // Si la méthode de la requête n'est pas POST
            ob_start(); // Démarre la bufferisation
            $this->view->show(); // Affiche la vue
            echo ob_get_clean(); // Nettoie le tampon de sortie
        }
    }
}