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
                    <h2>Historique des Expériences</h2>
                    <!-- Contenu de l'historique des expériences -->
                    <p>Aucune expérience enregistrée pour l'instant.</p>
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
