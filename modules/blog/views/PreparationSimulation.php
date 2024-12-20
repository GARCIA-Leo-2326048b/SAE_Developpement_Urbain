<?php

namespace blog\views;

use blog\controllers\Upload;

class PreparationSimulation
{
    private $files;
    private $upload;
    private $errorMessage;

    public function __construct($files){
        $this->files = $files;
        $this->upload = new Upload();
    }

// Fonction récursive pour afficher les dossiers et fichiers dans une structure imbriquée
private function displayFolderTree($folders) {
    echo '<ul>';
    foreach ($folders as $folder) {
        echo '<li>';

        // Affiche le nom du dossier
        if (isset($folder['type']) && $folder['type'] === 'file') {
            // Affiche les fichiers sans dossier
            echo "<button class='history-file' onclick=\"showPopup('" . htmlspecialchars($folder['name']) . "')\">" . htmlspecialchars($folder['name']) . "</button>";
        } else {
            echo "<button onclick='toggleFolder(this)' data-folder-id='" . htmlspecialchars($folder['name']) . "'>";
            echo "<i class='icon-folder'></i> " . htmlspecialchars($folder['name']) . "</button>";

            // Affiche les fichiers du dossier courant
            if (!empty($folder['files'])) {
                echo '<ul>';
                foreach ($folder['files'] as $file) {
                    echo "<li><button class='history-file' onclick=\"showPopup('" . htmlspecialchars($file) . "')\">" . htmlspecialchars($file) . "</button></li>";
                }
                echo '</ul>';
            }

            // Affiche les sous-dossiers
            if (!empty($folder['children'])) {
                echo "<ul style='display: none;'>";
                $this->displayFolderTree($folder['children']);
                echo "</ul>";
            }
        }

        echo '</li>';
    }
    echo '</ul>';
}



private function generateFolderOptions($folders, $prefix = '')
{
    foreach ($folders as $folder) {
        if(!($folder['type'] === 'file')) {
            echo "<option value='" . htmlspecialchars($folder['name']) . "'>" . $prefix . htmlspecialchars($folder['name']) . "</option>";
            if (!empty($folder['children'])) {
                $this->generateFolderOptions($folder['children'], $prefix . '--');
            }
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

                    </form>

                    <!-- Sélection du dossier -->
                    <div id="folder-selection">
                        <label for="folderSelect">Sélectionnez un dossier :</label>
                        <select id="folderSelect" name="folderSelect">
                            <?php $this->generateFolderOptions($this->files); ?>
                        </select>
                        <button onclick="createNewFolder()">Créer un nouveau dossier</button>
                    </div>


                    <!-- Formulaire pour Créer un Dossier -->
                    <form id="createFolderForm" action="?action=create_folder" method="POST" style="display: none;">
                        <h3>Créer un Dossier</h3>
                        <label for="dossier_name">Nom du dossier :</label>
                        <input type="text" id="dossier_name" name="dossier_name" required>
                        <br><br>
                        <label for="dossier_parent">Sélectionnez le dossier parent :</label>
                        <select id="dossier_parent" name="dossier_parent">
                            <?php $this->generateFolderOptions($this->files); ?>
                        </select>
                        <br><br>
                        <input type="submit" value="Créer">
                    </form>

                    <script>

                        function createNewFolder() {

                            document.getElementById('createFolderForm').style.display = 'block';
                            }


                        function toggleFolder(button) {
                            const nextElement = button.nextElementSibling;
                            if (nextElement && nextElement.tagName === 'UL') {
                                const isHidden = nextElement.style.display === 'none';
                                nextElement.style.display = isHidden ? 'block' : 'none';
                                button.querySelector('.icon-folder').textContent = isHidden ? '📂' : '📁';
                            }
                        }



                        document.getElementById("history").addEventListener("click", function(event) {
                            // Vérifie si l'élément cliqué est #history lui-même et non un fichier
                            if (event.target === this) {
                                createNewFolder();
                            }
                        });

                        function updateHistory() {
                            // Logique pour mettre à jour l'historique sans recharger la page
                            // Par exemple, vous pouvez refaire une requête pour récupérer les dossiers et fichiers mis à jour
                            fetch('get_history.php')
                                .then(response => response.json())
                                .then(data => {
                                    // Mettre à jour l'affichage avec les nouvelles données
                                    displayFolderTree(data);
                                })
                                .catch(error => {
                                    console.error("Erreur lors de la mise à jour de l'historique :", error);
                                });
                        }

                    </script>


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
                    <button class="popup-button" onclick="deleteFile()">Supprimer</button>
                    <button class="popup-close" onclick="closePopup()">Fermer</button>
                </div>
            </div>

            <!-- Bouton Comparer (visible uniquement en mode Comparaison) -->
            <div id="compare-section" style="display: none;">
                <button id="compareButton" onclick="compare()" disabled>Comparer</button>
            </div>
        </div>

        <script>
            let selectedFiles = [];
            let currentMode = 'simulation';

            function switchMode(mode) {
                currentMode = mode;
                document.getElementById('compare-section').style.display = (mode === 'comparaison') ? 'block' : 'none';
                document.getElementById('actionButton').textContent = (mode === 'simulation') ? 'Simuler' : 'Sélectionner';
                selectedFiles = [];
                updateCompareButtonState();
            }

            function showForm(type) {
                if (type === 'vector') {
                    document.getElementById('vectorForm').style.display = 'block';
                    document.getElementById('rasterForm').style.display = 'none';
                } else if (type === 'raster') {
                    document.getElementById('vectorForm').style.display = 'none';
                    document.getElementById('rasterForm').style.display = 'block';
                }
            }

            function showPopup(fileName) {
                document.getElementById('popup-file-name').textContent = fileName;
                document.getElementById('popup').style.display = 'block';
            }

            function closePopup() {
                document.getElementById('popup').style.display = 'none';
            }

            function performAction() {
                if (currentMode === 'simulation') {
                    alert("Simulation lancée pour " + document.getElementById('popup-file-name').textContent);
                } else {
                    selectFile();
                }
                closePopup();
            }

            function selectFile() {
                const fileName = document.getElementById('popup-file-name').textContent;
                if (selectedFiles.length < 2 && !selectedFiles.includes(fileName)) {
                    selectedFiles.push(fileName);
                    alert(fileName + " a été sélectionné.");
                } else if (selectedFiles.includes(fileName)) {
                    alert("Ce fichier est déjà sélectionné.");
                } else {
                    alert("Vous ne pouvez sélectionner que deux fichiers au maximum.");
                }
                updateCompareButtonState();
            }

            function deleteFile() {
                const fileName = document.getElementById('popup-file-name').textContent;
                if (confirm("Voulez-vous vraiment supprimer " + fileName + " ?")) {
                    alert(fileName + " a été supprimé.");
                    // Ajouter la logique de suppression ici
                }
                closePopup();
            }

            function updateCompareButtonState() {
                const compareButton = document.getElementById('compareButton');
                if (selectedFiles.length === 2) {
                    compareButton.disabled = false;
                    compareButton.classList.add('enabled'); // Ajouter la classe 'enabled'
                } else {
                    compareButton.disabled = true;
                    compareButton.classList.remove('enabled'); // Retirer la classe 'enabled'
                }
            }


            function compare() {
                if (selectedFiles.length === 2) {
                    alert("Comparaison entre " + selectedFiles[0] + " et " + selectedFiles[1] + " lancée !");
                    // Ajouter la logique de comparaison ici
                } else {
                    alert("Veuillez sélectionner exactement deux fichiers.");
                }
            }

            function toggleFolder(button) {
                const nextElement = button.nextElementSibling;
                if (nextElement && nextElement.tagName === 'UL') {
                    nextElement.style.display = nextElement.style.display === 'none' ? 'block' : 'none';
                }
            }


        </script>

        <style>
            .main-content {
                display: flex;
                justify-content: space-between;
                width: 100%;
            }

        </style>

        <?php
        (new GlobalLayout('Accueil', ob_get_clean()))->show();
    }
}
?>
