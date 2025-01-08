let chartType = 'bar';

//initialiser les données
function initializeChart(labels, dataSim, dataVer) {
    window.chartData = { labels, dataSim, dataVer };
}


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
    chartType = document.querySelector('input[name="chartType"]:checked').value;

    const selectedOptions = Array.from(document.querySelectorAll('#chartOptions input:checked')).map(opt => opt.id);
    const normalizeChecked = document.getElementById('normalizeCheckbox').checked;
    const showSimulation = document.getElementById('showSimulation').checked;
    const showVeriteTerrain = document.getElementById('showVeriteTerrain').checked;

    const chartsContainer = document.getElementById('chartsContainer');
    const chartDiv = document.createElement('div');
    chartDiv.className = 'chart-container';
    chartDiv.id = 'chart-' + chartType;
    const chartId = 'canvas-' + Date.now();
    chartDiv.innerHTML = `
        <h3>${chartName}</h3>
        <canvas id="${chartId}"></canvas>
        <button class="deleteChartBtn">
    <img src="./_assets/includes/trash-icon.png" alt="Supprimer" class="trash-icon" />
</button>
    `;
    chartsContainer.appendChild(chartDiv);

    chartDiv.querySelector('.deleteChartBtn').addEventListener('click', () => removeChart(chartDiv, null));

    const filteredData = getFilteredData(selectedOptions);

    // Appliquer la normalisation si nécessaire
    const normalizedData = applyNormalization(filteredData, normalizeChecked);

    // Gérer les datasets
    const datasets = createDatasets(normalizedData, chartType, showSimulation, showVeriteTerrain);

    // Créer le graphique
    configureChart(chartDiv, chartType, normalizedData, datasets);

    // Cacher le formulaire
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
function createDatasets(filteredData, chartType, showSimulation, showVeriteTerrain) {
    const datasets = [];
    const colors = [
        'rgba(255, 99, 132, 0.5)', // Couleur pour la première donnée
        'rgba(54, 162, 235, 0.5)', // Couleur pour la deuxième donnée
        'rgba(255, 206, 86, 0.5)', // Couleur pour la troisième donnée
        'rgba(75, 192, 192, 0.5)', // Couleur pour la quatrième donnée
        'rgba(153, 102, 255, 0.5)', // Couleur pour la cinquième donnée
        'rgba(255, 159, 64, 0.5)', // Couleur pour la sixième donnée
        // Ajoutez autant de couleurs que nécessaire
    ];

    // Si Simulation est sélectionné
    if (showSimulation) {
        if (showVeriteTerrain) {
            // Si les deux sont sélectionnés, utiliser une seule couleur pour la Simulation
            datasets.push({
                label: 'Simulation',
                data: filteredData.dataSim,
                backgroundColor: 'rgba(255, 99, 132, 0.5)', // Couleur uniforme pour la Simulation
                borderColor: 'rgba(255, 99, 132, 1)', // Bordure uniforme
                borderWidth: 1,
            });
        } else {
            // Si seul la Simulation est sélectionnée, utiliser des couleurs différentes
            datasets.push({
                label: 'Simulation',
                data: filteredData.dataSim,
                backgroundColor: colors.slice(0, filteredData.dataSim.length), // Couleurs différentes pour chaque donnée
                borderColor: colors.slice(0, filteredData.dataSim.length).map(color => color.replace('0.5', '1')), // Bordure avec couleurs différentes
                borderWidth: 1,
            });
        }
    }

    // Si Vérité terrain est sélectionné
    if (showVeriteTerrain) {
        if (showSimulation) {
            // Si les deux sont sélectionnés, utiliser une seule couleur pour la Vérité terrain
            datasets.push({
                label: 'Vérité terrain',
                data: filteredData.dataVer,
                backgroundColor: 'rgba(54, 162, 235, 0.5)', // Couleur uniforme pour la Vérité terrain
                borderColor: 'rgba(54, 162, 235, 1)', // Bordure uniforme
                borderWidth: 1,
            });
        } else {
            // Si seul la Vérité terrain est sélectionnée, utiliser des couleurs différentes
            datasets.push({
                label: 'Vérité terrain',
                data: filteredData.dataVer,
                backgroundColor: colors.slice(0, filteredData.dataVer.length), // Couleurs différentes pour chaque donnée
                borderColor: colors.slice(0, filteredData.dataVer.length).map(color => color.replace('0.5', '1')), // Bordure avec couleurs différentes
                borderWidth: 1,
            });
        }
    }

    return datasets;
}

function configureChart(chartDiv, chartType, filteredData, datasets) {
    const config = {
        type: chartType === 'bar' ? 'bar' : chartType === 'spider' ? 'radar' : 'pie',
        data: {
            labels: filteredData.labels,
            datasets: datasets,
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
                    : { r: { beginAtZero: true } },
        },
    };

    const ctx = chartDiv.querySelector('canvas').getContext('2d');
    return new Chart(ctx, config);
}
function applyNormalization(filteredData, normalizeChecked) {
    if (normalizeChecked) {
        const normalizedData = normalizeData(filteredData.dataSim, filteredData.dataVer);
        filteredData.dataSim = normalizedData.normalizedSim;
        filteredData.dataVer = normalizedData.normalizedVer;
    }
    return filteredData;
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
function removeChart(chartDiv, chart) {
    chartDiv.remove(); // Supprimer l'élément graphique
    if (chart) {
        chart.destroy(); // Détruire le graphique
    }
}
