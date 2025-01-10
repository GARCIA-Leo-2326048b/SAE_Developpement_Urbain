<?php

namespace blog\views;
class AffichageView
{
    private $files;

    private $fileSelectorView;
    public function __construct($files) {
        $this->files = $files;
        $this->fileSelectorView = new FileSelectorView($this->files);
    }

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
            initializeMap();
            ajouterGeoJson(<?php echo $house ?: 'null'; ?>, '<?php echo filter_input(INPUT_GET, 'house') ?: 'null'; ?>');
            ajouterGeoJson(<?php echo $road ?: 'null'; ?>, '<?php echo filter_input(INPUT_GET, 'road') ?: 'null'; ?>');

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


            function toggleFileSelector() {
                const container = document.getElementById('fileSelectorContainer');
                container.style.display = container.style.display === 'none' ? 'block' : 'none';
            }

            function closeFileSelector() {
                document.getElementById('fileSelectorContainer').style.display = 'none';
            }

        </script>

        <div class="compare-section">
            <button class="compare-button" onclick="toggleFileSelector()">Comparer la simulation aux données réelles</button>
        </div>

        <!-- Conteneur caché pour FileSelectorView -->
        <div id="fileSelectorContainer" style="display: none; position: fixed; top: 10%; left: 10%; width: 80%; height: 80%; background: #fff; overflow: auto; z-index: 1000;">
            <button onclick="closeFileSelector()" style="position: absolute; top: 5px; right: 5px;">&times;</button>
            <?php $this->fileSelectorView->show(); ?>

            <!-- Zone de sélection des fichiers -->
            <div id="file-selection">
                <h2>Fichiers sélectionnés</h2>
                <ul id="selected-files-list"></ul>
                <button id="compare-button" onclick="compareSelectedFiles()" disabled>Comparer au fichier sélectionné</button>
            </div>

            <!-- Pop-up pour les actions selon le mode -->
            <div id="popup" class="popup" style="display: none;">
                <div class="popup-content">
                    <h2 id="popup-file-name">File</h2>
                    <button class="popup-button" id="actionButton" onclick="">Comparer</button>
                    <button class="popup-button" id="actionButton" onclick="addToSelection()">Ajouter à la selection</button>
                    <button class="popup-button" id="actionButton" onclick="removeFromSelection()">Retirer de la selection</button>
                    <button class="popup-button" onclick="deleteFile()"><i class="fas fa-trash-alt"></i> </button>
                    <button class="popup-close" onclick="closePopup(this)"><i class="fas fa-window-close"></i></button>
                </div>
            </div>
        </div>

        <?php
        (new GlobalLayout('Affichage', ob_get_clean()))->show();
    }
}
?>