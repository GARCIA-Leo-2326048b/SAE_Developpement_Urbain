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
        const chartId = chartType;
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
                const chartType = chartContainer.querySelector('canvas').getAttribute('id');
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


    document.addEventListener('DOMContentLoaded', () => {
        const chartsElement = document.getElementById('chartsData');

        if (chartsElement && chartsElement.hasAttribute('data-charts')) {
            const jsonData = chartsElement.getAttribute('data-charts');

            try {
                const chartsData = JSON.parse(jsonData);

                if (Array.isArray(chartsData) && chartsData.length > 0) {
                    chartsData.forEach((chartData, index) => {
                        // Vérification et parsing des données internes
                        if (typeof chartData.data_xp === "string") {
                            try {
                                const parsedData = JSON.parse(chartData.data_xp);

                                // Si 'data_xp' contient plusieurs graphiques
                                if (Array.isArray(parsedData) && parsedData.length > 0) {
                                    parsedData.forEach((parsedChartData, innerIndex) => {
                                        createChart(
                                            parsedChartData,
                                            index,
                                            innerIndex
                                        );
                                    });
                                }
                            } catch (error) {
                                console.error(
                                    `Erreur lors du parsing interne de 'data_xp' pour le graphique ${index}:`,
                                    error
                                );
                            }
                        } else {
                            // Si les données ne sont pas imbriquées dans 'data_xp'
                            createChart(chartData, index);
                        }
                    });
                } else {
                    console.error("Aucun graphique valide trouvé dans les données JSON.");
                }
            } catch (error) {
                console.error("Erreur lors de la conversion des données JSON:", error);
            }
        } else {
            console.error("L'élément 'chartsData' n'a pas l'attribut 'data-charts'.");
        }
    });

    /**
     * Crée un graphique en fonction des données fournies.
     * @param {Object} chartData - Les données du graphique.
     * @param {number} index - L'index principal du graphique.
     * @param {number} [innerIndex] - L'index interne (si plusieurs graphiques dans 'data_xp').
     */
    function createChart(chartData, index, innerIndex = null) {
        const chartType = chartData.type || 'bar'; // Type de graphique par défaut : bar
        const labels = chartData.data.labels || [];
        const datasets = chartData.data.datasets || [];
        const chartName = chartData.name || `Graphique ${index + 1}${innerIndex !== null ? `-${innerIndex + 1}` : ''}`;

        // Génération d'un ID unique basé sur le chartType et les index
        const chartId = `${chartType}-${index}${innerIndex !== null ? `-${innerIndex}` : ''}`;

        // Créer un conteneur pour le graphique
        const chartsContainer = document.getElementById('chartsContainer');
        const chartDiv = document.createElement('div');
        chartDiv.className = 'chart-container';
        chartDiv.innerHTML = `
        <h3>${chartName}</h3>
        <canvas id="${chartId}"></canvas>
        <button class="deleteChartBtn">
            <img src="./_assets/includes/trash-icon.png" alt="Supprimer" class="trash-icon" />
        </button>
    `;

        chartsContainer.appendChild(chartDiv);

        // Configurez l'événement de suppression
        chartDiv.querySelector('.deleteChartBtn').addEventListener('click', () => removeChart(chartDiv));

        // Configurez et affichez le graphique
        configureChartReload(chartDiv, chartType, labels, datasets);
    }

    /**
     * Fonction pour configurer et recharger un graphique.
     * @param {HTMLElement} chartDiv - Le conteneur du graphique.
     * @param {string} chartType - Le type de graphique (bar, radar, etc.).
     * @param {Array} labels - Les étiquettes du graphique.
     * @param {Array} datasets - Les ensembles de données pour le graphique.
     */
    function configureChartReload(chartDiv, chartType, labels, datasets) {
        const config = {
            type: chartType === 'spider' ? 'radar' : chartType, // Convertit "spider" en "radar"
            data: {
                labels: labels,
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
                    : chartType === 'radar'
                        ? { r: { beginAtZero: true } }
                        : {},
            },
        };

        const ctx = chartDiv.querySelector('canvas').getContext('2d');
        const chart = new Chart(ctx, config);

        // Attacher l'objet chart à l'élément <canvas>
        chartDiv.querySelector('canvas').chart = chart;

        return chart;
    }

    //Le cas d'enregistrement après un reload
    function enregistrer() {
        const updateBtn = document.getElementById('updateBtn');
        const experimentationId = updateBtn.getAttribute('data-id'); // ID de l'expérimentation existante

        if (!experimentationId) {
            alert("ID de l'expérimentation introuvable !");
            return;
        }

        // Récupérer les graphiques dans #chartsContainer
        const charts = [];
        const chartContainers = document.querySelectorAll('#chartsContainer .chart-container');

        chartContainers.forEach(chartContainer => {
            const canvas = chartContainer.querySelector('canvas');

            if (canvas && canvas.chart) {
                const chartName = chartContainer.querySelector('h3')?.textContent || "Sans titre";
                const chartType = canvas.chart.config.type || 'bar'; // Utiliser le type de graphique du chart
                const chartData = canvas.chart.data; // Données du graphique
                const chartOptions = canvas.chart.options; // Options du graphique

                // Validation du type de graphique
                const validChartTypes = ['pie', 'bar', 'line', 'doughnut', 'radar'];
                if (!validChartTypes.includes(chartType)) {
                    console.error(`Type de graphique invalide : ${chartType}`);
                    return; // Ne pas envoyer ce graphique
                }

                charts.push({
                    name: chartName,
                    type: chartType,
                    data: chartData,
                    options: chartOptions,
                });
            }
        });

        // Préparer les données pour l'envoi
        const experimentationData = {
            id: experimentationId, // ID de l'expérimentation à mettre à jour
            charts: charts, // Nouveaux graphiques uniquement
        };

        // Envoi des graphiques via AJAX
        fetch(`index.php?action=reloadExpUpdate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(experimentationData),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Graphiques mis à jour avec succès !");
                } else {
                    alert("Une erreur est survenue lors de la mise à jour.");
                }
            })
            .catch(error => console.error('Erreur:', error));
    }










