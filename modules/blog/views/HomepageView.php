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
                    // Fonction pour afficher le formulaire correspondant à l'option choisie
                    function showForm(type) {
                        if (type === 'vector') {
                            document.getElementById('vectorForm').style.display = 'block';
                            document.getElementById('rasterForm').style.display = 'none';
                        } else if (type === 'raster') {
                            document.getElementById('vectorForm').style.display = 'none';
                            document.getElementById('rasterForm').style.display = 'block';
                        }
                    }
                </script>
                <!-- Boutons pour choisir le type de fichier -->
                <button onclick="showForm('vector')">Uploader un fichier Shapefile (Vecteur)</button>
                <button onclick="showForm('raster')">Uploader un fichier Raster (Image)</button>
            </section>
                <!-- Formulaire pour les fichiers Shapefile (Vecteur) -->
                <form id="vectorForm" action="?action=upload" method="POST" enctype="multipart/form-data" style="display: none;">
                    <h2>Téléchargement de Shapefile</h2>
                    <label for="shapefile">Sélectionnez un fichier Shapefile (.shp) :</label>
                    <input type="file" id="shapefile" name="shapefile[]" accept=".shp,.shx,.dbf,.prj" multiple required>
                    <br><br>
                    <input type="submit" value="Télécharger">
                </form>

                <!-- Formulaire pour les fichiers Raster -->
                <form id="rasterForm" action="?action=upload" method="POST" enctype="multipart/form-data" style="display: none;">
                    <h2>Téléchargement de Raster</h2>
                    <label for="rasterfile">Sélectionnez un fichier Raster (TIFF, PNG, etc.) :</label>
                    <input type="file" id="rasterfile" name="rasterfile" accept=".tif,.tiff,.png,.jpg,.jpeg" required>
                    <br><br>
                    <input type="submit" value="Télécharger">
                </form>
        </main>
<?php
        (new GlobalLayout('Accueil', ob_get_clean()))->show();
    }
}
?>