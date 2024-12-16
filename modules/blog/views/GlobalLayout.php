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
            <div class="logo">
                <!-- Exemple de logo SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" stroke="#957743" stroke-width="5" fill="#e2eba7"/>
                    <text x="50%" y="55%" text-anchor="middle" fill="#59481d" font-size="24px" font-family="Arial" dy=".3em">MAS</text>
                </svg>
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

        <?php
    }

}
?>