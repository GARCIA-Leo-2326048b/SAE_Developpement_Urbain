<?php
namespace blog\models;
use PDO;
use PDOException;

/**
 * Classe AuthentificationModel
 *
 * Cette classe gère les opérations d'authentification des utilisateurs.
 */
class AuthentificationModel {
    /**
     * @var \PDO $db Connexion à la base de données
     */
    private $db; // pour la connexion à la base de données

    /**
     * Constructeur de la classe AuthentificationModel
     *
     * Initialise la connexion à la base de données via SingletonModel.
     */
    public function __construct()
    {
        // Connexion à la base de données via SingletonModel
        $this->db = SingletonModel::getInstance()->getConnection();
    }

    /**
     * Tester le mot de passe
     *
     * Vérifie si l'utilisateur existe et si le mot de passe correspond.
     *
     * @param string $identifiant Identifiant de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @return array|false Les informations de l'utilisateur si l'authentification réussit, sinon false
     */
    public function test_Pass($identifiant, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM Utilisateur WHERE Identifiant = :Identifiant");
        $stmt->bindParam(':Identifiant', $identifiant);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie si l'utilisateur existe et si le mot de passe correspond
        if ($result && password_verify($password, $result['Password'])) {
            return $result;
        }

        return false;
    }
}
?>