<?php
	$currentSubmenu ='/pd/liftaccdailybalance';
	$tables = ['PdCargo'	=>['name'=>'Data']];
	$isAction = false;
?>

@extends('core.pd')
@section('funtionName')
LIFTING ACCT DAILY BALANCE
@stop

@section('adaptData')
@parent
<script>
	$( document ).ready(function() {
	    console.log( "ready!" );
	    var onChangeFunction = function() {
		    if($('#PdLiftingAccount option').size()>0 ) actions.doLoad(true);
	    };
	    
	    $( "#PdLiftingAccount" ).change(onChangeFunction);
// 		actions.doLoad(true);
	});

	actions.loadUrl = "/liftaccdailybalance/load";
// 	actions.saveUrl = "/liftaccdailybalance/save";
	actions.type = {
			idName:['ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};

// 	actions.renderFirsColumn = null;
	actions.getTableOption	= function(data){
		var monthlyData		= data.monthlyData;
		return {tableOption :{
								searching			: true,
								sort				: false,
								disableLeftFixer	: true,
								drawCallback: function ( settings ) {
						            var api = this.api();
						            var rowDatas	= api.data();
						            var rows = api.rows( {page:'current'} ).nodes();
						            var last=null;
						 
//						             groups = api.data();
						            groups = api.column(2, {page:'current'}).data();
						            
						            groups.each( function ( group, i ) {
						            	var xdate 	= moment.utc(group,configuration.time.DATETIME_FORMAT_UTC);
						                if ( !xdate.isSame( last, 'month')) {
								            var year 		= xdate.year();
								            var month 		= xdate.month();
							                var dateString 	= moment.monthsShort()[month]+" "+year;
							                var monthKey	= xdate.format('YYYY-MM');
		    								var total 		= "";
							                if(typeof monthlyData == "object"){
							                	var result = $.grep(monthlyData, function(e){ 
												               	 return e.MONTH_KEY == monthKey;
												             });
			    								if (result.length >0) {
			    									total 		= 0;
			    									$.each(result, function( index, accountData ) {
			    										$(rows).eq( i ).before(
			    						                        '<tr class="group"><td colspan="7"><b>'+(index==0?dateString:"")+
			    						                        '</b></td><td class="cellnumber"> '+accountData.ADJUST_NAME+
			    						                        '</td><td class="cellnumber">'+accountData.BAL_VOL+'</td></tr>'
			    						                );
			    										var volume	= parseFloat(accountData.BAL_VOL);
			    										volume	= isNaN(volume)?0:volume;
			    										total	+= volume;
		    							            });
				    								if (result.length >1) {
				    									$(rows).eq( i ).before(
			    						                        '<tr class="group"><td colspan="8">'+
			    						                        '</td><td class="cellnumber" style="background-color: #8edee2;"><b>'+total+'</b></td></tr>'
			    						                );
				    								}
								                    last = xdate;
								                    return;
			    								}
							                }

							                if(i>0) total = rowDatas[i-1].cal_qty;
							                
						                    $(rows).eq( i ).before(
						                        '<tr class="group"><td colspan="8"><b>'+dateString+
						                        '</td><td class="cellnumber" style="background-color: #8edee2;"><b>'+total+'</b></td></tr>'
						                    );
						                    last = xdate;
						                }
						            } );

							        var table = $('#table_PdCargo').DataTable();
							        actions.addClass2Header(table);
						        },
							},
				invisible:[]};
		
	}
	
</script>
@stop

