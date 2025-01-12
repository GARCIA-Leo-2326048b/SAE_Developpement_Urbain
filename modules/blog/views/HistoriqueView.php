<?php

namespace blog\views;

class HistoriqueView
{
    private $files;
    private $closetag = '</ul>';

    public function __construct($files) {
        $this->files = $files;
    }

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

    private function isFile(array $item): bool {
        return isset($item['type']) && $item['type'] === 'file';
    }

    private function renderFile(array $file): void {
        $name = htmlspecialchars($file['name']);
        if (isset($file['exp']) && $file['exp'] === 'oui') {
            $id = htmlspecialchars($file['id'] ?? '');
            echo "<button class='history-file experiment-file' onclick=\"showExperimentPopup('$name', '$id')\">$name</button>";
        } else {
            echo "<button class='history-file' onclick=\"showPopup('$name')\">$name</button>";
        }
    }

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

    private function renderFileList(array $files, string $folderId): void {
        echo "<ul id='" . $folderId . "-files' style='display: none;'>";
        foreach ($files as $file) {
            echo '<li>';
            $this->renderFile($file);
            echo '</li>';
        }
        echo $this->closetag;
    }

    private function renderChildFolders(array $children, string $historyId, string $parentFolderId): void {
        echo "<ul id='" . $parentFolderId . "-children' style='display: none;'>";
        $this->displayFolderTree($children, $historyId); // Recursive call
        echo $this->closetag;
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
        echo "<option value='root'>Root</option>";

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
