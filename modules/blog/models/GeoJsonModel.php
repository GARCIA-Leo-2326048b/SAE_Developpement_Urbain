<?php
namespace blog\models;
use geoPHP;
use proj4php\Point;
use proj4php\Proj;
use proj4php\Proj4php;

/**
 * Classe GeoJsonModel
 *
 * Cette classe gère les opérations liées aux données GeoJSON.
 */
class GeoJsonModel
{
    /**
     * @var \PDO $db Connexion à la base de données
     */
    private $db;

    /**
     * Constructeur de la classe GeoJsonModel
     *
     * Initialise la connexion à la base de données via SingletonModel.
     */
    public function __construct()
    {
        // Connexion à la base de données via SingletonModel
        $this->db = SingletonModel::getInstance()->getConnection();
    }

    /**
     * Récupérer les données GeoJSON
     *
     * Récupère les données GeoJSON d'un fichier à partir de son nom.
     *
     * @param string $name Nom du fichier GeoJSON
     * @return string Les données GeoJSON du fichier
     */
    public function fetchGeoJson($name)
    {
        $stmt = $this->db->prepare("SELECT file_data FROM uploadGJ WHERE file_name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();

        // Retourner les données GeoJSON
        return $stmt->fetchColumn();
    }
}
?>