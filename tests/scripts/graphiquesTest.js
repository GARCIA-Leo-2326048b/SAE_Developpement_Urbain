// graphiques.test.js

const {
    initializeChart,
    toggleChartForm,
    createNewChart,
    getFilteredData,
    createDatasets,
    configureChart,
    applyNormalization,
    normalizeData,
    removeChart,
    getTableData,
    saveExperimentation,
    configureChartReload
} = require('./graphiques');

describe('graphiques.js tests', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="chartFormContainer" style="display: none;"></div>
            <div id="chartsContainer"></div>
            <input id="chartName" value="Test Chart" />
            <input type="radio" name="chartType" value="bar" checked />
            <div id="chartOptions">
                <input id="areaMean" type="checkbox" checked />
            </div>
            <input id="normalizeCheckbox" type="checkbox" />
            <input id="showSimulation" type="checkbox" checked />
            <input id="showVeriteTerrain" type="checkbox" />
            <div id="geoJsonNames" data-geojson-sim="sim.geojson" data-geojson-ver="ver.geojson"></div>
            <button id="saveBtn"></button>
            <div id="saveModal" style="display: none;">
                <span class="close"></span>
                <form id="saveForm"></form>
            </div>
            <div id="chartsData" data-charts='[]'></div>
        `;
    });

    test('initializeChart should initialize chart data', () => {
        const labels = ['Label1', 'Label2'];
        const dataSim = [1, 2];
        const dataVer = [3, 4];
        initializeChart(labels, dataSim, dataVer);
        expect(window.chartData).toEqual({ labels, dataSim, dataVer });
    });

    test('toggleChartForm should toggle the display of the chart form', () => {
        toggleChartForm();
        expect(document.getElementById('chartFormContainer').style.display).toBe('block');
        toggleChartForm();
        expect(document.getElementById('chartFormContainer').style.display).toBe('none');
    });

    test('createNewChart should create a new chart', () => {
        createNewChart();
        const chartDiv = document.querySelector('.chart-container');
        expect(chartDiv).not.toBeNull();
        expect(chartDiv.querySelector('h3').textContent).toBe('Test Chart');
    });

    test('getFilteredData should filter data based on selected options', () => {
        window.chartData = {
            labels: ['Label1', 'Label2', 'Label3', 'Label4'],
            dataSim: [1, 2, 3, 4],
            dataVer: [5, 6, 7, 8]
        };
        const selectedOptions = ['areaMean'];
        const filteredData = getFilteredData(selectedOptions);
        expect(filteredData).toEqual({
            labels: ['Label1'],
            dataSim: [1],
            dataVer: [5]
        });
    });

    test('createDatasets should create datasets based on filtered data', () => {
        const filteredData = {
            labels: ['Label1'],
            dataSim: [1],
            dataVer: [5]
        };
        const datasets = createDatasets(filteredData, 'bar', true, true);
        expect(datasets.length).toBe(2);
    });

    test('configureChart should configure a chart', () => {
        const chartDiv = document.createElement('div');
        chartDiv.innerHTML = '<canvas></canvas>';
        const filteredData = {
            labels: ['Label1'],
            dataSim: [1],
            dataVer: [5]
        };
        const datasets = createDatasets(filteredData, 'bar', true, true);
        const chart = configureChart(chartDiv, 'bar', filteredData, datasets);
        expect(chart).not.toBeNull();
    });

    test('applyNormalization should apply normalization to data', () => {
        const filteredData = {
            labels: ['Label1'],
            dataSim: [1],
            dataVer: [5]
        };
        const normalizedData = applyNormalization(filteredData, true);
        expect(normalizedData.dataSim[0]).toBe(0.2);
        expect(normalizedData.dataVer[0]).toBe(1);
    });

    test('normalizeData should normalize data', () => {
        const dataSim = [1, 2];
        const dataVer = [3, 4];
        const normalizedData = normalizeData(dataSim, dataVer);
        expect(normalizedData.normalizedSim).toEqual([0.3333333333333333, 0.5]);
        expect(normalizedData.normalizedVer).toEqual([1, 1]);
    });

    test('removeChart should remove a chart', () => {
        const chartDiv = document.createElement('div');
        document.body.appendChild(chartDiv);
        removeChart(chartDiv, null);
        expect(document.body.contains(chartDiv)).toBe(false);
    });

    test('getTableData should get table data', () => {
        document.body.innerHTML += `
            <table>
                <tr><th>Header1</th><th>Header2</th></tr>
                <tr><td>Data1</td><td>Data2</td></tr>
            </table>
        `;
        const tableData = getTableData();
        expect(tableData).toEqual([
            ['Header1', 'Header2'],
            ['Data1', 'Data2']
        ]);
    });

    test('saveExperimentation should save experimentation data', () => {
        global.fetch = jest.fn(() =>
            Promise.resolve({
                text: () => Promise.resolve(JSON.stringify({ success: true }))
            })
        );
        const experimentationData = {
            name: 'Test Experiment',
            folder: 'Test Folder',
            charts: [],
            geoJsonSimName: 'sim.geojson',
            geoJsonVerName: 'ver.geojson',
            tableData: []
        };
        saveExperimentation(experimentationData);
        expect(fetch).toHaveBeenCalled();
    });

    test('configureChartReload should configure and reload a chart', () => {
        const chartDiv = document.createElement('div');
        chartDiv.innerHTML = '<canvas></canvas>';
        const labels = ['Label1'];
        const datasets = [{
            label: 'Test',
            data: [1],
            backgroundColor: 'rgba(255, 99, 132, 0.5)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }];
        const chart = configureChartReload(chartDiv, 'bar', labels, datasets);
        expect(chart).not.toBeNull();
    });
});