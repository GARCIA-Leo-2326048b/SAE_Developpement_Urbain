<?php

namespace blog\views;

class AffichageView
{
    function show($house = null, $road = null, $vegetation = null, $tiffPath = null): void
    {
        ob_start();
        ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script src="https://unpkg.com/georaster-layer-for-leaflet/dist/georaster-layer-for-leaflet.min.js"></script>
        <script src="https://unpkg.com/georaster"></script>
        <script src="/_assets/scripts/affichageCarte.js"></script> <!-- script de création de carte -->

        <div id="map"></div>

        <div id="controls">
            <h3>Contrôles de la carte</h3>

            <!-- Sélectionner la couche de fond -->
            <div>
                <button onclick="switchToSatellite()">Satellite</button>
                <button onclick="switchToStreets()">Streets</button>
            </div>

            <!-- Sélectionner la couche -->
            <div id="layerButtons"></div>

            <!-- Contrôle de l'opacité -->
            <h4>Opacité :</h4>
            <input type="range" id="opacitySlider" min="0" max="1" step="0.1" value="1" onchange="updateLayerOpacity()">

            <div>
                <button onclick="supprimerCouche()">Supprimer la couche sélectionnée</button>
            </div>

        </div>


        <script>
            let roadData = <?php echo $road ?: 'null'; ?>; // Convertit $road en JSON directement

            // Initialisation de la carte avec les couches GeoJSON et GeoTIFF
            initializeMap(<?php echo $house ?: 'null'; ?>, "<?php echo $tiffPath ?: ''; ?>");
        </script>

        <!-- Bouton pour ajouter la couche road -->
        <h4>Ajouter les routes :</h4>
        <button onclick="ajouterGeoJson(roadData, 'Routes')">Ajouter Routes</button>
        <button onclick="ajouterGeoTiff('<?php echo $tiffPath ?: ''; ?>', 'GeoTIFF')">Ajouter GeoTIFF</button>


        <?php
        (new GlobalLayout('Affichage', ob_get_clean()))->show();
    }
}
?>