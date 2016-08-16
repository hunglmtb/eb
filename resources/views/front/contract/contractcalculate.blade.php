<?php
	$currentSubmenu ='/pd/contractcalculate';
	$tables = ['PdContractQtyFormula'	=>['name'=>'Load']];
	$isAction = true;
?>

@extends('core.pd')

@section('adaptData')
@parent
<script>
	$( document ).ready(function() {
	    console.log( "ready!" );
	    var onChangeFunction = function() {
	    	actions.doLoad(true);
	    };
	    
	    $( "#PdContract" ).change(onChangeFunction);
    	actions.doLoad(true);
	});

	actions.loadUrl = "/contractcalculate/load";
	actions.saveUrl = "/contractcalculate/save";
	actions.type = {
			idName		: function (tab){
							if(tab=='PdContractData') return ['CONTRACT_ID_INDEX', 'ATTRIBUTE_ID_INDEX'];
							return ['ID'];
						},
			keyField:'ID',
			saveKeyField : function (model){
					return 'ID';
				},
			};

	actions.getTableOption	= function(data){
		return {tableOption :	{
									emptyTable			: true,
								},
				invisible:[]};
		
	}
	actions.renderFirsColumn = actions.defaultRenderFirsColumn;

	actions.isDisableAddingButton	= function (tab,table) {
		return "Add year";
	};

	function addYear(){
				showWaiting();
				$("#floatMoreBox").dialog('close');

				params		= actions.loadParams(true);
	            postData  	= {PdContract : $('#PdContract').val(),
	            				year : $('#year_monitoring').val()};
				jQuery.extend(postData, params);
	            $.ajax({
					url: '/contractcalculate/addyear',
					type: "post",
					data: postData,
					success:function(data){
						hideWaiting();
						console.log ( "addyear  success  ");
						alert("addyear  success  "/* +JSON.stringify(data) */);
						actions.loadSuccess(data);
					},
					error: function(data) {
						hideWaiting();
						console.log ( "addyear error "+JSON.stringify(data));
						alert("addyear  error  ");
						
					}
				});

	}
	
	
	editBox.initExtraPostData = function (id,rowData){
		currentContractId = id;
	 		return 	{
		 		id			: id,
		 		templateId	: rowData.CONTRACT_TEMPLATE};
	 	}

	actions.getAddButtonHandler = function (otable,otab){
		if(otab=='PdContractQtyFormula'){
			return function (e){
					var dialogOptions = {
							height: 100,
							width: 400,
 							position:  {my: 'left+80 bottom-80',at: "left bottom"},
							modal: true,
							of: $('#toolbar_PdContractQtyFormula'),
							title: 'input',
						};
					$("#floatMoreBox").dialog(dialogOptions);
				};
		}
		else return getAddButtonHandler(otable,otab);
	};

	editBox['initSavingDetailData'] = function(editId,success) {
		params 		= actions.loadSaveParams(true);
		editedData 	= {};
		deleteData 	= {};
		$.each(editBox.fields, function( index, value ) {
			editedData[value] 	= actions.editedData[value];
			deleteData[value] 	= actions.deleteData[value];
   		 });

  		 return {
  	  		 		id			: editId,
  	  		 		editedData	: editedData,
  	  		 		deleteData	: deleteData,
  	  		 };
	};

	 actions['initDeleteObject']  = function (tab,id, rowData) {
		 if(tab=='PdContractData') return {'ID':id, CONTRACT_ID : rowData.CONTRACT_ID_INDEX};
			return {'ID':id};
	 };
	
</script>
@stop

@section('floatMoreBoxContent')
    <table id="table_PdContractYear" border='0' style='width:100%;height:50px;' cellpadding='5' cellspacing='0'>
        <tr class='row_activity' style='cursor:pointer' >
            <td>Year</td>
            <td>
                <input id="year_monitoring" class="" type="text" value="" name="year_monitoring" >
            </td>
            <td>
                <input style="width:100px;font-size:10pt;" type="button" onClick="addYear()" value="Save" />
            </td>
        </tr>
    </table>
@stop
