<?php
namespace blog\controllers;
use blog\models\AuthentificationModel;
use blog\views\AuthentificationView;

/**
 * Classe AuthentificationController
 *
 * Cette classe gère l'authentification des utilisateurs.
 */
class AuthentificationController {
    /**
     * @var AuthentificationModel $userModel Instance du modèle d'authentification
     */
    private $userModel; // Sert à stocker l'instance du modèle d'authentification

    /**
     * @var AuthentificationView $view Instance de la vue d'authentification
     */
    private $view; // Stocke l'instance de la vue d'authentification

    /**
     * Constructeur de la classe AuthentificationController
     *
     * Initialise le modèle et la vue d'authentification.
     */
    public function __construct() {
        $this->userModel = new AuthentificationModel(); // Initialisation du modèle d'authentification
        $this->view = new AuthentificationView(); // Initialisation de la vue d'authentification
    }

    /**
     * Exécuter l'affichage de la vue d'authentification
     *
     * Affiche la vue d'authentification.
     *
     * @return void
     */
    public function execute() : void {
        (new \blog\views\AuthentificationView())->show(); // Affiche la vue d'authentification
    }

    /**
     * Gérer la connexion de l'utilisateur
     *
     * Vérifie si la méthode de la requête est POST. Si oui, récupère les informations du formulaire,
     * vérifie les informations d'identification, et si elles sont correctes, démarre une session et redirige l'utilisateur.
     * Sinon, affiche un message d'erreur. Si la méthode de la requête n'est pas POST, affiche la vue d'authentification.
     *
     * @return void
     */
    public function connexion() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Vérifie si la méthode de la requête est POST
            $identifiant = $_POST['identifiant']; // Récupère l'identifiant de l'utilisateur depuis le formulaire
            $password = $_POST['password']; // Récupère le mot de passe de l'utilisateur depuis le formulaire

            // Authentification de l'utilisateur
            $user = $this->userModel->test_Pass($identifiant, $password); // Vérifie les informations d'identification

            if ($user) { // Si l'utilisateur est authentifié
                session_start(); // Démarre une session
                $_SESSION['suid'] = session_id(); // Stocke l'ID de session
                $_SESSION['user_id'] = $identifiant; // Stocke l'identifiant de l'utilisateur dans la session
                header('Location: https://developpement-urbain.alwaysdata.net/index.php'); // Redirige vers la page d'accueil
                exit(); // Termine le script
            } else { // Si l'authentification échoue
                $error = "Nom d'utilisateur ou mot de passe incorrect."; // Message d'erreur
                ob_start(); // Démarre la bufferisation de sortie
                $this->view->show($error); // Affiche la vue avec le message d'erreur
                echo ob_get_clean(); // Envoie le contenu du tampon de sortie et nettoie le tampon
            }
        } else { // Si la méthode de la requête n'est pas POST
            ob_start(); // Démarre la bufferisation de sortie
            $this->view->show(); // Affiche la vue d'authentification
            echo ob_get_clean(); // Envoie le contenu du tampon de sortie et nettoie le tampon
        }
    }

    /**
     * Gérer la déconnexion de l'utilisateur
     *
     * Démarre une session, détruit la session, et redirige l'utilisateur vers la page d'accueil.
     *
     * @return void
     */
    public function deconnexion() {
        session_start(); // Démarre la session
        session_destroy(); // Détruit la session
        header('Location: https://developpement-urbain.alwaysdata.net/index.php'); // Redirige vers la page d'accueil
        exit(); // Termine le script
    }
}
?>