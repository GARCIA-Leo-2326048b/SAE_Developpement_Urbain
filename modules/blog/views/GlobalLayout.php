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
    function show() {?>
        <!-- views/layout.php -->
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $this->title ?></title>
            <link rel="stylesheet" href="/_assets/styles/style.css"> <!-- Chemin vers le CSS -->
        </head>
        <body>

        <header>
            <div class="titre">
                <h1>Le Titre</h1>
            </div>
            <section id="header">
                <a href="?action=accueil">Accueil</a> <a href="">À propos</a> <a href="">Contact</a>
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

        <?php
    }

}
?>