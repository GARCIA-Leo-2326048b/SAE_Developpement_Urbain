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
                    <label for="shapefile">Sélectionnez un fichier Shapefile (.shp) :</label>
                    <input type="file" id="shapefile" name="shapefile" accept=".shp" required>
                    <br><br>
                    <input type="submit" value="Télécharger">
                </form>
            </section>
        </main>
<?php
    }
}
?>