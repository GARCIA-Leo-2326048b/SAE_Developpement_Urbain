<?php
//namespace _assets\config;
//
//use PDO;
//use PDOException;
//
//class Database {
//    public $conn;
//
//    public function getConnection() {
//        $this->conn = null;
//
//        try {
//            $this->conn = new PDO('mysql:host=mysql-developpement-urbain.alwaysdata.net;
//            dbname=developpement-urbain_344;
//            charset=utf8',
//                '379003',
//                'saeflouvat');
//            // Configurer les erreurs PDO en exception
//            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//            // Désactiver les emulations de prepared statements pour éviter certains risques de sécurité
//            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
//        } catch(PDOException $exception) {
//            echo "Erreur de connexion : " . $exception->getMessage();
//            exit;
//        }
//
//        return $this->conn;
//    }
//}
//?>
