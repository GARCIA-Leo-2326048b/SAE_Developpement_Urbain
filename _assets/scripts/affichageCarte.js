let map;
let satelliteLayer, streetsLayer;
let houseLayer, roadLayer, vegetationLayer, tiffLayer;
const key = 'phT89U7mj4WtQWinX1ID';
let currentLayer = null; // Variable pour la couche sélectionnée

function initializeMap(house, road, vegetation, tiffUrl) {
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

    // Création des couches GeoJSON et ajout uniquement si les données existent
    const overlayMaps = {};

    if (house) {
        houseLayer = L.geoJSON(house, { style: (feature) => style(feature, 'house') }).addTo(map);
        overlayMaps["Maisons"] = houseLayer;
    }

    if (road) {
        roadLayer = L.geoJSON(road, { style: (feature) => style(feature, 'road') }).addTo(map);
        overlayMaps["Routes"] = roadLayer;
    }

    if (vegetation) {
        vegetationLayer = L.geoJSON(vegetation, { style: (feature) => style(feature, 'vegetation') }).addTo(map);
        overlayMaps["Végétation"] = vegetationLayer;
    }

    // Chargement du GeoTIFF uniquement si l'URL est fournie
    if (tiffUrl) {
        fetch(tiffUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('GeoTIFF non trouvé');
                }
                return response.arrayBuffer();
            })
            .then(arrayBuffer => parseGeoraster(arrayBuffer))
            .then(georaster => {
                tiffLayer = new GeoRasterLayer({
                    georaster: georaster,
                    opacity: 1,
                    resolution: 64
                });

                // Ajouter la couche GeoTIFF après son chargement
                tiffLayer.addTo(map);
                overlayMaps["GeoTIFF"] = tiffLayer;

                // Ajouter le contrôle des couches
                L.control.layers(null, overlayMaps, { position: 'topright', collapsed: false }).addTo(map);
            })
            .catch(error => {
                console.error('Erreur lors du chargement du GeoTIFF:', error);
                // Si le GeoTIFF échoue, ne pas l'ajouter au contrôle des couches
                L.control.layers(null, overlayMaps, { position: 'topright', collapsed: false }).addTo(map);
            });
    } else {
        // Si aucun GeoTIFF n'est fourni, ajouter les autres couches
        L.control.layers(null, overlayMaps, { position: 'topright', collapsed: false }).addTo(map);
    }

    // Ajuster les limites de la carte si des couches sont présentes
    if (house || road || vegetation) {
        map.fitBounds(L.featureGroup([houseLayer, roadLayer, vegetationLayer]).getBounds());
    }
}

// Fonction pour définir les styles des couches GeoJSON
function style(feature, type) {
    let color;
    if (type === 'vegetation') {
        switch (feature.properties.Type) {
            case "Sol nu":
                color = '#efb974';
                break;
            case "Herbe":
                color = '#52cd20';
                break;
            case "Végétation basse":
                color = '#f0ede7';
                break;
            case "Végétation haute":
                color = '#accf9d';
                break;
            case "Culture":
                color = '#e2e900';
                break;
            case "Habitation":
                color = '#f7b19b';
                break;
            case "Eau":
                color = '#a9d1dd';
                break;
            default:
                color = '#dedddd';
        }
    } else if (type === 'house') {
        color = '#e4a0b5';
    } else if (type === 'road') {
        color = '#614105';
    }

    return {
        color: color,
        weight: 2,
        fillColor: color,
        fillOpacity: 1 // Opacité de remplissage par défaut
    };
}

// Fonction pour sélectionner la couche à ajuster
function selectLayer(layerType) {
    if (layerType === 'house') {
        currentLayer = houseLayer;
    } else if (layerType === 'road') {
        currentLayer = roadLayer;
    } else if (layerType === 'vegetation') {
        currentLayer = vegetationLayer;
    } else if (layerType === 'tiff') {
        currentLayer = tiffLayer;
    }
    document.getElementById('opacitySlider').value = 1; // Réinitialiser le curseur d’opacité à 1
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
