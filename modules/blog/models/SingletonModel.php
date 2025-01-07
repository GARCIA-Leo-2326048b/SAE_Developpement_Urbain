<?php

namespace blog\models;

use PDO;
use PDOException;

class SingletonModel
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $dsn = 'mysql:host=mysql-developpement-urbain.alwaysdata.net;dbname=developpement-urbain_344;charset=utf8';
        $username = '379003';
        $password = 'saeflouvat';

        try {
            $this->connection = new PDO($dsn, $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}