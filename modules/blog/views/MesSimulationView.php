<?php

namespace blog\views;

class MesSimulationView
{
    private $historique;
    private $projects;

    public function __construct($project){
        $this->projects = $project;
        // Utiliser SingletonModel pour obtenir la connexion à la base de données
        $this->historique = \blog\views\HistoriqueView::getInstance($project);
    }


    public function show()
    { ob_start();?>
        <div class="switcher-container">
            <div class="tabs">
                <button id="simulation-tab" class="tab-button active">Simulation</button>
                <button id="experience-tab" class="tab-button">Expériences</button>
            </div>
            <div class="tabs-content">
                <div id="simulation-content" class="tab-content active">
                    <h2>Historique des Simulations</h2>
                    <?php
                        $this->historique->render();
                    if ($this->projects == null){

                    ?>
                    <!-- Contenu de l'historique des simulations -->
                    <p>Aucune simulation enregistrée pour l'instant.</p>
                    <?php
                    }
                    ?>
                </div>
                <div id="experience-content" class="tab-content">
                    <h2 style="display: flex; align-items: center; justify-content: space-between;">
                        Historique des Expériences
                        <!-- Bouton pour ajouter un dossier -->
                        <button onclick="createNewFolder()" style="font-size: 1.2em; padding: 5px 10px; cursor: pointer;">+</button>
                    </h2>

                    <!-- Contenu de l'historique -->
                    <div id="folder-history">
                        <?php
                        $folderHistory = HistoriqueView::getInstance([]); // Initialise l'historique
                        $folderHistory->render(); // Affiche l'arborescence des dossiers
                        ?>
                    </div>

                    <!-- Formulaire pour Créer un Dossier (masqué par défaut) -->
                    <form id="createFolderForm" method="POST" style="display: none; margin-top: 20px; border: 1px solid #ccc; padding: 15px; border-radius: 5px;">
                        <button type="button" onclick="closeCreateFolderForm()" style="position: absolute; top: 5px; right: 5px; background: none; border: none; font-size: 1.5em; cursor: pointer;">&times;</button>
                        <h3>Créer un Dossier</h3>

                        <!-- Nom du dossier -->
                        <label for="dossier_name">Nom du dossier :</label>
                        <input type="text" id="dossier_name" name="dossier_name" required style="margin-bottom: 10px;">
                        <br>

                        <!-- Dossier parent -->
                        <label for="dossier_parent1">Sélectionnez le dossier parent :</label>
                        <select class="folder-selector" id="dossier_parent1" name="dossier_parent" style="margin-bottom: 10px;">
                            <?php $folderHistory->generateFolderOptions($folderHistory->getFiles()); ?>
                        </select>
                        <br>

                        <!-- Bouton de création -->
                        <button type="button" id="createFolderButton" style="background-color: #28a745; color: white; padding: 5px 10px; border: none; cursor: pointer;">Créer</button>
                    </form>
                </div>

            </div>
        </div>


        <!-- Pop-up pour les actions selon le mode -->
        <div id="popup" class="popup" style="display: none;">
            <div class="popup-content">
                <h2 id="popup-file-name">File</h2>
                <button class="popup-button" id="actionButton" onclick="performAction()">Simuler</button>
                <button class="popup-button" onclick="deleteFile()"><i class="fas fa-trash-alt"></i> </button>
                <button class="popup-close" onclick="closePopup()"><i class="fas fa-window-close"></i></button>
            </div>
        </div>

    <?php (new GlobalLayout('Accueil', ob_get_clean()))->show();
    }

}
