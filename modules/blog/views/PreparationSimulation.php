<?php

namespace blog\views;

use blog\controllers\Upload;

class PreparationSimulation
{
    private $files;


    public function __construct($files){
        $this->files = $files;
    }

// Fonction récursive pour afficher les dossiers et fichiers dans une structure imbriquée
private function displayFolderTree($folders, $parentId = '') {
    echo '<ul>';
    foreach ($folders as $folder) {
        echo '<li>';

        if (isset($folder['type']) && $folder['type'] === 'file') {
            // Affichage des fichiers
            echo "<button class='history-file' onclick=\"showPopup('" . htmlspecialchars($folder['name']) . "')\">"
                . htmlspecialchars($folder['name']) . "</button>";
        } else {
            // Affichage des dossiers
           // echo "<button class='folder-toggle' data-folder-id='" . htmlspecialchars($folder['name']) . "' onclick='toggleFolder(\"" . htmlspecialchars($folder['name']) . "\")'>";
            echo "<button class='folder-toggle' data-folder-id='" . htmlspecialchars($folder['name']) . "' 
    oncontextmenu='showContextMenu(event, \"" . htmlspecialchars($folder['name']) . "\")' 
    onclick='toggleFolder(\"" . htmlspecialchars($folder['name']) . "\")'>";
            echo "<i class='icon-folder'>📁</i> " . htmlspecialchars($folder['name']) . "</button>";

            // Vérifie si le dossier a des fichiers
            if (!empty($folder['files'])) {
                echo "<ul id='" . htmlspecialchars($folder['name']) . "-files' style='display: none;'>";
                foreach ($folder['files'] as $file) {
                    echo "<li><button class='history-file' onclick=\"showPopup('" . htmlspecialchars($file) . "')\">"
                        . htmlspecialchars($file) . "</button></li>";
                }
                echo '</ul>';
            }

            // Vérifie si le dossier a des sous-dossiers
            if (!empty($folder['children'])) {
                echo "<ul id='" . htmlspecialchars($folder['name']) . "-children' style='display: none;'>";
                $this->displayFolderTree($folder['children'], $folder['name']);
                echo '</ul>';
            }
        }

        echo '</li>';
    }
    echo '</ul>';
}

private function generateFolderOptions($folders, $prefix = '')
{
    echo "<option value='" . htmlspecialchars("root") . "'>" . $prefix . htmlspecialchars(" ") . "</option>";
    foreach ($folders as $folder) {
        if(!($folder['type'] === 'file')) {
            echo "<option value='" . htmlspecialchars($folder['name']) . "' data-parent='" . htmlspecialchars($folder['parent_id']) . "'>" . $prefix . htmlspecialchars($folder['name']) . "</option>";
//            if (!empty($folder['children'])) {
//                $this->generateFolderOptions($folder['children'], $prefix . '--');
//            }
        }

    }
}


