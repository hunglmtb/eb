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
	    	actions.doLoad(true);
	    };
	    
	    $( "#PdLiftingAccount" ).change(onChangeFunction);
		actions.doLoad(true);
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
		return {tableOption :{
								searching			: true,
								sort				: false,
								disableLeftFixer	: true,
								drawCallback: function ( settings ) {
						            var api = this.api();
						            var rows = api.rows( {page:'current'} ).nodes();
						            var last=null;
						 
//						             groups = api.data();
						            groups = api.column(2, {page:'current'}).data();
						            
						            groups.each( function ( group, i ) {
						            	var xdate 	= moment.utc(group,configuration.time.DATETIME_FORMAT_UTC);
							            var month 	= xdate.month();
						                if ( last !== month ) {
								            var year 	= xdate.year();
							                var dateString = moment.monthsShort()[month]+" "+year;
						                    $(rows).eq( i ).before(
						                        '<tr class="group"><td colspan="8"><b>'+dateString+'</b></td></tr>'
						                    );
						                    last = month;
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

