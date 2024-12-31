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
    public function saveUploadGJ($fileName, $fileContent, $userId,$dossierParent)
    {
        try {
            $query = "INSERT INTO uploadGJ (file_name, file_data, user,dossier) 
                      VALUES (:file_name, :file_data, :user, :dossier)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':file_name', $fileName);
            $stmt->bindParam(':file_data', $fileContent, PDO::PARAM_STR);
            $stmt->bindParam(':user', $userId);
            $stmt->bindParam(':dossier', $dossierParent);

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

    public function deleteFileGJ($fileName,$userId) {

        $query = "DELETE FROM uploadGJ WHERE file_name = :file_name and user = :user";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':file_name', $fileName);
        $stmt->bindParam(':user', $userId);
        return $stmt->execute();
    }


    public function verifyFolder($userId, $parentFolder, $folderName): bool {
        $query = "
        SELECT COUNT(*) 
        FROM organisation 
        WHERE id_dossier = :folderName 
        AND dossierParent = :parentFolder 
        AND id_utilisateur = :userId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':folderName', $folderName);
        $stmt->bindParam(':parentFolder', $parentFolder);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    public function createFolder($userId, $parentFolder, $folderName): void {
        try {
            // Vérifier si le dossier existe déjà
            if ($this->verifyFolder($userId, $parentFolder, $folderName)) {
                throw new \Exception("Ce répertoire existe déjà.");
            }

            // Insérer le dossier dans 'organisation'
            $insertFolder = "
            INSERT INTO organisation (id_dossier, dossierParent, id_utilisateur) 
            VALUES (:folderName, :parentFolder, :userId)";
            $stmtFolder = $this->db->prepare($insertFolder);
            $stmtFolder->bindParam(':folderName', $folderName);
            $stmtFolder->bindParam(':parentFolder', $parentFolder);
            $stmtFolder->bindParam(':userId', $userId);
            $stmtFolder->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }



//    public function createFolder($userId, $parentFolder, $folderName): void {
//        try {
//            // Vérifier si cette combinaison existe déjà
//            $query = "
//            SELECT id_dossier
//            FROM organisation
//            WHERE id_dossier = :folderName
//            AND dossierParent = :parentFolder
//            AND id_utilisateur = :userId";
//            $stmt = $this->db->prepare($query);
//            $stmt->bindParam(':folderName', $folderName);
//            $stmt->bindParam(':parentFolder', $parentFolder);
//            $stmt->bindParam(':userId', $userId);
//            $stmt->execute();
//
//            $folderExists = $stmt->fetchColumn();
//
//            if ($folderExists) {
//                throw new \Exception("Ce répertoire existe déjà.");
//            }
//
//            // Insérer le dossier dans 'organisation'
//            $insertFolder = "
//            INSERT INTO organisation (id_dossier, dossierParent, id_utilisateur)
//            VALUES (:folderName, :parentFolder, :userId)";
//            $stmtFolder = $this->db->prepare($insertFolder);
//            $stmtFolder->bindParam(':folderName', $folderName);
//            $stmtFolder->bindParam(':parentFolder', $parentFolder);
//            $stmtFolder->bindParam(':userId', $userId);
//            $stmtFolder->execute();
//        } catch (\Exception $e) {
//            throw new \Exception($e->getMessage());
//        }
//    }
//

    public function getUserFilesWithFolders($userId) {
        // Récupérer les dossiers
        $queryFolders = "
        SELECT id_dossier AS dossier_id, id_dossier AS folder_name, dossierParent 
        FROM organisation 
        WHERE id_utilisateur = :userId
        ORDER BY dossierParent, id_dossier";
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
            if ($dossierId && isset($folderIndex[$dossierId]) && $dossierId !== 'root') {
                $folderIndex[$dossierId]['files'][] = $file['file_name'];
            } else {
                // Ajouter les fichiers sans dossier directement dans l'arborescence
                $folderTree[] = [
                    'name' => $file['file_name'],
                    'type' => 'file'
                ];
            }
        }



        foreach ($folderIndex as $folderId => &$f){
            if(isset($f['parent_id']) and !($f['parent_id'] === 'root')){
                $folderIndex[$f['parent_id']]['children'][] = &$f;
            }
        }

        // Construire l'arborescence hiérarchique
        foreach ($folderIndex as $folderId => &$folder) {
            if (empty($folder['parent_id']) || $folder['parent_id'] === 'root') {
                // Ajouter à la racine
                $folderTree[] = &$folder;
            } else {
                error_log("Parent ID introuvable pour : " . $folder['name']);
            }
        }
        return $folderTree; // Retourne l'arborescence
    }

    public function getSubFolder($currentUserId, $folderName) {
        $queryFolders = "
        SELECT id_dossier AS folder_id, id_dossier AS folder_name 
        FROM organisation 
        WHERE id_utilisateur = :userId 
        AND dossierParent = :folderName
        ORDER BY id_dossier";
        $stmtFolders = $this->db->prepare($queryFolders);
        $stmtFolders->bindParam(':userId', $currentUserId);
        $stmtFolders->bindParam(':folderName', $folderName);
        $stmtFolders->execute();
        return $stmtFolders->fetchAll(PDO::FETCH_ASSOC);
    }



    public function deleteFolderByName($folderName, $userId) {
        try {
            // Étape 1 : Supprimer les fichiers du dossier
            $deleteFilesQuery = "DELETE FROM uploadGJ WHERE dossier = :folderName AND user = :userId";
            $stmtFiles = $this->db->prepare($deleteFilesQuery);
            $stmtFiles->bindParam(':folderName', $folderName);
            $stmtFiles->bindParam(':userId', $userId);
            $stmtFiles->execute();
            error_log("Fichiers supprimés pour le dossier : " . $folderName);

            // Étape 2 : Récupérer les sous-dossiers
            $subFoldersQuery = "SELECT id_dossier FROM organisation WHERE dossierParent = :folderName AND id_utilisateur = :userId";
            $stmtSubFolders = $this->db->prepare($subFoldersQuery);
            $stmtSubFolders->bindParam(':folderName', $folderName);
            $stmtSubFolders->bindParam(':userId', $userId);
            $stmtSubFolders->execute();
            $subFolders = $stmtSubFolders->fetchAll(PDO::FETCH_ASSOC);

            foreach ($subFolders as $subFolder) {
                error_log("Suppression récursive pour le sous-dossier : " . $subFolder['id_dossier']);
                $this->deleteFolderByName($subFolder['id_dossier'], $userId);
            }

            // Étape 3 : Supprimer le dossier lui-même
            $deleteFolderQuery = "DELETE FROM organisation WHERE id_dossier = :folderName AND id_utilisateur = :userId";
            $stmtFolder = $this->db->prepare($deleteFolderQuery);
            $stmtFolder->bindParam(':folderName', $folderName);
            $stmtFolder->bindParam(':userId', $userId);
            $stmtFolder->execute();

            error_log("Dossier supprimé : " . $folderName);
            return $stmtFolder->rowCount() > 0; // Confirme si le dossier a été supprimé
        } catch (Exception $e) {
            error_log("Erreur dans deleteFolderByName : " . $e->getMessage());
            throw $e;
        }
    }


    public function deleteFolderT($folderName, $userId)
    {
        $this->db->beginTransaction();
        try {
            // Suppression des fichiers, sous-dossiers et du dossier
            $this->deleteFolderByName($folderName, $userId);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

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
