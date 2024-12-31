<?php
namespace blog\views;
class HomepageView {

    public function show() : void {
        ob_start();?>
        <main>
            <h3>Début de la Simulation</h3>
            <div  class="container">
                <?php
                if(isset($_SESSION['suid'])) {
                    ?>
                    <div class="welcome-message">
                        Bienvenue, <?php echo htmlspecialchars($_SESSION['user_id']); ?> !
                    </div>
                    <p>
                        Ce projet de recherche, organisé par plusieurs universités, dont celle de la Nouvelle-Calédonie, utilise le MAS comme moyen d'intelligence artificielle pour le développement urbain. Nous visons à améliorer la planification et la gestion des espaces urbains grâce à des analyses géospatiales avancées.
                    </p>
                    <div class="buttons">
                        <button onclick="location.href='?action=view_simulations'">Voir mes Simulations</button>
                        <button onclick="location.href='?action=new_simulation'">Démarrer une Nouvelle Simulation</button>
                    </div>
                    <div class="buttons" >
                    <button onclick="location.href='?action=logout'">Se déconnecter</button>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="container">
                        <h2>Bienvenue sur notre plateforme de Recherche de Développement Urbain</h2>
                        <p>Veuillez vous connecter pour accéder aux fonctionnalités de simulation et de comparaison.</p>

                        <div class="forms">
                            <h4>Upload de Fichiers pour Simulation ou Comparaison</h4>


                            <?php
                            $formView = new FormView();
                            $formView->renderAllForms();
                            ?>
                        </div>

                        <div class="buttons">
                            <button onclick="location.href='?action=compare_files'">Comparer Deux Fichiers</button>
                        </div>

                        <div class="buttons">
                        <button onclick="location.href='?action=authentification'">Se Connecter</button>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </main>
<?php
        (new GlobalLayout('Accueil', ob_get_clean()))->show();
    }
}
?>

