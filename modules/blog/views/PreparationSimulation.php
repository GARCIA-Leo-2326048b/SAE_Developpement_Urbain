<?php

namespace blog\views;

class PreparationSimulation
{
    private $files;
    public function __construct($files) {
        $this->files = $files;
    }

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
                        <button class="popup-button" id="actionButton" onclick="performAction()">Simuler</button>
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
