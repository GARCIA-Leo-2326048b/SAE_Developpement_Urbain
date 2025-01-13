<?php

namespace blog\controllers;

use blog\models\SingletonModel;
use CURLFile;
use ZipArchive;
use _assets\config\Database;
use blog\models\UploadModel;

class Upload
{

    private $db;
    private $uploadModel;
    private $currentUserId; // ID de l'utilisateur connecté
    private $header = 'Content-Type: application/json';
    private $pref = '/[^a-zA-Z0-9_-]/';


    public function __construct()
    {
        // Initialiser la connexion à la base de données

        // Utiliser SingletonModel pour obtenir la connexion à la base de données
        $this->db = SingletonModel::getInstance()->getConnection();
        $this->uploadModel = new UploadModel($this->db);


        // Vérifier l'authentification
        if (isset($_SESSION['user_id'])) {
            $this->currentUserId = $_SESSION['user_id'];
        } else {
            // Rediriger vers la page de connexion
           header("Location: index.php?action=authentification");
          exit();
        }
    }

    public function createProject()
    {
        header($this->header); // Réponse au format JSON
        try {

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Pas la bonne methode HTTP.']);
            }

            // Récupérer les données envoyées par AJAX en GET
            if (empty($_POST['new_project_name'])) {
                echo json_encode(['success' => false, 'message' => 'Le nom du projet est requis.']);
            }

            $project = trim($_POST['new_project_name']);
            $project = preg_replace($this->pref, '', $project); // Nettoyer le nom du dossier


            if ($this->uploadModel->projetExiste($project,$this->currentUserId)) {
                echo json_encode(['success' => false, 'message' => 'Ce Projet existe déjà']);
            }

            // Création du projet
            $this->uploadModel->createProjectM($project,$this->currentUserId);

            // Récupérer l'ID du projet récemment ajouté
            $newProjectId = $this->db->lastInsertId();

            // Ajouter ce projet à la session
            $_SESSION['projects'][] = [
                'id' => $newProjectId,
                'name' => $project
            ];

            // Mettre à jour le projet actif dans la session
            $_SESSION['current_project_id'] = $newProjectId;
            $_SESSION['current_project_name'] = $project;

            // Réponse JSON pour succès
            echo json_encode(['success' => true, 'message' => 'Projet créé avec succès.']);
        } catch (\Exception $e) {
            // Réponse JSON pour erreur
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function setProject()
    {
        if (!empty($_POST['project_id'])) {
            $_SESSION['current_project_id'] = $_POST['project_id'];
            $_SESSION['current_project_name'] =  $_POST['project_id'];
            header("Location: index.php?action=home");
        } else {
           echo json_encode(['success' => false, 'message' => 'Aucun projet sélectionné.']);
        }
        exit();
    }


    public function getProjects()
    {
        header($this->header);
        $files = $this->uploadModel->getUserProjects($this->currentUserId);
        $folderHistory = new \blog\views\HistoriqueView($files);
        return $folderHistory->generateProjects($files);
    }

    public function telechargement()
    {
        try {
            // Gestion des fichiers Shapefile (Vecteur)
            if (isset($_FILES['geojson'])) {
                $this->uploadfile();
            }
            // Gestion des fichiers Raster
            elseif (isset($_FILES['rasterfile'])) {
                $this->handleRasterUpload();
            } else {
                echo "Aucun fichier n'a été téléchargé.";
            }
        } catch (\Exception $e) {
            echo "Erreur: " . htmlspecialchars($e->getMessage());
        }
    }

    public function deleteFile() {
        $fileName = htmlspecialchars(filter_input(INPUT_GET, 'fileName', FILTER_SANITIZE_SPECIAL_CHARS));

        if ($this->uploadModel->deleteFileGJ($fileName, $this->currentUserId,$_SESSION['current_project_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function uploadfile()
    {
        header($this->header); // Réponse au format JSON
        try {

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Pas la bonne méthode HTTP.']);
            }

            // Récupérer les données envoyées par AJAX en GET
            if (empty($_POST['shapefile_name'])) {
                echo json_encode(['success' => false, 'message' => 'Le nom du fichier est requis.']);
            }

            $fileName = trim($_POST['shapefile_name']);
            $fileName = preg_replace($this->pref, '', $fileName); // Nettoyer le nom du dossier

            if (empty($fileName)) {
                echo json_encode(['success' => false, 'message' => 'Nom invalide.']);
            }

            $dossierParent = $_POST['dossier_parent'] ?? null;
            // Vérifier si le fichier existe déjà pour éviter les conflits
            $nom = $fileName . '.geojson';
            if ($this->uploadModel->fileExistGJ($nom,$this->currentUserId,$_SESSION['current_project_id'])) {
                echo json_encode(['success' => false, 'message' => 'Ce fichier existe déjà.']);
            }
            // Récupérer le contenu du fichier
            if (!isset($_FILES['geojson']) || $_FILES['geojson']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement du fichier.']);
            }

            $fileTmpPath = $_FILES['geojson']['tmp_name'];
            $fileContent = file_get_contents($fileTmpPath);

            if ($fileContent === false) {
                echo json_encode(['success' => false, 'message' => 'Impossible de lire le fichier GeoJSON.']);
            }

            // Création du dossier
            $this->uploadModel->saveUploadGJ($nom, $fileContent,$this->currentUserId, $dossierParent,$_SESSION['current_project_id']);

            // Réponse JSON pour succès
            echo json_encode(['success' => true, 'message' => 'Fichier téléchargé avec succès.']);
        } catch (\Exception $e) {
            // Réponse JSON pour erreur
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function deleteFolder()
    {
        header($this->header); // Indique que la réponse est au format JSON

        try {
            $folderName = htmlspecialchars(filter_input(INPUT_GET, 'folderName', FILTER_SANITIZE_SPECIAL_CHARS));

            if (!$folderName) {
                echo json_encode(['success' => false, 'message' => 'Nom du dossier manquant']);
                return;
            }


            $result = $this->uploadModel->deleteFolderT($folderName, $this->currentUserId,$_SESSION['current_project_id']);

            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                $errorInfo = $this->db->errorInfo(); // Affiche les infos d'erreur PDO
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression du dossier',
                    'errorInfo' => $errorInfo
                ]);
            }

        } catch (Exception $e) {
            http_response_code(500); // Indique une erreur interne
            echo json_encode(['success' => false, 'message' => 'Une erreur est survenue: ' . $e->getMessage()]);
        }
    }


    public function getArbre() {
        $files = $this->uploadModel->getFolderHierarchy($_SESSION['current_project_id'],$this->currentUserId);
        $folderHistory = new \blog\views\HistoriqueView($files);
        $historyId = 'history-' . uniqid();
        return $folderHistory->render($historyId);
    }

    public function getArbreExp()
    {
        $files = $this->uploadModel->getExperimentation($this->currentUserId,$_SESSION['current_project_id']);
        $folderHistory = new \blog\views\HistoriqueView($files);
        $historyId = 'history-' . uniqid();
        return $folderHistory->render($historyId);
    }

    public function selectFolder()
    {
        header($this->header);
        $files = $this->uploadModel->getFolderHierarchy($_SESSION['current_project_id'],$this->currentUserId);
        $folderHistory = new \blog\views\HistoriqueView($files);
        return $folderHistory->generateFolderOptions($folderHistory->getFiles());
    }


    public function folder1() {
        header('Content-Type: application/json'); // Réponse au format JSON
        try {
            // Récupérer les données envoyées par AJAX en POST
            $inputData = json_decode(file_get_contents('php://input'), true);

            if (empty($inputData['dossier_name'])) {
                echo json_encode(['success' => false, 'message' => 'Le nom du dossier est requis.']);
            }

            $folderName = trim($inputData['dossier_name']);
            $folderName = preg_replace($this->pref, '', $folderName); // Nettoyer le nom du dossier

            if (empty($folderName)) {
                echo json_encode(['success' => false, 'message' => 'Nom de dossier invalide.']);
            }

            $dossierParent = $inputData['dossier_parent'] ?? null;

            // Vérification de l'existence du dossier
            if ($this->uploadModel->verifyFolder($this->currentUserId, $dossierParent, $folderName, $_SESSION['current_project_id'])) {
                echo json_encode(['success' => false, 'message' => 'Ce dossier existe déjà']);
            }

            // Création du dossier
            $this->uploadModel->createFolder($this->currentUserId, $dossierParent, $folderName, $_SESSION['current_project_id']);

            // Réponse JSON pour succès
            echo json_encode(['success' => true, 'message' => 'Dossier créé avec succès.']);
        } catch (\Exception $e) {
            // Réponse JSON pour erreur
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }



    public function getSubFolders()
    {
        $folderName = htmlspecialchars(filter_input(INPUT_GET, 'folderName', FILTER_SANITIZE_SPECIAL_CHARS));
        $subFolders = $this->uploadModel->getSubFolder($this->currentUserId, $folderName,$_SESSION['current_project_id']);
        header($this->header);
        echo json_encode($subFolders);
    }

    // Gérer l'upload des fichiers Raster
    public function handleRasterUpload()
    {
        $file = $_FILES['rasterfile'];
        $uploadDir = __DIR__ . '/../../../assets/shapefile/'; // Dossier de destination

        try {
            // Récupérer le nom de fichier personnalisé
            if (isset($_POST['rasterfile_name']) && !empty(trim($_POST['rasterfile_name']))) {
                $customName = trim($_POST['rasterfile_name']);
                // Sanitize the custom name to prevent security issues
                $customName = preg_replace($this->pref, '', $customName);
                if (empty($customName)) {
                    echo json_encode(['success' => false, 'message' => 'Nom de fichier invalide.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Veuillez spécifiez un nom de fichier.']);
            }

            // Vérifiez les erreurs
            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement du fichier .']);
            }

            // Vérifier si le dossier est accessible en écriture
            if (!is_writable($uploadDir)) {
                echo json_encode(['success' => false, 'message' => 'Le dossier de destination est impossible à atteindre .']);
            }

            // Définir un nom de fichier unique avec le nom personnalisé
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['tif', 'tiff', 'png', 'jpg', 'jpeg'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                echo json_encode(['success' => false, 'message' => 'Extension de fichier non autorisée. Extensions valides : .tif, .tiff, .png, .jpg, .jpeg.']);
            }

            $uploadFilePath = $uploadDir . $customName . '.' . $fileExtension;

            // Vérifier si le fichier existe déjà pour éviter les conflits
            if (file_exists($uploadFilePath)) {
                echo json_encode(['success' => false, 'message' => 'Ce fichier existe déjà.']);
            }

            // Déplacer le fichier téléchargé
            if (!move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement du fichier .']);
            }


            // Appeler l'API pour convertir le fichier Raster en GeoTIFF
            $geoTiffFilePath = $this->convertRasterToGeoTIFF($uploadFilePath, $customName);

            // Enregistrer le GeoTIFF dans la base de données
            if ($geoTiffFilePath) {
                $geoTiffFileName = basename($geoTiffFilePath);
                $geoTiffContent = file_get_contents($geoTiffFilePath);
                $this->uploadModel->saveUploadGT($geoTiffFileName, $geoTiffContent, $this->currentUserId);

                header("Location: index.php?action=new_simulation");
            }
        } catch (\Exception $e) {
            echo "Erreur: " . htmlspecialchars($e->getMessage());
        }
    }

    // Fonction pour convertir les fichiers Raster en GeoTIFF via l'API OGRE
    private function convertRasterToGeoTIFF($rasterFilePath, $customName)
    {
        // URL de l'API pour la conversion
        $apiUrl = "https://ogre.adc4gis.com/convert";

        // Chemin de sortie pour le fichier GeoTIFF
        $geoTiffFileName = $customName . '.tiff';
        $geoTiffFilePath = __DIR__ . '/../../../assets/shapefile/' . $geoTiffFileName;

        // Utiliser curl pour faire une requête POST vers l'API
        $ch = curl_init();

        // Paramètres de la requête POST avec le fichier raster
        $data = array(
            'upload[]' => new CURLFile($rasterFilePath, 'application/octet-stream', basename($rasterFilePath))
        );

        // Configuration de curl
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // Exécution de la requête
        $response = curl_exec($ch);

        // Gérer les erreurs de curl
        if (curl_errno($ch)) {
            echo json_encode(['success' => false, 'message' => 'Erreur Api.']);
        }

        // Fermer la session curl
        curl_close($ch);

        // Sauvegarder la réponse (GeoTIFF) dans un fichier
        file_put_contents($geoTiffFilePath, $response);

        // Vérifier si le fichier GeoTIFF a bien été créé
        if (file_exists($geoTiffFilePath)) {
            return $geoTiffFilePath;
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la conversion.']);
        }
    }


}
?>
