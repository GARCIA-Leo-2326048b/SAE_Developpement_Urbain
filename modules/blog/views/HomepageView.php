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

            </section>
        </main>
<?php
    }
}
?>