<?php

namespace blog\views;

/**
 * Classe FormView
 *
 * Cette classe gère l'affichage des formulaires d'upload de fichiers.
 */
class FormView
{
    /**
     * @var HistoriqueView $folderHistory Vue pour l'historique des dossiers
     */
    private $folderHistory;

    /**
     * Constructeur de la classe FormView
     *
     * Initialise la vue avec l'historique des dossiers.
     *
     * @param array $files Liste des fichiers
     */
    public function __construct($files){
        $this->folderHistory = new HistoriqueView($files);
    }

    /**
     * Afficher les boutons de formulaire
     *
     * Affiche les boutons pour uploader des fichiers Shapefile et Raster.
     *
     * @return void
     */
    public function renderButtons(): void {
        ?>
        <h2>Uploader des fichiers</h2>
        <button onclick="showForm('vector')">Uploader un  Shapefile </button>
        <button onclick="showForm('raster')">Uploader un  Raster </button>
        <?php
    }


    /**
     * Afficher le formulaire
     *
     * Affiche le formulaire d'upload en fonction du type de fichier (vector ou raster).
     *
     * @param string $type Type de fichier (vector ou raster)
     * @return void
     */
    public function renderForm(string $type): void {

        if ($type === 'vector') {
            ?>
            <form id="vectorForm" action="?action=upload" method="POST" enctype="multipart/form-data" style="display: none; position: relative;">
                <button type="button" onclick="closeForm('vectorForm')" style="position: absolute; top: 5px; right: 5px; background: none; border: none; cursor: pointer;">&times;</button>
                <h2>Téléchargement de Shapefile</h2>
                <label for="shapefile_name">Nom du fichier (sans extension) :</label>
                <input type="text" id="shapefile_name" name="shapefile_name" required>
                <br><br>
                <label for="geojson">Sélectionnez un fichier GeoJSON :</label>
                <input type="file" id="geojson" name="geojson" accept=".geojson" required>
                <br><br>
                <?php if (isset($_SESSION['suid'])): ?>
                    <label for="dossier_parent">Sélectionnez un dossier :</label>
                    <select class="folder-selector" id="dossier_parent" name="dossier_parent">
                        <?php $this->folderHistory->generateFolderOptions($this->folderHistory->getFiles()); ?>
                    </select>
                <?php endif; ?>
                <input type="submit" value="Télécharger">
            </form>
            <?php
        } elseif ($type === 'raster') {
            ?>
            <form id="rasterForm" action="?action=upload" method="POST" enctype="multipart/form-data" style="display: none; position: relative;">
                <button type="button" onclick="closeForm('rasterForm')" style="position: absolute; top: 5px; right: 5px; background: none; border: none; cursor: pointer;">&times;</button>
                <h2>Téléchargement de Raster</h2>
                <label for="rasterfile_name">Nom du fichier (sans extension) :</label>
                <input type="text" id="rasterfile_name" name="rasterfile_name" required>
                <br><br>
                <label for="rasterfile">Sélectionnez un fichier Raster (TIFF, PNG, etc.) :</label>
                <input type="file" id="rasterfile" name="rasterfile" accept=".tif,.tiff,.png,.jpg,.jpeg" required>
                <br><br>
                <?php if (isset($_SESSION['suid'])): ?>
                    <label for="dossier_parent">Sélectionnez un dossier :</label>
                    <select id="dossier_parent" name="dossier_parent">
                        <?php $this->folderHistory->generateFolderOptions($this->folderHistory->getFiles()); ?>
                    </select>
                <?php endif; ?>
                <input type="submit" value="Télécharger">
            </form>

            <?php
        }
    }


    /**
     * Afficher tous les formulaires
     *
     * Affiche les boutons et les formulaires pour uploader des fichiers vector et raster.
     *
     * @return void
     */
    public function renderAllForms(): void {
        $this->renderButtons();
        $this->renderForm('vector');
        $this->renderForm('raster');
    }
}
?>
