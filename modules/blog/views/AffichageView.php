<?php
namespace blog\views;
class AffichageView
{
    function show($house=null, $road=null,$vegetation=null): void
    {
        ob_start();
        ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script src="/_assets/scripts/loadMap.js"></script>
        <div id="map" style="width: 100%; height: 500px;"></div>
        <script>
            createMap(<?php echo $house?:null; ?>, <?php echo $road?:null; ?>,<?php echo $vegetation ?: null ; ?>);
        </script><?php

        (new GlobalLayout('Affichage', ob_get_clean()))->show();
    }
}
?>