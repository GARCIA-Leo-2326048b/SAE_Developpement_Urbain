<?php
namespace blog\models;

use PDO;

class UploadModel {
    private $db;
    private $p = ':project';
    private $u = ':user';
    private $uId = ':userId';
    private $fname = ':folderName';
    private $file = ':file_name';

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function projetExiste($project,$userId)
    {
        $query = "SELECT COUNT(*) FROM projets WHERE nom = :projet and utilisateur = :utilisateur";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':projet', $project);
        $stmt->bindParam(':utilisateur', $userId);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function createProjectM($project,$userId)
    {
         try {
            $query = "INSERT INTO projets VALUES (:project, :user)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam($this->p, $project);
            $stmt->bindParam($this->u, $userId);


            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la création du projet : " . $e->getMessage());
        }
    }

    public function getUserProjects($userId)
    {
        $queryFolders = "SELECT nom AS projet FROM projets WHERE utilisateur = :userId ORDER BY nom";
        $stmtFolders = $this->db->prepare($queryFolders);
        $stmtFolders->bindParam($this->uId, $userId);
        $stmtFolders->execute();
        return $stmtFolders->fetchAll(PDO::FETCH_ASSOC);
    }


    // Enregistrer un upload GeoJSON
    public function saveUploadGJ($fileName, $fileContent, $userId,$dossierParent,$project)
    {
        try {
            $query = "INSERT INTO uploadGJ (file_name, file_data, user,dossier,projet) VALUES (:file_name, :file_data, :user, :dossier, :project)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam($this->file, $fileName);
            $stmt->bindParam(':file_data', $fileContent, PDO::PARAM_STR);
            $stmt->bindParam($this->u, $userId);
            $stmt->bindParam(':dossier', $dossierParent);
            $stmt->bindParam($this->p, $project);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'enregistrement du GeoJSON : " . $e->getMessage());
        }
    }

    // Enregistrer un upload GeoTIFF
    public function saveUploadGT($fileName, $fileContent, $userId)
    {
        try {
            $query = "INSERT INTO uploadGT (file_name, file_data, user) VALUES (:file_name, :file_data, :user)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam($this->file, $fileName);
            $stmt->bindParam(':file_data', $fileContent, PDO::PARAM_LOB);
            $stmt->bindParam($this->u, $userId);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'enregistrement du GeoTIFF : " . $e->getMessage());
        }
    }

    public function fileExistGJ($customName,$userID,$project)
    {
        $query = "SELECT COUNT(*) FROM uploadGJ WHERE file_name = :file_name and user = :user and projet = :project";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam($this->file, $customName);
        $stmt->bindParam($this->u, $userID);
        $stmt->bindParam($this->p, $project);
        $stmt->execute();

        return  $stmt->fetchColumn() > 0;
    }

    public function deleteFileGJ($fileName,$userId,$projet) {

        $query = "DELETE FROM uploadGJ WHERE file_name = :file_name and user = :user and projet = :projet";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam($this->file, $fileName);
        $stmt->bindParam($this->u, $userId);
        $stmt->bindParam(':projet', $projet);
        return $stmt->execute();
    }


