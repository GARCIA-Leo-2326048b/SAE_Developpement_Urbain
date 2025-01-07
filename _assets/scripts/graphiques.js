let activeChart = null;
let chartType = 'bar'; // Type de graphique par défaut

function initializeChart(labels, dataSim, dataVer) {
    // Stockage des données globalement
    window.chartData = { labels, dataSim, dataVer };

    const filteredData = getFilteredData();
    const normalizeChecked = document.getElementById('normalizeCheckbox').checked; // Vérifie si la case de normalisation est cochée
    const showSimulation = document.getElementById('showSimulation').checked; // Vérifie si on doit afficher la simulation
    const showVeriteTerrain = document.getElementById('showVeriteTerrain').checked; // Vérifie si on doit afficher la vérité terrain

    // Normalisation si la case est cochée
    if (normalizeChecked) {
        const normalizedData = normalizeData(filteredData.dataSim, filteredData.dataVer);
        filteredData.dataSim = normalizedData.normalizedSim;
        filteredData.dataVer = normalizedData.normalizedVer;
    }

    // Masquer tous les types de graphiques avant d'afficher celui sélectionné
    document.querySelectorAll('.chart-container').forEach(chart => {
        chart.style.display = 'none';
    });

    const chartDiv = document.getElementById('chart-' + chartType);
    if (chartDiv) {
        chartDiv.style.display = 'block'; // Affiche le graphique sélectionné
    }

    // Déterminer les datasets à afficher en fonction des options sélectionnées
    const datasets = [];
    if (showSimulation) {
        datasets.push({
            label: 'Simulation',
            data: filteredData.dataSim,
            backgroundColor: 'rgba(255, 99, 132, 0.5)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1,
            fill: chartType === 'spider',
        });
    }

    if (showVeriteTerrain) {
        datasets.push({
            label: 'Vérité terrain',
            data: filteredData.dataVer,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            fill: chartType === 'spider',
        });
    }

    const config = {
        type: chartType === 'bar' ? 'bar' : chartType === 'spider' ? 'radar' : 'pie',
        data: {
            labels: filteredData.labels,
            datasets: datasets, // Utilisation dynamique des datasets
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
                : chartType === 'pie'
                    ? {}
                    : { r: { beginAtZero: true } }, // Pour le type 'spider'
        },
    };

    if (activeChart) {
        activeChart.destroy(); // Détruire l'ancien graphique avant de créer un nouveau
    }

    const ctx = chartDiv.querySelector('canvas').getContext('2d');
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
    chartType = document.querySelector('input[name="chartType"]:checked').value; // Vérifie le type sélectionné
    const selectedOptions = Array.from(document.querySelectorAll('#chartOptions input:checked')).map(opt => opt.id);
    const normalizeChecked = document.getElementById('normalizeCheckbox').checked; // Vérifie si la case de normalisation est cochée
    const showSimulation = document.getElementById('showSimulation').checked; // Vérifie si on doit afficher la simulation
    const showVeriteTerrain = document.getElementById('showVeriteTerrain').checked; // Vérifie si on doit afficher la vérité terrain

    const chartsContainer = document.getElementById('chartsContainer');
    const chartDiv = document.createElement('div');
    chartDiv.className = 'chart-container';
    chartDiv.id = 'chart-' + chartType; // Ajout de l'ID pour chaque type
    const chartId = 'canvas-' + Date.now(); // ID unique pour chaque graphique
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

    // Déterminer les datasets à afficher en fonction des options sélectionnées
    const datasets = [];
    if (showSimulation) {
        datasets.push({
            label: 'Simulation',
            data: filteredData.dataSim,
            backgroundColor: 'rgba(255, 99, 132, 0.5)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1,
            fill: chartType === 'spider',
        });
    }

    if (showVeriteTerrain) {
        datasets.push({
            label: 'Vérité terrain',
            data: filteredData.dataVer,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            fill: chartType === 'spider',
        });
    }

    // Configuration du graphique en fonction du type sélectionné
    const config = {
        type: chartType === 'bar' ? 'bar' : chartType === 'spider' ? 'radar' : 'pie',
        data: {
            labels: filteredData.labels,
            datasets: datasets, // Utilisation dynamique des datasets
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
                : chartType === 'pie'
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
function getFilteredData(selectedOptions = []) {
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

    // Si aucune option n'est sélectionnée, on utilise toutes les données disponibles
    if (selectedOptions.length === 0) {
        selectedOptions = Object.keys(dataMap);
    }

    selectedOptions.forEach(option => {
        const index = dataMap[option];
        if (index !== undefined) {
            labels.push(window.chartData.labels[index]);
            dataSim.push(window.chartData.dataSim[index]);
            dataVer.push(window.chartData.dataVer[index]);
        }
    });

    return { labels, dataSim, dataVer };
}

function editChart(chart, chartName) {
    // Exemple d'action pour modifier un graphique
    alert(`Modifier le graphique : ${chartName}`);
}
