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
                        <button onclick="location.href='?action=new_simulation'" style="font-size: 1.2em; padding: 5px 10px; cursor: pointer;">+</button>
                    </h2>

                    <!-- Contenu de l'historique -->
                    <div id="exphistory">
                        <?php
                        $historyId = 'history-' . uniqid();
                        $exp = new \blog\views\HistoriqueView($this->experimentations);
                        $exp->render($historyId);
                        ?>
                    </div>

                </div>

            </div>
        </div>


        <!-- Pop-up pour les actions selon le mode -->
        <div id="popup2" class="popup" style="display: none;">
            <div class="popup-content">
                <h2 id="popup-file-nameS">File</h2>
                <button class="popup-button" id="actionButton" onclick="performAction()">Afficher</button>
                <button class="popup-button" onclick="deleteFile()"><i class="fas fa-trash-alt"></i> </button>
                <button class="popup-close" onclick="closePopup(this)"><i class="fas fa-window-close"></i></button>
            </div>
        </div>

        <div id="popupExp" class="popup" style="display: none;">
            <div class="popup-content">
                <h2 id="popup-file-nameExp">File</h2>
                <button class="popup-button" id="actionButton" onclick="reloadExp()">Continuer</button>
                <button class="popup-button" onclick="deleteFileExp()"><i class="fas fa-trash-alt"></i> </button>
                <button class="popup-close" onclick="closePopup(this)"><i class="fas fa-window-close"></i></button>
            </div>
        </div>


        <?php (new GlobalLayout('Accueil', ob_get_clean()))->show();
    }

}