    public function verifyFolder($userId, $parentFolder, $folderName,$project): bool {
        $query = "SELECT COUNT(*) FROM organisation WHERE id_dossier = :folderName AND dossierParent = :parentFolder AND id_utilisateur = :userId AND projet = :project";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam($this->fname, $folderName);
        $stmt->bindParam(':parentFolder', $parentFolder);
        $stmt->bindParam($this->uId, $userId);
        $stmt->bindParam($this->p, $project);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    public function createFolder($userId, $parentFolder, $folderName,$project): void {
        try {
            // Vérifier si le dossier existe déjà
            if ($this->verifyFolder($userId, $parentFolder, $folderName,$project)) {
                throw new \Exception("Ce répertoire existe déjà.");
            }

            // Insérer le dossier dans 'organisation'
            $insertFolder = "INSERT INTO organisation (id_dossier, dossierParent, id_utilisateur,projet) VALUES (:folderName, :parentFolder, :userId,:project)";
            $stmtFolder = $this->db->prepare($insertFolder);
            $stmtFolder->bindParam($this->fname, $folderName);
            $stmtFolder->bindParam(':parentFolder', $parentFolder);
            $stmtFolder->bindParam($this->uId, $userId);
            $stmtFolder->bindParam($this->p, $project);
            $stmtFolder->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getExperimentation($userId,$project) {
        // Récupérer les dossier
        $queryFolders = "SELECT id_dossier AS dossier_id, id_dossier AS folder_name, dossierParent FROM organisation WHERE id_utilisateur = :userId AND projet = :project ORDER BY dossierParent, id_dossier";
        $stmtFolders = $this->db->prepare($queryFolders);
        $stmtFolders->bindParam($this->uId, $userId);
        $stmtFolders->bindParam($this->p, $project);
        $stmtFolders->execute();
        $folders = $stmtFolders->fetchAll(PDO::FETCH_ASSOC);


        // Récupérer les fichiers
        $queryFiles = "SELECT e.id_xp,e.nom_xp, e.dossier as dossier_id FROM experimentation e WHERE id_utilisateur = :userId AND projet = :project";
        $stmtFiles = $this->db->prepare($queryFiles);
        $stmtFiles->bindParam($this->uId, $userId);
        $stmtFiles->bindParam($this->p, $project);
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
                // Ajouter le fichier dans le dossier avec la propriété "exp"
                $folderIndex[$dossierId]['files'][] = [
                    'id' => $file['id_xp'],
                    'name' => $file['nom_xp'],
                    'exp' => 'oui'
                ];
            } else {
                // Ajouter les fichiers sans dossier directement dans l'arborescence
                $folderTree[] = [
                    'id' => $file['id_xp'],
                    'name' => $file['nom_xp'],
                    'type' => 'file',
                    'exp' => 'oui'
                ];
            }
        }



        foreach ($folderIndex as  &$f){
            if(isset($f['parent_id']) && $f['parent_id'] !== 'root'){
                $folderIndex[$f['parent_id']]['children'][] = &$f;
            }
        }

        // Construire l'arborescence hiérarchique
        foreach ($folderIndex as  &$folder) {
            if (empty($folder['parent_id']) || $folder['parent_id'] === 'root') {
                // Ajouter à la racine
                $folderTree[] = &$folder;
            } else {
                error_log("Parent ID introuvable pour : " . $folder['name']);
            }
        }

        return $folderTree; // Retourne l'arborescence
    }

    public function getFolderHierarchy($project, $userId) {
        // Récupérer les dossiers
        $queryFolders = "SELECT id_dossier AS dossier_id, id_dossier AS folder_name, dossierParent FROM organisation WHERE id_utilisateur = :userId AND projet = :project ORDER BY dossierParent, id_dossier";
        $stmtFolders = $this->db->prepare($queryFolders);
        $stmtFolders->bindParam($this->uId, $userId);
        $stmtFolders->bindParam($this->p, $project);
        $stmtFolders->execute();
        $folders = $stmtFolders->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les fichiers
        $queryFiles = "SELECT f.file_name, f.dossier as dossier_id FROM uploadGJ f WHERE user = :userId AND projet = :project";
        $stmtFiles = $this->db->prepare($queryFiles);
        $stmtFiles->bindParam($this->uId, $userId);
        $stmtFiles->bindParam($this->p, $project);
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
                // Ajouter le fichier dans le dossier avec sa propriété "exp"
                $folderIndex[$dossierId]['files'][] = [
                    'name' => $file['file_name'],
                    'exp' =>  'non'
                ];
            } else {
                // Ajouter les fichiers sans dossier directement dans l'arborescence
                $folderTree[] = [
                    'name' => $file['file_name'],
                    'type' => 'file',
                    'exp' =>  'non'
                ];
            }
        }

