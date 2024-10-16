<?php

namespace blog\controllers;
use blog\views\AffichageTiffView;

class AffichageTiffController
{
    private $view;

    public function __construct() {
        $this->view = new AffichageTiffView(); // Passe l'URL du fichier TIFF à la vue
    }

    public function execute() {
        // Affiche la vue avec l'URL du fichier TIFF
        $tiffPath = '/_assets/utils/valenicina_17_08_19_dtm.tiff';
        $this->view->show($tiffPath);
    }
}