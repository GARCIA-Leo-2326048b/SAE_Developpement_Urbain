<?php

namespace blog\views;

class ComparaisonView
{
    public function showComparison($results)
    {

        ob_start();
        ?>
        <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
        <script src="/_assets/scripts/chart.js"></script>

        <h1>Comparaison des polygones (Simulation vs Vérité terrain)</h1>

        <h2>Statistiques des surfaces (en m²)</h2>
        <ul>
            <li><strong>Simulation :</strong></li>
            <ul>
                <li>Moyenne: <?= $results['areaStatsSim']['mean']; ?> m²</li>
                <li>Écart-type: <?= $results['areaStatsSim']['std']; ?> m²</li>
                <li>Minimum: <?= $results['areaStatsSim']['min']; ?> m²</li>
                <li>Maximum: <?= $results['areaStatsSim']['max']; ?> m²</li>
            </ul>
            <li><strong>Vérité terrain :</strong></li>
            <ul>
                <li>Moyenne: <?= $results['areaStatsVer']['mean']; ?> m²</li>
                <li>Écart-type: <?= $results['areaStatsVer']['std']; ?> m²</li>
                <li>Minimum: <?= $results['areaStatsVer']['min']; ?> m²</li>
                <li>Maximum: <?= $results['areaStatsVer']['max']; ?> m²</li>
            </ul>
        </ul>

        <h2>Statistiques des indices de forme (Shape Index)</h2>
        <ul>
            <li><strong>Simulation :</strong></li>
            <ul>
                <li>Moyenne: <?= $results['shapeIndexStatsSim']['mean']; ?></li>
                <li>Écart-type: <?= $results['shapeIndexStatsSim']['std']; ?></li>
                <li>Minimum: <?= $results['shapeIndexStatsSim']['min']; ?></li>
                <li>Maximum: <?= $results['shapeIndexStatsSim']['max']; ?></li>
            </ul>
            <li><strong>Vérité terrain :</strong></li>
            <ul>
                <li>Moyenne: <?= $results['shapeIndexStatsVer']['mean']; ?></li>
                <li>Écart-type: <?= $results['shapeIndexStatsVer']['std']; ?></li>
                <li>Minimum: <?= $results['shapeIndexStatsVer']['min']; ?></li>
                <li>Maximum: <?= $results['shapeIndexStatsVer']['max']; ?></li>
            </ul>
        </ul>

        <script>
            // Passer les données PHP au fichier JS via des variables JavaScript
            var DonneesSimulees = <?php echo json_encode($results['graph']['graphSim'], JSON_NUMERIC_CHECK); ?>;
            var DonneesVerite = <?php echo json_encode($results['graph']['graphVer'], JSON_NUMERIC_CHECK); ?>;
        </script>
        <ul>
            <div id="chartContainer" style="height: 370px; width: 100%;"></div>
        </ul>

        <?php
        (new GlobalLayout('comparer', ob_get_clean()))->show();
    }
}
