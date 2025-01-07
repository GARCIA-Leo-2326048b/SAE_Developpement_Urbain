<?php
namespace blog\views;
class HomepageView {
    private $projets;

    public function __construct($project){
        // Utiliser SingletonModel pour obtenir la connexion à la base de données
        $this->projets = $project;
    }

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


                    <!-- Section de gestion des projets -->
                    <section class="project-management">
                        <h4>Gestion des Projets</h4>
                        <div class="project-actions">
                            <!-- Sélection d'un projet -->
                            <form method="POST" action="?action=set_project" class="select-project-form">
                                <label for="project">Projet actif :</label>
                                <div class="project-selection">
                                    <select id="project" name="project_id" onchange="this.form.submit()">
                                        <option value="" disabled selected>Choisir un projet</option>
                                        <?php
                                        foreach ($this->projets as $project): ?>
                                            <option value="<?php echo htmlspecialchars($project['projet']); ?>"
                                                <?php echo ($_SESSION['current_project_id'] ?? '') === $project['projet'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($project['projet']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <!-- Petit bouton "+" pour ajouter un projet -->
                                    <button type="button" id="toggle-create-form" class="add-project-button">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </form>

                            <!-- Formulaire de création de projet (par défaut caché) -->
                            <form id="create-project-form" method="POST" action="" class="hidden create-project-form">
                                <label for="new_project_name">Créer un nouveau projet :</label>
                                <input type="text" id="new_project_name" name="new_project_name" placeholder="Nom du projet" required>
                                <div class="form-buttons">
                                    <button type="submit" class="create-button">Créer</button>
                                    <button type="button" id="cancel-create-form" class="cancel-button">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Projet actif -->
                        <?php if (!empty($_SESSION['current_project_id'])): ?>
                            <p class="active-project">Projet actif : <strong><?php echo htmlspecialchars($_SESSION['current_project_name']); ?></strong></p>
                        <?php else: ?>
                            <p class="info-message">Veuillez sélectionner ou créer un projet pour continuer.</p>
                        <?php endif; ?>
                    </section>



                    <!-- Section des simulations -->
                    <section class="simulation-section">
                        <h4>Simulations et Expériences</h4>
                        <div class="buttons">
                            <button onclick="location.href='?action=view_simulations'">Voir Mes Simulations</button>
                            <button onclick="location.href='?action=new_simulation'">Nouvelle Simulation</button>
                        </div>
                    </section>

                    <!-- Bouton de déconnexion -->
                    <div class="buttons">
                        <button onclick="location.href='?action=logout'">Se Déconnecter</button>
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
                        <button onclick="location.href='?action=authentification'">Se Connecter</button>
                        <button onclick="location.href='?action=inscription' ">S'inscrire</button>
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

