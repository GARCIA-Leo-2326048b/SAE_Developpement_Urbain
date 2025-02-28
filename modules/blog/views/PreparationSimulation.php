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
