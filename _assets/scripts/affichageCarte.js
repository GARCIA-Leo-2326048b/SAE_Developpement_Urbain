let map;
let satelliteLayer, streetsLayer;
let houseLayer, roadLayer, vegetationLayer, tiffLayer;
const key = 'phT89U7mj4WtQWinX1ID';
let currentLayer = null; // Variable pour la couche sélectionnée
let overlayMaps = {}; // Couches superposées
let layerControl = null; // Référence au contrôle des couches
let genericLayer = null; // Référence à la couche GeoJSON générique

function initializeMap(house, tiffUrl) {
    const firstHouseCoordinates = house && house.features && house.features[0] ? house.features[0].geometry.coordinates[0][0] : null;
    const lat = firstHouseCoordinates ? firstHouseCoordinates[1] : 0;
    const lng = firstHouseCoordinates ? firstHouseCoordinates[0] : 0;

    map = L.map('map').setView([lat, lng], 16);

    // Création des couches de fond de carte
    satelliteLayer = L.tileLayer(`https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key=${key}`, {
        tileSize: 512,
        zoomOffset: -1,
        minZoom: 1,
        attribution: "\u003ca href=\"https://www.maptiler.com/copyright/\" target=\"_blank\"\u003e\u0026copy; MapTiler\u003c/a\u003e \u003ca href=\"https://www.openstreetmap.org/copyright\" target=\"_blank\"\u003e\u0026copy; OpenStreetMap contributors\u003c/a\u003e",
        crossOrigin: true
    });

    streetsLayer = L.tileLayer(`https://api.maptiler.com/maps/streets/{z}/{x}/{y}.png?key=${key}`, {
        tileSize: 512,
        zoomOffset: -1,
        minZoom: 1,
        attribution: "\u003ca href=\"https://www.maptiler.com/copyright/\" target=\"_blank\"\u003e\u0026copy; MapTiler\u003c/a\u003e \u003ca href=\"https://www.openstreetmap.org/copyright\" target=\"_blank\"\u003e\u0026copy; OpenStreetMap contributors\u003c/a\u003e",
        crossOrigin: true
    });

    // Ajout de la couche satellite par défaut
    satelliteLayer.addTo(map);

    if (house) {
        houseLayer = L.geoJSON(house, { color: '#e4a0b5', weight: 2, fillColor: '#e4a0b5', fillOpacity: 1 }).addTo(map);
        overlayMaps["Maisons"] = houseLayer;
    }

    // Chargement du GeoTIFF uniquement si l'URL est fournie
    // Chargement du GeoTIFF
    if (tiffUrl) {
        fetch(tiffUrl)
            .then(response => response.arrayBuffer())
            .then(arrayBuffer => parseGeoraster(arrayBuffer))
            .then(georaster => {
                tiffLayer = new GeoRasterLayer({
                    georaster: georaster,
                    opacity: 1,
                    resolution: 64
                }).addTo(map);

                overlayMaps["GeoTIFF"] = tiffLayer;
                updateLayerControl();
            })
            .catch(error => console.error('Erreur lors du chargement du GeoTIFF:', error));
    }

    updateLayerControl();
    updateLayerButtons();

    // Ajuster les limites de la carte si des couches sont présentes
    if (house || road || vegetation) {
        map.fitBounds(L.featureGroup([houseLayer]).getBounds());
    }
}

// Fonction pour sélectionner la couche à ajuster
function selectLayer(layerName) {
    // Vérifie si la couche existe dans overlayMaps
    if (overlayMaps[layerName]) {
        currentLayer = overlayMaps[layerName];  // Sélectionne la couche correspondante
    } else {
        console.error("La couche spécifiée n'existe pas : ", layerName);
        return;
    }

    // Réinitialiser le curseur d'opacité
    document.getElementById('opacitySlider').value = 1;

    // Mettre à jour l'opacité de la couche sélectionnée
    updateLayerOpacity();
}





// Fonction pour mettre à jour l'opacité de la couche sélectionnée
function updateLayerOpacity() {
    const opacity = document.getElementById('opacitySlider').value;
    if (currentLayer) {
        if (currentLayer === tiffLayer){
            currentLayer.setOpacity(opacity);
        } else {
            currentLayer.eachLayer((layer) => {
                // Mettre à jour le style pour le remplissage et les bordures
                layer.setStyle({
                    fillOpacity: opacity, // Opacité de l'intérieur
                    opacity: opacity // Opacité des bordures
                });
            });
        }
    }
}

// Fonctions pour basculer entre les couches satellite et rues
function switchToSatellite() {
    map.removeLayer(streetsLayer);
    map.addLayer(satelliteLayer);
    satelliteLayer.bringToBack();
}

function switchToStreets() {
    map.removeLayer(satelliteLayer);
    map.addLayer(streetsLayer);
    streetsLayer.bringToBack();
}

function getRandomColor() {
    return '#' + Math.floor(Math.random() * 16777215).toString(16);
}

// Fonction pour mettre à jour le contrôle des couches
function updateLayerControl() {
    // Supprimer l'ancien contrôle s'il existe
    if (layerControl) {
        map.removeControl(layerControl);
    }

    // Créer un nouveau contrôle avec les couches mises à jour
    layerControl = L.control.layers(null, overlayMaps, { position: 'topright', collapsed: false });
    layerControl.addTo(map);
}

function updateLayerButtons() {
    const layerButtonsDiv = document.getElementById('layerButtons');
    layerButtonsDiv.innerHTML = ''; // Efface tous les boutons existants pour éviter les doublons

    // Titre
    const title = document.createElement('h4');
    title.innerText = "Sélectionnez la couche :";
    layerButtonsDiv.appendChild(title);

    // Parcours des couches dans overlayMaps
    Object.keys(overlayMaps).forEach(layerName => {
        const button = document.createElement('button');
        button.id = `btn-${layerName}`;
        button.innerText = layerName;

        // Ajoute l'événement onclick pour sélectionner la couche
        button.onclick = () => selectLayer(layerName);

        // Ajoute le bouton au conteneur
        layerButtonsDiv.appendChild(button);
    });
}

function ajouterGeoJson(fileName, genericGeoJson) {
    if (genericGeoJson) {
        // Supprime l'ancienne couche si elle existe
        if (genericLayer) {
            map.removeLayer(genericLayer);
        }

        const randomColor = getRandomColor();
        // Crée et ajoute la nouvelle couche GeoJSON générique
        genericLayer = L.geoJSON(genericGeoJson, {color: randomColor, weight: 2, fillColor: randomColor, fillOpacity: 1}).addTo(map);

        // Utilise le nom du fichier (sans l'extension .geojson) comme nom de la couche
        const layerName = fileName.replace('.geojson', '');

        // Ajoute la couche générique à overlayMaps avec le nom du fichier
        overlayMaps[layerName] = genericLayer;

        // Met à jour le contrôle des couches
        updateLayerControl();

        // Met à jour les boutons dynamiquement
        updateLayerButtons();
    } else {
        console.error("Aucune donnée GeoJSON générique.");
    }
}