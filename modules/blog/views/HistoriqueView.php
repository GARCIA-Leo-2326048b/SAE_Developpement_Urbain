<?php

namespace blog\views;

class HistoriqueView
{
    private $files;

    public function __construct($files) {
        $this->files = $files;
    }

    private function displayFolderTree($folders, $historyId): void {
        $ulCloseTag = '</ul>';
        echo '<ul>';
        foreach ($folders as $folder) {
            echo '<li>';
            if (isset($folder['type']) && $folder['type'] === 'file') {
                // Vérifier si le fichier est une expérimentation
                if (isset($folder['exp']) && $folder['exp'] === 'oui') {
                    // Pop-up pour les fichiers d'expérimentation
                    echo "<button class='history-file experiment-file' onclick=\"showExperimentPopup('" . htmlspecialchars($folder['name']) . "')\">"
                        . htmlspecialchars($folder['name']) . "</button>";
                } else {
                    // Pop-up classique pour les fichiers
                    echo "<button class='history-file' onclick=\"showPopup('" . htmlspecialchars($folder['name']) . "')\">"
                        . htmlspecialchars($folder['name']) . "</button>";
                }
            } else {
                // Utilisation de l'ID unique ici pour chaque dossier
                $folderId = $historyId . '-' . htmlspecialchars($folder['name']);
                echo "<button class='folder-toggle' data-folder-id='" . htmlspecialchars($folder['name']) . "' 
                  oncontextmenu='showContextMenu(event, \"" . htmlspecialchars($folder['name']) . "\")'
                  onclick='toggleFolder(\"" . $folderId . "\")'>
                  <i class='icon-folder'>📁</i> " . htmlspecialchars($folder['name']) . "</button>";

                if (!empty($folder['files'])) {
                    // Ajouter les fichiers dans une liste
                    echo "<ul id='" . $folderId . "-files' style='display: none;'>";
                    foreach ($folder['files'] as $file) {
                        // Vérifier si le fichier est une expérimentation
                        if (isset($file['exp']) && $file['exp'] === 'oui') {
                            echo "<li><button class='history-file experiment-file' onclick=\"showExperimentPopup('" . htmlspecialchars($file['name']) . "')\">"
                                . htmlspecialchars($file['name']) . "</button></li>";
                        } else {
                            echo "<li><button class='history-file' onclick=\"showPopup('" . htmlspecialchars($file['name']) . "')\">"
                                . htmlspecialchars($file['name']) . "</button></li>";
                        }
                    }
                    echo $ulCloseTag;
                }

                if (!empty($folder['children'])) {
                    // Afficher les dossiers enfants
                    echo "<ul id='" . $folderId . "-children' style='display: none;'>";
                    $this->displayFolderTree($folder['children'], $historyId);  // Recurse pour afficher les enfants
                    echo $ulCloseTag;
                }
            }
            echo '</li>';
        }
        echo $ulCloseTag;
    }



    public function render($historyId): void {
        // Encapsule le contenu dans un div
        echo "<div id='history-files'>";
        $this->displayFolderTree($this->files,$historyId);
        echo "</div>";
    }

    public function generateFolderOptions($folders, $prefix = ''): void
    {
        // Ajouter l'option par défaut "racine" ou "root"
        echo "<option value='root'>Choisir..</option>";

        // Parcourir les dossiers et générer les options
        foreach ($folders as $folder) {
            if (!isset($folder['type']) || $folder['type'] !== 'file') {
                echo "<option value='" . htmlspecialchars($folder['name']) . "'>" . $prefix . htmlspecialchars($folder['name']) . "</option>";
            }
        }
    }

    public function generateProjects($folders, $prefix = ''): void {
        foreach ($folders as $folder) {
                echo "<option value='" . htmlspecialchars($folder['projet']) . "'>" . $prefix . htmlspecialchars($folder['projet']) . "</option>";
        }
    }

    public function getFiles(): array {
        return $this->files;
    }
}
