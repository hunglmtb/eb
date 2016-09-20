<?php
	$currentSubmenu ='/pd/cargodocuments';
	$tables 		= ['PdCargoDocument'	=>['name'=>'Load']];
	$detailTableTab ='PdDocumentSetData';
	$isLoad 		= 1;
	$mdl			= "App\Models\PdDocumentSetContactData";
	$secondaryField = $mdl::getTableName();
?>

@extends('core.cargoaction')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargodocuments/load";
	actions.saveUrl = "/cargodocuments/save";
	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="PdCargoDocument";
	};

	editBox.initExtraPostData = function (id,rowData){
 		return 	{
 					id				: id,
 					voyageId		: rowData.VOYAGE_ID,
		 			cargoId			: rowData.CARGO_ID,
		 			parcelNo		: rowData.MASTER_NAME,
		 			lifftingAcount	: rowData.LIFTING_ACCOUNT,
	 		};
 	};

	editBox['filterField'] = 'CODE';
	
	editBox['addAttribute'] = function(addingRow,selectRow){
		addingRow['CODE'] 		= selectRow.CODE;
		addingRow['NAME'] 		= selectRow.NAME;
// 		addingRow['PARENT_ID'] 			= parentId;
// 		addingRow['IS_LOAD'] 			= {{$isLoad}};
		return addingRow;
	};

	var dataSet;
	contractOnchage = function(select,contractId,oindex){
		if(select.value==0){
			var tab = "PdDocumentSetContactData";
			var secondaryField = '{{$secondaryField}}';
   			var recordData = actions.deleteData;
	   		if (!(tab in recordData)) {
	    		recordData[tab] = [];
	    	}
	    	//remove in postdata
        	var eData = recordData[tab];
        	$.each(dataSet, function( dIndex, dEntry ) {
				$.each(dEntry[secondaryField], function( index, value ) {
					if(contractId==value.CONTACT_ID&&oindex==index){
		        		var deleteObject = actions.initDeleteObject(tab,value.ID,false);
				    	eData.push(deleteObject);
					}
			   	});
		   	});
		   	
		}
		else{
		}
	};
	
	editBox.editGroupSuccess = function(data,id){
		tab 	= '{{$detailTableTab}}';
		options = editBox.getEditTableOption(tab);
		subData = data[tab];
		$("#table_"+tab+"_containerdiv").html("<table id=\"table_"+tab+"\" class=\"fixedtable nowrap display\">");
		dataSet 	= subData.dataSet;
		if(dataSet.length>0){
			var set = dataSet[0];
			var secondaryField = '{{$secondaryField}}';
			var set2 = set[secondaryField];
			if(set2.length>0){
				var properties 	= subData.properties;
				var suoms		= [];
				subData['uoms'] = suoms;
				var selects 	= subData.selects['BaAddress'];
				$.each(set2, function( index, entry ) {
					var contractId = entry.CONTACT_ID;
					var sel = $('<select onchange="contractOnchage(this,'+contractId+','+index+');">');
					sel.addClass("withAuto");
					sel.append($("<option>").attr('value',0).text(''));
					$.each(selects, function( si, se ) {
						var sOption = $("<option>").attr('value',se.ID).text(se.NAME);
						if(contractId==se.ID){
							sOption.attr('selected', true);
						}
						sel.append(sOption);
					});
  					sel.val(contractId);
					var th 					= $('<div>').append(sel);
					var title 				= th.html();
					var originColumn 		= "ORIGIN_"+index;
					var numberCopyColumn 	= "NUMBERCOPY_"+index;
					var column 				= {	'data' 			: 	originColumn,
												'title' 		:  	title,
												'width'			: 	90,
												'INPUT_TYPE'	: 	1,
												'DATA_METHOD'	: 	1,
			 									'FIELD_ORDER'	: 	index*2+3
											};
					properties.push(column);
					properties.push({	'data' 			: 	numberCopyColumn,
										'title' 		:  	" ",
										'width'			: 	30,
										'INPUT_TYPE'	: 	2,
										'DATA_METHOD'	: 	1,
	 									'FIELD_ORDER'	: 	index*2+4
					});
					var sData 				= subData.suoms[0];
					suoms.push({	COLUMN_NAME : originColumn,
										data 	: sData,
										id 		: originColumn,
										targets	: index*2+2
									});

					sData 					= subData.suoms[1];
					suoms.push({	COLUMN_NAME : numberCopyColumn,
										data 	: sData,
										id 		: numberCopyColumn,
										targets	: index*2+3
									});
	            });

	            
				$.each(dataSet, function( dIndex, dEntry ) {
					$.each(dEntry[secondaryField], function( index, value ) {
						var originColumn 			= "ORIGIN_"+index;
						var numberCopyColumn 		= "NUMBERCOPY_"+index;
						dEntry[originColumn] 		= value.ORGINAL_ID;
						dEntry[numberCopyColumn] 	= value.NUMBER_COPY;
				   	});
			   	});
			}
		}
		etbl = renderTable(tab,subData,options,actions.createdFirstCellColumn);
		if(etbl!=null) actions.afterDataTable(etbl,tab);
	}
</script>
@stop


@section('editBoxParams')
@parent
<script>
 	editBox.loadUrl 		= "/documentset/load";
 	editBox.saveUrl 		= "/documentset/save";
	editBox.activitiesUrl 	= '/documentset/activities';
	editBox.fields 			= ['{{$detailTableTab}}','PdDocumentSetContactData'];

 	editBox['getEditTableOption'] = function(tab){
		return {
		 			tableOption :	{
							autoWidth	: false,
							ordering	: tab!='{{$detailTableTab}}',
							scrollX		: true,
							scrollY		: "200px",
					}
				};
	};
</script>
@stop
