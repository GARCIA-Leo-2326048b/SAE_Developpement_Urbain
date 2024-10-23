function displayGeoTIFF(tiffUrl) {
    //creer la map
    var map = L.map('map').setView([51.505, -0.09], 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    //charger le raster
    fetch(tiffUrl)
        .then(response => response.arrayBuffer())
        .then(arrayBuffer => {
            parseGeoraster(arrayBuffer).then(georaster => {
                console.log("georaster:", georaster);

                var layer = new GeoRasterLayer({
                    georaster: georaster,
                    opacity: 1,
                    // pixelValuesToColorFn: values => values[0] === 42 ? '#ffffff' : '#000000',
                    resolution: 64 // optional parameter for adjusting display resolution
                });
                layer.addTo(map);
                map.fitBounds(layer.getBounds());

            });
        });


}
