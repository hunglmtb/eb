<?php
	$currentSubmenu ='/pd/contractdata';
	$tables = ['PdContract'	=>['name'=>'Load']];
	$isAction = true;
?>

@extends('core.pd')

@section('adaptData')
@parent
<script>
	var contractAttributes = <?php echo json_encode($contractAttributes); ?>

	actions.loadUrl = "/contractdata/load";
	actions.saveUrl = "/contractdata/save";
	actions.type = {
			idName:['ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};

	actions.renderFirsColumn = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		var html = '';
		if(isAdding)
			html += '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
		else 
			html += '<a id="edit_row_'+id+'" class="actionLink">&nbsp;Select</a>';
		return html;
	};
	
	addingOptions.keepColumns = ['BEGIN_DATE','END_DATE','CONTRACT_TEMPLATE','CONTRACT_TYPE','CONTRACT_PERIOD','CONTRACT_EXPENDITURE'];

	editBox.initExtraPostData = function (id,rowData){
	 		return 	{
		 		id			: id,
		 		templateId	: rowData.CONTRACT_TEMPLATE};
	 	}

	actions.renderFirsEditColumn = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		var html = '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
		return html;
	};

	getAddButtonHandler = actions.getAddButtonHandler;
	actions.getAddButtonHandler = function (otable,otab){
		if(otab=='PdContractData'){
			return function (e){
					var dialogOptions = {
							height: 450,
							width: 400,
							position: { my: 'top', at: 'top+100' },
							modal: true,
							title: 'Attributes',
						};
					$("#floatMoreBox").dialog(dialogOptions);

					tab = 'PdCodeContractAttribute';
					options = {
			 					tableOption :	{
								 						searching			: true,
					 									autoWidth			: false,
					 									scrollX				: true,
					 									bInfo 				: false,
					 									scrollY				: "320px",
					 									renderFirsColumn 	: null,
					 									drawCallback	: function ( settings ) { 
					 								        var table = $('#table_'+tab).DataTable();
					 								        $('#table_'+tab+' tbody').on( 'click', 'tr', function () {
					 								            if ( $(this).hasClass('selected') ) {
						 							               	$('#table_'+tab+' tbody').off( 'click', 'tr');
					 								   				doMore = function(addingRow){
					 								   				 	selectRow = table.row('.selected').data();
					 								   					addingRow['ATTRIBUTE_ID'] 	= selectRow.CODE;
					 								   					addingRow['CONTRACT_ID'] = selectRow.NAME;
					 								   					return addingRow;
					 								   				}
					 								   				getAddButtonHandler(otable,otab,doMore)();
						 							                table.$('tr.selected').removeClass('selected');
					 								   				$('#floatMoreBox').dialog('close');
					 								            }
					 								            else {
						 								            table.$('tr.selected').removeClass('selected');
					 								                $(this).addClass('selected');
					 								            }
					 								        } );
					 								    }
					 							}
						};
					tableData = otable.data();
					var attributeData = $.grep(contractAttributes,function(el,i) {
							filters = $.grep(tableData,function(element,index) {
							  	return element.ATTRIBUTE_ID==el.CODE;
							});
						  	return filters.length<=0
						});
//            	    	attributeData = contractAttributes;
           	    	
					subData = {	dataSet			: attributeData,
								properties		: [{title:'CODE',data:'CODE',width:80},
					           	    				{title:'NAME',data:'NAME',width:205}]
					          };
					etbl = renderTable(tab,subData,options);
				};
		}
		else return getAddButtonHandler(otable,otab);
	};

</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.fields = ['PdContractData'];
	editBox.loadUrl = "/contractdetail/load";
	editBox.saveUrl = '/contractdetail/save';
	editBox.enableRefresh = true;

	editBox.editGroupSuccess = function(data,id){
		tab = 'PdContractData';
			options = {
	 					tableOption :	{
			 									autoWidth	: false,
 			 									scrollX		: false,
			 									scrollY		: "200px",
			 							}
				};
		subData = data[tab];
		etbl = renderTable(tab,subData,options,actions.createdFirstCellColumn);
		if(etbl!=null) actions.afterDataTable(etbl,tab);
	}

	editBox['saveFloatDialogSucess'] = function(data,id){
		close = false;
		return close;
	}

</script>
@stop

@section('editBoxContentview')
@parent
	<table border='0' cellpadding='0' style='width:100%;height:100%'>
		<tr>
			<td valign='top'>
				<div id="table_PdContractData_containerdiv" style='height:100%;overflow:auto'>
					<table id="table_PdContractData" class="fixedtable nowrap display"></table>
				</div>
			</td>
		</tr>
	</table>
@stop


@section('floatMoreBoxContent')
	<table id="table_PdCodeContractAttribute" class="fixedtable nowrap display"></table>
@stop