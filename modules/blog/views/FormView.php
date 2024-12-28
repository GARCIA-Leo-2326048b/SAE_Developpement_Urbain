<?php

namespace blog\views;

class FormView
{
    public function renderVectorForm(): void {
        $folderHistory = HistoriqueView::getInstance([]);
        ?>
        <form id="vectorForm" action="?action=upload" method="POST" enctype="multipart/form-data" style="display: none;">
            <h2>Téléchargement de Shapefile</h2>
            <label for="shapefile_name">Nom du fichier (sans extension) :</label>
            <input type="text" id="shapefile_name" name="shapefile_name" required>
            <br><br>
            <label for="shapefile">Sélectionnez les 3 fichiers requis :</label>
            <input type="file" id="shapefile" name="shapefile[]" accept=".shp,.shx,.dbf" multiple required>
            <br><br>
            <label for="dossier_parent">Sélectionnez un dossier :</label>
            <select class="folder-selector" id="dossier_parent" name="dossier_parent">
                <?php $folderHistory->generateFolderOptions($folderHistory->getFiles()); ?>
            </select>
            <input type="submit" value="Télécharger">
        </form>
        <?php
    }

    public function renderRasterForm(): void {
        $folderHistory = HistoriqueView::getInstance([]);
        ?>
        <form id="rasterForm" action="?action=upload" method="POST" enctype="multipart/form-data" style="display: none;">
            <h2>Téléchargement de Raster</h2>
            <label for="rasterfile_name">Nom du fichier (sans extension) :</label>
            <input type="text" id="rasterfile_name" name="rasterfile_name" required>
            <br><br>
            <label for="rasterfile">Sélectionnez un fichier Raster (TIFF, PNG, etc.) :</label>
            <input type="file" id="rasterfile" name="rasterfile" accept=".tif,.tiff,.png,.jpg,.jpeg" required>
            <br><br>
            <label for="dossier_parent">Sélectionnez un dossier :</label>
            <select class="folder-selector" id="dossier_parent" name="dossier_parent">
                <?php $folderHistory->generateFolderOptions($folderHistory->getFiles()); ?>
            </select>
            <input type="submit" value="Télécharger">
        </form>
        <?php
    }
}
