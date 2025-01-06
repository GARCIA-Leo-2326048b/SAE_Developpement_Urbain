<?php

namespace blog\views;

class InscriptionView
{
    public function show($error = null) { // Accepte un paramètre pour l'erreur
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