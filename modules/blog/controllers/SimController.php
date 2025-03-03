<?php
namespace blog\controllers;

use blog\models\SingletonModel;
use blog\models\UploadModel;
use Exception;

/**
 * Classe SimController
 *
 * Cette classe gère l'exécution des simulations pour les utilisateurs connectés.
 */
class SimController
{
    /**
     * @var \PDO $db Connexion à la base de données
     */
    private $db;

    /**
     * @var UploadModel $uploadModel Instance du modèle d'upload
     */
    private $uploadModel;

    /**
     * @var int $currentUserId ID de l'utilisateur actuel
     */
    private $currentUserId;

    /**
     * @var int|null $currentProject ID du projet actuel
     */
    private $currentProject;

    /**
     * Constructeur de la classe SimController
     *
     * Initialise la connexion à la base de données, le modèle d'upload et vérifie si l'utilisateur est connecté.
     *
     * @throws Exception Si l'utilisateur n'est pas connecté
     */
    public function __construct()
    {
        session_start();
        $this->db = SingletonModel::getInstance()->getConnection();
        $this->uploadModel = new UploadModel($this->db);

        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=authentification");
            exit();
        }

        $this->currentUserId = $_SESSION['user_id'];
        $this->currentProject = $_SESSION['current_project_id'] ?? null;
    }

    /**
     * Exécute la simulation
     *
     * Vérifie la méthode HTTP, lit les données JSON envoyées par le client, et exécute le script Python pour la simulation.
     *
     * @return void
     */
    public function runSimulation()
    {
        header('Content-Type: application/json');

        try {
            // 🔹 Vérification de la méthode HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée', 405);
            }

            // 🔹 Lire le JSON envoyé depuis le client
            $inputJSON = file_get_contents("php://input");
            $input = json_decode($inputJSON, true);

            if (!$input || !isset($input['params']) || !isset($input['files'])) {
                throw new Exception('Les paramètres ou la liste de fichiers sont manquants.', 400);
            }

            $params = $input['params'];
            $files = $input['files'];
            $starting_date = $input['starting_date'] ?? '1994';
            $validation_date = $input['validation_date'] ?? '2002';
            $building_delta = $input['building_delta'] ?? 22;
            $namesim = $input['sim_name'] ?? 'Simulation';
            $folder = $input['sim_folder'] ?? 'root';


            // Création d'un dossier temporaire
            $tempDir = $this->createTempDirectory();
            $filePaths = $this->storeGeoJSONFiles($files, $tempDir);
            $tomlPath = $this->generateTomlConfig($filePaths, $tempDir);
            $output = $this->executePythonScript($tomlPath, $params,$namesim,$folder);

            echo json_encode([
                'success' => true,
                'result' => json_decode($output, true),
                'downloadUrl' => "index.php?action=download&file=result.geojson"
            ]);

        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Crée un répertoire temporaire pour stocker les fichiers de simulation
     *
     * @return string Chemin du répertoire temporaire
     * @throws Exception Si la création du répertoire échoue
     */
    private function createTempDirectory()
    {
        $tempDir = sys_get_temp_dir() . '/sim_' . md5(session_id() . microtime());
        if (!mkdir($tempDir, 0755, true) && !is_dir($tempDir)) {
            throw new Exception('Erreur de création du dossier temporaire', 500);
        }
        return $tempDir;
    }

    /**
     * Stocke les fichiers GeoJSON dans le répertoire temporaire
     *
     * @param array $selectedFiles Liste des fichiers sélectionnés
     * @param string $tempDir Chemin du répertoire temporaire
     * @return array Chemins des fichiers stockés
     */
    private function storeGeoJSONFiles($selectedFiles, $tempDir)
    {
        $filePaths = [];

        foreach ($selectedFiles as $file) {
            $content = $this->uploadModel->getGeoJSONContent($file, $this->currentUserId, $this->currentProject);

            if (!$content) {
                error_log("⚠ Fichier {$file} introuvable.");
                continue;
            }

            $filePath = "{$tempDir}/" . basename($file);

            if (file_put_contents($filePath, $content) !== false) {
                $filePaths[] = $filePath;
            } else {
                error_log("❌ Impossible d'écrire {$filePath}.");
            }
        }

        return $filePaths;
    }

    /**
     * Génère le fichier de configuration TOML pour la simulation
     *
     * @param array $filePaths Chemins des fichiers GeoJSON
     * @param string $tempDir Chemin du répertoire temporaire
     * @return string Chemin du fichier TOML généré
     */
    private function generateTomlConfig($filePaths, $tempDir)
    {
        $starting_date = $_POST['starting_date'] ?? '1994';
        $validation_date = $_POST['validation_date'] ?? '2002';
        $building_delta = $_POST['building_delta'] ?? 22;
        $length = intval($validation_date) - intval($starting_date);
        $nameSim = $_POST['sim_name'] ?? 'Simulation';

        // 🔹 Obtenir le chemin absolu de `saeIA/`
        $basePath = realpath(dirname(__DIR__, 3) . "/saeIA");

        if (!$basePath) {
            throw new Exception("❌ Le dossier saeIA est introuvable !");
        }

        // 🔹 Définir les chemins dynamiques
        $roadFile = $basePath . "/data/valenicina/features/roads_2002.geojson";
        $buildingFile = $basePath . "/data/valenicina/features/buildings_1994.geojson";
        $borderFile = $basePath . "/data/valenicina/features/border.geojson";
        $demFile = $basePath . "/data/valenicina/features/dem_2019.tiff";
        $incomeFile = $basePath . "/data/valenicina/factors/income_2019.csv";

        foreach ($filePaths as $file) {
            $category = $this->checkGeoJSONCategory($file);
            if ($category["isRoad"]) {
                $roadFile = $file;
            }
            if ($category["isBuilding"]) {
                $buildingFile = $file;
            }
        }

        // 🔹 Remplacer `\` par `/` pour éviter les erreurs sous Windows
        $roadFile = str_replace('\\', '/', $roadFile);
        $buildingFile = str_replace('\\', '/', $buildingFile);
        $borderFile = str_replace('\\', '/', $borderFile);
        $demFile = str_replace('\\', '/', $demFile);
        $incomeFile = str_replace('\\', '/', $incomeFile);

        // 🔹 Générer le contenu du fichier TOML
        $tomlContent = <<<TOML
name = '{$nameSim}'
starting_date = '{$starting_date}'
validation_date = '{$validation_date}'
timezone = 'Pacific/Fiji'
crs = 'EPSG:3460'

[timestep]
length = {$length}
unit = 'years'
building_delta = {$building_delta}

[border]
file = '{$borderFile}'

[[agents]]
class_name = 'Road'
unique_id = "id"
scheduled = false
set_attributes = true
[agents.files]
{$starting_date} = '{$roadFile}'

[[agents]]
class_name = 'Dwelling'
unique_id = 'Id'
scheduled = true
set_attributes = false
[agents.files]
{$starting_date} = '{$buildingFile}'

[[agents]]
class_name = 'LandOwner'
scheduled = true
[[agents.individuals]]
unique_id = 'LO'

[[rasters]]
name = 'topography'
file = '{$demFile}'
undefined_value = -10000

# Beware, all CSV share this loading settings
[csv_options]
sep = ';'
decimal = ','
skipfooter = 1 # Number of lines to skip at the bottom of the csv files

[[factors]]
name = 'weekly_income'
index_column = 'Weekly income'
probabilities_column = 'Prob.'
[factors.files]
2019 = '{$incomeFile}'
TOML;

        $tomlPath = "{$tempDir}/config.toml";
        file_put_contents($tomlPath, $tomlContent);
        return $tomlPath;
    }


    /**
     * Vérifie la catégorie des fichiers GeoJSON
     *
     * @param string $filePath Chemin du fichier GeoJSON
     * @return array Catégories des fichiers (isRoad, isBuilding)
     */
    private function checkGeoJSONCategory($filePath)
    {
        if (!file_exists($filePath)) {
            return ["isRoad" => false, "isBuilding" => false];
        }

        $jsonContent = file_get_contents($filePath);
        $geojson = json_decode($jsonContent, true);

        if ($geojson === null) {
            return ["isRoad" => false, "isBuilding" => false];
        }

        $isRoad = false;
        $isBuilding = false;

        foreach ($geojson['features'] ?? [] as $feature) {
            $category = $feature['properties']['Category'] ?? null;
            if (is_numeric($category)) {
                $isRoad = true;
            }
            if ($category === "Dwelling") {
                $isBuilding = true;
            }
        }
        return ["isRoad" => $isRoad, "isBuilding" => $isBuilding];
    }

    /**
     * Exécute le script Python pour la simulation
     *
     * @param string $tomlPath Chemin du fichier TOML
     * @param array $params Paramètres de la simulation
     * @param string $namesim Nom de la simulation
     * @param string $folder Dossier de stockage
     * @return string Résultat de la simulation
     * @throws Exception Si l'exécution du script échoue
     */
    private function executePythonScript($tomlPath, $params, $namesim, $folder)
    {
        // Création d'un fichier temporaire pour les paramètres
        $tempFile = tempnam(sys_get_temp_dir(), 'params_');
        file_put_contents($tempFile, json_encode($params));

        $testPath = dirname(__DIR__, 3) . "/saeIA";
        error_log("🔍 PHP doit chercher ici : " . $testPath);

        if (!file_exists($testPath)) {
            throw new Exception("❌ Le dossier saeIA est toujours introuvable à : " . $testPath);
        }


        $basePath = realpath(dirname(__DIR__, 3) . "/saeIA");


        if (!$basePath) {
            throw new Exception("Le dossier saeIA est introuvable !");
        }

        // 🔹 Définir les chemins absolus
        $scriptPath = $basePath . "/run_simulation.py";
        $geojsonFile = $basePath . "/simulation_final.geojson";

        // 🔹 Correction du format des chemins pour éviter les erreurs sous Windows
        $scriptPath = str_replace('\\', '/', $scriptPath);
        $tomlPath = str_replace('\\', '/', $tomlPath);
        $geojsonFile = str_replace('\\', '/', $geojsonFile);

        // 🔹 Construire la commande d'exécution du script Python
        $command = sprintf(
            'pixi run python %s --config %s --params_file %s 2>&1',
            escapeshellarg($scriptPath),
            escapeshellarg($tomlPath),
            escapeshellarg($tempFile)
        );

        // 🔹 Debugging - Vérifier la commande exécutée
        error_log("Commande exécutée : " . $command);

        // 🔹 Vérifier si Pixi est bien installé dans le bon dossier
        if (!is_dir($basePath)) {
            throw new Exception("Le dossier saeIA est introuvable !");
        }

        // 🔹 Exécution de la commande
        $descriptorspec = [
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];
        $process = proc_open($command, $descriptorspec, $pipes, $basePath);

        if (!is_resource($process)) {
            throw new Exception("Échec de l'exécution du script", 500);
        }

        $output = stream_get_contents($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        // 🔹 Logs d'exécution pour le débogage
        error_log("Sortie script : " . $output);
        error_log("Erreur script : " . $errorOutput);

        // 🔹 Vérifier si le fichier GeoJSON a bien été généré
        if (!file_exists($geojsonFile)) {
            throw new Exception("Le fichier GeoJSON de sortie n'a pas été trouvé !", 500);
        }

        // 🔹 Lire et retourner le contenu du GeoJSON
        $geojsonContent = file_get_contents($geojsonFile);
        $this->uploadModel->saveUploadGJ($namesim, $geojsonContent, $this->currentUserId, $folder, $this->currentProject);

        // 🔹 Nettoyage du fichier temporaire
        unlink($tempFile);

        return json_encode([
            "success" => true,
            "geojson" => $geojsonFile
        ]);
    }





    /**
     * Formate le résultat de la simulation
     *
     * @param array $output Résultat brut de la simulation
     * @return array Résultat formaté
     */
    private function formatResult($output)
    {
        return array_map('htmlspecialchars', $output);
    }

    /**
     * Stocke le résultat de la simulation dans un fichier
     *
     * @param array $output Résultat de la simulation
     * @param string $tempDir Chemin du répertoire temporaire
     * @return string URL de téléchargement du fichier de résultat
     */
    private function storeResult($output, $tempDir)
    {
        $resultFile = "{$tempDir}/result.geojson";
        file_put_contents($resultFile, implode("\n", $output));
        return "index.php?action=download&file=" . basename($resultFile);
    }
}
