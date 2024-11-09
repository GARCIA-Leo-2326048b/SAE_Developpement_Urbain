<?php
namespace blog\views;
class AffichageView
{
    function show($house = null, $road = null, $vegetation = null): void
    {
        ob_start();
        ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script src="/_assets/scripts/loadMap.js"></script>
        <div id="map" style="width: 100%; height: 500px;"></div>

        <div>
            <!-- Boutons pour changer le fond de carte -->
            <button onclick="switchToSatellite()">Satellite</button>
            <button onclick="switchToStreets()">Streets</button>
        </div>

        <!-- Boutons pour sélectionner la couche pour ajuster l'opacité -->
        <div>
            <p>Sélectionnez la couche pour ajuster l'opacité :</p>
            <button onclick="selectLayer('house')">Maisons</button>
            <button onclick="selectLayer('road')">Routes</button>
            <button onclick="selectLayer('vegetation')">Végétation</button>
            <br><br>
            <label for="opacitySlider">Opacité de la couche :</label>
            <input type="range" id="opacitySlider" min="0" max="1" step="0.1" value="1" oninput="updateLayerOpacity()">
        </div>

        <script>
            createMap(<?php echo $house ?: 'null'; ?>, <?php echo $road ?: 'null'; ?>, <?php echo $vegetation ?: 'null'; ?>);
        </script>
        <?php
        (new GlobalLayout('Affichage', ob_get_clean()))->show();
    }
}
?>
