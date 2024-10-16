function displayGeoTIFF(tiffUrl) {
    // Créez la carte Leaflet
    var map = L.map('map').setView([45.0, 5.0], 6); // Exemple de coordonnées (modifiez en fonction de votre fichier)

    // Ajouter un fond de carte
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    // Utilisation correcte de la bibliothèque leaflet-geotiff
    var geotiffLayer = new L.LeafletGeoTIFF({
        url: tiffUrl,  // L'URL du fichier TIFF passé en paramètre
        band: 0,       // Choisissez le bon band, si nécessaire
        opacity: 0.7   // Opacité ajustable
    }).addTo(map);
}
