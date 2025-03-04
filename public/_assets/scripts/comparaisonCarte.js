/**
 * Fonction pour masquer ou afficher les cartes en arrière-plan.
 * @param {boolean} hide - Si vrai, masque les cartes en arrière-plan ; sinon, les affiche.
 */
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

/**
 * Fonction pour initialiser la carte dans une modale.
 * @param {Array} geoJsonData - Tableau de données GeoJSON.
 * @param {Array} geoJsonName - Tableau de noms de fichiers GeoJSON.
 * @param {string} mapId - L'ID du conteneur de la carte.
 */
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
    /**
     * Écouteur d'événement pour afficher la modale de la carte de simulation.
     */
    document.getElementById('showMapSimulation').addEventListener('click', function () {
        const modal = document.getElementById('mapModalSimulation');
        modal.style.display = 'block';
        toggleBackgroundMaps(true);
        initializeModalMap(window.geoJsonSimData, window.geoJsonSimName, 'mapSimulation');
    });

    /**
     * Écouteur d'événement pour afficher la modale de la carte de vérité.
     */
    document.getElementById('showMapVerite').addEventListener('click', function () {
        const modal = document.getElementById('mapModalVerite');
        modal.style.display = 'block';
        toggleBackgroundMaps(true);
        initializeModalMap(window.geoJsonVerData, window.geoJsonVerName, 'mapVerite');
    });

    /**
     * Écouteur d'événement pour fermer la modale de la carte de simulation.
     */
    document.getElementById('closeSimulation').addEventListener('click', function () {
        const modal = document.getElementById('mapModalSimulation');
        modal.style.display = 'none';
        toggleBackgroundMaps(false);
    });

    /**
     * Écouteur d'événement pour fermer la modale de la carte de vérité.
     */
    document.getElementById('closeVerite').addEventListener('click', function () {
        const modal = document.getElementById('mapModalVerite');
        modal.style.display = 'none';
        toggleBackgroundMaps(false);
    });

    /**
     * Écouteur d'événement pour fermer les modales en cliquant à l'extérieur.
     * @param {Event} event - L'événement de clic.
     */
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
