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

            <!-- Bouton pour uploader un fichier GeoTIFF -->
            <div>
                <h4>Uploader un fichier GeoTIFF :</h4>
                <input type="file" id="uploadGeoTiff" accept=".tif,.tiff" />
            </div>
        </div>

        <script>
            // Initialisation de la carte avec les couches GeoJSON et GeoTIFF
            initializeMap(<?php echo $house ?: 'null'; ?>, <?php echo $road ?: 'null'; ?>, "<?php echo $tiffPath ?: ''; ?>");

            // Gestion de l'upload et affichage du fichier GeoTIFF
            document.getElementById('uploadGeoTiff').addEventListener('change', async (event) => {
                const file = event.target.files[0];
                if (file) {
                    if (tiffLayer) {
                        map.removeLayer(tiffLayer);
                    }
                    const reader = new FileReader();
                    reader.onload = async (e) => {
                        const arrayBuffer = e.target.result;
                        const georaster = await parseGeoraster(arrayBuffer); // Utilisation de georaster pour lire le fichier
                        tiffLayer = new GeoRasterLayer({
                            georaster: georaster,
                            opacity: 1,
                            resolution: 64
                        }).addTo(map);
                        //recuperer le nom du fichier
                        const layerName = file.name; // Nom de la couche basé sur le nom du fichier
                        // Ajouter la couche à overlayMaps avec le nom de la couche
                        overlayMaps[layerName] = tiffLayer;

                        // Met à jour le contrôle des couches
                        updateLayerControl();

                        // Met à jour les boutons dynamiquement
                        updateLayerButtons();
                    };
                    reader.readAsArrayBuffer(file);
                }
            });

        </script>
        <div class="compare-section" >
            <button class="compare-button" onclick="compare()" >Comparer</button>
        </div>

        <?php
        (new GlobalLayout('Affichage', ob_get_clean()))->show();
    }
}
?>