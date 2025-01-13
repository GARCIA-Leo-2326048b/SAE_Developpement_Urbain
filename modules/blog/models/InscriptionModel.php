<?php
namespace blog\models;
use PDO;
use PDOException;

/**
 * Classe InscriptionModel
 *
 * Cette classe gère les opérations d'inscription des utilisateurs.
 */
class InscriptionModel
{
    /**
     * @var \PDO $db Connexion à la base de données
     */
    private $db; // pour la connexion à la base de données

    /**
     * Constructeur de la classe InscriptionModel
     *
     * Initialise la connexion à la base de données via SingletonModel.
     */
    public function __construct()
    {
        // Connexion à la base de données via SingletonModel
        $this->db = SingletonModel::getInstance()->getConnection();
    }

    /**
     * Tester l'existence de l'utilisateur
     *
     * Vérifie si un utilisateur existe dans la base de données à partir de son identifiant.
     *
     * @param string $identifiant Identifiant de l'utilisateur
     * @return array|false Les informations de l'utilisateur si l'utilisateur existe, sinon false
     */
    public function test_User($identifiant)
    {
        $stmt = $this->db->prepare("SELECT * FROM Utilisateur WHERE Identifiant = :Identifiant");
        $stmt->bindParam(':Identifiant', $identifiant);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie si l'utilisateur existe
        if ($result ) {
            return $result;
        }

        return false;
    }

    /**
     * Inscrire un nouvel utilisateur
     *
     * Ajoute un nouvel utilisateur dans la base de données avec son identifiant et son mot de passe.
     *
     * @param string $identifiant Identifiant de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @return bool True si l'inscription réussit, sinon false
     */
    public function inscrire($identifiant, $password) {
        $query = "INSERT INTO Utilisateur (Identifiant, Password) VALUES (:identifiant, :password)";
        $stmt = $this->db->prepare($query);
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bindParam(':identifiant', $identifiant);
        $stmt->bindParam(':password', $passwordHash);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>