<?php

namespace blog\views;

/**
 * Classe HistoriqueView
 *
 * Cette classe gère l'affichage de l'historique des fichiers et des dossiers.
 */
class HistoriqueView
{
    /**
     * @var array $files Liste des fichiers et dossiers
     */
    private $files;

    /**
     * @var string $closetag Balise de fermeture pour les listes
     */
    private $closetag = '</ul>';

    /**
     * Constructeur de la classe HistoriqueView
     *
     * Initialise la vue avec la liste des fichiers et dossiers.
     *
     * @param array $files Liste des fichiers et dossiers
     */
    public function __construct($files) {
        $this->files = $files;
    }

    /**
     * Afficher l'arborescence des dossiers
     *
     * Affiche l'arborescence des dossiers et fichiers sous forme de liste.
     *
     * @param array $folders Liste des dossiers
     * @param string $historyId ID de l'historique
     * @return void
     */
    private function displayFolderTree(array $folders, string $historyId): void {
        echo '<ul>';
        foreach ($folders as $folder) {
            echo '<li>';
            if ($this->isFile($folder)) {
                $this->renderFile($folder);
            } else {
                $this->renderFolder($folder, $historyId);
            }
            echo '</li>';
        }
        echo $this->closetag;
    }

    /**
     * Vérifier si l'élément est un fichier
     *
     * Vérifie si l'élément donné est un fichier.
     *
     * @param array $item Élément à vérifier
     * @return bool True si l'élément est un fichier, sinon False
     */
    private function isFile(array $item): bool {
        return isset($item['type']) && $item['type'] === 'file';
    }

    /**
     * Afficher un fichier
     *
     * Affiche un fichier sous forme de bouton.
     *
     * @param array $file Détails du fichier
     * @return void
     */
    private function renderFile(array $file): void {
        $name = htmlspecialchars($file['name']);
        if (isset($file['exp']) && $file['exp'] === 'oui') {
            $id = htmlspecialchars($file['id'] ?? '');
            echo "<button class='history-file experiment-file' onclick=\"showExperimentPopup('$name', '$id')\">$name</button>";
        } else {
            echo "<button class='history-file' onclick=\"showPopup('$name')\">$name</button>";
        }
    }

    /**
     * Afficher un dossier
     *
     * Affiche un dossier sous forme de bouton et son contenu.
     *
     * @param array $folder Détails du dossier
     * @param string $historyId ID de l'historique
     * @return void
     */
    private function renderFolder(array $folder, string $historyId): void {
        $name = htmlspecialchars($folder['name']);
        $folderId = $historyId . '-' . $name;

        echo "<button class='folder-toggle' data-folder-id='$name' oncontextmenu='showContextMenu(event, \"$name\")' onclick='toggleFolder(\"$folderId\")'>
          <i class='icon-folder'>📁</i> $name</button>";

        if (!empty($folder['files'])) {
            $this->renderFileList($folder['files'], $folderId);
        }

        if (!empty($folder['children'])) {
            $this->renderChildFolders($folder['children'], $historyId, $folderId);
        }
    }

    /**
     * Afficher la liste des fichiers
     *
     * Affiche la liste des fichiers d'un dossier.
     *
     * @param array $files Liste des fichiers
     * @param string $folderId ID du dossier
     * @return void
     */
    private function renderFileList(array $files, string $folderId): void {
        echo "<ul id='" . $folderId . "-files' style='display: none;'>";
        foreach ($files as $file) {
            echo '<li>';
            $this->renderFile($file);
            echo '</li>';
        }
        echo $this->closetag;
    }

    /**
     * Afficher les sous-dossiers
     *
     * Affiche les sous-dossiers d'un dossier.
     *
     * @param array $children Liste des sous-dossiers
     * @param string $historyId ID de l'historique
     * @param string $parentFolderId ID du dossier parent
     * @return void
     */
    private function renderChildFolders(array $children, string $historyId, string $parentFolderId): void {
        echo "<ul id='" . $parentFolderId . "-children' style='display: none;'>";
        $this->displayFolderTree($children, $historyId); // Appel récursif
        echo $this->closetag;
    }

    /**
     * Afficher l'historique des fichiers
     *
     * Affiche l'historique des fichiers et dossiers.
     *
     * @param string $historyId ID de l'historique
     * @return void
     */
    public function render($historyId): void {
        // Encapsule le contenu dans un div
        echo "<div id='history-files'>";
        $this->displayFolderTree($this->files, $historyId);
        echo "</div>";
    }

    /**
     * Générer les options de dossier
     *
     * Génère les options de sélection de dossier pour un formulaire.
     *
     * @param array $folders Liste des dossiers
     * @param string $prefix Préfixe pour les options
     * @return void
     */
    public function generateFolderOptions($folders, $prefix = ''): void {
        // Ajouter l'option par défaut "racine" ou "root"
        echo "<option value='root'>Root</option>";

        // Parcourir les dossiers et générer les options
        foreach ($folders as $folder) {
            if (!isset($folder['type']) || $folder['type'] !== 'file') {
                echo "<option value='" . htmlspecialchars($folder['name']) . "'>" . $prefix . htmlspecialchars($folder['name']) . "</option>";
            }
        }
    }

    /**
     * Générer les projets
     *
     * Génère les options de sélection de projet pour un formulaire.
     *
     * @param array $folders Liste des dossiers
     * @param string $prefix Préfixe pour les options
     * @return void
     */
    public function generateProjects($folders, $prefix = ''): void {
        foreach ($folders as $folder) {
            echo "<option value='" . htmlspecialchars($folder['projet']) . "'>" . $prefix . htmlspecialchars($folder['projet']) . "</option>";
        }
    }

    /**
     * Obtenir les fichiers
     *
     * Retourne la liste des fichiers et dossiers.
     *
     * @return array Liste des fichiers et dossiers
     */
    public function getFiles(): array {
        return $this->files;
    }
}

?>