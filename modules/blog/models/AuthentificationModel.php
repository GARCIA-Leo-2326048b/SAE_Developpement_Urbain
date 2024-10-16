<?php
namespace blog\models;
use PDO;
use PDOException;

class AuthentificationModel {
    private $db; // pour la connexion à la base de données

    public function __construct()
    {
        try {
            // Connexion à la base de données
            $this->db = new PDO('mysql:host=mysql-developpement-urbain.alwaysdata.net;
            dbname=developpement-urbain_344;
            charset=utf8',
                '379003',
                'saeflouvat');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // défini le mode d'erreur
        } catch (PDOException $e) {
            // En cas d'erreur, affiche un message et arrête le programme
            die('Erreur : ' . $e->getMessage());
        }
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