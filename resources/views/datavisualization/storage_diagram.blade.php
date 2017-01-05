@extends('choke.choke_diagram')
@section("renderChart")
@parent
<script type="text/javascript">
	chartParameter.url = "/storagedisplay/loadchart";

	editBox.genDiagram = function (diagram,view){
		if(typeof diagram == "undefined") return;
		var series 		= diagram.series;
		$.each(series, function( index, value ) {
			$.each(value.data, function( index, data ) {
				day	= getJsDate(data.D);
				pvalue = parseFloat(data.V);
				pvalue	= isNaN(pvalue)?null:pvalue;
				value.data[index]	= [day,pvalue];
	         });
         });

		$.each(diagram.plotLines, function( index, value ) {
			pvalue = parseFloat(value.value);
			pvalue	= isNaN(pvalue)?null:pvalue;
			value.value	= pvalue;
			value.width		= 2;
         });
		
		/* if(diagram.minY>0){
			var lineData = Array.apply(null, Array(diagram.groups.length)).map(function (_, i) {return diagram.minY;});
			series.push({
				type: 'line',
				color: 'red',
				name: 'MPP',
				lineWidth: 2,
				showInLegend:false,
				marker: {enabled: false},
				states: {hover: {enabled: false}},
				tooltip: {enabled: false,pointFormat: '{point.y:.2f}'},
				data: lineData,
			});
		}	 */

		var diagramOption	= {
				chart: {
		            zoomType		: 'xy',
		            backgroundColor	: diagram.bgcolor,
		        },
				credits: false,
		        title: {
		            text: diagram.title,
					style: {
						fontWeight:"bold"
					}
		        },
		        subtitle: {
		            text: null
		        },
		        tooltip: {
		            headerFormat: '<b>{series.name}</b><br>',
		            pointFormat: '{point.x:%e. %b}: {point.y:.2f}'
		        },
		        exporting: {
		            sourceWidth: view.width(),
		            sourceHeight:view.height(),
		            scale: 1,
		            chartOptions: {
		                subtitle: null
		            }
		        },
        		plotOptions: {
                    series: {
                        marker: {
                            enabled: true,
        					symbol:"circle",
        					radius : 3,
                        }
                    },
                    spline: {
                        marker: {
                            enabled: true
                        }
                    },
        			column: {
                        stacking: 'normal'
                    }
                },
        		series: series,
                xAxis: {
                    type: 'datetime',
                    dateTimeLabelFormats: { // don't display the dummy year
                        month: '%e. %b',
                        year: '%b'
                    },
                    title: {
                        text: 'Occur date',
                        style: {
                            fontWeight:"bold"
                        }
                    }
                },
                yAxis: { // Primary yAxis
                    labels: {
                        format: '{value}',
                    },
                    plotLines	: diagram.plotLines,
                    title: {
                        text: diagram.ycaption,
                        style: {
                            fontWeight:"bold"
                        }
                    },
                    opposite: false,
        			endOnTick: false,
                }
        };
		view.highcharts(diagramOption);
	}
</script>
@stop