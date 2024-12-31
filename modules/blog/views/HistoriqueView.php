<?php

namespace blog\views;

class HistoriqueView
{
    private static $instance = null;
    private $files;

    private function __construct($files) {
        $this->files = $files;
    }

    public static function getInstance($files): self {
        if (self::$instance === null) {
            self::$instance = new self($files);
        }
        return self::$instance;
    }

    private function displayFolderTree($folders): void {
        $ulCloseTag = '</ul>';
        echo '<ul>';
        foreach ($folders as $folder) {
            echo '<li>';
            if (isset($folder['type']) && $folder['type'] === 'file') {
                echo "<button class='history-file' onclick=\"showPopup('" . htmlspecialchars($folder['name']) . "')\">"
                    . htmlspecialchars($folder['name']) . "</button>";
            } else {
                echo "<button class='folder-toggle' data-folder-id='" . htmlspecialchars($folder['name']) . "'oncontextmenu='showContextMenu(event, \"" . htmlspecialchars($folder['name']) . "\")'onclick='toggleFolder(\"" . htmlspecialchars($folder['name']) . "\")'>";
                echo "<i class='icon-folder'>📁</i> " . htmlspecialchars($folder['name']) . "</button>";

                if (!empty($folder['files'])) {
                    echo "<ul id='" . htmlspecialchars($folder['name']) . "-files' style='display: none;'>";
                    foreach ($folder['files'] as $file) {
                        echo "<li><button class='history-file' onclick=\"showPopup('" . htmlspecialchars($file) . "')\">"
                            . htmlspecialchars($file) . "</button></li>";
                    }
                    echo $ulCloseTag;
                }

                if (!empty($folder['children'])) {
                    echo "<ul id='" . htmlspecialchars($folder['name']) . "-children' style='display: none;'>";
                    $this->displayFolderTree($folder['children']);
                    echo $ulCloseTag;
                }
            }
            echo '</li>';
        }
        echo $ulCloseTag;
    }

    public function render(): void {
        $this->displayFolderTree($this->files);
    }

    public function generateFolderOptions($folders, $prefix = ''): void {
        foreach ($folders as $folder) {
            if (!isset($folder['type']) || $folder['type'] !== 'file') {
                echo "<option value='" . htmlspecialchars($folder['name']) . "'>" . $prefix . htmlspecialchars($folder['name']) . "</option>";
            }
        }
    }

    public function getFiles(): array {
        return $this->files;
    }
}
