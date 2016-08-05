<?php
	$currentSubmenu ='/pd/contractdata';
	$tables = ['PdContract'	=>['name'=>'Load']];
	$isAction = true;
?>

@extends('core.pd')

@section('adaptData')
@parent
<script>
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
		var html = '<a id="edit_row_'+id+'" class="actionLink">Select</a>';
		return html;
	};

	drawCallback = function ( settings ) { 
        var table = $('#table_PdContract').DataTable();
        $('#table_PdContract tbody').on( 'click', 'tr', function () {
            if ( $(this).hasClass('selected') ) {
//                 $(this).removeClass('selected');
            }
            else {
                table.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
                /* var r = table.fnGetPosition(td)[0];
    		    var rowData = table.api().data()[ r];
    		    editBox.editRow(id,rowData); */
            }
        } );
    };

	actions.getTableOption	= function(data){
		return {tableOption :	{
									scrollY			: null,
//  									drawCallback 	: drawCallback,
								}
		};
		
	}

	editBox.initExtraPostData = function (id,rowData){
	 		return 	{
		 		id			: id,
		 		templateId	: rowData.CONTRACT_TEMPLATE};
	 	}

</script>
@stop



@section('editBoxParams')
@parent
<script>
	editBox.fields = ['PdContractDetail'];
	editBox.loadUrl = "/contractdetail/load";
	editBox.saveUrl = '/contractdetail/save';
	editBox.enableRefresh = true;
	
	editBox.editGroupSuccess = function(data,id){
		tab = 'PdContractDetail';
			options = {
	 					tableOption :	{
			 									searching	: false,
			 									autoWidth	: false,
			 									scrollX		: true,
			 									bInfo 		: false,
			 									scrollY		: "250px",
			 								}
				};
		subData = data[tab];
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
					<div id="table_PdContractDetail_containerdiv" class="secondaryTable" style='height:400px;width: 950px;overflow:auto'>
						<table id="table_PdContractDetail" class="fixedtable nowrap display"></table>
					</div>
				</td>
			</tr>
		</table>
@stop

@section('extraContent')
@parent

<div id="tabs-extra">
	<ul  id="containerExtraContentHeader">
			<li id="PdContractDetail"><a href="#tabs-PdContractDetail"><font size="2">Contract details</font></a></li>
	</ul>
	<div id="tabs_extra_contents">
		<div id="tabs-PdContractDetail">
			<div id="container_PdContractDetail" style="overflow-x:hidden">
				<table border="0" cellpadding="3" id="table_PdContractDetail" class="fixedtable nowrap display">
				</table>
			</div>
		</div>
	</div>
</div>
@section('script')
	@parent
		<script>
				$(document).ready(function () {
					$("#tabs-extra").tabs({
						active:0,
						activate: function(event, ui) {
// 					        actions.loadNeighbor(event, ui);
					    }
					});
				});
		</script>
	@stop
@stop
