<?php

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

/**
 * Point d'entrée principal de l'application.
 *
 * Ce script gère les différentes actions en fonction des paramètres passés dans l'URL.
 * Il initialise la session, charge les dépendances et appelle les contrôleurs appropriés.
 *
 * @throws Exception Si une erreur se produit lors de l'exécution des actions.
 */
try {
    if (filter_input(INPUT_GET, 'action')) {
        $action = filter_input(INPUT_GET, 'action');
        switch ($action) {
            case 'homepage':
                (new blog\controllers\HomepageController())->execute();
                break;

            case 'affichage':
                // Récupérer les fichiers depuis l'URL
                $files = filter_input(INPUT_GET, 'files'); // Cela sera une chaîne encodée
            var_dump("on est la ");
                var_dump($files);
                if ($files) {
                    // Décoder la chaîne et convertir en tableau
                    $filesArray = explode(',', urldecode($files));

                    // Passer la liste des fichiers au contrôleur
                    (new blog\controllers\AffichageController())->execute($filesArray);

                    // Stocker les fichiers de simulation en session pour réutilisation
                    $_SESSION['simFiles'] = $filesArray;
                }
                break;
            case 'affichagesim':
                $simName = filter_input(INPUT_GET, 'sim_name', FILTER_SANITIZE_STRING);

                $_SESSION['simFiles'] = $simName;

                $geojsonFile = filter_input(INPUT_GET, 'files');
                $geojsonFile = trim($geojsonFile, '"');  // Retire les guillemets en début et fin


                if (!file_exists($geojsonFile)) {
                    die("Erreur : Le fichier n'existe pas.");
                }

                $geojsonContent = file_get_contents($geojsonFile);



                $geojsonDecoded = json_decode($geojsonContent, true);


                if (json_last_error() !== JSON_ERROR_NONE) {
                    die("Erreur de décodage JSON : " . json_last_error_msg());
                }

// Affichage du GeoJSON dans ton contrôleur
                (new blog\controllers\AffichageController())->afficherSimulation($geojsonDecoded);
                break;
            case 'compare':
                // Récupérer les fichiers stockés en session
                $simFiles = $_SESSION['simFiles'] ?? [];

                // Récupérer la nouvelle liste de fichiers pour la comparaison
                $compareFiles = filter_input(INPUT_GET, 'files');

                if ($compareFiles) {
                    $compareFilesArray = explode(',', urldecode($compareFiles));
                    (new blog\controllers\ComparaisonController())->execute($simFiles, $compareFilesArray);
                }
                break;
            case 'authentification':
                (new blog\controllers\AuthentificationController())->execute();
                break;
            case 'login':
                (new blog\controllers\AuthentificationController())->connexion();
                break;
            case 'inscription':
                (new blog\controllers\InscriptionController())->execute();
                break;
            case 'creationCompte':
                (new blog\controllers\InscriptionController())->creationCompte();
                break;
            case 'create_project':
                (new blog\controllers\Upload())->createProject();
                break;
            case 'get_all_projects':
                (new blog\controllers\Upload())->getProjects();
                break;
            case 'set_project':
                (new blog\controllers\Upload())->setProject();
                break;
            case 'view_simulations':
                (new blog\controllers\WorkSpaceController())->project();
                break;
            case 'new_simulation':
                (new blog\controllers\WorkSpaceController())->execute();
                break;
            case 'run_simulation':
                (new blog\controllers\SimController())->runSimulation();
                break;
            case 'save_experimentation':
                (new blog\controllers\ComparaisonController())->saveExperimentation();
                break;
            case 'upload':
                (new blog\controllers\Upload())->telechargement();
                break;
            case 'create_folder':
                (new blog\controllers\Upload())->folder1();
                break;
            case 'get_subfolders':
                (new blog\controllers\Upload())->getSubFolders();
                break;
            case 'deletFile':
                (new blog\controllers\Upload())->deleteFile();
                break;
            case 'deleteFolder':
                (new blog\controllers\Upload())->deleteFolder();
                break;
            case 'reloading':
                (new blog\controllers\Upload())->getArbre();
                break;
            case 'reloadingExp':
                (new blog\controllers\Upload())->getArbreExp();
                break;
            case 'reloadExp':
                $id = filter_input(INPUT_GET, 'id'); // Récupérer le nom du fichier
                (new blog\controllers\ComparaisonController())->execute(null,null,$id);
                break;
            case 'reloadExpUpdate':
                $data = json_decode(file_get_contents('php://input'), true);
                (new blog\controllers\ComparaisonController())->updateExperimentationCharts($data);
                break;

            case 'get_all_folders':
                (new blog\controllers\Upload())->selectFolder();
                break;
            case 'deletFileExp':
                (new blog\controllers\ComparaisonController())->deleteExp();
                break;
            case 'logout':
                (new blog\controllers\AuthentificationController())->deconnexion();
                break;
            case 'home':
                (new blog\controllers\HomepageController())->execute();
            default:
                (new blog\controllers\HomepageController())->execute();
        }
    }else {
        (new blog\controllers\HomepageController())->execute();
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

