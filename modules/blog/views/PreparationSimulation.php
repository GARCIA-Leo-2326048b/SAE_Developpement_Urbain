<?php

namespace blog\views;

/**
 * Classe PreparationSimulation
 *
 * Cette classe gère l'affichage de la préparation de la simulation.
 */
class PreparationSimulation
{
    /**
     * @var array $files Liste des fichiers
     */
    private $files;

    /**
     * Constructeur de la classe PreparationSimulation
     *
     * Initialise la vue avec la liste des fichiers.
     *
     * @param array $files Liste des fichiers
     */
    public function __construct($files) {
        $this->files = $files;
    }

    /**
     * Afficher la préparation de la simulation
     *
     * Affiche le contenu de la préparation de la simulation, y compris la sélection des fichiers et les actions associées.
     *
     * @return void
     */
    public function show(): void {
        ob_start(); ?>
        <div class="container-content">
        <div class="main-content">
            <?php
            if(isset($_SESSION['current_project_id'])){


                (new FileSelectorView($this->files))->show();
                ?>

                <!-- Zone de sélection des fichiers -->
                <div id="file-selection">
                    <h2>Fichiers sélectionnés</h2>
                    <ul id="selected-files-list"></ul>
                    <button id="simulate-button" onclick="simulateSelectedFiles()" disabled>Simuler les fichiers sélectionnés</button>
                </div>

                <!-- Pop-up pour la simulation -->
                <div id="simulationParamPopup" class="popup">
                    <button class="close-btn" onclick="closeParamPop()">&times;</button>
                    <h2>Paramètres de la simulation</h2>
                    <div class="popup-field">
                        <label for="starting_date">Starting Date</label>
                        <input type="number" id="starting_date" name="starting_date" min="0" max="200" step="0.0001" value="1994">
                    </div>
                    <div class="popup-field">
                        <label for="validation_date">Validation Date</label>
                        <input type="number" id="validation_date" name="validation_date" min="0" max="200" step="0.0001" value="2002">
                    </div>
                    <div class="popup-field">
                        <label for="building_delta">Building Delta</label>
                        <input type="number" id="building_delta" name="building_delta" min="0" max="200" step="0.0001" value="22">
                    </div>
                    <div class="popup-field">
                        <label for="neighbours_l_min">Neighbours L Min</label>
                        <input type="number" id="neighbours_l_min" name="neighbours_l_min" min="0" max="200" step="0.0001" value="10.8696">
                    </div>
                    <div class="popup-field">
                        <label for="neighbours_l_0">Neighbours L 0</label>
                        <input type="number" id="neighbours_l_0" name="neighbours_l_0" min="0" max="200" step="0.0001" value="85.4895">
                    </div>
                    <div class="popup-field">
                        <label for="neighbours_l_max">Neighbours L Max</label>
                        <input type="number" id="neighbours_l_max" name="neighbours_l_max" min="0" max="200" step="0.0001" value="185.1387">
                    </div>
                    <div class="popup-field">
                        <label for="neighbours_w">Neighbours W</label>
                        <input type="number" id="neighbours_w" name="neighbours_w" min="0" max="1" step="0.0001" value="0.3543">
                    </div>
                    <div class="popup-field">
                        <label for="roads_l_min">Roads L Min</label>
                        <input type="number" id="roads_l_min" name="roads_l_min" min="0" max="200" step="0.0001" value="6.6065">
                    </div>
                    <div class="popup-field">
                        <label for="roads_l_0">Roads L 0</label>
                        <input type="number" id="roads_l_0" name="roads_l_0" min="0" max="200" step="0.0001" value="73.5635">
                    </div>
                    <div class="popup-field">
                        <label for="roads_l_max">Roads L Max</label>
                        <input type="number" id="roads_l_max" name="roads_l_max" min="0" max="200" step="0.0001" value="99.0350">
                    </div>
                    <div class="popup-field">
                        <label for="roads_w">Roads W</label>
                        <input type="number" id="roads_w" name="roads_w" min="0" max="1" step="0.0001" value="0.2363">
                    </div>
                    <div class="popup-field">
                        <label for="paths_l_min">Paths L Min</label>
                        <input type="number" id="paths_l_min" name="paths_l_min" min="0" max="200" step="0.0001" value="94.1375">
                    </div>
                    <div class="popup-field">
                        <label for="paths_l_max">Paths L Max</label>
                        <input type="number" id="paths_l_max" name="paths_l_max" min="0" max="200" step="0.0001" value="95.9844">
                    </div>
                    <div class="popup-field">
                        <label for="paths_w">Paths W</label>
                        <input type="number" id="paths_w" name="paths_w" min="0" max="1" step="0.0001" value="0.1263">
                    </div>
                    <div class="popup-field">
                        <label for="slope_l_min">Slope L Min</label>
                        <input type="number" id="slope_l_min" name="slope_l_min" min="0" max="1" step="0.0001" value="0.7988">
                    </div>
                    <div class="popup-field">
                        <label for="slope_l_max">Slope L Max</label>
                        <input type="number" id="slope_l_max" name="slope_l_max" min="0" max="1" step="0.0001" value="0.8041">
                    </div>
                    <div class="popup-field">
                        <label for="slope_w">Slope W</label>
                        <input type="number" id="slope_w" name="slope_w" min="0" max="1" step="0.0001" value="0.2832">
                    </div>
                    <div class="popup-field">
                        <button class="popup-submit" onclick="executeSimulationP()">Exécuter la simulation</button>
                    </div>
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

        </div>
        <?php
                } else { ?>
            <p>Veuillez Choisir ou crée un projet</p>
        <?php
        }
        (new GlobalLayout('Simulation', ob_get_clean()))->show();
    }
}
