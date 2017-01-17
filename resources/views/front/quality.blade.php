<?php
	$currentSubmenu ='/dc/quality';
	$tables = ['QltyData'	=>['name'=>'QUALITY DATA']];
 	$active = 0;
	$isAction = true;
 ?>

@extends('core.pm')
@section('funtionName')
QUALITY DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/quality/load";
	actions.saveUrl			= "/quality/save";
	actions.historyUrl 		= "/quality/history";

	actions.validating = function (reLoadParams){
		return true;
	}
	
	actions.type = {
					idName:['ID'/* ,'PRODUCT_TYPE' */],
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

	actions.generateTableFoot  = function ( tab,properties ) {
		$("#table_"+tab+"_containerdiv").html("<table id='table_"+tab+"' class='fixedtable nowrap display'>"); 
		
// 		document.getElementById("table_"+tab).deleteTFoot();
		if(typeof properties == 'object'){
			var tfoot = $('<tfoot></tfoot>'); 
			var foot = $('<tr></tr>'); 
			foot.appendTo(tfoot); 
			for (var i = 0; i < properties.length; i++) {
				if(i==0) footColumn	= $('<td style="text-align:left">Sum:</td>');
				else footColumn	= $('<td style="text-align:left"></td>');
			    foot.append(footColumn);
			}
			tfoot.appendTo("#table_"+tab); 
		}
	};
	
	editBox.editGroupSuccess = function(data,id){
	//	    				$('#tableEditGroup').html(JSON.stringify(data));
					scrollY = "200px";
	    			tab = 'oil';
	    			options = {
	    					tableOption :{	searching	: false,
	    									autoWidth	: true,
	    									bInfo 		: false,
	    	 								scrollY		: scrollY,
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
	    			$("#table_"+tab+"_containerdiv").html("<table id='table_"+tab+"' class='fixedtable nowrap display'>"); 
	    			
	    			if(typeof subData == "object"){
		    			actions.generateTableFoot(tab,subData.properties);
		    			renderTable(tab,subData,options);
	    			}

	    			tab = 'gas';
    				options = {
    		 					tableOption :	{
    	 		 									searching	: false,
    	 		 									autoWidth	: true,
    	 		 									bInfo 		: false,
    	 		 									scrollY		: scrollY,
    	 		 									footerCallback : function ( row, data3, start, end, display ) {
				    									            var api = this.api();
				    									            columns = [1,2];
				    									            editBox.renderSumRow(api,columns);
    									        	}
    	 		 								}
    					};
	    			subData = data['MOLE_FACTION'];
	    			$("#table_"+tab+"_containerdiv").html("<table id='table_"+tab+"' class='fixedtable nowrap display'>"); 
    				if(typeof subData == "object"){
		    			actions.generateTableFoot(tab,subData.properties);
		    			renderTable(tab,subData,options);
	    			}
    			
	}
	</script>
	
@stop

@section('editBoxContentview')
@parent
<table border='0' cellpadding='0' style='width:100%;height:100%'>
			<tr>
				<td valign='top'>
					<div id="table_oil_containerdiv" class="secondaryTable" style='height:100%;overflow:auto'>
					</div>
				</td>
				<td valign='top' width="10">
					<div class="paddingOfTable" style='width:10px;overflow:auto'>
					</div>
				</td>
				<td valign='top'>
					<div id="table_gas_containerdiv" class="secondaryTable" style='height:100%;overflow:auto'>
					</div>
				</td>
			</tr>
		</table>
@stop
