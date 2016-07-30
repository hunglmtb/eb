<?php
	$currentSubmenu ='/dc/quality';
	$tables = ['QltyData'	=>['name'=>'QUALITY DATA']];
 	$active = 0;
?>

@extends('core.action')
@section('funtionName')
QUALITY DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/quality/load";
	actions.saveUrl			= "/quality/save";
	actions.historyUrl 		= "/quality/history";
	actions.type = {
					idName:['ID'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
						return 'ID';
						},
					};
	actions.extraDataSetColumns = {'SRC_ID':'SRC_TYPE'};

	source['SRC_TYPE']={dependenceColumnName	:	['SRC_ID'],
						url						: 	'/quality/loadsrc'
						};
	
	source.initRequest = function(tab,columnName,newValue,collection){
		postData = actions.loadedData[tab];
		var srcType = null;
		var result = $.grep(collection, function(e){ 
          	 return e['ID'] == newValue;
           });
		if (result.length > 0) {
			srcType = result[0]['CODE'];
		}
		else return null;

		srcData = {name : columnName,
					value : newValue,
					srcType : srcType,
					Facility : postData['Facility']};
		return srcData;
	}

	addingOptions.keepColumns = ['SAMPLE_DATE','TEST_DATE','EFFECTIVE_DATE','PRODUCT_TYPE','SRC_ID','SRC_TYPE'];

	var renderFirsColumn = actions.renderFirsColumn;
	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var html = renderFirsColumn(data, type, rowData );
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
			html += '<a id="edit_row_'+id+'" class="actionLink">Edit</a>';
		}
		return html;
	};

</script>
@stop


@section('editBoxParams')
@parent
<script>
	editBox.fields = {	gas		:	'gas',
						oil		:	'oil'
					};
	
	editBox.loadUrl = "/quality/edit";
	editBox.saveUrl = '/quality/edit/saving';
	editBox.editGroupSuccess = function(data,id){
	//	    				$('#tableEditGroup').html(JSON.stringify(data));
	    			tab = 'gas';
	    				options = {
	    		 					tableOption :	{
	    	 		 									searching: false,
	    	 		 									autoWidth: true,
	    	 		 									bInfo 		: false,
	    	 		 									scrollY		:	"320px",
	    	 		 									footerCallback : function ( row, data3, start, end, display ) {
					    									            var api = this.api();
					    									            columns = [1,2,3];
					    									            renderSumRow(api,columns);
	    									        	}
	    	 		 								}
	    					};
	    			subData = data['MOLE_FACTION'];
	    			renderTable(tab,subData,options);

	    			tab = 'oil';
	    			options = {
	    					tableOption :{	searching	: false,
	    									autoWidth	: true,
	    									bInfo 		: false,
	    	 								scrollY		:	"250px",
	    									footerCallback : function ( row, data3, start, end, display ) {
	    														var api = this.api();
	    											            columns = [1];
	    											            $.each(columns, function( i, column ) {
	    											                total = 0;
	    											                $.each(api.columns(column).data()[0], function( index, value ) {
	    											                	total += intVal(value);
	    											        		});
	    											                // Update footer
	    											                $( api.columns(column).footer() ).html(total.toFixed(3));
	    											        	});
	    										       		}
	    	        					}
	    			};
	    			subData = data['NONE_MOLE_FACTION'];
	    			renderTable(tab,subData,options);
	}
	</script>
	
@stop

@section('editBoxContentview')
@parent
<table border='0' cellpadding='0' style='width:100%;height:100%'>
			<caption style='background:gray;color:white;height:20px;font-size:10.5pt' id = 'cationEditGroup'></caption>
			<tr>
				<td valign='top'>
					<div id="table_oil_containerdiv" class="secondaryTable" style='height:400px;overflow:auto'>
						<table id="table_oil" class="fixedtable nowrap display">
							<tfoot>
								<tr>
									<td style="text-align:left">Sum:</td>
									<td style="text-align:left"></td>
									<td style="text-align:left" colspan="1"></td>
								</tr>
							</tfoot>
						</table>
					</div>
				</td>
				<td valign='top' width="10">
					<div class="paddingOfTable" style='width:10px;overflow:auto'>
					</div>
				</td>
				<td valign='top'>
					<div id="table_gas_containerdiv" class="secondaryTable" style='height:400px;overflow:auto'>
						<table id="table_gas" class="fixedtable nowrap display">
						<tfoot>
							<tr>
								<td style="text-align:left">Sum:</td>
								<td style="text-align:left"></td>
								<td style="text-align:left"></td>
								<td style="text-align:left" colspan="3"></td>
							</tr>
						</tfoot>
					</table>
					</div>
				</td>
			</tr>
		</table>
@stop
