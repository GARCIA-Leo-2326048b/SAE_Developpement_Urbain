<?php

namespace blog\views;

class PreparationSimulation
{
    private $files;
    private $errorMessage;

    public function __construct($files){
        $this->files = $files;
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
                <aside id="history">
                    <h2>Historique des fichiers</h2>
                    <div id="history-files">
                        <?php
                        // Affichage des fichiers GeoJSON
                        if (!empty($this->files['GeoJSON'])) {
                            foreach ($this->files['GeoJSON'] as $file) {
                                if (isset($file['file_name'])) { ?>
                                    <button class="history-file" onclick="showPopup('<?php echo $file['file_name']; ?>')">
                                        <?php echo htmlspecialchars($file['file_name']); ?>
                                    </button>
                                <?php }
                            }
                        }

                        // Affichage des fichiers GeoTIFF
                        if (!empty($this->files['GeoTIFF'])) {
                            foreach ($this->files['GeoTIFF'] as $file) {
                                if (isset($file['file_name'])) { ?>
                                    <button class="history-file" onclick="showPopup('<?php echo $file['file_name']; ?>')">
                                        <?php echo htmlspecialchars($file['file_name']); ?>
                                    </button>
                                <?php }
                            }
                        } ?>
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
                        <label for="shapefile">Sélectionnez les 3 fichiers requis:</label>
                        <input type="file" id="shapefile" name="shapefile[]" accept=".shp,.shx,.dbf" multiple required>
                        <br><br>
                        <input type="submit" value="Télécharger">
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
                        <input type="submit" value="Télécharger">
                    </form>
                </section>
            </div>

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
