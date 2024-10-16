<?php

namespace blog\views;

class ComparaisonView
{
    public function showComparison($results)
    {
        ob_start();
        ?>
        <h1>Comparaison des polygones (Simulation vs Vérité terrain)</h1>
        <ul>
            <li>Aire ttl simulation: <?= $results['avgAreaSim']; ?> m²</li>
            <li>Aire ttl vérité terrain: <?= $results['avgAreaVer']; ?> m²</li>
        </ul>
        <?php
        (new GlobalLayout('comparer', ob_get_clean()))->show();
    }

}