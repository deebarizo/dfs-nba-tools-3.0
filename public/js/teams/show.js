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
            categories: dates,
            labels: {
                formatter: function() {
                    return this.value+'<br>'+game_data[this.value]['team1']+' '+game_data[this.value]['score1']+', '+game_data[this.value]['team2']+' '+game_data[this.value]['score2']+'<br><a target="_blank" href="'+game_data[this.value]['espn_link']+'">ESPN</a><br><a target="_blank" href="'+game_data[this.value]['pm_link']+'">PM</a>';
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