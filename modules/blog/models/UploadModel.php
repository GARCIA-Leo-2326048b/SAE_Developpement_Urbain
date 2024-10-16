<?php
namespace blog\models;

use PDO;

class UploadModel {
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    // Enregistrer un upload
    public function saveUploadToGJ($id, $fileName, $fileContent, $metadata = null)
    {
        $query = "INSERT INTO uploadGJ (id, file_name, file_data, metadata) 
                  VALUES (:id, :file_name, :fileContent, :metadata)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':file_name', $fileName);
        $stmt->bindParam(':file_text', $fileContent, PDO::PARAM_STR);
        $stmt->bindParam(':metadata', $metadata);

        return $stmt->execute();
    }

    public function saveUploadToGT($id, $fileName, $fileContent, $metadata = null)
    {
        $query = "INSERT INTO uploadGT (id, file_name, file_data, metadata) 
                  VALUES (:id, :file_name, :fileContent, :metadata)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':file_name', $fileName);
        $stmt->bindParam(':file_data', $fileContent, PDO::PARAM_LOB);
        $stmt->bindParam(':metadata', $metadata);

        return $stmt->execute();
    }

    // Enregistrer une action dans l'historique
    public function saveHistory($id, $id_user, $file_type, $uploadgj, $uploadgt)
    {
        $query = "INSERT INTO history (id, id_user, file_type, uploadgj, uploadgt) 
                  VALUES (:id, :upload_id, :file_type, :uploadgj, :uploadgt)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':upload_id', $id_user);
        $stmt->bindParam(':file_type', $file_type);
        if ($file_type === 'geotiff') {
            $stmt->bindParam(':uploadgt', $uploadgt, PDO::PARAM_LOB);
            $stmt->bindValue(':uploadgj', null, PDO::PARAM_NULL);
        } elseif ($file_type === 'geojson') {
            $stmt->bindValue(':uploadgt', null, PDO::PARAM_NULL);
            $stmt->bindParam(':uploadgj', $uploadgj, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

    // Récupérer les uploads d'un utilisateur
    public function getUploadsByUser($userId)
    {
        $query = "SELECT * FROM uploadGJ WHERE user_id = :user_id ORDER BY uploaded_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un upload par ID
    public function getUploadById($uploadId)
    {
        $query = "SELECT * FROM uploads WHERE id = :upload_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':upload_id', $uploadId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
