// loadMap.js

function createMap(house,road,vegetation ){
    //API JavaScript fetch recupere des ressources
    fetch(house)
        // possibilité d'une valeur
        .then(response => {
            //false si requete a échoué
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            //lit la reponse et convertit en json
            return response.json();
        })
        .then(data => {
            console.log(data); // Affichez les données pour vérifier leur structure
            if (!data["features"] || data["features"].length === 0) {
                throw new Error('Aucune maison trouvée dans le fichier GeoJSON');
            }
            //prend les coordonnees de la premiere maison
            const firstHouse = data["features"][0]["geometry"]["coordinates"][0][0];
            const lat = firstHouse[1]; // Latitude
            const lng = firstHouse[0]; // Longitude
            // Créer la carte
            var map = L.map('map').setView([lat, lng], 16);
            // Ajouter le fond de carte OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(map);
            // Charge la végétation
            load_map(vegetation, 'vegetation', map)
                .then(() => load_map(road, 'road', map))
                .then(() => load_map(house, 'house', map));
        })
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

function load_map(element, type, map) {
    //API JavaScript fetch recupere des ressources
    return fetch(element)
        // possibilité d'une valeur
        .then(response => {
            //false si requete a échoué
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            //lit la reponse et convertit en json
            return response.json();
        })
        .then(data => {
            L.geoJSON(data, {
                style: (feature) => style(feature,type)
            }).addTo(map); // Ajouter la couche
        })
        .catch(error => {
            console.error('Erreur lors du chargement du GeoJSON:', error);
        });
}
