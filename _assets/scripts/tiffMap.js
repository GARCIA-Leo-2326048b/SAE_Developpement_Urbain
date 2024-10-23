function displayGeoTIFF(tiffUrl) {
    //creer la map
    // var map = L.map('map').setView([51.505, -0.09], 13);
    // L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //     attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    // }).addTo(map);

    const key = 'phT89U7mj4WtQWinX1ID';
    const map = L.map('map').setView([49.2125578, 16.62662018], 14); //starting position
    L.tileLayer(`https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key=${key}`,{ //style URL
        tileSize: 512,
        zoomOffset: -1,
        minZoom: 1,
        attribution: "\u003ca href=\"https://www.maptiler.com/copyright/\" target=\"_blank\"\u003e\u0026copy; MapTiler\u003c/a\u003e \u003ca href=\"https://www.openstreetmap.org/copyright\" target=\"_blank\"\u003e\u0026copy; OpenStreetMap contributors\u003c/a\u003e",
        crossOrigin: true
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
