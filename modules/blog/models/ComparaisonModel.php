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
    public function saveExperimentation($data, $geoJsonNameSim, $geoJsonNameVer,$name,$dossier,$project)
    {
        $userId = $_SESSION['user_id'];
//        $name = 'Nom de l\'expérience'; // Vous pouvez remplacer ceci par un nom dynamique si nécessaire

        // Convertir les données en JSON pour les stocker dans la base de données
        $chartsJson = json_encode($data['charts']); // Données des graphiques
        $tableDataJson = json_encode($data['tableData']); // Données du tableau

        // Sauvegarder dans la base de données
        $sql = "INSERT INTO experimentation (nom_sim, nom_ver, nom_xp, data_xp, table_data, id_utilisateur,dossier,projet) 
            VALUES (:geoJsonNameSim, :geoJsonNameVer, :name, :chartsJson, :tableDataJson, :user_id, :dossier,:project)";
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


    public function loadExperimentation($experimentId)
    {
        // Récupérer l'expérience depuis la base de données
        $sql = "SELECT * FROM experimentation WHERE id = :experimentId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':experimentId', $experimentId);
        $stmt->execute();
        $experiment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($experiment) {
            $chartsData = json_decode($experiment['data'], true);

            // Retourner les données des graphiques
            return $chartsData;
        }
        return null;
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
    public function calculateError($results)
    {

    }

    public function grapheDonnees($areaStatsSim, $areaStatsVer, $shapeIndexStatsSim, $shapeIndexStatsVer): array
    {
        // Arrondir les données de simulation
        $graphSim = array(
            array("label" => "Area mean", "y" => round($areaStatsSim['mean'], 2)),
            array("label" => "Area min", "y" => round($areaStatsSim['min'], 2)),
            array("label" => "Area max", "y" => round($areaStatsSim['max'], 2)),
            array("label" => "Area Std", "y" => round($areaStatsSim['std'], 2)),
            array("label" => "Shape Index Mean", "y" => round($shapeIndexStatsSim['mean'], 2)),
            array("label" => "Shape Index Min", "y" => round($shapeIndexStatsSim['min'], 2)),
            array("label" => "Shape Index Max", "y" => round($shapeIndexStatsSim['max'], 2)),
            array("label" => "Shape Index Std", "y" => round($shapeIndexStatsSim['std'], 2)),
        );

        // Arrondir les données de vérité terrain
        $graphVer = array(
            array("label" => "Area mean", "y" => round($areaStatsVer['mean'], 2)),
            array("label" => "Area min", "y" => round($areaStatsVer['min'], 2)),
            array("label" => "Area max", "y" => round($areaStatsVer['max'], 2)),
            array("label" => "Area Std", "y" => round($areaStatsVer['std'], 2)),
            array("label" => "Shape Index Mean", "y" => round($shapeIndexStatsVer['mean'], 2)),
            array("label" => "Shape Index Min", "y" => round($shapeIndexStatsVer['min'], 2)),
            array("label" => "Shape Index Max", "y" => round($shapeIndexStatsVer['max'], 2)),
            array("label" => "Shape Index Std", "y" => round($shapeIndexStatsVer['std'], 2)),
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