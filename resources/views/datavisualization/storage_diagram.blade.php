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
				value.data[index]	= {
										x				: day,
										y				: pvalue,
										extraTooltip	: data.E
									};
	         });
	         if(typeof value.extraTooltip == "object" && value.extraTooltip.length>0)
		         value.extraTooltip = value.extraTooltip.join("<br>");
	         else 
		         value.extraTooltip = "";
         });

// 		var minRange	= 0;
		$.each(diagram.plotLines, function( index, value ) {
			pvalue = parseFloat(value.value);
			pvalue	= isNaN(pvalue)?null:pvalue;
			value.value	= pvalue;
			value.width		= 2;
// 			if(minRange<pvalue) minRange = pvalue;
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
		            formatter: function () {
		            	var extraTooltip = (this.point.extraTooltip!="-1"&&this.point.extraTooltip!=-1)?this.point.extraTooltip:this.series.userOptions.extraTooltip;
		            	extraTooltip = typeof extraTooltip == "undefined"?"":extraTooltip;
		            	var point = "<br>"+Highcharts.dateFormat('%b %e', this.x) +': '+this.y;
		                return '<b>' + this.series.name + '</b><br>' + extraTooltip+point;
		                
		            },
	        	}, 
	        
		       /* tooltip: {
		            headerFormat: function () {
	// 		            '<b>{series.name}</b><br>{series.userOptions.extraTooltip}<br>';
						var extraTooltip = (point.extraTooltip!="-1"&&point.extraTooltip!=-1)?this.series.userOptions.extraTooltip:point.extraTooltip;
		                return '<b>' + this.series.name + '</b>' + extraTooltip;
		            },
// 	            '<b>{series.name}</b><br>{point.extraTooltip}<br>',
		            pointFormat: '{point.x:%e. %b}: {point.y:.2f}'
		        }, */
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
//                 	minRange	: minRange,
                	max			: diagram.maxY,
                    min			: diagram.minY,
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