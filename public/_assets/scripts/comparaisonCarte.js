// Fonction pour masquer les cartes en arrière-plan
function toggleBackgroundMaps(hide) {
    const mapContainer = document.querySelector('.map-container');
    if (mapContainer) {
        if (hide) {
            mapContainer.classList.add('hidden');
        } else {
            mapContainer.classList.remove('hidden');
        }
    }
}

// Objets pour gérer les cartes
const mapManagers = {};

// Fonction pour initialiser la carte dans une modale
function initializeModalMap(geoJsonData, geoJsonName, mapId) {
    if (!mapManagers[mapId]) {
        mapManagers[mapId] = new MapManager(null,null, null, null, mapId);
        geoJsonData.forEach((file, index) => {
            mapManagers[mapId].addGeoJsonLayer(file, geoJsonName[index]);
        });
    }
}

// Gestion des événements DOM
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('showMapSimulation').addEventListener('click', function () {
        const modal = document.getElementById('mapModalSimulation');
        modal.style.display = 'block';
        toggleBackgroundMaps(true);
        initializeModalMap(window.geoJsonSimData, window.geoJsonSimName, 'mapSimulation');
    });

    document.getElementById('showMapVerite').addEventListener('click', function () {
        const modal = document.getElementById('mapModalVerite');
        modal.style.display = 'block';
        toggleBackgroundMaps(true);
        initializeModalMap(window.geoJsonVerData, window.geoJsonVerName, 'mapVerite');
    });

    document.getElementById('closeSimulation').addEventListener('click', function () {
        const modal = document.getElementById('mapModalSimulation');
        modal.style.display = 'none';
        toggleBackgroundMaps(false);
    });

    document.getElementById('closeVerite').addEventListener('click', function () {
        const modal = document.getElementById('mapModalVerite');
        modal.style.display = 'none';
        toggleBackgroundMaps(false);
    });

    window.addEventListener('click', function (event) {
        const modalSimulation = document.getElementById('mapModalSimulation');
        const modalVerite = document.getElementById('mapModalVerite');
        if (event.target === modalSimulation) {
            modalSimulation.style.display = 'none';
            toggleBackgroundMaps(false);
        }
        if (event.target === modalVerite) {
            modalVerite.style.display = 'none';
            toggleBackgroundMaps(false);
        }
    });
});

