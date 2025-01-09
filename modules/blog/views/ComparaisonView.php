<?php

namespace blog\views;

class ComparaisonView
{
    private $hfolders;

    public function __construct($hfolders){
        $this->hfolders = new HistoriqueView($hfolders);
    }
    public function showComparison($results, $geoJsonSim,$geoJsonVer,$geoJsonSimName,$geoJsonVerName): void
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
        <script src="/_assets/scripts/redirect.js"></script>

        <!-- Affichage cartes -->
        <div class="map-container">
            <form method="POST" action="">
                <!-- Formulaire pour la Carte Simulation -->
                <input type="hidden" name="geoJsonName" value="<?php echo $geoJsonSimName; ?>">
                <h4><button type="submit">Carte Simulation</button></h4>
            </form>
            <!-- Formulaire pour la Carte Vérité Terrain -->
            <form method="POST" action="">
                <input type="hidden" name="geoJsonName" value="<?php echo $geoJsonVerName; ?>">
                <h4><button type="submit">Carte Vérité Terrain</button></h4>
            </form>
        </div>
        <div class="map-container">
            <div class="map-card">
                <div id="mapSim"></div>
                <script>
                    initializeMap(<?php echo $geoJsonSim ?>, '', 'mapSim');
                </script>
            </div>

            <div class="map-card">
                <div id="mapVer"></div>
                <script>
                    initializeMap(<?php echo $geoJsonVer ?>, '', 'mapVer');
                </script>
            </div>
        </div>
        <!-- Statistiques des surfaces -->
        <ul>
            <table border="1">
                <tr><th>Statistique</th><th>Simulation</th><th>Vérité terrain</th><th>Erreur</th></tr>
                <?php $this->renderRow('Moyenne des surfaces (m²)', $results['graphSim'][0]['y'], $results['graphVer'][0]['y'], $results['errors'][0]['y']); ?>
                <?php $this->renderRow('Écart-type des surfaces (m²)', $results['graphSim'][3]['y'], $results['graphVer'][3]['y'], $results['errors'][3]['y']); ?>
                <?php $this->renderRow('Minimum des surfaces (m²)', $results['graphSim'][1]['y'], $results['graphVer'][1]['y'], $results['errors'][1]['y']); ?>
                <?php $this->renderRow('Maximum des surfaces (m²)', $results['graphSim'][2]['y'], $results['graphVer'][2]['y'], $results['errors'][2]['y']); ?>
            </table>
        </ul>

        <!-- Statistiques des indices de forme -->
        <ul>
            <table border="1">
                <tr><th>Statistique</th><th>Simulation</th><th>Vérité terrain</th><th>Erreur</th></tr>
                <?php $this->renderRow('Moyenne des Shape Index', $results['graphSim'][4]['y'], $results['graphVer'][4]['y'], $results['errors'][4]['y']); ?>
                <?php $this->renderRow('Écart-type des Shape Index', $results['graphSim'][7]['y'], $results['graphVer'][7]['y'], $results['errors'][7]['y']); ?>
                <?php $this->renderRow('Minimum des Shape Index', $results['graphSim'][5]['y'], $results['graphVer'][5]['y'], $results['errors'][5]['y']); ?>
                <?php $this->renderRow('Maximum des Shape Index', $results['graphSim'][6]['y'], $results['graphVer'][6]['y'], $results['errors'][6]['y']); ?>
            </table>
        </ul>

        <!-- Graphiques -->
        <script>
            // Initialisation du graphique
            window.initializeChart(
                ['Area Mean (m²)', 'Area Min(m²)', 'Area Max(m²)', 'Area Std(m²)',
                    "Shape Index Max", "Shape Index Min", "Shape Index Mean", "Shape Index Std"],
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
        <div id="chartsContainer"></div>
        <!-- Passer les noms des fichiers GeoJSON au JavaScript via des attributs data-* -->
        <div id="geoJsonNames"
             data-geojson-sim="<?php echo $geoJsonSimName; ?>"
             data-geojson-ver="<?php echo $geoJsonVerName; ?>">
        </div>
        <button type="submit" id="saveBtn">Sauvegarder</button>

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

    private function renderRow($label, $simValue, $verValue, $errorValue)
    {
        echo "<tr>
            <td>{$label}</td>
            <td>{$simValue}</td>
            <td>{$verValue}</td>
            <td>{$errorValue}</td>
          </tr>";
    }
}
