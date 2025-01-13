<?php

namespace blog\models;
use geoPHP;
use PDO;
use proj4php\Point;
use proj4php\Proj;
use proj4php\Proj4php;

class ComparaisonModel
{
    private $db;

    public function __construct()
    {
        // Connexion à la base de données via SingletonModel
        $this->db = SingletonModel::getInstance()->getConnection();
    }
    public function saveExperimentationM($data, $geoJsonNameSim, $geoJsonNameVer, $name, $dossier, $project)
    {
        $userId = $_SESSION['user_id'];

        // Encoder et valider les données JSON
        try {
            $chartsJson = json_encode($data['charts'], JSON_THROW_ON_ERROR);
            $tableDataJson = json_encode($data['tableData'], JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            error_log('Erreur lors de l\'encodage JSON : ' . $e->getMessage());
            return false;
        }

        // Sauvegarder dans la base de données
        $sql = "INSERT INTO experimentation (nom_sim, nom_ver, nom_xp, data_xp, table_data, id_utilisateur, dossier, projet) 
            VALUES (:geoJsonNameSim, :geoJsonNameVer, :name, :chartsJson, :tableDataJson, :user_id, :dossier, :project)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':geoJsonNameSim', $geoJsonNameSim);
        $stmt->bindParam(':geoJsonNameVer', $geoJsonNameVer);
        $stmt->bindParam(':chartsJson', $chartsJson);
        $stmt->bindParam(':tableDataJson', $tableDataJson);
        $stmt->bindParam(':dossier', $dossier);
        $stmt->bindParam(':project', $project);

        if ($stmt->execute()) {
            return true;
        } else {
            error_log('Erreur lors de la sauvegarde : ' . print_r($stmt->errorInfo(), true));
            return false;
        }
    }

