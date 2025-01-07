<?php

namespace blog\views;

class ComparaisonView
{
    public function showComparison($results): void
    {
        ob_start();
        ?>
        <!-- Bibliothèques JS -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="/_assets/scripts/graphiques.js"></script>

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
                <label for="chartName">Nom du graphique :</label>
                <input type="text" id="chartName" name="chartName" required>

                <h4>Type de graphique</h4>
                <div>
                    <label><input type="radio" name="chartType" value="bar" checked> Histogramme</label>
                    <label><input type="radio" name="chartType" value="spider"> Radar</label>
                </div>

                <h4>Options de données</h4>
                <label><input type="checkbox" id="normalizeCheckbox"> Normaliser les données</label>
                <div id="chartOptions">

                    <label><input type="checkbox" id="areaMean" checked> Area Mean</label><br>
                    <label><input type="checkbox" id="areaMin" checked> Area Min</label><br>
                    <label><input type="checkbox" id="areaMax" checked> Area Max</label><br>
                    <label><input type="checkbox" id="areaStd" checked> Area Std</label><br>
                    <label><input type="checkbox" id="shapeIndexMax" checked> Shape Index Max</label><br>
                    <label><input type="checkbox" id="shapeIndexMean" checked> Shape Index Mean</label><br>
                    <label><input type="checkbox" id="shapeIndexMin" checked> Shape Index Min</label><br>
                    <label><input type="checkbox" id="shapeIndexStd" checked> Shape Index Std</label><br>
                </div>

                <button type="button" id="createChart">Créer</button>
            </form>
        </div>

        <!-- Conteneur des graphiques -->
        <div id="chartsContainer"></div>

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
