<?php

namespace blog\views;

/**
 * Classe AuthentificationView
 *
 * Cette classe gère l'affichage de la vue d'authentification.
 */
class AuthentificationView {
    /**
     * Afficher la vue d'authentification
     *
     * Affiche le formulaire de connexion et gère l'affichage des erreurs.
     *
     * @param string|null $error Message d'erreur à afficher (optionnel)
     * @return void
     */
    public function show($error = null) { // Accepte un paramètre pour l'erreur
        if (isset($_SESSION['suid'])){
            header('location: https://developpement-urbain.alwaysdata.net/index.php');
        } else {
            ob_start();
            if ($error): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="?action=login" method="post">
                <label for="identifiant">Identifiant :</label>
                <input type="text" id="identifiant" name="identifiant" required>

                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
                <br><br>

                <input type="submit" name="action" value="Se connecter">
            </form>
            <?php
            (new GlobalLayout('Authentification', ob_get_clean()))->show();
        }

    }
}

?>

