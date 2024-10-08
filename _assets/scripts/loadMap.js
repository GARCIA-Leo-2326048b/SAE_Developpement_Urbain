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

            load_map(vegetation, 'green', map);
            load_map(road, 'brown', map);
            load_map(  house, 'red', map);
        })
}
function style(color) {
    return {
        color: color,
        weight: 2,
        fillColor: color,
        fillOpacity: 0.5
    };
}

function load_map(element, color, map) {
    //API JavaScript fetch recupere des ressources
    fetch(element)
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
                style: style(color)
            }).addTo(map); // Ajouter la couche
        })
        .catch(error => {
            console.error('Erreur lors du chargement du GeoJSON:', error);
        });
}
