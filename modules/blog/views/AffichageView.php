<?php
namespace blog\views;
class AffichageView
{
    function show($house, $road,$vegetation): void
    {
        ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script src="/_assets/scripts/loadMap.js"></script>
        <div id="map" style="width: 50%; height: 500px;"></div>
        <script>
            createMap('<?php echo $house; ?>', '<?php echo $road; ?>', '<?php echo $vegetation; ?>');
        </script><?php

        (new GlobalLayout('Affichage', ob_get_clean()))->show();
    }
}
?>