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
                (new blog\controllers\AffichageController())->execute();
                break;
            case 'compare':
                (new blog\controllers\ComparaisonController())->compare();
                break;
            case 'affichageTiff':
                (new blog\controllers\AffichageTiffController())->execute();
                break;
            case 'authentification':
                (new blog\controllers\AuthentificationController())->execute();
                break;
            case 'login':
                (new blog\controllers\AuthentificationController())->connexion();
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
                (new blog\controllers\Upload())->folder();
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
