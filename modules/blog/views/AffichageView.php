<?php

namespace blog\views;
/**
 * Classe AffichageView
 *
 * Cette classe gère l'affichage de la carte et des contrôles associés.
 */
class AffichageView
{
    /**
     * @var array $files Liste des fichiers
     */
    private $files;

    /**
     * @var FileSelectorView $fileSelectorView Vue pour la sélection des fichiers
     */
    private $fileSelectorView;

    /**
     * Constructeur de la classe AffichageView
     *
     * Initialise les fichiers et la vue de sélection des fichiers.
     *
     * @param array $files Liste des fichiers
     */
    public function __construct($files) {
        $this->files = $files;
        $this->fileSelectorView = new FileSelectorView($this->files);
    }

    /**
     * Afficher la vue
     *
     * Affiche la carte et les contrôles associés.
     *
     * @param array $filesData Données des fichiers
     * @return void
     */
    function show($filesData): void
    {
        ob_start();
        ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" ></script>
        <script src="https://unpkg.com/georaster-layer-for-leaflet/dist/georaster-layer-for-leaflet.min.js" ></script>
        <script src="https://unpkg.com/georaster" ></script>
        <script src="/_assets/scripts/affichageCarte.js" ></script> <!-- script de création de carte -->
        <link rel="stylesheet" href="/_assets/styles/affichage.css">

        <div id="mapContainer">
            <div id="map"></div>
        </div>
        <button id="toggleControls" onclick="toggleControls()">→</button>
        <div id="controls">

            <h3>Contrôles de la carte</h3>

            <!-- Sélectionner la couche de fond -->
            <div>
                <button onclick="map.switchToSatellite()">Satellite</button>
                <button onclick="map.switchToStreets()">Streets</button>
            </div>

            <!-- Sélectionner la couche -->
            <div id="layerButtonsmap"></div>

            <!-- Contrôle de l'opacité -->
            <h4>Opacité </h4>
            <input type="range" id="opacitySlidermap" min="0" max="1" step="0.1" value="1" onchange="map.updateLayerOpacity()">

            <div>
                <button onclick="map.supprimerCouche()">Supprimer la couche sélectionnée</button>
            </div>

            <!-- Bouton pour uploader un fichier GeoTIFF -->
            <div>
                <h4>Uploader un fichier GeoTIFF </h4>
                <input type="file" id="uploadGeoTiff" accept=".tif,.tiff" />
            </div>

        </div>

        <script>
            const files = <?php echo json_encode($filesData); ?>;
            const map = new MapManager(files, null, null, null, 'map');

            function toggleFileSelector() {
                const container = document.getElementById('fileSelectorContainer');
                container.style.display = container.style.display === 'none' ? 'block' : 'none';
            }

            function closeFileSelector() {
                document.getElementById('fileSelectorContainer').style.display = 'none';
            }
            function toggleControls() {
                const controls = document.getElementById('controls');
                const toggleButton = document.getElementById('toggleControls');

                if (controls.style.display === 'none' || controls.style.display === '') {
                    controls.style.display = 'block';
                    toggleButton.textContent = '×';
                } else {
                    controls.style.display = 'none';
                    toggleButton.textContent = '→';
                }
            }
        </script>

        <div class="compare-section">
            <button class="compare-button" onclick="toggleFileSelector()">Comparer la simulation aux données réelles</button>
        </div>

        <!-- Bouton pour lancer la simulation -->
        <div>
            <button onclick="showSimulationPopup()">Lancer simulation</button>
        </div>

        <!-- Pop-up pour la simulation -->
        <div id="simulationPopup" class="popup" style="display: none; position: fixed; top: 10%; left: 10%; width: 80%; height: 80%; background: #fff; overflow: auto; z-index: 1000;">
            <button onclick="closeSimulationPopup()" style="position: absolute; top: 5px; right: 5px;">&times;</button>
            <h2>Paramètres de la simulation</h2>
            <div>
                <label for="neighbours_l_min">Neighbours L Min</label>
                <input type="number" id="neighbours_l_min" name="neighbours_l_min" min="0" max="200" step="0.0001" value="10.8696">
            </div>
            <div>
                <label for="neighbours_l_0">Neighbours L 0</label>
                <input type="number" id="neighbours_l_0" name="neighbours_l_0" min="0" max="200" step="0.0001" value="85.4895">
            </div>
            <div>
                <label for="neighbours_l_max">Neighbours L Max</label>
                <input type="number" id="neighbours_l_max" name="neighbours_l_max" min="0" max="200" step="0.0001" value="185.1387">
            </div>
            <div>
                <label for="neighbours_w">Neighbours W</label>
                <input type="number" id="neighbours_w" name="neighbours_w" min="0" max="1" step="0.0001" value="0.3543">
            </div>
            <div>
                <label for="roads_l_min">Roads L Min</label>
                <input type="number" id="roads_l_min" name="roads_l_min" min="0" max="200" step="0.0001" value="6.6065">
            </div>
            <div>
                <label for="roads_l_0">Roads L 0</label>
                <input type="number" id="roads_l_0" name="roads_l_0" min="0" max="200" step="0.0001" value="73.5635">
            </div>
            <div>
                <label for="roads_l_max">Roads L Max</label>
                <input type="number" id="roads_l_max" name="roads_l_max" min="0" max="200" step="0.0001" value="99.0350">
            </div>
            <div>
                <label for="roads_w">Roads W</label>
                <input type="number" id="roads_w" name="roads_w" min="0" max="1" step="0.0001" value="0.2363">
            </div>
            <div>
                <label for="paths_l_min">Paths L Min</label>
                <input type="number" id="paths_l_min" name="paths_l_min" min="0" max="200" step="0.0001" value="94.1375">
            </div>
            <div>
                <label for="paths_l_max">Paths L Max</label>
                <input type="number" id="paths_l_max" name="paths_l_max" min="0" max="200" step="0.0001" value="95.9844">
            </div>
            <div>
                <label for="paths_w">Paths W</label>
                <input type="number" id="paths_w" name="paths_w" min="0" max="1" step="0.0001" value="0.1263">
            </div>
            <div>
                <label for="slope_l_min">Slope L Min</label>
                <input type="number" id="slope_l_min" name="slope_l_min" min="0" max="1" step="0.0001" value="0.7988">
            </div>
            <div>
                <label for="slope_l_max">Slope L Max</label>
                <input type="number" id="slope_l_max" name="slope_l_max" min="0" max="1" step="0.0001" value="0.8041">
            </div>
            <div>
                <label for="slope_w">Slope W</label>
                <input type="number" id="slope_w" name="slope_w" min="0" max="1" step="0.0001" value="0.2832">
            </div>
            <div>
                <button onclick="executeSimulation()">Exécuter simulation avec les paramètres choisis</button>
            </div>
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