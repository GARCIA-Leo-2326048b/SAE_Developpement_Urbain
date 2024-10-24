<?php

namespace blog\views;

class AffichageTiffView
{
    public function show($tiffPath) {
        ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script src="https://unpkg.com/georaster-layer-for-leaflet/dist/georaster-layer-for-leaflet.min.js"></script>
        <script src="https://unpkg.com/georaster"></script>
        <script src="/_assets/scripts/tiffMap.js"></script>
        <div id="map" style="width: 100%; height: 500px;"></div>
        <div>
            <!-- Ajout de boutons pour changer le fond de carte -->
            <button onclick="switchToSatellite()">Satellite</button>
            <button onclick="switchToStreets()">Streets</button>
        </div>
        <script>
            // Appeler la fonction 'displayGeoTIFF' avec l'URL du fichier TIFF comme argument
            initializeMap("<?php echo $tiffPath; ?>");
        </script>
        <?php
        (new GlobalLayout('AffichageTiff', ob_get_clean()))->show();
    }

}