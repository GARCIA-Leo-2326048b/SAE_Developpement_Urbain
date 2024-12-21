<?php
namespace blog\models;

use PDO;

class UploadModel {
    private $db;
    private $errorMessage="";

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
    public function saveSimulation($fileName, $userId, $simulationData )
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

    public function file_existGJ($customName)
    {
        $query = "SELECT COUNT(*) FROM uploadGJ WHERE file_name = :file_name";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':file_name', $customName);
        $stmt->execute();

        if( $stmt->fetchColumn() > 0){
            return true;

        }else{
            return false;
        }
    }

    public function deleteFileGJ($fileName) {
        $query = "DELETE FROM uploadGJ WHERE file_name = :file_name";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':file_name', $fileName);
        return $stmt->execute();
    }


    public function createFolder($userId, $parentFolder, $folderName): void {
        try {


            // Vérifier si cette combinaison existe déjà chez l'utilisateur
            $query2 = "SELECT d.nom
                   FROM organisation o
                   INNER JOIN dossier d ON d.nom = o.id_dossier
                   WHERE d.nom = :folderName
                   AND d.dossierParent = :parentFolder
                   AND o.id_utilisateur = :userId";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->bindParam(':folderName', $folderName);
            $stmt2->bindParam(':parentFolder', $parentFolder);
            $stmt2->bindParam(':userId', $userId);
            $stmt2->execute();

            $folderId = $stmt2->fetchColumn();


            if ($folderId) {
                throw new \Exception("Ce répertoire existe déjà.");
            }

            // Vérifier si la combinaison de dossier existe déjà
            $query = "SELECT nom FROM dossier WHERE nom = :folderName AND dossierParent = :parentFolder";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':folderName', $folderName);
            $stmt->bindParam(':parentFolder', $parentFolder);
            $stmt->execute();

            $folderId = $stmt->fetchColumn();

            if (!$folderId) {
                // Insérer le dossier dans la table 'dossier'
                $insertFolder = "INSERT INTO dossier (nom, dossierParent) VALUES (:folderName, :parentFolder)";
                $stmtFolder = $this->db->prepare($insertFolder);
                $stmtFolder->bindParam(':folderName', $folderName);
                $stmtFolder->bindParam(':parentFolder', $parentFolder);
                $stmtFolder->execute();

                // Récupérer l'ID du dossier nouvellement créé
                $folderId = $this->db->lastInsertId();

            }


            // Insérer l'association du dossier avec l'utilisateur dans 'organisation'
            $insertOrg = "INSERT INTO organisation (id_dossier, id_utilisateur) VALUES (:folderName, :userId)";
            $stmtOrg = $this->db->prepare($insertOrg);
            $stmtOrg->bindParam(':folderName', $folderName, PDO::PARAM_INT);
            $stmtOrg->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmtOrg->execute();

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getUserFilesWithFolders($userId) {


        // Récupérer les dossiers
        $queryFolders = "
    SELECT d.nom AS folder_name, d.nom AS dossier_id, d.dossierParent
    FROM dossier d
    INNER JOIN organisation o ON d.nom = o.id_dossier
    WHERE o.id_utilisateur = :userId
    ORDER BY d.dossierParent, d.nom";
        $stmtFolders = $this->db->prepare($queryFolders);
        $stmtFolders->bindParam(':userId', $userId);
        $stmtFolders->execute();
        $folders = $stmtFolders->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les fichiers
        $queryFiles = "
    SELECT f.file_name, f.dossier as dossier_id
    FROM uploadGJ f
    WHERE user = :userId";
        $stmtFiles = $this->db->prepare($queryFiles);
        $stmtFiles->bindParam(':userId', $userId);
        $stmtFiles->execute();
        $files = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);

        // Construire la hiérarchie
        $folderTree = [];
        $folderIndex = [];

        // Ajouter les dossiers au tableau d'index
        foreach ($folders as $folder) {
            $folderIndex[$folder['dossier_id']] = [
                'name' => $folder['folder_name'],
                'parent_id' => $folder['dossierParent'],
                'children' => [],
                'files' => []
            ];
        }

        // Ajouter les fichiers dans les dossiers correspondants
        foreach ($files as $file) {
            $dossierId = $file['dossier_id'] ?? null;
            if ($dossierId && isset($folderIndex[$dossierId])) {
                $folderIndex[$dossierId]['files'][] = $file['file_name'];
            } else {
                // Ajouter les fichiers sans dossier directement dans l'arborescence
                $folderTree[] = [
                    'name' => $file['file_name'],
                    'type' => 'file'
                ];
            }
        }

        // Construire l'arborescence hiérarchique
        foreach ($folderIndex as $folderId => &$folder) {
            if (empty($folder['parent_id']) || $folder['parent_id'] === 'root') {
                // Ajouter à la racine
                $folderTree[] = $folder;
            } elseif (isset($folderIndex[$folder['parent_id']])) {
                // Ajouter comme enfant du parent
                $folderIndex[$folder['parent_id']]['children'][] = $folder;


            } else {
                error_log("Parent ID introuvable pour : " . $folder['name']);
            }
        }

        return $folderTree; // Retourne l'arborescence
    }




    public function file_existGT($customName)
    {
        $query = "SELECT COUNT(*) FROM uploadGT WHERE file_name = :file_name";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':file_name', $customName);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage(){
        return $this->errorMessage;
    }

}
?>
