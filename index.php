<?php

require __DIR__ . '/vendor/autoload.php';

session_start();


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

                if ($files) {
                    // Décoder la chaîne et convertir en tableau
                    $filesArray = explode(',', urldecode($files));

                    // Passer la liste des fichiers au contrôleur
                    (new blog\controllers\AffichageController())->execute($filesArray);

                    // Stocker les fichiers de simulation en session pour réutilisation
                    $_SESSION['simFiles'] = $filesArray;
                }
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
                case 'simulation':
                    (new blog\controllers\SimulationController())->simuler();
                    break;
            case 'new_simulation':
                (new blog\controllers\WorkSpaceController())->execute();
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
                (new blog\controllers\ComparaisonController())->execute(null,null,null,$id);
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
            default:
                (new blog\controllers\HomepageController())->execute();
        }
    }else {
        (new blog\controllers\HomepageController())->execute();
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}

?>
