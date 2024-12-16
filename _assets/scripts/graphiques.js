function diagrammeBarre(DonneesSimulees, DonneesVerite) {

    var chart = new CanvasJS.Chart("diagrammeBarre", {
        animationEnabled: true,
        theme: "light2",
        title:{
            text: "Comparaison des données de simulation et de verité terrain"
        },
        axisY:{
            includeZero: true,
            suffix: " m²"
        },
        legend:{
            cursor: "pointer",
            verticalAlign: "center",
            horizontalAlign: "right",
            itemclick: toggleDataSeries
        },
        data: [{
            type: "column",
            name: "Simulation",
            indexLabel: "{y}",
            yValueFormatString: "##.## m²",
            showInLegend: true,
            dataPoints: DonneesSimulees
        },{
            type: "column",
            name: "Verité terrain",
            indexLabel: "{y}",
            yValueFormatString: "##.## m²",
            showInLegend: true,
            dataPoints: DonneesVerite
        }]
    });
    chart.render();

    function toggleDataSeries(e){
        if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
            e.dataSeries.visible = false;
        }
        else{
            e.dataSeries.visible = true;
        }
        chart.render();
    }
}

function diagrammeBarre2(labels, dataSim, dataVer){
    const ctx = document.getElementById('diagrammeBarre2');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,  // Utilisation des labels passés en paramètre
            datasets: [{
                label: 'Simulation',
                data: dataSim,  // Utilisation des données de simulation passées en paramètre
                borderWidth: 1
            }, {
                label: 'Vérité terrain',
                data: dataVer,  // Utilisation des données de vérité terrain passées en paramètre
                borderWidth: 1
            }]
        },

        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => `${value} m²`
                    }
                }
            }
        }
    });
}

function normalizeData(dataSim, dataVer) {
    const normalizedSim = [];
    const normalizedVer = [];

    // Normalisation par label
    for (let i = 0; i < dataSim.length; i++) {
        const maxVal = Math.max(dataSim[i], dataVer[i]); // Trouver la valeur maximale entre les deux
        normalizedSim.push(dataSim[i] / maxVal); // Normaliser la valeur de simulation
        normalizedVer.push(dataVer[i] / maxVal); // Normaliser la valeur de vérité terrain
    }

    return { normalizedSim, normalizedVer };
}

function spiderChart(labels, dataSim, dataVer) {
    // Normaliser les données
    const { normalizedSim, normalizedVer } = normalizeData(dataSim, dataVer);

    const ctx = document.getElementById('spiderChart');

    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: labels, // Utilisation des labels passés en paramètre
            datasets: [{
                label: 'Simulation',
                data: normalizedSim, // Données normalisées pour Simulation
                fill: true,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgb(255, 99, 132)',
                pointBackgroundColor: 'rgb(255, 99, 132)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(255, 99, 132)'
            }, {
                label: 'Vérité terrain',
                data: normalizedVer, // Données normalisées pour Vérité terrain
                fill: true,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgb(54, 162, 235)',
                pointBackgroundColor: 'rgb(54, 162, 235)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(54, 162, 235)'
            }]
        },

        options: {
            scales: {
                r: {
                    beginAtZero: true,
                    min: 0,
                    max: 1, // Fixer l'échelle à 0-1
                    ticks: {
                        stepSize: 0.2 // Graduation tous les 0.2
                    }
                }
            }
        }
    });
}
