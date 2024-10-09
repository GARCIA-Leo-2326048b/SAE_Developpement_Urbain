<?php

namespace blog\controllers;
use \CURLFile;
class Upload
{
    public function telechargement()
    {
        echo var_dump($_FILES);
        // Vérifiez si un fichier a été téléchargé
        if (isset($_FILES['shapefiles'])) {
            $files = $_FILES['shapefiles'];
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
                $this->convertShapefileToGeoJSON($uploadedFiles);
            } else {
                echo "Tous les fichiers requis (.shp, .shx, .dbf) ne sont pas présents.<br>";
            }
        } else {
            echo "Aucun fichier n'a été téléchargé.<br>";
        }
    }

    // Fonction pour appeler l'API OGRE pour convertir le fichier
    private function convertShapefileToGeoJSON($uploadedFiles)
    {
        // URL de l'API OGRE pour la conversion
        $apiUrl = "https://ogre.adc4gis.com/convert";

        // Chemin de sortie pour le fichier GeoJSON
        $shapefilePath = $uploadedFiles['shp']; // Chemin du fichier .shp
        $geojsonFilePath = __DIR__ . '/../../../assets/shapefile/' . pathinfo($shapefilePath, PATHINFO_FILENAME) . '.geojson';

        // Utiliser curl pour faire une requête POST vers l'API
        $ch = curl_init();

        // Paramètres de la requête POST avec les fichiers shapefile
        $data = array(
            'upload' => new CURLFile($shapefilePath, 'application/octet-stream', basename($shapefilePath)),
            'upload_shx' => new CURLFile($uploadedFiles['shx'], 'application/octet-stream', basename($uploadedFiles['shx'])),
            'upload_dbf' => new CURLFile($uploadedFiles['dbf'], 'application/octet-stream', basename($uploadedFiles['dbf']))
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
}