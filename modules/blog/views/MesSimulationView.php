<?php

namespace blog\views;

class MesSimulationView
{

    private $projects;
    private $experimentations;

    public function __construct($project,$experimentation){
        $this->projects = $project;
        // Utiliser SingletonModel pour obtenir la connexion à la base de données
        $this->experimentations = $experimentation;
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
                    if ($this->projects != null) {
                        $historyId = 'history-' . uniqid();
                        $historique = new \blog\views\HistoriqueView($this->projects);
                        $historique->render($historyId);
                    }else{
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
                        $historyId = 'history-' . uniqid();
                        $exp = new \blog\views\HistoriqueView($this->experimentations);
                        $exp->render($historyId);
                        ?>
                    </div>

                    <div id="popupExp" class="popup" style="display: none;">
                        <div class="popup-content">
                            <h2 id="popup-file-name">File</h2>
                            <button class="popup-close" onclick="closePopup()"><i class="fas fa-window-close"></i></button>
                        </div>
                    </div>

                    <!-- Formulaire pour Créer un Dossier -->
                    <form id="createFolderFormExp" method="POST" style="display: none; position: relative;">
                        <button type="button" onclick="closeCreateFolderForm()" style="position: absolute; top: 5px; right: 5px; background: none; border: none; cursor: pointer;">&times;</button>
                        <h3>Créer un Dossier</h3>
                        <label for="dossier_name">Nom du dossier :</label>
                        <input type="text" id="dossier_name" name="dossier_name" required>
                        <br><br>
                        <label for="dossier_parent1">Sélectionnez le dossier parent :</label>
                        <select class="folder-selector" id="dossier_parent1" name="dossier_parent">
                            <?php $exp->generateFolderOptions($this->projects); ?>
                        </select>
                        <br><br>
                        <button type="button" id="createFolderButton" class="buttons">Créer</button>
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
