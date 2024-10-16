<?php

namespace blog\views;

class AffichageTiffView
{
    public function show($tiffPath) {
        ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet-geotiff.js"></script>
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <div id="map" style="width: 100%; height: 100vh;"></div>
        <script>
            // Appeler la fonction 'displayGeoTIFF' avec l'URL du fichier TIFF comme argument
            displayGeoTIFF("<?php echo $tiffPath; ?>");
        </script>
        <?php
        (new GlobalLayout('AffichageTiff', ob_get_clean()))->show();
    }

}