        // Ajouter les dossiers enfants dans leurs dossiers parents
        foreach ($folderIndex as  &$folder) {
            if (isset($folder['parent_id']) && $folder['parent_id'] !== 'root') {
                $folderIndex[$folder['parent_id']]['children'][] = &$folder;
            }
        }
        unset($folder);

        // Ajouter les dossiers racines à l'arborescence principale
        foreach ($folderIndex as  &$folder) {
            if (empty($folder['parent_id']) || $folder['parent_id'] === 'root') {
                $folderTree[] = &$folder;
            } else {
                error_log("Parent ID introuvable pour : " . $folder['name']);
            }
        }

        return $folderTree; // Retourne l'arborescence
    }


    public function getSubFolder($currentUserId, $folderName,$project) {
        $queryFolders = "SELECT id_dossier AS folder_id, id_dossier AS folder_name FROM organisation WHERE id_utilisateur = :userId AND dossierParent = :folderName AND projet = :project ORDER BY id_dossier";
        $stmtFolders = $this->db->prepare($queryFolders);
        $stmtFolders->bindParam($this->uId, $currentUserId);
        $stmtFolders->bindParam($this->fname, $folderName);
        $stmtFolders->bindParam($this->p, $project);
        $stmtFolders->execute();
        return $stmtFolders->fetchAll(PDO::FETCH_ASSOC);
    }



    public function deleteFolderByName($folderName, $userId,$project) {
        try {
            // Étape 1 : Supprimer les fichiers du dossier
            $deleteFilesQuery = "DELETE FROM uploadGJ WHERE dossier = :folderName AND user = :userId AND projet = :project";
            $stmtFiles = $this->db->prepare($deleteFilesQuery);
            $stmtFiles->bindParam($this->fname, $folderName);
            $stmtFiles->bindParam($this->uId, $userId);
            $stmtFiles->bindParam($this->p, $project);
            $stmtFiles->execute();
            error_log("Fichiers supprimés pour le dossier : " . $folderName);

            // Étape 2 : Récupérer les sous-dossiers
            $subFoldersQuery = "SELECT id_dossier FROM organisation WHERE dossierParent = :folderName AND id_utilisateur = :userId AND projet = :project";
            $stmtSubFolders = $this->db->prepare($subFoldersQuery);
            $stmtSubFolders->bindParam($this->fname, $folderName);
            $stmtSubFolders->bindParam($this->uId, $userId);
            $stmtSubFolders->bindParam($this->p, $project);
            $stmtSubFolders->execute();
            $subFolders = $stmtSubFolders->fetchAll(PDO::FETCH_ASSOC);

            foreach ($subFolders as $subFolder) {
                error_log("Suppression récursive pour le sous-dossier : " . $subFolder['id_dossier']);
                $this->deleteFolderByName($subFolder['id_dossier'], $userId, $project);
            }

            // Étape 3 : Supprimer le dossier lui-même
            $deleteFolderQuery = "DELETE FROM organisation WHERE id_dossier = :folderName AND id_utilisateur = :userId AND projet = :project";
            $stmtFolder = $this->db->prepare($deleteFolderQuery);
            $stmtFolder->bindParam($this->fname, $folderName);
            $stmtFolder->bindParam($this->uId, $userId);
            $stmtFolder->bindParam($this->p, $project);
            $stmtFolder->execute();

            error_log("Dossier supprimé : " . $folderName);
            return $stmtFolder->rowCount() > 0; // Confirme si le dossier a été supprimé
        } catch (Exception $e) {
            error_log("Erreur dans deleteFolderByName : " . $e->getMessage());
            throw $e;
        }
    }


    public function deleteFolderT($folderName, $userId,$project)
    {
        $this->db->beginTransaction();
        try {
            // Suppression des fichiers, sous-dossiers et du dossier
            $this->deleteFolderByName($folderName, $userId,$project);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

    }




}
?>
