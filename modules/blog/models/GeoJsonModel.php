<?php
namespace blog\models;
use geoPHP;
use proj4php\Point;
use proj4php\Proj;
use proj4php\Proj4php;

class GeoJsonModel
{

    private $db;

    public function __construct()
    {
        // Connexion à la base de données via SingletonModel
        $this->db = SingletonModel::getInstance()->getConnection();
    }

    public function fetchGeoJson($name)
    {
        $stmt = $this->db->prepare("SELECT file_data FROM uploadGJ WHERE file_name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();

        // Retourner les données GeoJSON
        return $stmt->fetchColumn();
    }
    public function fetchIdGeoJson($geojson) {
        try {

            $stmt = $this->db->prepare("SELECT file_name FROM uploadGJ WHERE file_data LIKE :geojson");
            $stmt->bindValue(':geojson', "%$geojson%", \PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            return null;
        }
    }
}