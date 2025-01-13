// comparaisonCarte.test.js

const { toggleBackgroundMaps, initializeModalMap } = require('./comparaisonCarte');

describe('toggleBackgroundMaps', () => {
    let mapContainer;

    beforeEach(() => {
        document.body.innerHTML = '<div class="map-container"></div>';
        mapContainer = document.querySelector('.map-container');
    });

    test('should add hidden class when hide is true', () => {
        toggleBackgroundMaps(true);
        expect(mapContainer.classList.contains('hidden')).toBe(true);
    });

    test('should remove hidden class when hide is false', () => {
        mapContainer.classList.add('hidden');
        toggleBackgroundMaps(false);
        expect(mapContainer.classList.contains('hidden')).toBe(false);
    });
});

describe('initializeModalMap', () => {
    let mapManagers;

    beforeEach(() => {
        mapManagers = {};
        global.MapManager = jest.fn().mockImplementation(() => ({
            addHouseLayer: jest.fn(),
            addRoadLayer: jest.fn(),
        }));
    });

    test('should initialize map and add house layer', () => {
        const geoJsonHouse = {};
        const mapId = 'map1';
        initializeModalMap(geoJsonHouse, null, mapId);
        expect(mapManagers[mapId]).toBeDefined();
        expect(mapManagers[mapId].addHouseLayer).toHaveBeenCalledWith(geoJsonHouse);
    });

    test('should add road layer if geoJsonRoad is provided', () => {
        const geoJsonHouse = {};
        const geoJsonRoad = {};
        const mapId = 'map1';
        initializeModalMap(geoJsonHouse, geoJsonRoad, mapId);
        expect(mapManagers[mapId]).toBeDefined();
        expect(mapManagers[mapId].addRoadLayer).toHaveBeenCalledWith(geoJsonRoad);
    });
});