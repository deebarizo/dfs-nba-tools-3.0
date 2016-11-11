$(document).ready(function() {

	console.log(series);

	$('.team-rotation-line-chart').highcharts({
        chart: {
            type: 'line'
        },
        title: {
            text: team
        },
        xAxis: {
            categories: dates
        },
        yAxis: {
            title: {
                text: 'Minutes'
            },
            tickInterval: 5,
        	tickPixelInterval: 400,
        	min: 0
        },

        series: series
    });

});