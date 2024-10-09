<?php

namespace blog\controllers;
use \CURLFile;
class Upload
{
    public function telechargement()
    {
        echo var_dump($_FILES);
        // Vérifiez si un fichier a été téléchargé
        if (isset($_FILES['shapefile'])) {
            $file = $_FILES['shapefile'];

            // Vérifiez les erreurs
            if ($file['error'] === UPLOAD_ERR_OK) {
                // Définissez le chemin de destination temporaire pour le fichier
                $uploadDir = __DIR__ . '/../../../assets/shapefile/'; // Dossier où vous voulez stocker les fichiers uploadés temporairement
                $uploadFile = $uploadDir . basename($file['name']);

                // Déplacez le fichier téléchargé
                if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                    echo "Le fichier Shapefile a été téléchargé avec succès.<br>";

                    // Appeler l'API OGRE pour convertir le Shapefile en GeoJSON
                    $this->convertShapefileToGeoJSON($uploadFile);
                } else {
                    echo "Erreur lors du déplacement du fichier.";
                }
            } else {
                echo "Erreur lors du téléchargement du fichier : " . $file['error'];
            }
        } else {
            echo "Aucun fichier n'a été téléchargé.";
        }
    }

    // Fonction pour appeler l'API OGRE pour convertir le fichier
    private function convertShapefileToGeoJSON($shapefilePath)
    {
        // URL de l'API OGRE pour la conversion
        $apiUrl = "https://ogre.adc4gis.com/convert";

        // Chemin de sortie pour le fichier GeoJSON
        $geojsonFilePath = __DIR__ . '/../../../assets/shapefile/' . pathinfo($shapefilePath, PATHINFO_FILENAME) . '.geojson';

        // Utiliser curl pour faire une requête POST vers l'API
        $ch = curl_init();

        // Paramètres de la requête POST avec le fichier shapefile
        $data = array(
            'upload' => new \CURLFile($shapefilePath, 'application/octet-stream', basename($shapefilePath))
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
