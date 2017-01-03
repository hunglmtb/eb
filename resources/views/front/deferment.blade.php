<?php
	$currentSubmenu ='/dc/deferment';
	$tables = ['Deferment'	=>['name'=>'DEFERMENT'],'MisMeasurement'	=>['name'=>'MIS-MEASUREMENT']];
 	$active = 0;
	$isAction = true;
	$floatContents 	= ['editBoxContentview','woList','woListMmr'];
	
 ?>

@extends('core.pm')
@section('funtionName')
DEFERMENT DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/deferment/load";
	actions.saveUrl 		= "/deferment/save";
	actions.historyUrl 		= "/deferment/history";
	actions.type = {
					idName:['ID' ,'DEFERMENT_ID', 'MMR_ID'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
						},
					};
	  /*
	 var aLoadNeighbor = actions.loadNeighbor;
	 actions.loadNeighbor = function() {
	  var activeTabID = getActiveTabID();
	  if(activeTabID=='MisMeasurement'){
	   $('.IntObjectType').css('display','block');
	  }
	  else{
	   $('.IntObjectType').css('display','none');
	  }
	  aLoadNeighbor();
	 }
	  */

 actions.extraDataSetColumns = {'DEFER_TARGET':'DEFER_GROUP_TYPE','CODE2':'CODE1','CODE3':'CODE2','OBJECT_ID':'OBJECT_TYPE'};
	
	source['DEFER_GROUP_TYPE']	={	dependenceColumnName	:	['DEFER_TARGET'],
									url						: 	'/deferment/loadsrc'
								};
	source['CODE1']	={	dependenceColumnName	:	['CODE2','CODE3'],
						url						: 	'/deferment/loadsrc'
		};
	source['CODE2']	={	dependenceColumnName	:	['CODE3'],
						url						: 	'/deferment/loadsrc'
	};
	source['OBJECT_TYPE']	={	dependenceColumnName	:	['OBJECT_ID'],
						url						: 	'/deferment/loadsrc'
	};

	actions.isDisableAddingButton	= function (tab,table) {
		return tab!="Deferment"&&tab!="MisMeasurement"&&tab!="WorkOrder"&&tab!="WorkOrderMmr";
	};	

	actions.enableUpdateView = function(tab,postData){
		return tab=="Deferment";
	};
	
	source.initRequest = function(tab,columnName,newValue,collection){
		postData = actions.loadedData[tab];
		srcData = {	name : columnName,
					value : newValue,
					Facility : postData['Facility'],
 					target: source[columnName].dependenceColumnName,
// 					srcType : srcType,
				};
		return srcData;
	}

	actions.getTableOption	= function(data,tab){
		if(tab=="Deferment") return {};
		var height = tab=="WorkOrder"?"220px":"250px";
		return  {
					tableOption :	{
							searching	: false,
							autoWidth	: false,
							scrollX		: true,
							bInfo 		: false,
							scrollY		: height,
						}
				};
										
	}
	
	var renderFirsColumn = actions.renderFirsColumn;
	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var html = renderFirsColumn(data, type, rowData );
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
			
			html += '<a onclick="actions.editWO('+id+',this)" class="actionLink">Edit WO</a>';
			if(rowData.DEFER_GROUP_TYPE!='3'&&rowData.DEFER_GROUP_TYPE!=3) 
				html += '<a class="actionLink" onclick="actions.editDetailDeferment('+id+','+rowData.DEFER_GROUP_TYPE+')">Detail</a>';
		}
		return html;
	};
	actions.renderFirsColumnMmr  = function ( data, type, rowData ) {
		var html = renderFirsColumn(data, type, rowData );
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
			html += '<a onclick="actions.editWOMmr('+id+',this)" class="actionLink">Edit WO</a>';
			//if(rowData.DEFER_GROUP_TYPE!='3'&&rowData.DEFER_GROUP_TYPE!=3) 
			//	html += '<a class="actionLink" onclick="actions.editDetailMmr('+id+')">Detail</a>';
		}
		return html;
	};

	actions.getRenderFirsColumnFn =  function (tab) {
		if(tab=="WorkOrder" || tab=="WorkOrderMmr") return renderFirsColumn;
		if(tab=="MisMeasurement") return actions.renderFirsColumnMmr;
		return (tab=="Deferment")?actions.renderFirsColumn:actions.renderFirsEditColumn;
	},

	actions.editDetailDeferment = function(id,deferGroupType){
	    editBox.editRow(id,{},editBox.loadUrl);
	}
	actions.editDetailMmr = function(id){
	    editBox.editRow(id,{},editBox.loadUrl);
	}
	actions.editWO = function(id,woa){
		var table = $('#table_Deferment').DataTable();
	    var rowData = table.row('#'+id).data();
	    editBox.editRow(id,rowData,editBox.woLoadUrl,"woList");
	}
	actions.editWOMmr = function(id,woa){
		var table = $('#table_MisMeasurement').DataTable();
	    var rowData = table.row('#'+id).data();
	    editBox.editRow(id,rowData,editBox.woMmrLoadUrl,"woListMmr");
	}

	var defermentBundle;
	editBox.initExtraPostData = function (id,rowData,url){
		var tab = "DefermentDetail";
		if(url == editBox.woLoadUrl)
			tab = "WorkOrder";
		else if(url == editBox.woMmrLoadUrl)
			tab = "WorkOrderMmr";
		defermentBundle = {
				id									: id,
				{{config("constants.tabTable")}}	: tab,
				DEFERMENT_ID						: rowData.DEFERMENT_ID,
				MMR_ID						: rowData.MMR_ID,
		};
		return defermentBundle;
 	}
 	
	actions['doMoreAddingRow'] = function(addingRow){
		if(typeof defermentBundle!="undefined" ) {
			if(defermentBundle["tabTable"] == "WorkOrderMmr")
				addingRow['MMR_ID'] 	= defermentBundle.MMR_ID;
			else
				addingRow['DEFERMENT_ID'] 	= defermentBundle.DEFERMENT_ID;
		}
		return addingRow;
	}

