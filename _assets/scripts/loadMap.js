
function createMap(house=null,road=null,vegetation=null){
    //prend les coordonnees de la premiere maison
    const firstHouseCoordinates = house.features[0].geometry.coordinates[0][0];
    var lat = firstHouseCoordinates[1];
    var lng = firstHouseCoordinates[0];
    // Créer la carte
    var map = L.map('map').setView([lat, lng], 16);
    // Ajouter le fond de carte OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);
    // Si des données GeoJSON pour les maisons existent, les ajouter à la carte
    if (house) {
        L.geoJSON(house, {
            style: (feature) => style(feature, 'house') // Appliquer un style aux maisons
        }).addTo(map);
    }

    // Si des données GeoJSON pour les routes existent, les ajouter à la carte
    if (road) {
        L.geoJSON(road, {
            style: (feature) => style(feature, 'road') // Appliquer un style aux routes
        }).addTo(map);
    }

    // Si des données GeoJSON pour la végétation existent, les ajouter à la carte avec un style spécifique
    if (vegetation) {
        L.geoJSON(vegetation, {
            style: (feature) => style(feature, 'vegetation') // Appliquer un style à la végétation
        }).addTo(map);
    }
    // Optionnel : Ajuster les limites de la carte en fonction des couches chargées
    var bounds = L.featureGroup([L.geoJSON(house), L.geoJSON(road), L.geoJSON(vegetation)]).getBounds();
    map.fitBounds(bounds);

}

function style(feature,type) {
    let color;
    if (type === 'vegetation') {
        // Coloration en fonction du type de végétation
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
        fillOpacity: 1
    };
}
