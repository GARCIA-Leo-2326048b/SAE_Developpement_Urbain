<?php

namespace blog\controllers;
use GeoPHP;

class ComparaisonController{
    public function CompareVec($polygoneSim, $polygoneVer){

        // Charger les GeoJSON
        $geometrieSim = GeoPHP::load($polygoneSim, 'json');
        $geometrieVer = GeoPHP::load($polygoneVer, 'json');


        // PARTIE SIMULATION


        // initialisser l'aire totale et le nombre de maisons de la simulation
        $airettlSim = 0;
        $nbmaisonSim = 0;

        // calculer l'aire totale et le nombre de maisons de la simulation
        foreach ($geometrieSim->getComponents() as $maisonSim) {
            if ($maisonSim->geometryType() === 'Polygon') {
                $airettlSim += $maisonSim->area();
                $nbmaisonSim++;
            }
        }

        // Calculer l'aire moyenne des maisons de la simulation
        if ($nbmaisonSim > 0) {
            $airemoySim = $airettlSim / $nbmaisonSim;
            echo "Aire moyenne des maisons dans la simulation: " . $airemoySim;
        } else {
            echo "Le fichier GeoJSON n'a pas été détecté ou ne contient pas de polygones";
        }


        // PARTIE VERITE TERRAIN


        // initialisser l'aire totale et le nombre de maisons de la vérité terrain
        $airettlVer = 0;
        $nbmaisonVer = 0;

        // calculer l'aire totale et le nombre de maisons de la vérité terrain
        foreach ($geometrieVer->getComponents() as $maisonVer) {
            if ($maisonVer->geometryType() === 'Polygon') {
                $airettlVer += $maisonVer->area();
                $nbmaisonVer++;
            }
        }

        // Calculer l'aire moyenne des maisons de la vérité terrain
        if ($nbmaisonVer > 0) {
            $airemoyVer = $airettlVer / $nbmaisonVer;
            echo "Aire moyenne des maisons dans la vérité terrain: " . $airemoyVer;
        } else {
            echo "Le fichier GeoJSON n'a pas été détecté ou ne contient pas de polygones";
        }

    }

}