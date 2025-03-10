<?php

namespace blog\views;

/**
 * Class ComparaisonView
 * Gère l'affichage de la vue de comparaison entre les données de simulation et les données de vérité terrain.
 */
class ComparaisonView
{
    /**
     * @var HistoriqueView Instance de la classe HistoriqueView pour gérer les dossiers d'historique.
     */
    private HistoriqueView $hfolders;

    /**
     * @var int|null Identifiant de l'expérimentation (null si non défini).
     */
    private $idExp = null;

    /**
     * ComparaisonView constructor.
     *
     * @param HistoriqueView $hfolders Instance de la classe HistoriqueView.
     */
    public function __construct($hfolders)
    {
        $this->hfolders = new HistoriqueView($hfolders);
    }

    /**
     * Affiche la vue de comparaison avec les cartes, graphiques, et statistiques.
     *
     * @param array $results Résultats contenant les statistiques et erreurs pour les graphiques.
     * @param array $filesSimName Noms des fichiers GeoJSON pour la simulation.
     * @param array $filesVerName Noms des fichiers GeoJSON pour la vérité terrain.
     * @param array $fileDataSim Données GeoJSON pour la simulation.
     * @param array $fileDataVer Données GeoJSON pour la vérité terrain.
     * @param array|null $charts Liste des graphiques déjà générés (optionnel).
     */
    public function showComparison($results, $filesSimName,$filesVerName,$fileDataSim,$fileDataVer,$charts = null): void
    {
        ob_start();
        ?>
        <!-- Bibliothèques JS -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="/_assets/scripts/graphiques.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script src="https://unpkg.com/georaster-layer-for-leaflet/dist/georaster-layer-for-leaflet.min.js"></script>
        <script src="https://unpkg.com/georaster"></script>
        <script src="/_assets/scripts/affichageCarte.js"></script>
        <script src="/_assets/scripts/comparaisonCarte.js"></script>
        <link rel="stylesheet" href="/_assets/styles/comparaison.css">
        <!-- Affichage cartes -->
        <!-- Bouton pour afficher la carte Simulation -->
        <button id="showMapSimulation">Afficher la carte Simulation</button>

        <!-- Bouton pour afficher la carte Vérité terrain -->
        <button id="showMapVerite">Afficher la carte Vérité terrain</button>

        <!-- Div modale pour la carte Simulation -->
        <div id="mapModalSimulation" class="modal" style="display: none;">
            <?php
            $this->mapControls('mapSimulation');
            ?>
            <div class="modal-content">
                <span class="close" id="closeSimulation">&times;</span>
                <div id="mapSimulation" style="height: 80vh; width: 50vw;"></div> <!-- Taille ajustée -->
            </div>
        </div>

        <!-- Div modale pour la carte Vérité terrain -->
        <div id="mapModalVerite" class="modal" style="display: none;">
            <?php
            $this->mapControls('mapVerite');
            ?>
            <div class="modal-content">
                <span class="close" id="closeVerite">&times;</span>
                <div id="mapVerite" style="height: 80vh; width: 50vw;"></div> <!-- Taille ajustée -->
            </div>
        </div>

        <script>
            window.geoJsonSimData = <?php echo json_encode($fileDataSim); ?>;
            window.geoJsonSimName = <?php echo json_encode($filesSimName); ?>;

            window.geoJsonVerData = <?php echo json_encode($fileDataVer); ?>;
            window.geoJsonVerName = <?php echo json_encode($filesVerName); ?>;
        </script>


        <div class="map-container">
            <div class="map-card">
                <div id="mapSim"></div>
                <script>
                    const filesSimData = <?php echo json_encode($fileDataSim); ?>;
                    const filesSimName = <?php echo json_encode($filesSimName); ?>;
                    const mapSim = new MapManager(null, null, null, null, 'mapSim');
                    filesSimData.forEach((file, index) => {
                        mapSim.addGeoJsonLayer(file, filesSimName[index]);
                    });
                </script>
            </div>

            <div class="map-card">
                <div id="mapVer"></div>
                <script>
                    const filesVerData = <?php echo json_encode($fileDataVer); ?>;
                    const filesVerName = <?php echo json_encode($filesVerName); ?>;
                    const mapVer = new MapManager(null, null, null, null, 'mapVer');
                    filesVerData.forEach((file, index) => {
                        mapVer.addGeoJsonLayer(file, filesVerName[index]);
                    });
                </script>
            </div>
        </div>
        <!-- Statistiques des surfaces -->
        <ul>
            <table border="1">
                <tr><th>Statistique</th><th>Simulation</th><th>Vérité terrain</th><th>Erreur</th></tr>
                <?php $this->renderRow('Moyenne des surfaces (m²)', $results['graphSim'][0]['y'], $results['graphVer'][0]['y'], $results['errors'][0]['y']); ?>
                <?php $this->renderRow('Écart-type des surfaces (m²)', $results['graphSim'][1]['y'], $results['graphVer'][1]['y'], $results['errors'][1]['y']); ?>
                <?php $this->renderRow('Minimum des surfaces (m²)', $results['graphSim'][2]['y'], $results['graphVer'][2]['y'], $results['errors'][2]['y']); ?>
                <?php $this->renderRow('Maximum des surfaces (m²)', $results['graphSim'][3]['y'], $results['graphVer'][3]['y'], $results['errors'][3]['y']); ?>
            </table>
        </ul>

        <!-- Statistiques des indices de forme -->
        <ul>
            <table border="1">
                <tr><th>Statistique</th><th>Simulation</th><th>Vérité terrain</th><th>Erreur</th></tr>
                <?php $this->renderRow('Moyenne des Shape Index', $results['graphSim'][4]['y'], $results['graphVer'][4]['y'], $results['errors'][4]['y']); ?>
                <?php $this->renderRow('Écart-type des Shape Index', $results['graphSim'][5]['y'], $results['graphVer'][5]['y'], $results['errors'][5]['y']); ?>
                <?php $this->renderRow('Minimum des Shape Index', $results['graphSim'][6]['y'], $results['graphVer'][6]['y'], $results['errors'][6]['y']); ?>
                <?php $this->renderRow('Maximum des Shape Index', $results['graphSim'][7]['y'], $results['graphVer'][7]['y'], $results['errors'][7]['y']); ?>
            </table>
        </ul>

        <!-- Graphiques -->
        <script>
            // Initialisation du graphique
            window.initializeChart(
                ['Area Mean (m²)',   'Area Std(m²)','Area Min(m²)','Area Max(m²)',
                    'Shape Index Mean', 'Shape Index Std','Shape Index Min','Shape Index Max'  ],
                <?php echo json_encode(array_column($results['graphSim'], 'y')); ?>,  // Données pour la simulation
                <?php echo json_encode(array_column($results['graphVer'], 'y')); ?>   // Données pour la vérité terrain
            );
        </script>
        <!-- Options de contrôle -->
        <div style="display: none;">
            <div style="width: 30%; padding: 20px; background-color: #e0f7f4;">
                <h3>Options</h3>
                <div class="chart-controls">
                    <h4>Type de graphique</h4>
                    <div class="chart-type">
                        <label>
                            <input type="radio" name="chartType1" value="bar" checked>
                            Histogramme
                        </label>
                        <label>
                            <input type="radio" name="chartType1" value="spider">
                            Radar
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton pour ajouter un graphique -->
        <button id="addChartBtn">Ajouter un graphique</button>

        <!-- Formulaire de création de graphique -->
        <div id="chartFormContainer" style="display: none;">
            <form id="chartForm">
                <h4><label for="chartName">Nom du graphique :</label></h4>
                <input type="text" id="chartName" name="chartName" required>

                <h4>Type de graphique</h4>
                <div>
                    <label><input type="radio" name="chartType" value="bar" checked> Histogramme</label>
                    <label><input type="radio" name="chartType" value="spider"> Radar</label>
                    <label><input type="radio" name="chartType" value="pie"> Camembert</label>
                </div>
                <h4>Options de données</h4>
                <div>
                    <label>
                        <input type="checkbox" id="showSimulation" checked> Simulation
                    </label>
                    <label>
                        <input type="checkbox" id="showVeriteTerrain" checked> Vérité terrain
                    </label>
                    <label><input type="checkbox" id="normalizeCheckbox"> Normaliser les données</label>
                </div>

                <h4>Données</h4>

                <div id="chartOptions">

                    <label><input type="checkbox" id="areaMean" checked> Area Mean</label><br>
                    <label><input type="checkbox" id="areaMin" checked> Area Min</label><br>
                    <label><input type="checkbox" id="areaMax" checked> Area Max</label><br>
                    <label><input type="checkbox" id="areaStd" checked> Area Std</label><br>
                    <label><input type="checkbox" id="shapeIndexMax" > Shape Index Max</label><br>
                    <label><input type="checkbox" id="shapeIndexMean" > Shape Index Mean</label><br>
                    <label><input type="checkbox" id="shapeIndexMin" > Shape Index Min</label><br>
                    <label><input type="checkbox" id="shapeIndexStd" > Shape Index Std</label><br>
                </div>

                <button type="button" id="createChart">Créer</button>
            </form>
        </div>

        <!-- Conteneur des graphiques -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <div id="chartsData" data-charts='<?php echo json_encode($charts); ?>'></div>
        <div id="chartsContainer">
        </div>
        <!-- Passer les noms des fichiers GeoJSON au JavaScript via des attributs data-* -->
        <div id="geoJsonNames"
             data-geojson-sim='<?php echo json_encode($filesSimName, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'
             data-geojson-ver='<?php echo json_encode($filesVerName, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
        </div>

        <?php
            if($this->idExp === null){
        ?>
                <button type="submit" id="saveBtn">Sauvegarder</button>
            <?php }else{ ?>
                <button type="submit" id="updateBtn" data-id="<?= $this->idExp ?>" onclick="enregistrer()">Update</button>
                <?php
            }
                ?>

        <!-- Modale pour entrer le nom et choisir le dossier -->
        <div id="saveModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Sauvegarder l'expérimentation</h2>
                <form id="saveForm">
                    <label for="experimentName">Nom de l'expérimentation :</label>
                    <input type="text" id="experimentName" name="experimentName" required>

                    <label for="folderSelect">Choisir un dossier :</label>
                    <select id="folderSelect" name="folderSelect" required>
                        <!-- Les options seront générées ici via PHP -->
                        <?php $this->hfolders->generateFolderOptions($this->hfolders->getFiles()); ?>
                    </select>

                    <button type="submit" id="confirmSaveBtn">Confirmer</button>
                </form>
            </div>
        </div>


        <?php
        // Affichage du layout global
        (new GlobalLayout('comparer', ob_get_clean()))->show();
    }

