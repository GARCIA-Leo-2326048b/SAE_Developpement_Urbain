<?php

namespace blog\views;

class GlobalLayout
{
    private $content;
    private $title;

    public function __construct($title, $content) {
        $this->title = $title;
        $this->content = $content;
    }
    public function show() {?>
        <!-- views/layout.php -->
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $this->title ?></title>
            <link rel="stylesheet" href="/_assets/styles/style.css"> <!-- Chemin vers le CSS -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        </head>
        <body>

        <header>
            <div class="logo">
                <!--  logo -->
                <img class="logo-img" src="_assets/includes/logoMAS.png" alt="Logo MAS">
            </div>

            <h1>Recherche de Développement Urbain</h1>
            <section id="header">
                <a href="?action=accueil">Accueil</a> <a href="?action=affichage">Affichage</a><a href="?action=compare">Comparer</a>
            </section>
        </header>

        <div class="content">
            <?php echo $this->content; ?> <!-- Injection du contenu spécifique -->
        </div>

        <footer>
            <p>&copy; 2024 Mon Site Web</p>
        </footer>

        </body>
        </html>


        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="/_assets/scripts/workspace.js"></script>
        <?php
    }

}
?>