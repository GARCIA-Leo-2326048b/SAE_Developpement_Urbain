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
        <div class="container-content">
            <!-- Barre de défilement pour l'historique -->
            <aside id="history">
                <h2>Historique des fichiers</h2>
                <div id="history-files">
                    <?php
                    // Affichage des fichiers GeoJSON
                    if (!empty($this->files['GeoJSON'])) {
                        foreach ($this->files['GeoJSON'] as $file) {
                            // Vérification de la présence de 'file_name'
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
                            // Vérification de la présence de 'file_name'
                            if (isset($file['file_name'])) { ?>
                                <button class="history-file" onclick="showPopup('<?php echo $file['file_name']; ?>')">
                                    <?php echo htmlspecialchars($file['file_name']); ?>
                                </button>
                            <?php }
                        }
                    } ?>
                </div>
            </aside>

            <!-- Section pour les boutons d'upload -->
            <section id="import">
                <button onclick="showForm('vector')">Uploader un fichier Shapefile (Vecteur)</button>
                <button onclick="showForm('raster')">Uploader un fichier Raster (Image)</button>
            </section>

            <!-- HTML pour le modal -->
            <div id="errorModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; border: 1px solid #ccc; padding: 20px; z-index: 1000;">
                <span onclick="closeModal()" style="cursor: pointer; position: absolute; top: 10px; right: 10px;">&times;</span>
                <p id="modalMessage"><?php echo htmlspecialchars($errorMessage); ?></p>
                <button onclick="closeModal()">Non</button>
            </div>

            <!-- Script JavaScript pour gérer le modal -->
            <script>
                function closeModal() {
                    document.getElementById('errorModal').style.display = 'none';
                }

                // Affiche le modal si un message d'erreur est présent
                <?php if (!empty($errorMessage)): ?>
                document.getElementById('errorModal').style.display = 'block';
                <?php endif; ?>
            </script>

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
        </div>

        <!-- Pop-up pour simulation ou comparaison -->
        <div id="popup" class="popup">
            <div class="popup-content">
                <h2 id="popup-file-name"></h2>
                <button class="popup-button" onclick="simulate()">Simuler</button>
                <button class="popup-button" onclick="compare()">Comparer</button>
                <button class="popup-close" onclick="closePopup()">Fermer</button>
            </div>
        </div>

        <script>
            // Affiche le formulaire approprié
            function showForm(type) {
                if (type === 'vector') {
                    document.getElementById('vectorForm').style.display = 'block';
                    document.getElementById('rasterForm').style.display = 'none';
                } else if (type === 'raster') {
                    document.getElementById('vectorForm').style.display = 'none';
                    document.getElementById('rasterForm').style.display = 'block';
                }
            }

            // Affiche la pop-up pour le fichier sélectionné
            function showPopup(fileName) {
                document.getElementById('popup-file-name').textContent = fileName;
                document.getElementById('popup').style.display = 'block';
            }

            // Ferme la pop-up
            function closePopup() {
                document.getElementById('popup').style.display = 'none';
            }

            function simulate() {
                // Logique pour la simulation
                alert("Simulation lancée !");
                closePopup();
            }

            function compare() {
                // Logique pour la comparaison
                alert("Comparaison lancée !");
                closePopup();
            }
        </script>



        <?php
        (new GlobalLayout('Accueil', ob_get_clean()))->show();
    }
}
?>
