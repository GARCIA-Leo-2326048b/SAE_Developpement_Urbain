let map;
let satelliteLayer, streetsLayer;
let houseLayer, roadLayer, vegetationLayer, tiffLayer;
const key = 'phT89U7mj4WtQWinX1ID';
let currentLayer = null; // Variable pour la couche sélectionnée
let overlayMaps = {}; // Couches superposées
let layerControl = null; // Référence au contrôle des couches
let genericLayer = null; // Référence à la couche GeoJSON générique

function initializeMap() {

    map = L.map('map').setView([0, 0], 16);

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

    map.addLayer(streetsLayer);
}

// Fonction pour sélectionner la couche à ajuster
function selectLayer(layerName) {
    // Réinitialise la sélection des boutons
    const buttons = document.querySelectorAll('#layerButtons button');
    buttons.forEach(button => {
        button.classList.remove('selected-button');
    });

    // Ajoute la classe au bouton sélectionné
    const selectedButton = document.getElementById(`btn-${layerName}`);
    if (selectedButton) {
        selectedButton.classList.add('selected-button');
    }

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

function ajouterGeoJson(genericGeoJson, layerName) {
    if (genericGeoJson) {
        // Supprime l'ancienne couche si elle existe
        if (genericLayer) {
            map.removeLayer(genericLayer);
        }

        const randomColor = getRandomColor();
        // Crée et ajoute la nouvelle couche GeoJSON générique
        genericLayer = L.geoJSON(genericGeoJson, {color: randomColor, weight: 2, fillColor: randomColor, fillOpacity: 1}).addTo(map);

        // Ajoute la couche générique à overlayMaps avec le nom du fichier (layerName)
        overlayMaps[layerName] = genericLayer;

        map.fitBounds(genericLayer.getBounds());

        // Met à jour le contrôle des couches
        updateLayerControl();

        // Met à jour les boutons dynamiquement
        updateLayerButtons();
    } else {
        console.error("Aucune donnée GeoJSON générique.");
    }
}

function supprimerCouche() {
    if (currentLayer) {
        // Supprimer la couche de la carte
        map.removeLayer(currentLayer);

        // Supprimer la couche de overlayMaps
        const layerName = getLayerName(currentLayer); // On récupère le nom de la couche
        if (overlayMaps[layerName]) {
            delete overlayMaps[layerName];
        }

        // Réinitialiser la couche sélectionnée
        currentLayer = null;

        // Mettre à jour le contrôle des couches
        updateLayerControl();

        // Mettre à jour les boutons dynamiquement
        updateLayerButtons();
    } else {
        console.error("Aucune couche sélectionnée à supprimer.");
    }
}

// Fonction pour récupérer le nom de la couche (c'est un exemple, tu peux l'adapter selon ton besoin)
function getLayerName(layer) {
    // Si tu as un nom spécifique pour chaque couche, tu peux ajuster cette fonction.
    for (let key in overlayMaps) {
        if (overlayMaps[key] === layer) {
            return key;
        }
    }
    return null;
}

function ajouterGeoTiff(tiffUrl, layerName) {
    if (tiffUrl) {
        // Supprime l'ancienne couche GeoTIFF si elle existe
        if (tiffLayer) {
            map.removeLayer(tiffLayer);
        }

        // Chargement du GeoTIFF
        fetch(tiffUrl)
            .then(response => response.arrayBuffer())
            .then(arrayBuffer => parseGeoraster(arrayBuffer))
            .then(georaster => {
                // Crée une nouvelle couche GeoTIFF
                tiffLayer = new GeoRasterLayer({
                    georaster: georaster,
                    opacity: 1,
                    resolution: 64
                }).addTo(map);

                // Ajouter la couche à overlayMaps avec le nom de la couche
                overlayMaps[layerName] = tiffLayer;

                // Met à jour le contrôle des couches
                updateLayerControl();

                // Met à jour les boutons dynamiquement
                updateLayerButtons();
            })
            .catch(error => console.error('Erreur lors du chargement du GeoTIFF:', error));
    } else {
        console.error("URL du GeoTIFF manquant.");
    }
}
