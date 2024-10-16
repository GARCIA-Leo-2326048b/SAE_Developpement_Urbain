<?php

namespace blog\controllers;
use GeoPHP;

class ComparaisonController{
    public function CompareVec($polygoneSim, $polygoneVer){

        // Charger les GeoJSON
        $geometrieSim = GeoPHP::load($polygoneSim, 'json');
        $geometrieVer = GeoPHP::load($polygoneVer, 'json');

        // Calculer l'aire de la simulation
        $aireSim = $geometrieSim->area();
        echo "Aire de la simulation: " . $aireSim;
        // Calculer l'aire de la vérité terrain
        $aireVer = $geometrieVer->area();
        echo "Aire de la vérité terrain: " . $aireVer;

        // Calculer le périmètre de la simulation
        $perimetreSim = $geometrieSim->length();
        echo "Périmètre de la simulation: " . $perimetreSim;
        // Calculer le périmètre de la vérité terrain
        $perimetreVer = $geometrieVer->length();
        echo "Périmètre de la vérité terrain: " . $perimetreVer;



    }

}