<script>
	$(function () {
	    $('#container').highcharts({
	        title: {
	            text: '<?php echo $data["xTitle"]; ?> and <?php echo $data["yTitle"]; ?>'
	        },
	        xAxis: {
	            title: {
	                enabled: true,
	                text: '<?php echo $data["xTitle"]; ?>'
	            },
	            startOnTick: true,
	            endOnTick: true,
	            showLastLabel: true
	        },
	        yAxis: {
	            title: {
	                text: '<?php echo $data["yTitle"]; ?>'
	            }
	        },
	        plotOptions: {
	            scatter: {
	                marker: {
	                    radius: 3,
	                    states: {
	                        hover: {
	                            enabled: true,
	                            lineColor: 'rgb(100,100,100)'
	                        }
	                    }
	                },
	                states: {
	                    hover: {
	                        marker: {
	                            enabled: false
	                        }
	                    }
	                },
	                tooltip: {
	                    pointFormat: '{point.x} <?php echo $data["xTitle"]; ?>, {point.y} <?php echo $data["yTitle"]; ?>'
	                }
	            }
	        },
	        series: [{
	        	type: 'scatter',
	        	name: 'Actual Results',
	            data: <?php echo json_encode($data['jsonNumbers']); ?>
	        }, {
	            type: 'scatter',
	            name: 'Line of Best Fit',
	            data: <?php echo json_encode($data['jsonLineOfBestFit']); ?>,
	        }, {
	        	type: 'scatter',
	        	name: 'Perfect Correlation',
	        	data: <?php echo json_encode($data['jsonPerfectLine']); ?>
	        }]
	    });
	});
</script>