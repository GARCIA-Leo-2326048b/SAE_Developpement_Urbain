// let map;
// let satelliteLayer, streetsLayer;
// let houseLayer, roadLayer, vegetationLayer, tiffLayer;
// const key = 'phT89U7mj4WtQWinX1ID';
// let currentLayer = null; // Variable pour la couche sélectionnée
// let overlayMaps = {}; // Couches superposées
// let layerControl = null; // Référence au contrôle des couches
// let genericLayer = null; // Référence à la couche GeoJSON générique
//
// function initializeMap(house = null, road = null, tiffUrl = null,idMap = 'map') {
//     map = L.map(idMap).setView([0, 0], 16);
//
//     // Création des couches de fond de carte
//     satelliteLayer = L.tileLayer(`https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key=${key}`, {
//         tileSize: 512,
//         zoomOffset: -1,
//         minZoom: 1,
//         attribution: "\u003ca href=\"https://www.maptiler.com/copyright/\" target=\"_blank\"\u003e\u0026copy; MapTiler\u003c/a\u003e \u003ca href=\"https://www.openstreetmap.org/copyright\" target=\"_blank\"\u003e\u0026copy; OpenStreetMap contributors\u003c/a\u003e",
//         crossOrigin: true
//     });
//
//     streetsLayer = L.tileLayer(`https://api.maptiler.com/maps/streets/{z}/{x}/{y}.png?key=${key}`, {
//         tileSize: 512,
//         zoomOffset: -1,
//         minZoom: 1,
//         attribution: "\u003ca href=\"https://www.maptiler.com/copyright/\" target=\"_blank\"\u003e\u0026copy; MapTiler\u003c/a\u003e \u003ca href=\"https://www.openstreetmap.org/copyright\" target=\"_blank\"\u003e\u0026copy; OpenStreetMap contributors\u003c/a\u003e",
//         crossOrigin: true
//     });
//
//     // Ajout de la couche satellite par défaut
//     streetsLayer.addTo(map);
//
//     if (house) {
//         houseLayer = L.geoJSON(house, { color: '#e4a0b5', weight: 2, fillColor: '#e4a0b5', fillOpacity: 1 }).addTo(map);
//         overlayMaps["Maisons"] = houseLayer;
//     }
//
//     if (road) {
//         roadLayer = L.geoJSON(road, { color: '#0d61fa', weight: 2, fillColor: '#0d61fa', fillOpacity: 1 }).addTo(map);
//         overlayMaps["Routes"] = roadLayer;
//     }
//
//
//
//     // Chargement du GeoTIFF uniquement si l'URL est fournie
//     // Chargement du GeoTIFF
//     if (tiffUrl) {
//         fetch(tiffUrl)
//             .then(response => response.arrayBuffer())
//             .then(arrayBuffer => parseGeoraster(arrayBuffer))
//             .then(georaster => {
//                 tiffLayer = new GeoRasterLayer({
//                     georaster: georaster,
//                     opacity: 1,
//                     resolution: 64
//                 }).addTo(map);
//
//                 overlayMaps["GeoTIFF"] = tiffLayer;
//                 updateLayerControl();
//                 updateLayerButtons();
//             })
//             .catch(error => console.error('Erreur lors du chargement du GeoTIFF:', error));
//     }
//
//     // Ajuster les limites de la carte si des couches sont présentes
//     if (house) {
//         map.fitBounds(L.featureGroup([houseLayer]).getBounds());
//     }
// }
//
// // Fonction pour sélectionner la couche à ajuster
// function selectLayer(layerName) {
//     // Réinitialise la sélection des boutons
//     const buttons = document.querySelectorAll('#layerButtons button');
//     buttons.forEach(button => {
//         button.classList.remove('selected-button');
//     });
//
//     // Ajoute la classe au bouton sélectionné
//     const selectedButton = document.getElementById(`btn-${layerName}`);
//     if (selectedButton) {
//         selectedButton.classList.add('selected-button');
//     }
//
//     // Vérifie si la couche existe dans overlayMaps
//     if (overlayMaps[layerName]) {
//         currentLayer = overlayMaps[layerName];  // Sélectionne la couche correspondante
//     } else {
//         console.error("La couche spécifiée n'existe pas : ", layerName);
//         return;
//     }
//
//     // Réinitialiser le curseur d'opacité
//     document.getElementById('opacitySlider').value = 1;
//
//     // Mettre à jour l'opacité de la couche sélectionnée
//     updateLayerOpacity();
// }
//
//
//
//
//
//
// // Fonction pour mettre à jour l'opacité de la couche sélectionnée
// function updateLayerOpacity() {
//     const opacity = document.getElementById('opacitySlider').value;
//     if (currentLayer) {
//         if (currentLayer === tiffLayer){
//             currentLayer.setOpacity(opacity);
//         } else {
//             currentLayer.eachLayer((layer) => {
//                 // Mettre à jour le style pour le remplissage et les bordures
//                 layer.setStyle({
//                     fillOpacity: opacity, // Opacité de l'intérieur
//                     opacity: opacity // Opacité des bordures
//                 });
//             });
//         }
//     }
// }
//
// // Fonctions pour basculer entre les couches satellite et rues
// function switchToSatellite() {
//     map.removeLayer(streetsLayer);
//     map.addLayer(satelliteLayer);
//     satelliteLayer.bringToBack();
// }
//
// function switchToStreets() {
//     map.removeLayer(satelliteLayer);
//     map.addLayer(streetsLayer);
//     streetsLayer.bringToBack();
// }
//
// function getRandomColor() {
//     return '#' + Math.floor(Math.random() * 16777215).toString(16);
// }
//
// // Fonction pour mettre à jour le contrôle des couches
// function updateLayerControl() {
//     // Supprimer l'ancien contrôle s'il existe
//     if (layerControl) {
//         map.removeControl(layerControl);
//     }
//
//     // Créer un nouveau contrôle avec les couches mises à jour
//     layerControl = L.control.layers(null, overlayMaps, { position: 'topright', collapsed: false });
//     layerControl.addTo(map);
// }
//
// function updateLayerButtons() {
//     const layerButtonsDiv = document.getElementById('layerButtons');
//     layerButtonsDiv.innerHTML = ''; // Efface tous les boutons existants pour éviter les doublons
//
//     // Titre
//     const title = document.createElement('h4');
//     title.innerText = "Sélectionnez la couche :";
//     layerButtonsDiv.appendChild(title);
//
//     // Parcours des couches dans overlayMaps
//     Object.keys(overlayMaps).forEach(layerName => {
//         const button = document.createElement('button');
//         button.id = `btn-${layerName}`;
//         button.innerText = layerName;
//
//         // Ajoute l'événement onclick pour sélectionner la couche
//         button.onclick = () => selectLayer(layerName);
//
//         // Ajoute le bouton au conteneur
//         layerButtonsDiv.appendChild(button);
//     });
// }
//
// function ajouterGeoJson(genericGeoJson, layerName) {
//     if (genericGeoJson) {
//         // Supprime l'ancienne couche si elle existe
//         if (genericLayer) {
//             map.removeLayer(genericLayer);
//         }
//
//         const randomColor = getRandomColor();
//         // Crée et ajoute la nouvelle couche GeoJSON générique
//         genericLayer = L.geoJSON(genericGeoJson, {color: randomColor, weight: 2, fillColor: randomColor, fillOpacity: 1}).addTo(map);
//
//         // Ajoute la couche générique à overlayMaps avec le nom du fichier (layerName)
//         overlayMaps[layerName] = genericLayer;
//
//         map.fitBounds(genericLayer.getBounds());
//
//         // Met à jour le contrôle des couches
//         updateLayerControl();
//
//         // Met à jour les boutons dynamiquement
//         updateLayerButtons();
//     } else {
//         console.error("Aucune donnée GeoJSON générique.");
//     }
// }
//
// function supprimerCouche() {
//     if (currentLayer) {
//         // Supprimer la couche de la carte
//         map.removeLayer(currentLayer);
//
//         // Supprimer la couche de overlayMaps
//         const layerName = getLayerName(currentLayer); // On récupère le nom de la couche
//         if (overlayMaps[layerName]) {
//             delete overlayMaps[layerName];
//         }
//
//         // Réinitialiser la couche sélectionnée
//         currentLayer = null;
//
//         // Mettre à jour le contrôle des couches
//         updateLayerControl();
//
//         // Mettre à jour les boutons dynamiquement
//         updateLayerButtons();
//     } else {
//         console.error("Aucune couche sélectionnée à supprimer.");
//     }
// }
//
// // Fonction pour récupérer le nom de la couche (c'est un exemple, tu peux l'adapter selon ton besoin)
// function getLayerName(layer) {
//     // Si tu as un nom spécifique pour chaque couche, tu peux ajuster cette fonction.
//     for (let key in overlayMaps) {
//         if (overlayMaps[key] === layer) {
//             return key;
//         }
//     }
//     return null;
// }
//
// function ajouterGeoTiff(tiffUrl, layerName) {
//     if (tiffUrl) {
//         // Supprime l'ancienne couche GeoTIFF si elle existe
//         if (tiffLayer) {
//             map.removeLayer(tiffLayer);
//         }
//
//         // Chargement du GeoTIFF
//         fetch(tiffUrl)
//             .then(response => response.arrayBuffer())
//             .then(arrayBuffer => parseGeoraster(arrayBuffer))
//             .then(georaster => {
//                 // Crée une nouvelle couche GeoTIFF
//                 tiffLayer = new GeoRasterLayer({
//                     georaster: georaster,
//                     opacity: 1,
//                     resolution: 64
//                 }).addTo(map);
//
//                 // Ajouter la couche à overlayMaps avec le nom de la couche
//                 overlayMaps[layerName] = tiffLayer;
//
//                 // Met à jour le contrôle des couches
//                 updateLayerControl();
//
//                 // Met à jour les boutons dynamiquement
//                 updateLayerButtons();
//             })
//             .catch(error => console.error('Erreur lors du chargement du GeoTIFF:', error));
//     } else {
//         console.error("URL du GeoTIFF manquant.");
//     }
// }
//
//


