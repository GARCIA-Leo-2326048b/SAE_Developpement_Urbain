
    let activeChart = null;

    function initializeChart(labels, dataSim, dataVer) {
    // Store data globally
    window.chartData = { labels, dataSim, dataVer };

    // Create initial chart when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
    createChart('bar', labels, dataSim, dataVer);
    setupEventListeners();
});
}

    function setupEventListeners() {
    document.querySelectorAll('input[name="chartType"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            createChart(e.target.value, window.chartData.labels, window.chartData.dataSim, window.chartData.dataVer);
        });
    });

    document.querySelectorAll('#optionsForm input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
    const type = document.querySelector('input[name="chartType"]:checked').value;
    createChart(type, window.chartData.labels, window.chartData.dataSim, window.chartData.dataVer);
});
});
}

    function createChart(type, labels, dataSim, dataVer) {
    const ctx = document.getElementById(type === 'bar' ? 'diagrammeBarre' : 'spiderChart');

    if (activeChart) {
    activeChart.destroy();
}

    const filteredData = getFilteredData();

    // Display correct chart based on selected type
    document.getElementById('diagrammeBarre').style.display = type === 'bar' ? 'block' : 'none';
    document.getElementById('spiderChart').style.display = type === 'spider' ? 'block' : 'none';

    const config = {
    type: type === 'bar' ? 'bar' : 'radar',
    data: {
    labels: filteredData.labels,
    datasets: [{
    label: 'Simulation',
    data: type === 'spider' ? normalizeData(filteredData.dataSim, filteredData.dataVer).normalizedSim : filteredData.dataSim,
    borderWidth: 1,
    backgroundColor: 'rgba(255, 99, 132, 0.5)',
    borderColor: 'rgba(255, 99, 132, 1)',
    fill: type === 'spider'
}, {
    label: 'Vérité terrain',
    data: type === 'spider' ? normalizeData(filteredData.dataSim, filteredData.dataVer).normalizedVer : filteredData.dataVer,
    borderWidth: 1,
    backgroundColor: 'rgba(54, 162, 235, 0.5)',
    borderColor: 'rgba(54, 162, 235, 1)',
    fill: type === 'spider'
}]
},
    options: {
    responsive: true,
    scales: type === 'bar' ? {
    y: {
    beginAtZero: true,
    ticks: { callback: value => `${value} m²` }
}
} : { r: { beginAtZero: true } }
}
};

    activeChart = new Chart(ctx, config);
}

    function getFilteredData() {
    const checkboxes = {
    areaMean: document.getElementById('areaMean')?.checked || false,
    areaMin: document.getElementById('areaMin')?.checked || false,
    areaMax: document.getElementById('areaMax')?.checked || false,
    areaStd: document.getElementById('areaStd')?.checked || false,
    shapeIndexMax: document.getElementById('shapeIndexMax')?.checked || false,
    shapeIndexMin: document.getElementById('shapeIndexMin')?.checked || false,
    shapeIndexMean: document.getElementById('shapeIndexMean')?.checked || false,
    shapeIndexStd: document.getElementById('shapeIndexStd')?.checked || false
};

    // Use filter and map to simplify the logic of checking checkboxes
    const filtered = {
    labels: [],
    dataSim: [],
    dataVer: []
};

    Object.entries(checkboxes).forEach(([key, checked], index) => {
    if (checked) {
    filtered.labels.push(window.chartData.labels[index]);
    filtered.dataSim.push(window.chartData.dataSim[index]);
    filtered.dataVer.push(window.chartData.dataVer[index]);
}
});

    return filtered;
}

    function normalizeData(dataSim, dataVer) {
    const normalizedSim = [];
    const normalizedVer = [];

    // Normalisation by label
    for (let i = 0; i < dataSim.length; i++) {
    const maxVal = Math.max(dataSim[i], dataVer[i]);
    normalizedSim.push(dataSim[i] / maxVal);
    normalizedVer.push(dataVer[i] / maxVal);
}

    return { normalizedSim, normalizedVer };
}

    window.diagrammeBarre = initializeChart;

