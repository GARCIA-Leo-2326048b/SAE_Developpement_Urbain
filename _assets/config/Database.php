<?php
namespace _assets\config\Database;

use PDO;
use PDOException;

class Database {
    private $host = "mysql-developpement-urbain.alwaysdata.ne"; // Adresse du serveur de base de données
    private $db_name = "developpement-urbain_344"; // Nom de la base de données
    private $username = "379003	"; // Remplacez par votre username
    private $password = "saeflouvat"; // Remplacez par votre mot de passe
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            // Configurer les erreurs PDO en exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Désactiver les emulations de prepared statements pour éviter certains risques de sécurité
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $exception) {
            echo "Erreur de connexion : " . $exception->getMessage();
            exit;
        }

        return $this->conn;
    }
}
?>