    /**
     * Génère une ligne de tableau pour afficher une statistique.
     *
     * @param string $label Libellé de la statistique (ex. "Moyenne des surfaces").
     * @param mixed $simValue Valeur pour la simulation.
     * @param mixed $verValue Valeur pour la vérité terrain.
     * @param mixed $errorValue Valeur de l'erreur.
     */
    private function renderRow($label, $simValue, $verValue, $errorValue)
    {
        echo "<tr>
            <td>{$label}</td>
            <td>{$simValue}</td>
            <td>{$verValue}</td>
            <td>{$errorValue}</td>
          </tr>";
    }

    /**
     * Génère les contrôles de la carte (opacité, couches, etc.).
     *
     * @param string $mapId Identifiant de la carte à laquelle appliquer les contrôles.
     */
    private function mapControls($mapId) { ?>
        <div id="controls-Map" style="width: 300px; padding: 20px; background-color: #e0f7f4; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); font-family: Arial, sans-serif;">
            <h3>Contrôles de la carte</h3>

            <!-- Sélectionner la couche de fond -->
            <div>
                <button onclick="mapManagers['<?php echo htmlspecialchars($mapId) ?>'].switchToSatellite()">Satellite</button>
                <button onclick="mapManagers['<?php echo htmlspecialchars($mapId) ?>'].switchToStreets()">Streets</button>
            </div>

            <!-- Sélectionner la couche -->
            <div id="layerButtons<?php echo htmlspecialchars($mapId) ?>"></div>

            <!-- Contrôle de l'opacité -->
            <h4>Opacité :</h4>
            <input type="range" id="opacitySlider<?php echo htmlspecialchars($mapId) ?>" min="0" max="1" step="0.1" value="1" onchange="mapManagers['<?php echo htmlspecialchars($mapId) ?>'].updateLayerOpacity()">

            <div>
                <button onclick="mapManagers['<?php echo htmlspecialchars($mapId) ?>'].supprimerCouche()">Supprimer la couche sélectionnée</button>
            </div>

            <!-- Bouton pour uploader un fichier GeoTIFF -->
            <div>
                <h4>Uploader un fichier GeoTIFF :</h4>
                <input type="file" id="uploadGeoTiff" accept=".tif,.tiff" />
            </div>
        </div>

    <?php }

    /**
     * Définit l'identifiant de l'expérimentation pour les opérations de sauvegarde ou mise à jour.
     *
     * @param int $idExp Identifiant de l'expérimentation.
     */
    public function setId($idExp)
    {
       $this->idExp = $idExp;
    }

}
