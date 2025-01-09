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
        <div id="mode-switch">
            <button class="buttons" onclick="switchMode('simulation')">Mode Simulation</button>
            <button class="buttons" onclick="switchMode('comparaison')">Mode Comparaison</button>
        </div>
        <div class="container-content">
        <div class="main-content">
            <!-- Barre de défilement pour l'historique -->
            <aside id="history" >
                <h2>Historique des fichiers</h2>
                    <?php HistoriqueView::getInstance($this->files)->render(); ?>
            </aside>

            <section id="import">
                <?php
                $formView = new FormView();
                $formView->renderAllForms();
                ?>

                <button onclick="createNewFolder()"><i class="fas fa-folder-plus"></i>  Nouveau dossier</button>


                <!-- Formulaire pour Créer un Dossier -->
                <?php
                $folderHistory = HistoriqueView::getInstance([]);
                ?>
                <form id="createFolderForm" method="POST" style="display: none; position: relative;">
                    <button type="button" onclick="closeCreateFolderForm()" style="position: absolute; top: 5px; right: 5px; background: none; border: none; cursor: pointer;">&times;</button>
                    <h3>Créer un Dossier</h3>
                    <label for="dossier_name">Nom du dossier :</label>
                    <input type="text" id="dossier_name" name="dossier_name" required>
                    <br><br>
                    <label for="dossier_parent1">Sélectionnez le dossier parent :</label>
                    <select class="folder-selector" id="dossier_parent1" name="dossier_parent">
                        <?php $folderHistory->generateFolderOptions($folderHistory->getFiles()); ?>
                    </select>
                    <br><br>
                    <button type="button" id="createFolderButton">Créer</button>
                </form>

                <!-- Menu contextuel pour la suppression -->
                <div id="context-menu" class="context-menu" style="display: none;">
                    <ul>
                        <li>
                            <button onclick="deleteFolder()">Supprimer le dossier</button>
                        </li>
                    </ul>
                </div>

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
                        <button class="popup-close" onclick="closePopup()"><i class="fas fa-window-close"></i></button>
                    </div>
                </div>

                <!-- Bouton Comparer (visible uniquement en mode Comparaison) -->
                <div class="compare-section" style="display: none;">
                    <button class="compare-button" onclick="compare()" disabled>Comparer</button>
                </div>
        </div>

        </div>
        <?php
        (new GlobalLayout('Simulation', ob_get_clean()))->show();
    }
}
