<?php
namespace blog\models;

use PDO;

class UploadModel {
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    // Enregistrer un upload GeoJSON
    public function saveUploadGJ($fileName, $fileContent, $userId)
    {
        try {
            $query = "INSERT INTO uploadGJ (file_name, file_data, user) 
                      VALUES (:file_name, :file_data, :user)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':file_name', $fileName);
            $stmt->bindParam(':file_data', $fileContent, PDO::PARAM_STR);
            $stmt->bindParam(':user', $userId);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'enregistrement du GeoJSON : " . $e->getMessage());
        }
    }

    // Enregistrer un upload GeoTIFF
    public function saveUploadGT($fileName, $fileContent, $userId)
    {
        try {
            $query = "INSERT INTO uploadGT (file_name, file_data, user) 
                      VALUES (:file_name, :file_data, :user)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':file_name', $fileName);
            $stmt->bindParam(':file_data', $fileContent, PDO::PARAM_LOB);
            $stmt->bindParam(':user', $userId);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'enregistrement du GeoTIFF : " . $e->getMessage());
        }
    }

    // Récupérer les uploads GeoJSON d'un utilisateur
    public function getUploadsGJByUser($userId)
    {
        try {
            $query = "SELECT * FROM uploadGJ WHERE user = :user_id ORDER BY dateEnr DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des GeoJSON : " . $e->getMessage());
        }
    }

    // Récupérer les uploads GeoTIFF d'un utilisateur
    public function getUploadsGTByUser($userId)
    {
        try {
            $query = "SELECT * FROM uploadGT WHERE user = :user_id ORDER BY dateEnr DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des GeoTIFF : " . $e->getMessage());
        }
    }

    // Enregistrer une simulation
    public function saveSimulation($fileName, $userId, $simulationData = null)
    {
        try {
            $query = "INSERT INTO simulations (user_id, file_name, simulation_data) 
                      VALUES (:user_id, :file_name, :simulation_data)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':file_name', $fileName);
            $stmt->bindParam(':simulation_data', $simulationData);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'enregistrement de la simulation : " . $e->getMessage());
        }
    }

    // Enregistrer une comparaison
    public function saveComparison($userId, $fileSimulated, $fileComparing, $comparisonData = null)
    {
        try {
            $query = "INSERT INTO comparisons (user_id, file_simulated, file_comparing, comparison_data) 
                      VALUES (:user_id, :file_simulated, :file_comparing, :comparison_data)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':file_simulated', $fileSimulated);
            $stmt->bindParam(':file_comparing', $fileComparing);
            $stmt->bindParam(':comparison_data', $comparisonData);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'enregistrement de la comparaison : " . $e->getMessage());
        }
    }

    // Récupérer les uploads d'un utilisateur (GeoJSON et GeoTIFF)
    public function getAllUploadsByUser($userId)
    {
        try {
            $uploadsGJ = $this->getUploadsGJByUser($userId);
            $uploadsGT = $this->getUploadsGTByUser($userId);
            return ['GeoJSON' => $uploadsGJ, 'GeoTIFF' => $uploadsGT];
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la récupération des uploads : " . $e->getMessage());
        }
    }

    // Vérifier si les shapefiles ont le même système de référence
    public function verifyShapefileReferenceSystems($filePaths)
    {
        // Cette fonction doit vérifier que tous les shapefiles ont le même SRID
        // Cela nécessite de lire les fichiers .prj ou d'utiliser une bibliothèque PHP appropriée

        // Pour l'exemple, nous allons supposer que tous les shapefiles ont le même SRID
        // Implémentez la logique réelle selon vos besoins
        return true;
    }

    // Vérifier si deux fichiers peuvent être comparés (même référence géologique)
    public function verifyGeologicalReferences($fileSimulated, $fileComparing)
    {
        // Cette fonction doit vérifier que les deux fichiers ont la même référence géologique
        // Implémentez la logique réelle selon vos besoins

        // Pour l'exemple, nous allons supposer que les références géologiques sont les mêmes
        // Implémentez la logique réelle selon vos besoins
        return true;
    }
}
?>
