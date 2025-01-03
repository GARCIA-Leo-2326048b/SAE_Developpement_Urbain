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

        <h1>Comparaison des polygones (Simulation vs Vérité terrain)</h1>

        <!-- Statistiques des surfaces -->
        <h2>Statistiques des surfaces (en m²)</h2>
        <ul>
            <li><strong>Simulation :</strong>
                <ul>
                    <li>Moyenne: <?= $results['graphSim'][0]['y']; ?> m²</li>
                    <li>Écart-type: <?= $results['graphSim'][3]['y']; ?> m²</li>
                    <li>Minimum: <?= $results['graphSim'][1]['y']; ?> m²</li>
                    <li>Maximum: <?= $results['graphSim'][2]['y']; ?> m²</li>
                </ul>
            </li>
            <li><strong>Vérité terrain :</strong>
                <ul>
                    <li>Moyenne: <?= $results['graphVer'][0]['y']; ?> m²</li>
                    <li>Écart-type: <?= $results['graphVer'][3]['y']; ?> m²</li>
                    <li>Minimum: <?= $results['graphVer'][1]['y']; ?> m²</li>
                    <li>Maximum: <?= $results['graphVer'][2]['y']; ?> m²</li>
                </ul>
            </li>
        </ul>

        <!-- Statistiques des indices de forme -->
        <h2>Statistiques des indices de forme (Shape Index)</h2>
        <ul>
            <li><strong>Simulation :</strong>
                <ul>
                    <li>Moyenne: <?= $results['graphSim'][4]['y']; ?></li>
                    <li>Écart-type: <?= $results['graphSim'][7]['y']; ?></li>
                    <li>Minimum: <?= $results['graphSim'][5]['y']; ?></li>
                    <li>Maximum: <?= $results['graphSim'][6]['y']; ?></li>
                </ul>
            </li>
            <li><strong>Vérité terrain :</strong>
                <ul>
                    <li>Moyenne: <?= $results['graphVer'][4]['y']; ?></li>
                    <li>Écart-type: <?= $results['graphVer'][7]['y']; ?></li>
                    <li>Minimum: <?= $results['graphVer'][5]['y']; ?></li>
                    <li>Maximum: <?= $results['graphVer'][6]['y']; ?></li>
                </ul>
            </li>
        </ul>

        <!-- Graphiques -->
        <div>
            <canvas id="diagrammeBarre"></canvas>
            <canvas id="spiderChart" style="display:none;"></canvas>
        </div>

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
        <div style="display: flex;">
            <div style="width: 30%; padding: 20px; background-color: #e0f7f4;">
                <h3>Options</h3>
                <div class="chart-controls">
                    <h4>Type de graphique</h4>
                    <div class="chart-type">
                        <label>
                            <input type="radio" name="chartType" value="bar" checked>
                            Histogramme
                        </label>
                        <label>
                            <input type="radio" name="chartType" value="spider">
                            Radar
                        </label>
                    </div>

                    <form id="optionsForm">
                        <label><input type="checkbox" id="areaMax" checked> Area Max</label><br>
                        <label><input type="checkbox" id="areaMean" checked> Area Mean</label><br>
                        <label><input type="checkbox" id="areaMin" checked> Area Min</label><br>
                        <label><input type="checkbox" id="areaStd" checked> Area Std</label><br>
                        <label><input type="checkbox" id="shapeIndexMax" checked> Shape Index Max</label><br>
                        <label><input type="checkbox" id="shapeIndexMean" checked> Shape Index Mean</label><br>
                        <label><input type="checkbox" id="shapeIndexMin" checked> Shape Index Min</label><br>
                        <label><input type="checkbox" id="shapeIndexStd" checked> Shape Index Std</label><br>
                    </form>
                </div>
            </div>
        </div>

        <?php
        // Affichage du layout global
        (new GlobalLayout('comparer', ob_get_clean()))->show();
    }
}

