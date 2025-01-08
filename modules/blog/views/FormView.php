<?php

namespace blog\views;

class FormView
{
    public function renderButtons(): void {
        ?>
        <h2>Uploader des fichiers</h2>
        <button onclick="showForm('vector')">Uploader un fichier Shapefile (Vecteur)</button>
        <button onclick="showForm('raster')">Uploader un fichier Raster (Image)</button>
        <?php
    }

    public function renderForm(string $type): void {
        $folderHistory = HistoriqueView::getInstance([]);

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
                        <?php $folderHistory->generateFolderOptions($folderHistory->getFiles()); ?>
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
                    <select class="folder-selector" id="dossier_parent" name="dossier_parent">
                        <?php $folderHistory->generateFolderOptions($folderHistory->getFiles()); ?>
                    </select>
                <?php endif; ?>
                <input type="submit" value="Télécharger">
            </form>
            <?php
        }
    }

    public function renderAllForms(): void {
        $this->renderButtons();
        $this->renderForm('vector');
        $this->renderForm('raster');
    }
}
?>
