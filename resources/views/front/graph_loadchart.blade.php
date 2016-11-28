<html>
<head>
  	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
  	<title>Load chart</title>
  	<script type='text/javascript' src='/common/js/jquery-1.9.1.js'></script>
  	<script src="/common/js/highcharts.js"></script>
	<script src="/common/js/highcharts_exporting.js?1"></script>
	<script src="/common/js/highcharts-offline-exporting.js?1"></script>
<script type='text/javascript'>
$(function () {
    $('#container').highcharts({
        chart: {
            zoomType: 'xy'
        },
		credits: false,
        title: {
            text: <?php echo ($title?"'$title'":"null"); ?>
        },
        subtitle: {
            text: null
        },
        xAxis: {
            type: 'datetime',
            dateTimeLabelFormats: { // don't display the dummy year
                month: '%e. %b',
                year: '%b'
            },
            title: {
                text: 'Occur date'
            }
        },
        tooltip: {
            headerFormat: '<b>{series.name}</b><br>',
            pointFormat: '{point.x:%e. %b}: {point.y:.2f} kL'
        },

        plotOptions: {
            spline: {
                marker: {
                    enabled: true
                }
            },
            pie: {
                dataLabels: {
//                    distance: -50,
                    format: '{point.name}: {point.y}'
                },
    			tooltip: {
    				headerFormat: '',
    				pointFormat: '{point.name}: {point.y}'
    			},
            } 
        },
        exporting: {
            sourceWidth: $('#container').width(),
            sourceHeight: $('#container').height(),
            scale: 1,
            chartOptions: {
                subtitle: null
            }
        },
        series: [
				<?php echo preg_replace('/_@/', ' ',$series);?> 
             ],
        yAxis: [{ // Primary yAxis
			min: <?php echo $min1;?>,
			max: <?php echo $max1;?>,
            labels: {
                format: '{value}',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            title: {
                text: 'Liquid Volume (kL)',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            opposite: false

        },{ // Secondary yAxis
        	min: <?php echo $min2;?>,
        	max: <?php echo $max2;?>,
            labels: {
                format: '{value}',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            title: {
                text: 'Gas Volume (scm)',
                style: {
                    color: 'green'
                }
            },
            opposite: true

        }
		]
    });
});
</script>
</head>
<body>
	<div id="container" style="min-width: 400px; height: 380px; margin: 0 auto"></div>
</body>
</html>
