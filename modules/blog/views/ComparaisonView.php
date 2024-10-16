<?php

namespace blog\views;

class ComparaisonView
{
    public function showComparison($results)
    {
        ob_start();
        ?>
        <h1>Comparaison des polygones (Simulation vs Vérité terrain)</h1>
        <h2>Simulation</h2>
        <ul>
            <li>Aire moyenne: <?= $results['sim']['mean']; ?> m²</li>
            <li>Écart-type: <?= $results['sim']['std']; ?> m²</li>
            <li>Aire minimum: <?= $results['sim']['min']; ?> m²</li>
            <li>Aire maximum: <?= $results['sim']['max']; ?> m²</li>
        </ul>

        <h2>Vérité terrain</h2>
        <ul>
            <li>Aire moyenne: <?= $results['ver']['mean']; ?> m²</li>
            <li>Écart-type: <?= $results['ver']['std']; ?> m²</li>
            <li>Aire minimum: <?= $results['ver']['min']; ?> m²</li>
            <li>Aire maximum: <?= $results['ver']['max']; ?> m²</li>
        </ul>
        <?php
        (new GlobalLayout('comparer', ob_get_clean()))->show();
    }

}