<?php

namespace blog\controllers;
use GeoPHP;

class Comparaison{
    public function CompareVec(){

        // Charger le GeoJSON
        $polygone = '{"type":"Polygon","coordinates":[[[30,10],[40,40],[20,40],[10,20],[30,10]]]}';
        $geometrie = GeoPHP::load($polygone, 'json');

        // Calculer l'aire
        $area = $geometrie->area();
        echo "Aire du polygone: " . $area;

    }

}