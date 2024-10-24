let map;
let satelliteLayer, streetsLayer;
const key = 'phT89U7mj4WtQWinX1ID';
function initializeMap(tiffUrl) {
    // Initialisation de la carte
    map = L.map('map').setView([49.2125578, 16.62662018], 14);

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

    // Chargement du GeoTIFF
    fetch(tiffUrl)
        .then(response => response.arrayBuffer())
        .then(arrayBuffer => parseGeoraster(arrayBuffer))
        .then(georaster => {
            var layer = new GeoRasterLayer({
                georaster: georaster,
                opacity: 1,
                resolution: 64
            });
            layer.addTo(map);
            map.fitBounds(layer.getBounds());
        })
        .catch(error => {
            console.error('Erreur lors du chargement du GeoTIFF:', error);
        });
}

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