</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.fields 			= ['DefermentDetail','WorkOrder','WorkOrderMmr'];
	editBox.loadUrl 		= "/deferment/detail/load";
	editBox.saveUrl 		= '/deferment/detail/save';
	editBox.woLoadUrl 		= "/deferment/wo/load";
	editBox.woSaveUrl 		= '/deferment/wo/save';
	editBox.woMmrLoadUrl 		= "/deferment/wommr/load";
	editBox.woMmrSaveUrl 		= '/deferment/wommr/save';
	editBox.enableRefresh 	= false;

	editBox.size = {
		  height  : 370,
		  width  : 950,
		 };

		 editBox.getSaveDetailUrl = function (url,editId,viewId){
		if(url==editBox.woLoadUrl) 	return editBox.woSaveUrl;
		if(url==editBox.woMmrLoadUrl) 	return editBox.woMmrSaveUrl;
		if(url==actions.loadUrl) 	return editBox.saveUrl;
		return editBox.saveUrl;
 	}
 	
	editBox.editGroupSuccess = function(data,id,url){
		actions.loadSuccess(data);
	}

	editBox['saveFloatDialogSucess'] = function(data,saveUrl){
		if(saveUrl==editBox.saveUrl) {
			data.postData.tabTable = "DefermentDetail";
			actions.loadSuccess(data);
			editBox.enableRefresh = true;
		}
		else if(saveUrl==editBox.woSaveUrl || saveUrl==editBox.woMmrSaveUrl) actions.saveSuccess(data);
		close = false;
		return close;
	}

	editBox['initSavingDetailData'] = function(editId,saveUrl) {
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
</script>
@stop


@section('editBoxContentview')
@parent
<div id="table_DefermentDetail_containerdiv" class="secondaryTable" style='height:100%;width: 100%;overflow:auto'>
	<table id="table_DefermentDetail" class="fixedtable nowrap display"></table>
</div>
@stop

@section('woList')
@parent
<div id="table_WorkOrder_containerdiv" class="secondaryTable" style='height:100%;width: 100%;overflow:auto'>
	<table id="table_WorkOrder" class="fixedtable nowrap display"></table>
</div>
@stop

@section('woListMmr')
@parent
<div id="table_WorkOrderMmr_containerdiv" class="secondaryTable" style='height:100%;width: 100%;overflow:auto'>
	<table id="table_WorkOrderMmr" class="fixedtable nowrap display"></table>
</div>
@stop
