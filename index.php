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

                // Récupérer le paramètre 'file_name' depuis l'URL
                $house = filter_input(INPUT_GET, 'house'); // Récupérer le nom du fichier
                $road = filter_input(INPUT_GET, 'road');
                if ($house || $road) {
                    // Passer le paramètre 'file_name' au contrôleur pour traitement
                    (new blog\controllers\AffichageController())->execute($house, $road);
                } else {
                    echo "Aucun fichier sélectionné.";
                }
                break;
            case 'compare':
                (new blog\controllers\ComparaisonController())->execute('Household_3-2019.geojson','Buildings2019_ABM');
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
