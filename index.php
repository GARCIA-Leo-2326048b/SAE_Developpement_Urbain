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
                $fileId = filter_input(INPUT_GET, 'file_name'); // Récupérer le nom du fichier
                if ($fileId) {
                    // Passer le paramètre 'file_name' au contrôleur pour traitement
                    (new blog\controllers\AffichageController())->execute($fileId);
                } else {
                    echo "Aucun fichier sélectionné.";
                }
                break;
            case 'compare':
                (new blog\controllers\ComparaisonController())->compare('Household_3-2019.geojson','Buildings2019_ABM');
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
            case 'view_simulations':
                (new blog\controllers\Upload())->telechargement();
                break;
            case 'new_simulation':
                (new blog\controllers\SimulationController())->execute();
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
            case 'get_all_folders':
                (new blog\controllers\Upload())->selectFolder();
                break;
            case 'logout':
                (new blog\controllers\AuthentificationController())->deconnexion();
                break;
            case 'save_experimentation':
                (new blog\controllers\ComparaisonController())->saveExperimentation();
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
