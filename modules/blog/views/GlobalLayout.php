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
    public function show() : void {?>
        <!-- views/layout.php -->
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $this->title ?></title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="/_assets/styles/style.css"> <!-- Chemin vers le CSS -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />
            <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
        <header data-aos="fade-down">
            <div class="logo">
                <a href="?action=accueil"><img class="logo-img" src="_assets/includes/logoMAS.png" alt="Logo MAS"></a>
            </div>
        </header>
        <!-- Bouton de retour -->
        <button class="return-button" onclick="goBack()">
            <i class="fas fa-arrow-left"></i>
        </button>

        <div class="content" data-aos="fade-up">
            <?php echo $this->content; ?>
        </div>

        <button id="backToTop" style="display: none; position: fixed; bottom: 20px; right: 20px; background-color: #957743; color: #fff; border: none; border-radius: 50%; width: 50px; height: 50px; cursor: pointer;">
            ↑
        </button>

        <footer data-aos="fade-up">
            <p>&copy; 2024 Simulation de développement urbain</p>
        </footer>

        </body>

        <script src="/_assets/scripts/workspace.js"></script>
        </html>
        <?php

    }

}
?>