class MapManager {
    constructor(house = null, road = null, tiffUrl = null, idMap = 'map') {
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
            const houseLayer = L.geoJSON(house, { color: '#e4a0b5', weight: 2, fillColor: '#e4a0b5', fillOpacity: 1 }).addTo(this.map);
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
    }

    addHouseLayer(house) {
        const houseLayer = L.geoJSON(house, { color: '#e4a0b5', weight: 2, fillColor: '#e4a0b5', fillOpacity: 1 }).addTo(this.map);
        this.overlayMaps["Maisons"] = houseLayer;
        this.updateLayerControl();
        this.updateLayerButtons();
        this.map.fitBounds(houseLayer.getBounds());
    }

    addRoadLayer(road) {
        const roadLayer = L.geoJSON(road, { color: '#0d61fa', weight: 2, fillColor: '#0d61fa', fillOpacity: 1 }).addTo(this.map);
        this.overlayMaps["Routes"] = roadLayer;
        this.updateLayerControl();
        this.updateLayerButtons();
    }

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

    updateLayerControl() {
        // Supprimer l'ancien contrôle s'il existe
        if (this.layerControl) {
            this.map.removeControl(this.layerControl);
        }

        // Créer un nouveau contrôle avec les couches mises à jour
        this.layerControl = L.control.layers(null, this.overlayMaps, { position: 'topright', collapsed: false });
        this.layerControl.addTo(this.map);
    }

    selectLayer(layerName) {
        if (this.overlayMaps[layerName]) {
            this.currentLayer = this.overlayMaps[layerName];
        } else {
            console.error("La couche spécifiée n'existe pas : ", layerName);
        }
    }

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

    switchToSatellite() {
        this.map.removeLayer(this.streetsLayer);
        this.map.addLayer(this.satelliteLayer);
        this.satelliteLayer.bringToBack();
    }

    switchToStreets() {
        this.map.removeLayer(this.satelliteLayer);
        this.map.addLayer(this.streetsLayer);
        this.streetsLayer.bringToBack();
    }

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

    // Fonction pour récupérer le nom de la couche (c'est un exemple, tu peux l'adapter selon ton besoin)
    getLayerName(layer) {
        // Si tu as un nom spécifique pour chaque couche, tu peux ajuster cette fonction.
        for (let key in this.overlayMaps) {
            if (this.overlayMaps[key] === layer) {
                return key;
            }
        }
        return null;
    }

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
}


// Gestion de l'upload et affichage du fichier GeoTIFF
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