    public function deleteFileExp($filename,$project)
    {
        $userId = $_SESSION['user_id'];

        // Préparer la requête SQL pour supprimer une expérimentation spécifique
        $sql = "DELETE FROM experimentation WHERE nom_xp = :filename AND id_utilisateur = :user_id AND projet = :project";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':filename', $filename);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':project', $project);

        $u = $stmt->execute();
        $affectedRows = $stmt->rowCount();
        error_log('Lignes affectées : ' . $affectedRows);
        return $u;
    }


    public function loadExperimentation($id) {
        $stmt = $this->db->prepare("SELECT * FROM experimentation WHERE id_xp = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Si l'expérimentation existe, on la retourne
        if ($stmt->rowCount() > 0) {
            $experiment = $stmt->fetch(PDO::FETCH_ASSOC);

            // Récupérer les charts et les données du tableau
            $experiment['charts'] = $this->getChartsByExperimentationId($id);

            // Vérifier si les noms de fichiers GeoJSON existent et les décoder
            $experiment['geoJsonSimName'] = isset($experiment['geoJsonSimName']) ? json_decode($experiment['geoJsonSimName'], true) : [];
            $experiment['geoJsonVerName'] = isset($experiment['geoJsonVerName']) ? json_decode($experiment['geoJsonVerName'], true) : [];

            // Vérifier si les noms de fichiers GeoJSON sont des tableaux
            if (!is_array($experiment['geoJsonSimName'])) {
                $experiment['geoJsonSimName'] = [];
            }
            if (!is_array($experiment['geoJsonVerName'])) {
                $experiment['geoJsonVerName'] = [];
            }

            $experiment['tableData'] = $this->getTableDataByExperimentationId($id);

            // Initialiser les tableaux pour GeoJSON
            $experiment['geoJsonSim'] = [];
            $experiment['geoJsonVer'] = [];

            // Ajouter les données GeoJSON pour chaque nom de fichier
            foreach ($experiment['geoJsonSimName'] as $geoJsonNameSim) {
                $experiment['geoJsonSim'][] = $this->fetchGeoJson($geoJsonNameSim);
            }

            foreach ($experiment['geoJsonVerName'] as $geoJsonNameVer) {
                $experiment['geoJsonVer'][] = $this->fetchGeoJson($geoJsonNameVer);
            }

            return $experiment;
        }

        return null;
    }


    // Fonction pour reformater les données avant de les envoyer à la vue
    public function reformaterDonnees($tableData)
    {
        // Décoder la chaîne JSON en tableau PHP
        $tableDataF = json_decode($tableData[0]['table_data'], true); // true pour obtenir un tableau associatif

        // Vérifier si le décodage a fonctionné
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Erreur lors du décodage JSON : " . json_last_error_msg();
            return [];
        }

        // Initialisation des tableaux pour Simulation, Vérité terrain et Erreur
        $graphSim = [];
        $graphVer = [];
        $errors = [];

        // Pour éviter les en-têtes dupliqués, ignorer les lignes contenant "Statistique"
        foreach ($tableDataF as $index => $row) {
            // Ignorer la première ligne qui contient les en-têtes, ainsi que les duplications des en-têtes
            if ($index === 0 || $row[0] === 'Statistique') {
                continue;
            }

            // Vérifier que chaque ligne a bien 4 colonnes
            if (count($row) === 4) {
                $label = $row[0];        // Le nom de la statistique
                $simValue = (float)$row[1];  // Simulation
                $verValue = (float)$row[2];  // Vérité terrain
                $errorValue = (float)$row[3]; // Erreur

                // Ajouter les données dans les tableaux formatés
                $graphSim[] = ["label" => $label, "y" => round($simValue, 2)];
                $graphVer[] = ["label" => $label, "y" => round($verValue, 2)];
                $errors[] = ["label" => "Error " . $label, "y" => round($errorValue, 2)];
            } else {
                echo "Erreur : Ligne inattendue au format incorrect.";
            }
        }

        // Retourner les données reformattées
        return [
            'graphSim' => $graphSim,
            'graphVer' => $graphVer,
            'errors' => $errors,
        ];
    }


    public function fetchGeoJson($name)
    {
        $stmt = $this->db->prepare("SELECT file_data FROM uploadGJ WHERE file_name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();

        // Retourner les données GeoJSON
        return $stmt->fetchColumn();
    }

    private function getChartsByExperimentationId($id) {
        // Exemple pour récupérer les charts depuis la base de données
        $stmt = $this->db->prepare("SELECT data_xp FROM experimentation WHERE id_xp = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTableDataByExperimentationId($id) {
        // Exemple pour récupérer les données du tableau
        $stmt = $this->db->prepare("SELECT table_data FROM experimentation WHERE id_xp = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEPSGCode($geoJson)
    {
        $geometry = geoPHP::load($geoJson);

        // Récupérer la longitude, en prenant en compte le type de géométrie
        $longitude = match ($geometry->geometryType()) {
            'Point' => $geometry->x(),
            default => $geometry->centroid()?->x() ?? $geometry->getComponents()[0]?->centroid()?->x(),
        };


        if ($longitude === null || $longitude < -180 || $longitude > 180) {
            throw new \InvalidArgumentException('Longitude invalide : doit être entre -180 et 180 degrés.');
        }

        // Calculer la zone UTM
        $utmZone = floor(($longitude + 180) / 6) + 1;

        // Déterminer le code EPSG
        return 'EPSG:' . (isset($utmZone) ? '326' . str_pad($utmZone, 2, '0', STR_PAD_LEFT) : '327' . str_pad($utmZone, 2, '0', STR_PAD_LEFT));
    }

    public function projectGeoJson($geoJson) {
        $proj4 = new Proj4Php();

        $sourceProjection = 'EPSG:4326';
        $targetProjection = $this->getEPSGCode($geoJson);

        $projSource = new Proj($sourceProjection, $proj4);
        $projTarget = new Proj($targetProjection, $proj4);

        // Convertir en tableau PHP
        $geoJsonArray = json_decode($geoJson,true);

        foreach ($geoJsonArray['features'] as &$feature) {
            if (isset($feature['geometry']['bbox'])) {
                $this->transformBbox($feature['geometry']['bbox'], $projSource, $projTarget, $proj4);
            }
            if (isset($feature['geometry']['coordinates'])) {
                $this->transformCoordinates($feature['geometry']['coordinates'],$projSource, $projTarget, $proj4);
            }
        }
        return json_encode($geoJsonArray);
    }

    private function transformCoordinates(&$coordinates, $projSource, $projTarget, $proj4) {
        //si les coordonnées ne sont pas celle d'un point
        if (is_array($coordinates[0])) {

            foreach ($coordinates as &$coord) {
                //on parcours chaque anneaux du fichier
                $this->transformCoordinates($coord, $projSource, $projTarget, $proj4);
            }
        } else {
            $srcPoint = new Point($coordinates[0], $coordinates[1], $projSource);
            $destPoint = $proj4->transform($projSource, $projTarget, $srcPoint);

            // Mettre à jour les coordonnées du point transformé
            $coordinates[0] = (float) $destPoint->x;
            $coordinates[1] = (float) $destPoint->y;
        }
    }

    private function transformBbox(&$bbox, $projSource, $projTarget, $proj4) {
        for ($i = 0; $i < count($bbox); $i += 2) {
            $srcPoint = new Point($bbox[$i], $bbox[$i + 1], $projSource);
            $destPoint = $proj4->transform($projSource, $projTarget, $srcPoint);

            $bbox[$i] = (float) $destPoint->x;
            $bbox[$i + 1] = (float) $destPoint->y;
        }
    }

    public function getAreasAndPerimeters($geometry,&$areas = [], &$perimeters = []){
        //on rentre les aires de tous les batiments dans un tableau
        $geometryType = $geometry->geometryType();
        switch ($geometryType){
            case 'MultiPolygon':
                foreach ($geometry->getComponents() as $component) {
                    $this->getAreasAndPerimeters($component,$areas,$perimeters);
                }
                break;
            case 'LineString':
                $perimeters[] = $geometry->length();
                break;
            case 'Polygon':
                $areas[] = $geometry->area();
                // Parcours des composants pour les contours et les trous (LineString)
                foreach ($geometry->getComponents() as $subComponent) {
                    if ($subComponent->geometryType() === 'LineString') {
                        // Calcul du périmètre de chaque contour
                        $perimeters[] = $subComponent->length();
                    }
                }
                break;
            default:
                echo "Type de géométrie non pris en charge : " . $geometryType . "\n";
                break;

        }
        return [
            'areas'=>$areas,
            'perimeters'=>$perimeters];
    }


    public function getShapeIndexStats($polygon)
    {
        $shapeIndexes = [];
        foreach ($polygon['areas'] as $i => $area) {
            if ($area > 0) {
                $shapeIndex = $polygon['perimeters'][$i] / (2 * sqrt(pi() * $area));
                $shapeIndexes[] = $shapeIndex;
            }
        }
        return $shapeIndexes;
    }
    public function getStat($values) {

        if (count($values) > 0) {
            $mean = array_sum($values)/count($values);//moyenne des aires
            $min = min($values);//aire minimum
            $max = max($values);//aire maximum
            $std = $this->calculateStandardDeviation($values,$mean);//ecart-type
        } else {
            $mean =$max=$min=$std= 0;
        }
        return [
            'mean' => $mean,
            'min' => $min,
            'max' => $max,
            'std' => $std
        ];
    }

    public function calculateStandardDeviation($areas, $mean) {
        $sum = 0;
        foreach ($areas as $area) {
            $sum += pow($area - $mean, 2); // Calcul de l'écart à la moyenne au carré
        }
        $variance = $sum / count($areas);  // Calcul de la variance
        return sqrt($variance);            // Retourne l'écart-type (racine carrée de la variance)
    }

    public function getHausdorffDistance($geometry1, $geometry2)
    {
        if (!$geometry1 || !$geometry2) {
            throw new InvalidArgumentException("Les géométries fournies sont invalides ou nulles.");
        }

        // Convertir les géométries en collections de points
        $points1 = $this->extractPoints($geometry1);
        $points2 = $this->extractPoints($geometry2);

        // Calculer la distance maximale minimale (Hausdorff)
        $maxMinDistance1 = $this->calculateMaxMinDistance($points1, $points2);
        $maxMinDistance2 = $this->calculateMaxMinDistance($points2, $points1);

        return max($maxMinDistance1, $maxMinDistance2);
    }

    public function grapheDonnees($areaStatsSim, $areaStatsVer, $shapeIndexStatsSim, $shapeIndexStatsVer): array
    {
        // Arrondir les données de simulation
        $graphSim = array(
            array("label" => "Area mean", "y" => round($areaStatsSim['mean'], 2)),
            array("label" => "Area Std", "y" => round($areaStatsSim['std'], 2)),
            array("label" => "Area min", "y" => round($areaStatsSim['min'], 2)),
            array("label" => "Area max", "y" => round($areaStatsSim['max'], 2)),

            array("label" => "Shape Index Mean", "y" => round($shapeIndexStatsSim['mean'], 2)),
            array("label" => "Shape Index Std", "y" => round($shapeIndexStatsSim['std'], 2)),
            array("label" => "Shape Index Min", "y" => round($shapeIndexStatsSim['min'], 2)),
            array("label" => "Shape Index Max", "y" => round($shapeIndexStatsSim['max'], 2)),

        );

        // Arrondir les données de vérité terrain
        $graphVer = array(
            array("label" => "Area mean", "y" => round($areaStatsVer['mean'], 2)),
            array("label" => "Area Std", "y" => round($areaStatsVer['std'], 2)),
            array("label" => "Area min", "y" => round($areaStatsVer['min'], 2)),
            array("label" => "Area max", "y" => round($areaStatsVer['max'], 2)),

            array("label" => "Shape Index Mean", "y" => round($shapeIndexStatsVer['mean'], 2)),
            array("label" => "Shape Index Std", "y" => round($shapeIndexStatsVer['std'], 2)),
            array("label" => "Shape Index Min", "y" => round($shapeIndexStatsVer['min'], 2)),
            array("label" => "Shape Index Max", "y" => round($shapeIndexStatsVer['max'], 2)),

        );
         // Calcul des erreurs (différences)
        // Calcul des erreurs (différences entre valeurs arrondies)
        $errors = [];
        foreach ($graphSim as $index => $simData) {
            $simValue = $simData['y']; // Valeur de simulation arrondie
            $verValue = $graphVer[$index]['y']; // Valeur de vérité terrain arrondie
            $errors[] = [
                "label" => "Error " . $simData['label'],
                "y" => round(abs($simValue - $verValue), 2), // Erreur calculée à partir des valeurs arrondies
            ];
        }

        return [
             'graphSim' => $graphSim,
             'graphVer' => $graphVer,
             'errors' => $errors
         ];
     }


}