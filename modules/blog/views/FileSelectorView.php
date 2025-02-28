<?php

namespace blog\views;

/**
 * Classe FileSelectorView
 *
 * Cette classe gère l'affichage de la vue de sélection des fichiers.
 */
class FileSelectorView
{
    /**
     * @var array $files Liste des fichiers
     */
    private $files;

    /**
     * @var HistoriqueView $historiqueView Vue pour l'historique des fichiers
     */
    private $historiqueView;

    /**
     * Constructeur de la classe FileSelectorView
     *
     * Initialise la vue avec la liste des fichiers et l'historique des fichiers.
     *
     * @param array $files Liste des fichiers
     */
    public function __construct($files) {
        $this->files = $files;
        $this->historiqueView = new HistoriqueView($this->files);
    }

    /**
     * Afficher la vue de sélection des fichiers
     *
     * Affiche l'historique des fichiers, le formulaire d'importation et le formulaire de création de dossier.
     *
     * @return void
     */
    public function show() : void{?>

        <div class="container-content">
        <div class="main-content">
            <!-- Barre de défilement pour l'historique -->
            <aside id="history" >
                <h2>Historique des fichiers</h2>
                    <?php
                    $historyId = 'history-' . uniqid();
                    $this->historiqueView->render($historyId);?>
            </aside>

            <section id="import">
                <?php
                $formView = new FormView($this->files);
                $formView->renderAllForms();
                ?>

                <button onclick="createNewFolder()"><i class="fas fa-folder-plus"></i>  Nouveau dossier</button>


                <!-- Formulaire pour Créer un Dossier -->
                <?php
                $folderHistory =  $this->historiqueView;
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


   <?}
}
