<?php
namespace blog\views;
class HomepageView {

    function show() : void {
        ob_start();
        ?>
        <main>
            <h3>salut</h3>
            <div>
                <?php
                if(isset($_SESSION['suid'])) {
                    ?>
                    <a href="?action=logout" >
                        Se déconnecter</a>
                    <?php
                } else {
                    ?>
                    <a href="?action=authentification" >
                        Se connecter</a>
                    <?php
                }
                ?>
            </div>
            <section id="">
                <h2>Importer un fichier Shapefile</h2>
                <?php
                echo var_dump($_SESSION);

                ?>
                <!-- Formulaire pour télécharger un fichier Shapefile -->
                <form action="?action=upload" method="POST" enctype="multipart/form-data">
                    <label for="shapefile">Sélectionnez les fichiers du Shapefile (.shp, .shx, .dbf) :</label>
                    <input type="file" id="shapefile" name="shapefiles[]" accept=".shp,.shx,.dbf" multiple required>
                    <br><br>
                    <input type="submit" value="Télécharger">
                </form>

            </section>
        </main>
<?php
    }
}
?>