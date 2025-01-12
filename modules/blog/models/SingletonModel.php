<?php

namespace blog\models;

use _assets\config\Config;
use PDO;
use PDOException;



class SingletonModel
{
    private static $instance = null; // Instance unique de la classe
    private $connection;            // Connexion PDO

    private $config;

    // Constructeur privé pour le Singleton
    private function __construct()
    {
        // Charger la configuration depuis une fonction dédiée
        $this->config = new Config();
        $conn = $this->config->getDatabaseConfig();

        try {
            // Créer la connexion PDO avec les paramètres de configuration
            $this->connection = new PDO($conn['dsn'], $conn['username'], $conn['password']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Gérer les erreurs de connexion
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Méthode pour obtenir l'instance unique de la classe
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Méthode pour récupérer la connexion PDO
    public function getConnection()
    {
        return $this->connection;
    }
}
