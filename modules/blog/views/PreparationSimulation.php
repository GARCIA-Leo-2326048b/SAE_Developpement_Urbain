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
private function displayFolderTree($folders)
{
    echo '<ul>';
    foreach ($folders as $folder) {
        echo "<li>";
        echo "<button onclick=\"toggleFolder(this)\">" . htmlspecialchars($folder['name']) . "</button>";

        // Afficher les fichiers de ce dossier
        if (!empty($folder['files'])) {
            echo '<ul>';
            foreach ($folder['files'] as $file) {
                echo "<li><button class='history-file' onclick=\"showPopup('" . htmlspecialchars($file) . "')\">" . htmlspecialchars($file) . "</button></li>";
            }
            echo '</ul>';
        }

        // Afficher les sous-dossiers de manière récursive
        if (!empty($folder['children'])) {
            $this->displayFolderTree($folder['children']);
        }

        echo "</li>";
    }
    echo '</ul>';
}

private function generateFolderOptions($folders, $prefix = '')
{
    foreach ($folders as $folder) {
        echo "<option value='" . htmlspecialchars($folder['name']) . "'>" . $prefix . htmlspecialchars($folder['name']) . "</option>";
        if (!empty($folder['children'])) {
            $this->generateFolderOptions($folder['children'], $prefix . '--');
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

                    <script>
                        document.getElementById("history").addEventListener("click", function(event) {
                            // Vérifie si l'élément cliqué est #history lui-même et non un fichier
                            if (event.target === this) {
                                createNewFolder();
                            }
                        });

                        function createNewFolder(dossierParent = null) {
                            const folderName = prompt("Entrez le nom du nouveau répertoire :");
                            if (folderName) {
                                // Crée un nouvel élément div pour l'affichage
                                const folderDiv = document.createElement("div");
                                folderDiv.className = "history-folder";
                                folderDiv.addEventListener("click", () => createNewFolder(folderName));  // Le dossier parent est défini
                                folderDiv.textContent = folderName;
                                document.getElementById("history-files").appendChild(folderDiv);

                                // Envoyer folderName et dossierParent au serveur via AJAX
                                fetch('create_folder.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({ folderName: folderName, dossierParent: dossierParent })
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            console.log("Dossier créé avec succès.");
                                        } else {
                                            console.error("Erreur lors de la création du dossier :", data.error);
                                        }
                                    })
                                    .catch(error => {
                                        console.error("Erreur réseau :", error);
                                    });

                                <?php
                                // Récupérer les données JSON
                                    $data = json_decode(file_get_contents("php://input"), true);
                                    $this->upload->folder($data);
                                ?>A

                            }
                        }

                        // Fonction pour charger les dossiers principaux au chargement de la page
                        window.onload = function() {
                            loadFolders();
                        };

                        // Charger les dossiers principaux
                        function loadFolders(parentId = null) {
                            // Requête AJAX pour récupérer les dossiers principaux (ou sous-dossiers si parentId est défini)
                            fetch(`getFolders.php?parent_id=${parentId || ''}`)
                                .then(response => response.json())
                                .then(folders => {
                                    const folderSelect = document.getElementById("folderSelect");
                                    folderSelect.innerHTML = '<option value="">-- Sélectionner un dossier principal --</option>';

                                    folders.forEach(folder => {
                                        const option = document.createElement("option");
                                        option.value = folder.id;
                                        option.textContent = folder.name;
                                        folderSelect.appendChild(option);
                                    });
                                });
                        }

                        // Charger les sous-dossiers lorsque le dossier est sélectionné
                        function loadSubFolders(parentId) {
                            if (!parentId) return;

                            fetch(`getFolders.php?parent_id=${parentId}`)
                                .then(response => response.json())
                                .then(folders => {
                                    // Créer une nouvelle liste déroulante pour les sous-dossiers
                                    const subFolderSelect = document.createElement("select");
                                    subFolderSelect.onchange = () => loadSubFolders(subFolderSelect.value);

                                    // Ajouter une option par défaut
                                    subFolderSelect.innerHTML = '<option value="">-- Sélectionner un sous-dossier --</option>';

                                    // Ajouter les sous-dossiers récupérés
                                    folders.forEach(folder => {
                                        const option = document.createElement("option");
                                        option.value = folder.id;
                                        option.textContent = folder.name;
                                        subFolderSelect.appendChild(option);
                                    });

                                    // Ajouter le nouveau select après le select actuel
                                    document.getElementById("folder-selection").appendChild(subFolderSelect);
                                });
                        }

                        // Définir le dossier sélectionné dans le formulaire
                        function setSelectedFolder(folderId) {
                            document.getElementById("selectedFolder").value = folderId;
                            document.getElementById("selectedFolderRaster").value = folderId;
                        }


                    </script>

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
                            <label for="folderSelect">Sélectionnez un dossier :</label>
                            <select id="folderSelect" name="selectedFolder">
                                <?php $this->generateFolderOptions($this->files); ?>
                            </select>
                            <button onclick="createNewFolder()">Créer un nouveau dossier</button>
                        </div>


                    </form>

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
