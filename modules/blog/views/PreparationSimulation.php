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
            echo "<button class='folder-toggle' data-folder-id='" . htmlspecialchars($folder['name']) . "' onclick='toggleFolder(\"" . htmlspecialchars($folder['name']) . "\")'>";
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
                            <select id="dossier_parent" name="dossier_parent">
                                <?php $this->generateFolderOptions($this->files); ?>
                            </select>
                            <button onclick="createNewFolder()"><i class="fas fa-folder-plus"></i>  Nouveau dossier</button>
                        </div>

                    </form>




                    <!-- Formulaire pour Créer un Dossier -->
                    <form id="createFolderForm" action="?action=create_folder" method="POST" style="display: none;">
                        <h3>Créer un Dossier</h3>
                        <label for="dossier_name">Nom du dossier :</label>
                        <input type="text" id="dossier_name" name="dossier_name" required>
                        <br><br>
                        <label for="dossier_parent">Sélectionnez le dossier parent :</label>
                        <select id="dossier_parent" name="dossier_parent" ">
                            <?php $this->generateFolderOptions($this->files); ?>
                        </select>
                        <br><br>
                        <input type="submit" value="Créer" id="create">
                    </form>

                    <script>

                        function createNewFolder() {

                            document.getElementById('createFolderForm').style.display = 'block';

                        }


                        function toggleFolder(folderId) {
                            const filesElement = document.getElementById(`${folderId}-files`);
                            const childrenElement = document.getElementById(`${folderId}-children`);

                            // Basculer l'affichage des fichiers
                            if (filesElement) {
                                filesElement.style.display = filesElement.style.display === 'none' ? 'block' : 'none';
                            }

                            // Basculer l'affichage des enfants
                            if (childrenElement) {
                                childrenElement.style.display = childrenElement.style.display === 'none' ? 'block' : 'none';
                            }

                            // Basculer l'icône de dossier
                            const button = document.querySelector(`[data-folder-id="${folderId}"]`);
                            if (button) {
                                const icon = button.querySelector('.icon-folder');
                                if (icon) {
                                    icon.textContent = icon.textContent === '📁' ? '📂' : '📁';
                                }
                            }
                        }



                        document.getElementById("history").addEventListener("click", function(event) {
                            // Vérifie si l'élément cliqué est #history lui-même et non un fichier
                            if (event.target === this) {
                                createNewFolder();
                            }
                        });

                       function updateHistory() {
                           fetch('index.php?action=reloading')
                               .then(response => response.text()) // Change to .text() to handle HTML response
                               .then(data => {
                                   const historyFiles = document.getElementById('history-files');
                                   historyFiles.innerHTML = data; // Update the history with the new HTML
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
                    <button class="popup-button" onclick="deleteFile()"><i class="fas fa-trash-alt"></i> </button>
                    <button class="popup-close" onclick="closePopup()"><i class="fas fa-window-close"></i></button>
                </div>
            </div>

            <!-- Bouton Comparer (visible uniquement en mode Comparaison) -->
            <div id="compare-section" style="display: none;">
                <button id="compareButton" onclick="compare()" disabled>Comparer</button>
            </div>
        </div>

        <script defer>
            let selectedFiles = [];
            let currentMode = 'simulation';

            function switchMode(mode) {
                // Met à jour le mode courant
                currentMode = mode;

                // Affiche ou masque la section de comparaison
                document.getElementById('compare-section').style.display = (mode === 'comparaison') ? 'block' : 'none';

                // Change le texte du bouton d'action
                document.getElementById('actionButton').textContent = (mode === 'simulation') ? 'Simuler' : 'Sélectionner';

                // Réinitialise les fichiers sélectionnés
                selectedFiles = [];
                updateCompareButtonState();

                // Gère la classe active pour les boutons
                const buttons = document.querySelectorAll('#mode-switch button');
                buttons.forEach(button => {
                    if (button.textContent.includes(mode.charAt(0).toUpperCase() + mode.slice(1))) {
                        button.classList.add('active');
                    } else {
                        button.classList.remove('active');
                    }
                });
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
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`index.php?action=deletFile&fileName=${fileName}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({fileName: fileName})
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: "Deleted!",
                                        text: "Your file has been deleted.",
                                        icon: "success"
                                    });
                                    updateHistory();
                                } else {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Oops...",
                                        text: "Erreur lors de la suppression de " + fileName,
                                        footer: '<a href="#">Why do I have this issue?</a>'
                                    });

                                }
                            });
                    }
                });
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
