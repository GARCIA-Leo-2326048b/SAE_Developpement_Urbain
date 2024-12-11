function diagrammeBarre(DonneesSimulees, DonneesVerite) {

    var chart = new CanvasJS.Chart("chartContainer", {
        animationEnabled: true,
        theme: "light2",
        title:{
            text: "Comparaison des données de simulation et de verité terrain"
        },
        axisY:{
            includeZero: true
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
            name: "Vertité terrain",
            indexLabel: "{y}",
            yValueFormatString: "$##.## m²",
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
