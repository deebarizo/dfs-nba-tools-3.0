$(document).ready(function() {

	console.log(games);

	$('.team-rotation-line-chart').highcharts({
        chart: {
            type: 'line'
        },
        title: {
            text: team
        },
        xAxis: {
            categories: dates,
            labels: {
                formatter: function() {
                    return this.value+'<br>'+games[this.value]['game_lines'][0]['dk_name']+' '+games[this.value]['game_lines'][0]['pts']+' @'+games[this.value]['game_lines'][1]['dk_name']+' '+games[this.value]['game_lines'][1]['pts']+'<br><a target="_blank" href="'+games[this.value]['game_lines'][0]['br_link']+'">BR</a><br><a target="_blank" href="'+games[this.value]['game_lines'][0]['br_link']+'">PM</a>';
                },
                useHTML: true
            }
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