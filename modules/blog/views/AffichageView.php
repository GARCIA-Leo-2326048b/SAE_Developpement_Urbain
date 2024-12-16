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

        <div id="map" style="width: 100%; height: 500px;"></div>

        <div id="controls">
            <h3>Contrôles de la carte</h3>

            <!-- Sélectionner la couche de fond -->
            <div>
                <button onclick="switchToSatellite()">Satellite</button>
                <button onclick="switchToStreets()">Streets</button>
            </div>

            <!-- Sélectionner la couche -->
            <h4>Sélectionnez la couche :</h4>
            <?php if ($house !== null): ?>
                <button onclick="selectLayer('house')">Maisons</button>
            <?php endif; ?>
            <?php if ($road !== null): ?>
                <button onclick="selectLayer('road')">Routes</button>
            <?php endif; ?>
            <?php if ($vegetation !== null): ?>
                <button onclick="selectLayer('vegetation')">Végétation</button>
            <?php endif; ?>
            <?php if ($tiffPath !== null): ?>
                <button onclick="selectLayer('tiff')">GeoTIFF</button>
            <?php endif; ?>

            <!-- Contrôle de l'opacité -->
            <h4>Opacité :</h4>
            <input type="range" id="opacitySlider" min="0" max="1" step="0.1" value="1" onchange="updateLayerOpacity()">
        </div>

        <script>
            // Initialisation de la carte avec les couches GeoJSON et GeoTIFF
            initializeMap(<?php echo $house ?: 'null'; ?>, <?php echo $road ?: 'null'; ?>, <?php echo $vegetation ?: 'null'; ?>, "<?php echo $tiffPath ?: ''; ?>");
        </script>
        <?php
        (new GlobalLayout('Affichage', ob_get_clean()))->show();
    }
}
?>
