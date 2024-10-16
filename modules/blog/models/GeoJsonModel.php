<?php
namespace blog\models;
use geoPHP;
class GeoJsonModel
{

    private $db;

    public function __construct()
    {
        // Connexion à la base de données
        $this->db = new \PDO('mysql:host=mysql-developpement-urbain.alwaysdata.net;dbname=developpement-urbain_344', '379003', 'saeflouvat');

    }
    public function fetchGeoJson($name)
    {
        $stmt = $this->db->prepare("SELECT file_data FROM uploadGJ WHERE file_name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();

        // Retourner les données GeoJSON
        return $stmt->fetchColumn();
    }


}