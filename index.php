<?php
require 'modules/blog/controllers/HomepageController.php';
require  'modules/blog/controllers/Upload.php';


require 'modules/blog/controllers/AffichageController.php';
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
            case 'upload':
                (new blog\controllers\Upload())->telechargement();
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
