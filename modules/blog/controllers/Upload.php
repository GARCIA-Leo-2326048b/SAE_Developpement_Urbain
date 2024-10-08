<?php

namespace blog\controllers;

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
                // Définissez le chemin de destination pour le fichier
                $uploadDir = __DIR__ . '/../../../assets/shapefile/'; // Dossier où vous voulez stocker les fichiers uploadés
                $uploadFile = $uploadDir . basename($file['name']);

                // Déplacez le fichier téléchargé
                if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                    echo "Le fichier Shapefile a été téléchargé avec succès.<br>";

                    // Chemin du fichier GeoJSON de sortie
                    $geojsonFilePath = $uploadDir . pathinfo($file['name'], PATHINFO_FILENAME) . '.geojson';

                    // Appeler QGIS en ligne de commande pour convertir le Shapefile en GeoJSON
                    $qgisPath = "C:\ProgramData\Microsoft\Windows\Start Menu\Programs\QGIS 3.38.3\Qt Designer with QGIS 3.38.3 custom widgets.lnk"; // Modifiez selon votre installation
                    $command = "$qgisPath run native:convertformat --INPUT=\"$uploadFile\" --OUTPUT=\"$geojsonFilePath\" --OUTPUT_FORMAT=\"GeoJSON\"";
                    echo $command;
                    $output = shell_exec($command);

                    // Vérifier si le fichier GeoJSON a été généré
                    if (file_exists($geojsonFilePath)) {
                        echo "Conversion réussie. <a href='../../../assets/shapefile/" . pathinfo($geojsonFilePath, PATHINFO_BASENAME) . "'>Télécharger le fichier GeoJSON</a>";
                    } else {
                        echo "La conversion a échoué. Voici la sortie : <br>" . nl2br($output);
                    }
                } else {
                    echo "Erreur lors du déplacement du fichier.";
                }
            } else {
                echo "Erreur lors du téléchargement du fichier : " . $file['error'];
            }
        } else {
            echo "Aucun fichier n'a été téléchargé.";
        }
    }}