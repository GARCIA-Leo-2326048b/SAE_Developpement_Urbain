<?php
namespace blog\views;
require_once 'GlobalLayout.php';
class HomepageView {

    function show() : void {
        ob_start();?>
        <main>
            <h3>Début de la Simulation</h3>
            <div>
                <?php
                if(isset($_SESSION['suid'])) {
                    ?>
                    <a href="?action=logout">Se déconnecter</a>
                    <?php
                } else {
                    ?>
                    <a href="?action=authentification">Se connecter</a>
                    <?php
                }
                ?>
            </div>
            <section id="import">
                <script>
                    // Fonction pour afficher les boutons correspondants
                    function showButtonsC() {
                        document.getElementById('butoonC').style.display = 'block';
                        document.getElementById('butoonS').style.display = 'none';
                        document.getElementById('compareOptions').style.display = 'block';
                        document.getElementById('compareForms').style.display = 'none';
                    }

                    function showButtonS() {
                        document.getElementById('butoonS').style.display = 'block';
                        document.getElementById('butoonC').style.display = 'none';
                        document.getElementById('compareOptions').style.display = 'none';
                        document.getElementById('compareForms').style.display = 'none';
                    }

                    // Fonction pour afficher le formulaire correspondant à l'option choisie
                    function showForm(type) {
                        if (type === 'vector') {
                            document.getElementById('vectorForm').style.display = 'block';
                            document.getElementById('rasterForm').style.display = 'none';
                            document.getElementById('compareForms').style.display = 'none';
                        } else if (type === 'raster') {
                            document.getElementById('vectorForm').style.display = 'none';
                            document.getElementById('rasterForm').style.display = 'block';
                            document.getElementById('compareForms').style.display = 'none';
                        } else if (type === 'compareVector') {
                            document.getElementById('vectorForm').style.display = 'none';
                            document.getElementById('rasterForm').style.display = 'none';
                            document.getElementById('compareForms').style.display = 'block';
                            document.getElementById('compareVectorForm').style.display = 'block';
                            document.getElementById('compareRasterForm').style.display = 'none';
                        } else if (type === 'compareRaster') {
                            document.getElementById('vectorForm').style.display = 'none';
                            document.getElementById('rasterForm').style.display = 'none';
                            document.getElementById('compareForms').style.display = 'block';
                            document.getElementById('compareVectorForm').style.display = 'none';
                            document.getElementById('compareRasterForm').style.display = 'block';
                        }
                    }
                </script>
                <div>
                    <button onclick="showButtonsC()" id="comparer">Comparer</button>
                    <button onclick="showButtonS()" id="simuler">Simuler</button>
                </div>
                <!-- Boutons pour choisir le type de fichier -->
                <div id="butoonS" style="display:none;">
                    <button onclick="showForm('vector')">Uploader un fichier Shapefile (Vecteur)</button>
                    <button onclick="showForm('raster')">Uploader un fichier Raster (Image)</button>
                </div>
                <!-- Boutons pour choisir le type de comparaison -->
                <div id="butoonC" style="display:none;">
                    <button onclick="showForm('compareVector')">Comparer des fichiers Shapefile (Vecteur)</button>
                    <button onclick="showForm('compareRaster')">Comparer des fichiers Raster (Image)</button>
                </div>
                <!-- Options de comparaison -->
                <div id="compareOptions" style="display:none;">
                    <!-- Vous pouvez ajouter des options supplémentaires ici si nécessaire -->
                </div>
            </section>
            <!-- Formulaire pour les fichiers Shapefile (Vecteur) -->
            <form class="importform" id="vectorForm" action="?action=upload" method="POST" enctype="multipart/form-data" style="display: none;">
                <h2>Téléchargement de Shapefile</h2>
                <label for="shapefile">Sélectionnez les fichiers Shapefile (.shp, .shx, .dbf, .prj) :</label>
                <input type="file" id="shapefile" name="shapefile[]" accept=".shp,.shx,.dbf,.prj" multiple required>
                <br><br>
                <input type="submit" value="Télécharger">
            </form>

            <!-- Formulaire pour les fichiers Raster -->
            <form class="importform" id="rasterForm" action="?action=upload" method="POST" enctype="multipart/form-data" style="display: none;">
                <h2>Téléchargement de Raster</h2>
                <label for="rasterfile">Sélectionnez un fichier Raster (TIFF, PNG, etc.) :</label>
                <input type="file" id="rasterfile" name="rasterfile" accept=".tif,.tiff,.png,.jpg,.jpeg" required>
                <br><br>
                <input type="submit" value="Télécharger">
            </form>

            <!-- Section pour les formulaires de comparaison -->
            <div id="compareForms" style="display: none;">
                <!-- Formulaire pour comparer deux Shapefiles (Vecteur) -->
                <form class="importform" id="compareVectorForm" action="?action=compareVector" method="POST" enctype="multipart/form-data" style="display: none;">
                    <h2>Comparaison de Shapefiles</h2>
                    <label for="shapefile1">Sélectionnez le fichier Shapefile à simuler :</label>
                    <input type="file" id="shapefile1" name="shapefile1[]" accept=".shp,.shx,.dbf,.prj" multiple required>
                    <br><br>
                    <label for="shapefile2">Sélectionnez le fichier Shapefile de vérité :</label>
                    <input type="file" id="shapefile2" name="shapefile2[]" accept=".shp,.shx,.dbf,.prj" multiple required>
                    <br><br>
                    <input type="submit" value="Comparer">
                </form>

                <!-- Formulaire pour comparer deux Rasters -->
                <form class="importform" id="compareRasterForm" action="?action=compareRaster" method="POST" enctype="multipart/form-data" style="display: none;">
                    <h2>Comparaison de Rasters</h2>
                    <label for="rasterfile1">Sélectionnez le premier fichier Raster (à simuler) :</label>
                    <input type="file" id="rasterfile1" name="rasterfile1" accept=".tif,.tiff,.png,.jpg,.jpeg" required>
                    <br><br>
                    <label for="rasterfile2">Sélectionnez le second fichier Raster (vérité) :</label>
                    <input type="file" id="rasterfile2" name="rasterfile2" accept=".tif,.tiff,.png,.jpg,.jpeg" required>
                    <br><br>
                    <input type="submit" value="Comparer">
                </form>
            </div>
        </main>
        <?php
        (new GlobalLayout('Accueil', ob_get_clean()))->show();
    }
}
?>
