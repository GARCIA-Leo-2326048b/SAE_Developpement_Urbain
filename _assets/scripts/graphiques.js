let activeChart = null;

function initializeChart(labels, dataSim, dataVer) {
    // Stockage des données globalement
    window.chartData = { labels, dataSim, dataVer };

    const filteredData = getFilteredData();
    const normalizeChecked = document.getElementById('normalizeCheckbox').checked; // Vérifie si la case de normalisation est cochée

    // Normalisation si la case est cochée
    if (normalizeChecked) {
        const normalizedData = normalizeData(filteredData.dataSim, filteredData.dataVer);
        filteredData.dataSim = normalizedData.normalizedSim;
        filteredData.dataVer = normalizedData.normalizedVer;
    }

    // Affichage du graphique correct en fonction du type sélectionné
    document.getElementById('diagrammeBarre').style.display = chartType === 'bar' ? 'block' : 'none';
    document.getElementById('spiderChart').style.display = chartType === 'spider' ? 'block' : 'none';
    document.getElementById('pieChart').style.display = chartType === 'pie' ? 'block' : 'none';

    const config = {
        type: chartType === 'bar' ? 'bar' : chartType === 'spider' ? 'radar' : 'pie', // Ajout du type 'pie'
        data: {
            labels: filteredData.labels,
            datasets: [
                {
                    label: 'Simulation',
                    data: filteredData.dataSim,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    fill: chartType === 'spider',
                },
                {
                    label: 'Vérité terrain',
                    data: filteredData.dataVer,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    fill: chartType === 'spider',
                },
            ],
        },
        options: {
            responsive: true,
            scales: chartType === 'bar'
                ? {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => `${value} m²`,
                        },
                    },
                }
                : chartType === 'pie' // Pas de scale pour le camembert
                    ? {}
                    : { r: { beginAtZero: true } }, // Pour le type 'spider'
        },
    };

    activeChart = new Chart(ctx, config);
}

function normalizeData(dataSim, dataVer) {
    const normalizedSim = [];
    const normalizedVer = [];

    // Normalisation par rapport à la valeur maximale entre les deux séries
    for (let i = 0; i < dataSim.length; i++) {
        const maxVal = Math.max(dataSim[i], dataVer[i]);
        normalizedSim.push(dataSim[i] / maxVal);
        normalizedVer.push(dataVer[i] / maxVal);
    }

    return { normalizedSim, normalizedVer };
}

///

// Fonction pour initialiser la page
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('addChartBtn').addEventListener('click', toggleChartForm);
    document.getElementById('createChart').addEventListener('click', createNewChart);
});

// Afficher/Masquer le formulaire d'ajout
function toggleChartForm() {
    const formContainer = document.getElementById('chartFormContainer');
    formContainer.style.display = formContainer.style.display === 'none' ? 'block' : 'none';
}

// Créer un nouveau graphique
function createNewChart() {
    const chartName = document.getElementById('chartName').value;
    const chartType = document.querySelector('input[name="chartType"]:checked').value; // Vérifie le type sélectionné
    const selectedOptions = Array.from(document.querySelectorAll('#chartOptions input:checked')).map(opt => opt.id);
    const normalizeChecked = document.getElementById('normalizeCheckbox').checked; // Vérifie si la case de normalisation est cochée

    const chartsContainer = document.getElementById('chartsContainer');
    const chartDiv = document.createElement('div');
    chartDiv.className = 'chart-container';
    const chartId = 'chart-' + Date.now(); // ID unique pour chaque graphique
    chartDiv.innerHTML = `
    <h3>${chartName}</h3>
    <canvas id="${chartId}"></canvas>
    <button class="editChartBtn">Modifier</button>
`;
    chartsContainer.appendChild(chartDiv);

    const ctx = chartDiv.querySelector('canvas').getContext('2d');
    const filteredData = getFilteredData(selectedOptions);

    // Normalisation si la case est cochée
    if (normalizeChecked) {
        const normalizedData = normalizeData(filteredData.dataSim, filteredData.dataVer);
        filteredData.dataSim = normalizedData.normalizedSim;
        filteredData.dataVer = normalizedData.normalizedVer;
    }

    // Configuration du graphique en fonction du type sélectionné
    const config = {
        type: chartType === 'bar' ? 'bar' : chartType === 'spider' ? 'radar' : 'pie', // Ajout du type 'pie'
        data: {
            labels: filteredData.labels,
            datasets: [
                {
                    label: 'Simulation',
                    data: filteredData.dataSim,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    fill: chartType === 'spider',
                },
                {
                    label: 'Vérité terrain',
                    data: filteredData.dataVer,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    fill: chartType === 'spider',
                },
            ],
        },
        options: {
            responsive: true,
            scales: chartType === 'bar'
                ? {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => `${value} m²`,
                        },
                    },
                }
                : chartType === 'pie' // Pas de scale pour le camembert
                    ? {}
                    : { r: { beginAtZero: true } }, // Pour le type 'spider'
        },
    };

    const chart = new Chart(ctx, config);

    // Ajouter l'événement pour modifier le graphique
    chartDiv.querySelector('.editChartBtn').addEventListener('click', () => editChart(chart, chartName));

    // Cacher le formulaire après la création du graphique
    document.getElementById('chartFormContainer').style.display = 'none';
}

// Filtrer les données en fonction des options sélectionnées
function getFilteredData(selectedOptions) {
    const labels = [];
    const dataSim = [];
    const dataVer = [];

    // Map des options disponibles
    const dataMap = {
        areaMean: 0,
        areaMin: 1,
        areaMax: 2,
        areaStd: 3,
        shapeIndexMax: 6,
        shapeIndexMin: 5,
        shapeIndexMean: 4,
        shapeIndexStd: 7,
    };

    selectedOptions.forEach(option => {
        const index = dataMap[option];
        labels.push(window.chartData.labels[index]);
        dataSim.push(window.chartData.dataSim[index]);
        dataVer.push(window.chartData.dataVer[index]);
    });

    return {labels, dataSim, dataVer};
}
