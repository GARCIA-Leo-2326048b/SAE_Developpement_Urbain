<?php
namespace blog\models;
use PDO;
use PDOException;

class InscriptionModel
{
    private $db; // pour la connexion à la base de données

    public function __construct()
    {
        // Connexion à la base de données via SingletonModel
        $this->db = SingletonModel::getInstance()->getConnection();
    }

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