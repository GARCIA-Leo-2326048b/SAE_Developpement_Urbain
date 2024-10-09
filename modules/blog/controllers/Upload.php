<?php

namespace blog\controllers;

use \CURLFile;
use \ZipArchive;

class Upload
{
    public function telechargement()
    {

        // Gestion des fichiers Shapefile (Vecteur)
        if (isset($_FILES['shapefile'])) {
            $this->handleShapefileUpload();
        } // Gestion des fichiers Raster
        elseif (isset($_FILES['rasterfile'])) {
            $this->handleRasterUpload();
        } else {
            echo "Aucun fichier n'a été téléchargé.";
        }
    }


        // On telecharge des Shapefile
        public function handleShapefileUpload()
        {
            $files = $_FILES['shapefile'];
            $requiredExtensions = ['shp', 'shx', 'dbf']; // Extensions requises
            $uploadedFiles = [];

            $uploadDir = __DIR__ . '/../../../assets/shapefile/'; // Dossier de destination

            // Parcourir tous les fichiers téléchargés
            foreach ($files['name'] as $key => $name) {
                $fileTmpPath = $files['tmp_name'][$key];
                $fileExtension = pathinfo($name, PATHINFO_EXTENSION);

                // Vérifiez si l'extension est dans la liste des fichiers requis
                if (in_array($fileExtension, $requiredExtensions)) {
                    $uploadFilePath = $uploadDir . basename($name);

                    // Déplacez chaque fichier dans le répertoire de destination
                    if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                        echo "Le fichier $name a été téléchargé avec succès.<br>";
                        $uploadedFiles[$fileExtension] = $uploadFilePath;
                    } else {
                        echo "Erreur lors du téléchargement de $name.<br>";
                    }
                } else {
                    echo "Fichier $name non valide. Extensions valides : .shp, .shx, .dbf<br>";
                }
            }

            // Vérifiez que tous les fichiers requis (.shp, .shx, .dbf) sont présents
            if (count($uploadedFiles) === count($requiredExtensions)) {
                // Créer un fichier ZIP avec les fichiers téléchargés
                $zipFilePath = $this->createZipFile($uploadedFiles);
                if ($zipFilePath) {
                    // Envoyer le fichier ZIP à l'API OGRE
                    $this->convertShapefileToGeoJSON($zipFilePath);
                } else {
                    echo "Erreur lors de la création du fichier ZIP.";
                }
            } else {
                echo "Tous les fichiers requis (.shp, .shx, .dbf) ne sont pas présents.<br>";
            }
        } //else {
         //   echo "Aucun fichier n'a été téléchargé.<br>";
      //  }
  //  }

    // Fonction pour compresser les fichiers shapefiles dans un fichier ZIP
    private function createZipFile($files)
    {
        $zip = new ZipArchive();
        $zipFilePath = __DIR__ . '/../../../assets/shapefile/shapefile.zip';

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

    // Fonction pour appeler l'API OGRE pour convertir le fichier
    private function convertShapefileToGeoJSON($zipFilePath)
    {
        // URL de l'API OGRE pour la conversion
        $apiUrl = "https://ogre.adc4gis.com/convert";

        // Chemin de sortie pour le fichier GeoJSON
        $geojsonFilePath = __DIR__ . '/../../../assets/shapefile/' . pathinfo($zipFilePath, PATHINFO_FILENAME) . '.geojson';

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
            echo "Erreur API: " . curl_error($ch);
            curl_close($ch);
            return;
        }

        // Fermer la session curl
        curl_close($ch);

        // Sauvegarder la réponse (GeoJSON) dans un fichier
        file_put_contents($geojsonFilePath, $response);

        // Vérifier si le fichier GeoJSON a bien été créé
        if (file_exists($geojsonFilePath)) {
            echo "Conversion réussie. <a href='../../../assets/shapefile/" . basename($geojsonFilePath) . "'>Télécharger le fichier GeoJSON</a>";
        } else {
            echo "La conversion a échoué.";
        }
    }

    public function handleRasterUpload()
    {
        $file = $_FILES['rasterfile'];

        // Vérifiez les erreurs
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Définissez le chemin de destination temporaire pour le fichier
            $uploadDir = __DIR__ . '/../../../assets/shapefile/'; // Dossier pour stocker temporairement

            // Afficher le chemin du répertoire
            echo "Upload dir: " . realpath($uploadDir) . "<br>";

            $uploadFile = $uploadDir . basename($file['name']);

            // Vérifiez si le dossier est accessible en écriture
            if (!is_writable($uploadDir)) {
                echo "Le dossier de destination n'est pas accessible en écriture.";
                return;
            }


            // Déplacez le fichier téléchargé
            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                echo "Le fichier raster a été téléchargé avec succès.<br>";

                // Appeler l'API pour convertir le fichier Raster en GeoTIFF
                $this->convertRasterToGeoTIFF($uploadFile);
            } else {
                echo "Erreur lors du déplacement du fichier.";
            }
        } else {
            echo "Erreur lors du téléchargement du fichier : " . $file['error'];
        }

    }
    private function convertRasterToGeoTIFF($rasterFilePath)
    {
        // URL de l'API pour la conversion (ou changer par un service local si disponible)
        $apiUrl = "https://ogre.adc4gis.com/convert";  // Exemple d'API, change si nécessaire

        // Chemin de sortie pour le fichier GeoTIFF
        $geoTiffFilePath = __DIR__ . '/../../../assets/shapefile/' . pathinfo($rasterFilePath, PATHINFO_FILENAME) . '.tiff';

        // Utiliser curl pour faire une requête POST vers l'API
        $ch = curl_init();

        // Paramètres de la requête POST avec le fichier raster
        $data = array(
            'upload[]' => new \CURLFile($rasterFilePath, 'application/octet-stream', basename($rasterFilePath))
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
            echo "Erreur API: " . curl_error($ch);
            curl_close($ch);
            return;
        }

        // Fermer la session curl
        curl_close($ch);

        // Sauvegarder la réponse (GeoTIFF) dans un fichier
        file_put_contents($geoTiffFilePath, $response);

        // Vérifier si le fichier GeoTIFF a bien été créé
        if (file_exists($geoTiffFilePath)) {
            echo "Conversion réussie. <a href='../../../assets/shapefile/" . basename($geoTiffFilePath) . "'>Télécharger le fichier GeoTIFF</a>";
        } else {
            echo "La conversion a échoué.";
        }
    }
}
