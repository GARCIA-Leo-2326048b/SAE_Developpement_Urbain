<?php

namespace blog\controllers;

use CURLFile;
use ZipArchive;
use _assets\config\Database;
use blog\models\UploadModel;

class Upload
{
    private $db;
    private $uploadModel;
    private $currentUserId; // ID de l'utilisateur connecté
    private $errorMessage="";

    public function __construct()
    {
        // Initialiser la connexion à la base de données
        $database = new Database();
        $this->db = $database->getConnection();
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

    public function telechargement()
    {
        try {
            // Gestion des fichiers Shapefile (Vecteur)
            if (isset($_FILES['shapefile'])) {
                $this->handleShapefileUpload();
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

        if ($this->uploadModel->deleteFileGJ($fileName, $this->currentUserId)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function deleteFolder()
    {
        header('Content-Type: application/json'); // Indique que la réponse est au format JSON

        try {
            $folderName = htmlspecialchars(filter_input(INPUT_GET, 'folderName', FILTER_SANITIZE_SPECIAL_CHARS));

            if (!$folderName) {
                echo json_encode(['success' => false, 'message' => 'Nom du dossier manquant']);
                return;
            }


            $result = $this->uploadModel->deleteFolderT($folderName, $this->currentUserId);

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
        $files = $this->uploadModel->getUserFilesWithFolders($this->currentUserId);
        return $this->displayFolderTree($files);
    }

    private function displayFolderTree($folders, $parentId = '') {
        echo '<ul>';
        foreach ($folders as $folder) {
            echo '<li>';

            if (isset($folder['type']) && $folder['type'] === 'file') {
                // Affichage des fichiers
                echo "<button class='history-file' onclick=\"showPopup('" . htmlspecialchars($folder['name']) . "')\">"
                    . htmlspecialchars($folder['name']) . "</button>";
            } else {
                // Affichage des dossiers
                echo "<button class='folder-toggle' data-folder-id='" . htmlspecialchars($folder['name']) . "' onclick='toggleFolder(\"" . htmlspecialchars($folder['name']) . "\")'>";
                echo "<i class='icon-folder'>📁</i> " . htmlspecialchars($folder['name']) . "</button>";

                // Vérifie si le dossier a des fichiers
                if (!empty($folder['files'])) {
                    echo "<ul id='" . htmlspecialchars($folder['name']) . "-files' style='display: none;'>";
                    foreach ($folder['files'] as $file) {
                        echo "<li><button class='history-file' onclick=\"showPopup('" . htmlspecialchars($file) . "')\">"
                            . htmlspecialchars($file) . "</button></li>";
                    }
                    echo '</ul>';
                }

                // Vérifie si le dossier a des sous-dossiers
                if (!empty($folder['children'])) {
                    echo "<ul id='" . htmlspecialchars($folder['name']) . "-children' style='display: none;'>";
                    $this->displayFolderTree($folder['children'], $folder['name']);
                    echo '</ul>';
                }
            }

            echo '</li>';
        }
        echo '</ul>';
    }
    public function folder1() {
        header('Content-Type: application/json'); // Réponse au format JSON
        try {
            // Récupérer les données envoyées par AJAX en GET
            if (empty($_GET['dossier_name'])) {
                throw new \Exception("Le nom du dossier est requis.");
            }

            $folderName = trim($_GET['dossier_name']);
            $folderName = preg_replace('/[^a-zA-Z0-9_-]/', '', $folderName); // Nettoyer le nom du dossier

            if (empty($folderName)) {
                throw new \Exception("Nom de dossier invalide.");
            }

            $dossierParent = $_GET['dossier_parent'] ?? null;

            // Vérification de l'existence du dossier
            if ($this->uploadModel->verifyFolder($this->currentUserId, $dossierParent, $folderName)) {
                throw new \Exception("Ce répertoire existe déjà.");
            }

            // Création du dossier
            $this->uploadModel->createFolder($this->currentUserId, $dossierParent, $folderName);

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
        $subFolders = $this->uploadModel->getSubFolder($this->currentUserId, $folderName);
        header('Content-Type: application/json');
        echo json_encode($subFolders);
    }

    public function folder() {
        try {
            // Vérifier que les données nécessaires sont présentes
            if (empty($_POST['dossier_name'])) {
                throw new \Exception("Le nom du dossier est requis.");
            }

            $folderName = trim($_POST['dossier_name']);
            $folderName = preg_replace('/[^a-zA-Z0-9_-]/', '', $folderName); // Nettoyer le nom du dossier
            if (empty($folderName)) {
                throw new \Exception("Nom de dossier invalide.");
            }

            if (isset($_POST['dossier_parent'])){
                $dossierParent = $_POST['dossier_parent'];
            } else {
                $dossierParent = null;
            }

            var_dump(
                $dossierParent,$folderName
            );
            // Appeler la méthode pour créer le dossier
            $this->uploadModel->createFolder($this->currentUserId, $dossierParent, $folderName);

            // Rediriger vers une page de succès ou afficher un message de succès
            header("Location: index.php?action=new_simulation");
            exit();
        } catch (\Exception $e) {
            // Rediriger vers une page d'erreur ou afficher un message d'erreur
            header("Location: index.php?action=new_simulation&error=" . urlencode($e->getMessage()));
            exit();
        }
    }


    // Gérer l'upload des Shapefiles
    public function handleShapefileUpload()
    {
        $files = $_FILES['shapefile'];
        $requiredExtensions = ['shp', 'shx', 'dbf']; // Extensions requises
        $uploadedFiles = [];
        $uploadDir = __DIR__ . '/../../../assets/shapefile/'; // Dossier de destination

        if (isset($_POST['dossier_parent'])){
            $dossierParent = $_POST['dossier_parent'];
        } else {
            $dossierParent = null;
        }

        // Récupérer le nom de fichier personnalisé
        if (isset($_POST['shapefile_name']) && !empty(trim($_POST['shapefile_name']))) {
            $customName = trim($_POST['shapefile_name']);
            // Sanitize the custom name to prevent security issues
            $customName = preg_replace('/[^a-zA-Z0-9_-]/', '', $customName);
            if (empty($customName)) {
                throw new \Exception("Nom de fichier invalide.");
            }
        } else {
            throw new \Exception("Veuillez spécifier un nom de fichier.");
        }
        // Vérifier si le fichier existe déjà pour éviter les conflits
        $nom = $customName . '.geojson';
        if ($this->uploadModel->file_existGJ($nom)) {
            $this->errorMessage = "Le fichier " . htmlspecialchars($customName . '.geojson') . " existe déjà.";
            return $this->errorMessage;
        }
        // Vérifier si le dossier est accessible en écriture
        if (!is_writable($uploadDir)) {
            throw new \Exception("Le dossier de destination n'est pas accessible en écriture.");
        }

        // Vérifier le nombre de fichiers uploadés
        if (count($files['name']) > count($requiredExtensions)) {
            throw new \Exception("Vous ne pouvez pas télécharger plus de " . count($requiredExtensions) . " fichiers shapefile à la fois.");
        }

        // Parcourir tous les fichiers téléchargés
        foreach ($files['name'] as $key => $name) {
            $fileTmpPath = $files['tmp_name'][$key];
            $fileExtension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            // Vérifiez si l'extension est dans la liste des fichiers requis
            if (in_array($fileExtension, $requiredExtensions)) {
                $uploadFilePath = $uploadDir . $customName . '.' . $fileExtension;


                // Déplacer chaque fichier dans le répertoire de destination
                if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                    $uploadedFiles[$fileExtension] = $uploadFilePath;
                } else {
                    throw new \Exception("Erreur lors du téléchargement de " . htmlspecialchars($customName . '.' . $fileExtension) . ".");
                }
            } else {
                throw new \Exception("Fichier " . htmlspecialchars($name) . " non valide. Extensions valides : .shp, .shx, .dbf");
            }
        }

        // Vérifier que tous les fichiers requis (.shp, .shx, .dbf) sont présents
        foreach ($requiredExtensions as $ext) {
            if (!isset($uploadedFiles[$ext])) {
                throw new \Exception("Le fichier ." . $ext . " est manquant.");
            }
        }

        // Vérifier que les shapefiles ont le même système de référence
        if (!$this->uploadModel->verifyShapefileReferenceSystems($uploadedFiles)) {
            throw new \Exception("Les shapefiles ont des systèmes de référence différents.");
        }

        // Créer un fichier ZIP avec les fichiers téléchargés
        $zipFilePath = $this->createZipFile($uploadedFiles, $customName);
        if ($zipFilePath) {
            // Envoyer le fichier ZIP à l'API OGRE
            $geojsonFilePath = $this->convertShapefileToGeoJSON($zipFilePath, $customName);

            // Enregistrer le GeoJSON dans la base de données
            if ($geojsonFilePath) {
                $geojsonFileName = basename($geojsonFilePath);
                $geojsonContent = file_get_contents($geojsonFilePath);
                $this->uploadModel->saveUploadGJ($geojsonFileName, $geojsonContent, $this->currentUserId,$dossierParent);
                header("Location: index.php?action=new_simulation");

            }
        } else {
            throw new \Exception("Erreur lors de la création du fichier ZIP.");
        }
    }

    // Fonction pour compresser les shapefiles dans un fichier ZIP
    private function createZipFile($files, $customName)
    {
        $zip = new ZipArchive();
        $zipFileName = $customName . '_shapefile_' . time() . '.zip';
        $zipFilePath = __DIR__ . '/../../../assets/shapefile/' . $zipFileName;

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
            return $zipFilePath;
        } else {
            return false;
        }
    }

    // Fonction pour convertir les shapefiles en GeoJSON via l'API OGRE
    private function convertShapefileToGeoJSON($zipFilePath, $customName)
    {
        // URL de l'API OGRE pour la conversion
        $apiUrl = "https://ogre.adc4gis.com/convert";

        // Chemin de sortie pour le fichier GeoJSON
        $geojsonFileName = $customName . '.geojson';
        $geojsonFilePath = __DIR__ . '/../../../assets/shapefile/' . $geojsonFileName;

        // Utiliser curl pour faire une requête POST vers l'API
        $ch = curl_init();

        // Paramètres de la requête POST avec le fichier ZIP
        $data = array(
            'upload' => new CURLFile($zipFilePath, 'application/zip', basename($zipFilePath))
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
            throw new \Exception("Erreur API: " . curl_error($ch));
        }

        // Fermer la session curl
        curl_close($ch);

        // Sauvegarder la réponse (GeoJSON) dans un fichier
        file_put_contents($geojsonFilePath, $response);

        // Vérifier si le fichier GeoJSON a bien été créé
        if (file_exists($geojsonFilePath)) {
            return $geojsonFilePath;
        } else {
            throw new \Exception("La conversion a échoué.");
        }
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
                $customName = preg_replace('/[^a-zA-Z0-9_-]/', '', $customName);
                if (empty($customName)) {
                    throw new \Exception("Nom de fichier invalide.");
                }
            } else {
                throw new \Exception("Veuillez spécifier un nom de fichier.");
            }

            // Vérifiez les erreurs
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception("Erreur lors du téléchargement du fichier : " . $this->codeToMessage($file['error']));
            }

            // Vérifier si le dossier est accessible en écriture
            if (!is_writable($uploadDir)) {
                throw new \Exception("Le dossier de destination n'est pas accessible en écriture.");
            }

            // Définir un nom de fichier unique avec le nom personnalisé
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['tif', 'tiff', 'png', 'jpg', 'jpeg'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new \Exception("Extension de fichier non autorisée. Extensions valides : .tif, .tiff, .png, .jpg, .jpeg");
            }

            $uploadFilePath = $uploadDir . $customName . '.' . $fileExtension;

            // Vérifier si le fichier existe déjà pour éviter les conflits
            if (file_exists($uploadFilePath)) {
                throw new \Exception("Le fichier " . htmlspecialchars($customName . '.' . $fileExtension) . " existe déjà.");
            }

            // Déplacer le fichier téléchargé
            if (!move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
                throw new \Exception("Erreur lors du déplacement du fichier.");
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
            throw new \Exception("Erreur API: " . curl_error($ch));
        }

        // Fermer la session curl
        curl_close($ch);

        // Sauvegarder la réponse (GeoTIFF) dans un fichier
        file_put_contents($geoTiffFilePath, $response);

        // Vérifier si le fichier GeoTIFF a bien été créé
        if (file_exists($geoTiffFilePath)) {
            return $geoTiffFilePath;
        } else {
            throw new \Exception("La conversion a échoué.");
        }
    }

    // Fonction pour traduire les codes d'erreur d'upload en messages
    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "Le fichier dépasse la directive upload_max_filesize dans php.ini.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "Le fichier dépasse la directive MAX_FILE_SIZE spécifiée dans le formulaire HTML.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "Le fichier n'a été que partiellement téléchargé.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "Aucun fichier n'a été téléchargé.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Manque un dossier temporaire.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Échec de l'écriture du fichier sur le disque.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "Une extension PHP a arrêté le téléchargement du fichier.";
                break;

            default:
                $message = "Erreur inconnue lors du téléchargement du fichier.";
                break;
        }
        return $message;
    }


}
?>
