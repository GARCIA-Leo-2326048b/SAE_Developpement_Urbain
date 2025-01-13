<?php

namespace blog\views;

/**
 * Classe InscriptionView
 *
 * Cette classe gère l'affichage de la page d'inscription.
 */
class InscriptionView
{
    /**
     * Afficher la page d'inscription
     *
     * Affiche le formulaire d'inscription et gère les erreurs éventuelles.
     *
     * @param string|null $error Message d'erreur à afficher (optionnel)
     * @return void
     */
    public function show($error = null) : void{ // Accepte un paramètre pour l'erreur
        if (isset($_SESSION['suid'])){
            header('location: https://developpement-urbain.alwaysdata.net/index.php');
        } else {
            ob_start();
            if ($error): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="?action=creationCompte" method="post">
                <label for="identifiant">Identifiant (mail) :</label>
                <input type="text" id="identifiant" name="identifiant" required>

                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
                <br><br>

                <input type="submit" name="action" value="S'inscrire">
            </form>
            <?php
            (new GlobalLayout('Inscription', ob_get_clean()))->show();
        }

    }
}

?>

