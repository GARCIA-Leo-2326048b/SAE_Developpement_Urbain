<?php

use PHPUnit\Framework\TestCase;
use blog\controllers\AffichageController;
use blog\models\GeoJsonModel;
use blog\views\AffichageView;

class AffichageControllerTest extends TestCase
{
    private $controller;
    private $mockModel;
    private $mockView;

    protected function setUp(): void
    {
        // Création des mocks pour les dépendances
        $this->mockModel = $this->createMock(GeoJsonModel::class);
        $this->mockView = $this->createMock(AffichageView::class);

        // Création de l'instance du contrôleur avec les mocks injectés
        $this->controller = new AffichageController(); // Utilise le vrai contrôleur
        $this->controller->setModel($this->mockModel); // Injection du modèle mocké
        $this->controller->setView($this->mockView);   // Injection de la vue mockée
    }

    public function testExecuteWithValidFileId()
    {
        // ID de fichier de tests
        $fileId = 'validFile';
        $geoJsonData = '{"type": "FeatureCollection", "features": []}';

        // Configurer le mock du modèle pour retourner des données GeoJSON
        $this->mockModel->expects($this->once())
            ->method('fetchGeoJson')
            ->with($fileId)
            ->willReturn($geoJsonData);

        // Configurer le mock de la vue pour vérifier qu'elle reçoit les bonnes données
        $this->mockView->expects($this->once())
            ->method('show')
            ->with($geoJsonData, null, null, null);

        // Exécuter la méthode avec un ID valide
        $this->controller->execute($fileId);
    }

    public function testExecuteWithInvalidFileId()
    {
        // ID de fichier inexistant
        $fileId = 'invalidFile';

        // Configurer le mock du modèle pour retourner `null` si le fichier n'existe pas
        $this->mockModel->expects($this->once())
            ->method('fetchGeoJson')
            ->with($fileId)
            ->willReturn(null);

        // Configurer le mock de la vue pour vérifier qu'elle reçoit `null`
        $this->mockView->expects($this->once())
            ->method('show')
            ->with(null, null, null, null);

        // Exécuter la méthode avec un ID invalide
        $this->controller->execute($fileId);
    }
}
