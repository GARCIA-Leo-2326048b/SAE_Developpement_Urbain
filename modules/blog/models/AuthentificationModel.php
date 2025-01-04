<?php
namespace blog\models;
use PDO;
use PDOException;

class AuthentificationModel {
    private $db; // pour la connexion à la base de données

    public function __construct()
    {
        // Connexion à la base de données via SingletonModel
        $this->db = SingletonModel::getInstance()->getConnection();
    }

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