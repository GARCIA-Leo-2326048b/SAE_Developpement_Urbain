<?php

namespace blog\views;

class MesSimulationView
{
    private $historique;

    public function __construct($project){
        // Utiliser SingletonModel pour obtenir la connexion à la base de données
        $this->historique = \blog\views\HistoriqueView::getInstance($project);
    }


    public function show()
    { ?>
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
                    ?>
                    <!-- Contenu de l'historique des simulations -->
                    <p>Aucune simulation enregistrée pour l'instant.</p>
                </div>
                <div id="experience-content" class="tab-content">
                    <h2>Historique des Expériences</h2>
                    <!-- Contenu de l'historique des expériences -->
                    <p>Aucune expérience enregistrée pour l'instant.</p>
                </div>
            </div>
        </div>

        <style>
            .switcher-container {
                width: 80%;
                margin: auto;
                padding: 20px;
                border: 1px solid #ccc;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                background-color: #f9f9f9;
            }

            .tabs {
                display: flex;
                justify-content: space-around;
                margin-bottom: 20px;
            }

            .tab-button {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                background-color: #e0e0e0;
                font-size: 16px;
                transition: background-color 0.3s ease;
            }

            .tab-button.active {
                background-color: #957743;
                color: white;
            }

            .tabs-content {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
            }
        </style>

        <script>
            $(document).ready(function () {
                // Tab switching logic
                $('.tab-button').on('click', function () {
                    const targetId = $(this).attr('id').replace('-tab', '-content');

                    // Remove active state from buttons
                    $('.tab-button').removeClass('active');
                    $(this).addClass('active');

                    // Switch content
                    $('.tab-content').removeClass('active');
                    $('#' + targetId).addClass('active');
                });
            });
        </script>
    <?php (new GlobalLayout('Accueil', ob_get_clean()))->show(); }

}
