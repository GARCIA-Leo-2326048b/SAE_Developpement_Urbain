<?php
namespace blog\models;
use geoPHP;
use proj4php\Point;
use proj4php\Proj;
use proj4php\Proj4php;

class GeoJsonModel
{

    private $db;

    public function __construct()
    {
        // Connexion à la base de données via SingletonModel
        $this->db = SingletonModel::getInstance()->getConnection();
    }
    public function fetchGeoJson($name)
    {
        $stmt = $this->db->prepare("SELECT file_data FROM uploadGJ WHERE file_name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();

        // Retourner les données GeoJSON
        return $stmt->fetchColumn();
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

    function projectGeoJson($geoJson) {
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

}