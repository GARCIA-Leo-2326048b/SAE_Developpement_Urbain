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
                <!-- Logo SVG amélioré -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120">
                    <!-- Cercle extérieur -->
                    <circle cx="60" cy="60" r="50" stroke="#59481d" stroke-width="5" fill="none"/>

                    <!-- Cercle intérieur -->
                    <circle cx="60" cy="60" r="45" fill="#957743"/>

                    <!-- Texte principal -->
                    <text x="50%" y="52%" text-anchor="middle" fill="#2D3748" font-size="22px" font-family="Segoe UI, Arial, sans-serif" font-weight="bold" dy=".3em">
                        MAS
                    </text>

                    <!-- Slogan ou sous-texte -->
                    <text x="50%" y="66%" text-anchor="middle" fill="#A0AEC0" font-size="12px" font-family="Segoe UI, Arial, sans-serif">
                        Solutions
                    </text>
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


        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="/_assets/scripts/workspace.js"></script>
        <?php
    }

}
?>

