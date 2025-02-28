/**
 * Classe gérant l'affichage de la carte et les différentes couches.
 */
class MapManager {
    /**
     * Constructeur de la classe MapManager.
     * @param {Array} layers - Liste des données GeoJSON et GeoTIFF.
     * @param {Object} house - Données GeoJSON des maisons.
     * @param {Object} road - Données GeoJSON des routes.
     * @param {string} tiffUrl - URL du fichier GeoTIFF.
     * @param {string} idMap - ID de l'élément HTML contenant la carte.
     */
    constructor(layers = [] = null, house = null, road = null, tiffUrl = null, idMap = 'map') {
        this.map = L.map(idMap).setView([0, 0], 16);
        this.idMap = idMap;
        this.key = 'phT89U7mj4WtQWinX1ID';
        this.currentLayer = null;
        this.overlayMaps = {};
        this.layerControl = null;
        this.genericLayer = null;

        // Création des couches de fond de carte
        this.satelliteLayer = L.tileLayer(`https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key=${this.key}`, {
            tileSize: 512,
            zoomOffset: -1,
            minZoom: 1,
            attribution: "\u003ca href=\"https://www.maptiler.com/copyright/\" target=\"_blank\"\u003e\u0026copy; MapTiler\u003c/a\u003e \u003ca href=\"https://www.openstreetmap.org/copyright\" target=\"_blank\"\u003e\u0026copy; OpenStreetMap contributors\u003c/a\u003e",
            crossOrigin: true
        });

        this.streetsLayer = L.tileLayer(`https://api.maptiler.com/maps/streets/{z}/{x}/{y}.png?key=${this.key}`, {
            tileSize: 512,
            zoomOffset: -1,
            minZoom: 1,
            attribution: "\u003ca href=\"https://www.maptiler.com/copyright/\" target=\"_blank\"\u003e\u0026copy; MapTiler\u003c/a\u003e \u003ca href=\"https://www.openstreetmap.org/copyright\" target=\"_blank\"\u003e\u0026copy; OpenStreetMap contributors\u003c/a\u003e",
            crossOrigin: true
        });

        // Ajout de la couche satellite par défaut
        this.streetsLayer.addTo(this.map);

        if (house) {
            const houseLayer = L.geoJSON(house, { color: '#bd134c', weight: 2, fillColor: '#e4a0b5', fillOpacity: 1 }).addTo(this.map);
            this.overlayMaps["Maisons"] = houseLayer;
            this.map.fitBounds(houseLayer.getBounds());
        }

        if (road) {
            const roadLayer = L.geoJSON(road, { color: '#0d61fa', weight: 2, fillColor: '#0d61fa', fillOpacity: 1 }).addTo(this.map);
            this.overlayMaps["Routes"] = roadLayer;
        }

        if (tiffUrl) {
            this.addGeoTiffLayer(tiffUrl, "GeoTIFF");
        }


        if (layers) {
            const fileNames = this.getFilesFromCurrentUrl();
            console.log("Fichiers trouvés dans l'URL :", fileNames);

            const mappedLayers = fileNames.map((name, index) => ({
                name,
                data: layers[index] || null
            }));
            console.log("Association fichiers - layers :", mappedLayers);


            layers.forEach((layer, index) => {
                this.addGeoJsonLayer(layer, mappedLayers[index].name);
                this.updateLayerControl();
            });
        }
    }

    getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    getFilesFromCurrentUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const filesParam = urlParams.get("files");
        return filesParam ? filesParam.split(",") : [];
    }

    /**
     * Ajoute une couche GeoJSON à la carte.
     * @param {Object} geoJsonData - Données GeoJSON.
     * @param {string} layerName - Nom de la couche.
     */
    addGeoJsonLayer(geoJsonData, layerName) {
        if (typeof geoJsonData === 'string') {
            try {
                geoJsonData = JSON.parse(geoJsonData);
            } catch (e) {
                console.error("Invalid GeoJSON string:", e);
                return;
            }
        }
        const color = this.getRandomColor();
        const geoJsonLayer = L.geoJSON(geoJsonData, { color: color, weight: 2, fillColor: color, fillOpacity: 1 }).addTo(this.map);
        this.overlayMaps[layerName] = geoJsonLayer;
        this.updateLayerControl();
        // this.updateLayerButtons();
        this.map.fitBounds(geoJsonLayer.getBounds());
    }

    /**
     * Ajoute une couche de maisons à la carte.
     * @param {Object} house - Données GeoJSON des maisons.
     */
    addHouseLayer(house) {
        const houseLayer = L.geoJSON(house, { color: '#bd134c', weight: 2, fillColor: '#e4a0b5', fillOpacity: 1 }).addTo(this.map);
        this.overlayMaps["Maisons"] = houseLayer;
        this.updateLayerControl();
        this.updateLayerButtons();
        this.map.fitBounds(houseLayer.getBounds());
    }

    /**
     * Ajoute une couche de routes à la carte.
     * @param {Object} road - Données GeoJSON des routes.
     */
    addRoadLayer(road) {
        const roadLayer = L.geoJSON(road, { color: '#0d61fa', weight: 2, fillColor: '#0d61fa', fillOpacity: 1 }).addTo(this.map);
        this.overlayMaps["Routes"] = roadLayer;
        this.updateLayerControl();
        this.updateLayerButtons();
    }

    /**
     * Ajoute une couche GeoTIFF à la carte.
     * @param {string} tiffUrl - URL du fichier GeoTIFF.
     * @param {string} layerName - Nom de la couche.
     */
    addGeoTiffLayer(tiffUrl, layerName) {
        fetch(tiffUrl)
            .then(response => response.arrayBuffer())
            .then(arrayBuffer => parseGeoraster(arrayBuffer))
            .then(georaster => {
                const tiffLayer = new GeoRasterLayer({
                    georaster: georaster,
                    opacity: 1,
                    resolution: 64
                }).addTo(this.map);

                this.overlayMaps[layerName] = tiffLayer;
                this.updateLayerControl();
            })
            .catch(error => console.error('Erreur lors du chargement du GeoTIFF:', error));
    }

    /**
     * Met à jour le contrôle des couches.
     */
    updateLayerControl() {
        // Supprimer l'ancien contrôle s'il existe
        if (this.layerControl) {
            this.map.removeControl(this.layerControl);
        }

        // Créer un nouveau contrôle avec les couches mises à jour
        this.layerControl = L.control.layers(null, this.overlayMaps, { position: 'topright', collapsed: false });
        this.layerControl.addTo(this.map);
    }

    /**
     * Sélectionne une couche à afficher.
     * @param {string} layerName - Nom de la couche à sélectionner.
     */
    selectLayer(layerName) {
        if (this.overlayMaps[layerName]) {
            this.currentLayer = this.overlayMaps[layerName];
        } else {
            console.error("La couche spécifiée n'existe pas : ", layerName);
        }
    }

    /**
     * Met à jour l'opacité de la couche sélectionnée.
     */
    updateLayerOpacity() {
        const opacity = document.getElementById('opacitySlider'+this.idMap).value;
        if (this.currentLayer) {
            if (this.currentLayer.setOpacity) {
                this.currentLayer.setOpacity(opacity);
            } else {
                this.currentLayer.eachLayer(layer => {
                    layer.setStyle({
                        fillOpacity: opacity,
                        opacity: opacity
                    });
                });
            }
        }
    }

    /**
     * Bascule vers la couche satellite.
     */
    switchToSatellite() {
        this.map.removeLayer(this.streetsLayer);
        this.map.addLayer(this.satelliteLayer);
        this.satelliteLayer.bringToBack();
    }

    /**
     * Bascule vers la couche des rues.
     */
    switchToStreets() {
        this.map.removeLayer(this.satelliteLayer);
        this.map.addLayer(this.streetsLayer);
        this.streetsLayer.bringToBack();
    }

    /**
     * Supprime la couche sélectionnée.
     */
    supprimerCouche() {
        if (this.currentLayer) {
            // Supprimer la couche de la carte
            this.map.removeLayer(this.currentLayer);

            // Supprimer la couche de overlayMaps
            const layerName = this.getLayerName(this.currentLayer); // On récupère le nom de la couche
            if (this.overlayMaps[layerName]) {
                delete this.overlayMaps[layerName];
            }

            // Réinitialiser la couche sélectionnée
            this.currentLayer = null;

            // Mettre à jour le contrôle des couches
            this.updateLayerControl();

            // Mettre à jour les boutons dynamiquement
            this.updateLayerButtons();
        } else {
            console.error("Aucune couche sélectionnée à supprimer.");
        }
    }

    /**
     * Récupère le nom de la couche.
     * @param {Object} layer - La couche dont on veut le nom.
     * @returns {string|null} - Le nom de la couche ou null si non trouvé.
     */
    getLayerName(layer) {
        // Si tu as un nom spécifique pour chaque couche, tu peux ajuster cette fonction.
        for (let key in this.overlayMaps) {
            if (this.overlayMaps[key] === layer) {
                return key;
            }
        }
        return null;
    }

    /**
     * Met à jour les boutons de sélection des couches.
     */
    updateLayerButtons() {
        const layerButtonsDiv = document.getElementById('layerButtons'+ this.idMap);
        layerButtonsDiv.innerHTML = ''; // Efface tous les boutons existants pour éviter les doublons

        // Titre
        const title = document.createElement('h4');
        title.innerText = "Sélectionnez la couche :";
        layerButtonsDiv.appendChild(title);

        // Parcours des couches dans overlayMaps
        Object.keys(this.overlayMaps).forEach(layerName => {
            const button = document.createElement('button');
            button.id = `btn-${layerName}`;
            button.innerText = layerName;

            // Ajoute l'événement onclick pour sélectionner la couche
            button.onclick = () => this.selectLayer(layerName);

            // Ajoute le bouton au conteneur
            layerButtonsDiv.appendChild(button);
        });
    }

    /**
     * Affiche la pop-up de simulation.
     *
     */
    showSimulationPopup() {
        document.getElementById('simulationPopup').style.display = 'block';
    }

    /**
     * Ferme la pop-up de simulation.
     */
    closeSimulationPopup() {
        document.getElementById('simulationPopup').style.display = 'none';
    }

    /**
     * Exécute la simulation avec les paramètres choisis.
     */
    executeSimulation() {
        // Récupérer les valeurs des sliders
        const params = {
            neighbours_l_min: document.getElementById('neighbours_l_min').value,
            neighbours_l_0: document.getElementById('neighbours_l_0').value,
            neighbours_l_max: document.getElementById('neighbours_l_max').value,
            neighbours_w: document.getElementById('neighbours_w').value,
            roads_l_min: document.getElementById('roads_l_min').value,
            roads_l_0: document.getElementById('roads_l_0').value,
            roads_l_max: document.getElementById('roads_l_max').value,
            roads_w: document.getElementById('roads_w').value,
            paths_l_min: document.getElementById('paths_l_min').value,
            paths_l_max: document.getElementById('paths_l_max').value,
            paths_w: document.getElementById('paths_w').value,
            slope_l_min: document.getElementById('slope_l_min').value,
            slope_l_max: document.getElementById('slope_l_max').value,
            slope_w: document.getElementById('slope_w').value
        };

        // Construire la commande terminal
        let command = 'COMMANDE TERMINAL A FAIRE A FAAAAAIIIRE';
        for (const [key, value] of Object.entries(params)) {
            command += ` ${key}=${value}`;
        }

        // Exécuter la commande terminal
        console.log('Commande à exécuter :', command);
    }


}


/**
 * Gestion de l'upload et affichage du fichier GeoTIFF.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('uploadGeoTiff').addEventListener('change', async (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = async (e) => {
                const arrayBuffer = e.target.result;
                const georaster = await parseGeoraster(arrayBuffer); // Utilisation de georaster pour lire le fichier
                tiffLayer = new GeoRasterLayer({
                    georaster: georaster,
                    opacity: 1,
                    resolution: 64
                }).addTo(map.map);
                //recuperer le nom du fichier
                const layerName = file.name; // Nom de la couche basé sur le nom du fichier
                // Ajouter la couche à overlayMaps avec le nom de la couche
                map.overlayMaps[layerName] = tiffLayer;

                // Met à jour le contrôle des couches
                map.updateLayerControl();

                // Met à jour les boutons dynamiquement
                map.updateLayerButtons();
            };
            reader.readAsArrayBuffer(file);
        }
    });
})