    function show() : void {
        ob_start();?>
        <!-- Switch entre Simulation et Comparaison -->
        <div id="mode-switch">
            <button class="buttons" onclick="switchMode('simulation')">Mode Simulation</button>
            <button class="buttons" onclick="switchMode('comparaison')">Mode Comparaison</button>
        </div>
        <div class="container-content">

            <div class="main-content">
                <!-- Barre de défilement pour l'historique -->
                <aside id="history" >

                    <h2>Historique des fichiers</h2>
                    <div id="history-files"  >
                        <?php $this->displayFolderTree($this->files); ?>
                    </div>
                </aside>

                <!-- Section pour les formulaires d'upload -->
                <section id="import">
                    <h2>Uploader des fichiers</h2>
                    <button onclick="showForm('vector')">Uploader un fichier Shapefile (Vecteur)</button>
                    <button onclick="showForm('raster')">Uploader un fichier Raster (Image)</button>


                    <!-- Formulaire pour les fichiers Shapefile (Vecteur) -->
                    <form id="vectorForm" action="?action=upload" method="POST" enctype="multipart/form-data" style="display: none;">
                        <h2>Téléchargement de Shapefile</h2>
                        <label for="shapefile_name">Nom du fichier (sans extension) :</label>
                        <input type="text" id="shapefile_name" name="shapefile_name" required>
                        <br><br>
                        <label for="shapefile">Sélectionnez les 3 fichiers requis :</label>
                        <input type="file" id="shapefile" name="shapefile[]" accept=".shp,.shx,.dbf" multiple required>
                        <br><br>
                        <input type="hidden" id="selectedFolder" name="selectedFolder">
                        <input type="submit" value="Télécharger">

                        <!-- Sélection du dossier -->
                        <div id="folder-selection">
                            <label for="dossier_parent">Sélectionnez un dossier :</label>
                            <select class="folder-selector" id="dossier_parent" name="dossier_parent">
                                <?php $this->generateFolderOptions($this->files); ?>
                            </select>
                        </div>

                    </form>
                    <button onclick="createNewFolder()"><i class="fas fa-folder-plus"></i>  Nouveau dossier</button>



                    <!-- Formulaire pour Créer un Dossier -->
                    <form id="createFolderForm" method="POST" style="display: none; position: relative;">
                        <button type="button" onclick="closeCreateFolderForm()" style="position: absolute; top: 5px; right: 5px; background: none; border: none; cursor: pointer;">&times;</button>
                        <h3>Créer un Dossier</h3>
                        <label for="dossier_name">Nom du dossier :</label>
                        <input type="text" id="dossier_name" name="dossier_name" required>
                        <br><br>
                        <label for="dossier_parent">Sélectionnez le dossier parent :</label>
                        <select class="folder-selector" id="dossier_parent" name="dossier_parent">
                            <?php $this->generateFolderOptions($this->files); ?>
                        </select>
                        <br><br>
                        <button type="button" id="createFolderButton">Créer</button>
                    </form>

                    <!-- Menu contextuel pour la suppression -->
                    <div id="context-menu" class="context-menu" style="display: none;">
                        <ul>
                            <li onclick="deleteFolder()">Supprimer le dossier</li>
                        </ul>
                    </div>


                    <!-- Formulaire pour les fichiers Raster -->
                    <form id="rasterForm" action="?action=upload" method="POST" enctype="multipart/form-data" style="display: none;">
                        <h2>Téléchargement de Raster</h2>
                        <label for="rasterfile_name">Nom du fichier (sans extension) :</label>
                        <input type="text" id="rasterfile_name" name="rasterfile_name" required>
                        <br><br>
                        <label for="rasterfile">Sélectionnez un fichier Raster (TIFF, PNG, etc.) :</label>
                        <input type="file" id="rasterfile" name="rasterfile" accept=".tif,.tiff,.png,.jpg,.jpeg" required>
                        <br><br>
                        <input type="hidden" id="selectedFolderRaster" name="selectedFolderRaster">
                        <input type="submit" value="Télécharger">

                        <!-- Sélection du dossier -->
                        <div id="folder-selection">
                            <label for="folderSelect">Sélectionnez un dossier :</label>
                            <select id="folderSelect" onchange="loadSubFolders(this.value)">
                                <option value="">-- Sélectionner un dossier principal --</option>
                                <!-- Dossiers principaux chargés dynamiquement ici -->
                            </select>
                            <button onclick="createNewFolder()">Créer un nouveau dossier</button>
                        </div>
                    </form>
                </section>

                <!-- Pop-up pour les actions selon le mode -->
            <div id="popup" class="popup" style="display: none;">
                <div class="popup-content">
                    <h2 id="popup-file-name"></h2>
                    <button class="popup-button" id="actionButton" onclick="performAction()">Simuler</button>
                    <button class="popup-button" onclick="deleteFile()"><i class="fas fa-trash-alt"></i> </button>
                    <button class="popup-close" onclick="closePopup()"><i class="fas fa-window-close"></i></button>
                </div>
            </div>

            <!-- Bouton Comparer (visible uniquement en mode Comparaison) -->
            <div id="compare-section" style="display: none;">
                <button id="compareButton" onclick="compare()" disabled>Comparer</button>
            </div>
        </div>

        <?php
        (new GlobalLayout('Accueil', ob_get_clean()))->show();
    }
}
?>
