<?php

namespace blog\views;

class ComparaisonView
{
    public function showComparison($results): void
    {

        ob_start();
        ?>
        <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="/_assets/scripts/graphiques.js"></script>

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

        <ul>
            <div id="diagrammeBarre" style="height: 370px; width: 100%;"></div>
        </ul>

        <ul>
            <div>
                <canvas id="diagrammeBarre2"></canvas>
            </div>
        </ul>

        <ul>
            <div>
                <canvas id="spiderChart"></canvas>
            </div>
        </ul>

        <script>
            // Appel de la fonction JavaScript pour le diagramme de barre
            diagrammeBarre(
                <?php echo json_encode($results['graph']['graphSim'])?>,
                <?php echo json_encode($results['graph']['graphVer'])?>
            );

            // Appel de la fonction graph() avec les labels et les données passées
            diagrammeBarre2(
                ['Moyenne', 'Minimum', 'Maximum', 'Écart-type'],  // Les labels que tu veux afficher
                <?php echo json_encode(array_column($results['graph']['graphSim'], 'y')) ?>,  // Données pour la simulation
                <?php echo json_encode(array_column($results['graph']['graphVer'], 'y')) ?>   // Données pour la vérité terrain
            );
            spiderChart(['Moyenne', 'Minimum', 'Maximum', 'Écart-type'],  // Les labels que tu veux afficher
                <?php echo json_encode(array_column($results['graph']['graphSim'], 'y')) ?>,  // Données pour la simulation
                <?php echo json_encode(array_column($results['graph']['graphVer'], 'y')) ?>   // Données pour la vérité terrain
            );
        </script>

        <?php
        (new GlobalLayout('comparer', ob_get_clean()))->show();
    }
}
