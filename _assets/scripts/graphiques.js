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
    const chart = new Chart(ctx, config);

    // Attacher l'objet chart à l'élément <canvas>
    chartDiv.querySelector('canvas').chart = chart;

    return chart;
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

document.addEventListener('DOMContentLoaded', () => {
    const geoJsonNamesElement = document.getElementById('geoJsonNames');
    const geoJsonSimName = geoJsonNamesElement.getAttribute('data-geojson-sim');
    const geoJsonVerName = geoJsonNamesElement.getAttribute('data-geojson-ver');

    const saveBtn = document.getElementById('saveBtn');
    const modal = document.getElementById('saveModal');
    const closeBtn = modal.querySelector('.close');
    const saveForm = document.getElementById('saveForm');

    // Ouvrir la modale
    saveBtn.addEventListener('click', () => {
        modal.style.display = 'block';
    });

    // Fermer la modale
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Fermer la modale si on clique en dehors
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Confirmer la sauvegarde
    saveForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const experimentName = document.getElementById('experimentName').value;
        const folderName = document.getElementById('folderSelect').value;

        const charts = [];
        const chartContainers = document.querySelectorAll('.chart-container');

        chartContainers.forEach(chartContainer => {
            const chartName = chartContainer.querySelector('h3').textContent;
            const chartType = chartContainer.querySelector('canvas').getAttribute('id').split('-')[1];
            const chartData = chartContainer.querySelector('canvas').chart.data; // Récupérer les données du graphique
            const chartOptions = chartContainer.querySelector('canvas').chart.options; // Récupérer les options

            // Rassembler toutes les informations du graphique
            charts.push({
                name: chartName,
                type: chartType,
                data: chartData,
                options: chartOptions,
            });
        });

        // Créer un objet avec toutes les informations à sauvegarder
        const tableData = getTableData();

        const experimentationData = {
            name: experimentName,
            folder: folderName,
            charts: charts,
            geoJsonSimName: geoJsonSimName,
            geoJsonVerName: geoJsonVerName,
            tableData: tableData, // Inclure les données du tableau
        };

        // Sauvegarder dans la base de données (envoi de données à PHP)
        saveExperimentation(experimentationData);

        // Fermer la modale
        modal.style.display = 'none';
    });
});


function getTableData() {
    const tableRows = document.querySelectorAll('table tr'); // Sélectionne toutes les lignes de tous les tableaux
    const tableData = Array.from(tableRows).map(row => {
        const cells = row.querySelectorAll('td, th'); // Sélectionne toutes les cellules (y compris les en-têtes)
        return Array.from(cells).map(cell => cell.textContent.trim()); // Extrait le contenu de chaque cellule
    });

    // On supprime les lignes qui ne contiennent pas de données utiles (celles avec uniquement des en-têtes)
    return tableData.filter(row => row.some(cell => cell.trim() !== ''));
}

function saveExperimentation(experimentationData) {
    fetch('index.php?action=save_experimentation', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(experimentationData),
    })
        .then(response => response.text())  // Récupère la réponse en tant que texte
        .then(data => {
            console.log('Réponse brute du serveur:', data);
            try {
                const jsonData = JSON.parse(data); // Essaie de convertir la réponse en JSON
                if (jsonData.success) {
                    alert("Données enregistrées avec succès !");
                } else {
                    alert("Une erreur est survenue lors de la sauvegarde.");
                }
            } catch (error) {
                console.error('Erreur lors du parsing du JSON:', error);
                //console.log('Réponse brute:', data);  // Affiche la réponse brute pour débogage
            }
        })
        .catch(error => console.error('Erreur:', error));
}

function recreateCharts(experimentationData) {
    const { charts, geoJsonSimName, geoJsonVerName, tableData } = experimentationData;

    // Réinsertion des graphiques
    const chartsContainer = document.getElementById('chartsContainer');

    // Nettoyer les anciens graphiques avant d'ajouter les nouveaux
    chartsContainer.innerHTML = '';

    charts.forEach(chartData => {
        // Réutiliser createNewChart pour chaque graphique
        createNewChart(chartData); // Passer l'objet chartData pour recréer le graphique
    });

    // Vous pouvez également recréer ou afficher les données du tableau et les fichiers GeoJSON si nécessaire
    // Ex: afficher `geoJsonSimName`, `geoJsonVerName`, et `tableData`
}

// Fonction pour reformater les données reçues de PHP
function reformatDataForComparison(tableData) {
    // Initialisation des arrays pour les différentes catégories
    const graphSim = [];
    const graphVer = [];
    const errors = [];

    // On parcourt les lignes du tableau en sautant la première ligne (entêtes)
    for (let i = 1; i < tableData.length; i++) {
        const row = tableData[i];

        // Si la ligne est valide
        if (row.length === 4) {
            const label = row[0];
            const simValue = parseFloat(row[1]);  // Simulation
            const verValue = parseFloat(row[2]);  // Vérité terrain
            const errorValue = parseFloat(row[3]); // Erreur

            // Ajouter les données dans les arrays correspondants
            graphSim.push({ label: label, y: simValue });
            graphVer.push({ label: label, y: verValue });
            errors.push({ label: "Error " + label, y: errorValue });
        }
    }

    // Retourner un objet avec les données formatées
    return {
        graphSim: graphSim,
        graphVer: graphVer,
        errors: errors
    };
}
