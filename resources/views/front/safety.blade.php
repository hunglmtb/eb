<?php
$currentSubmenu = 'safety';
$groups = [ 
		array (
				'name' => 'group.date',
				'data' => 'Date' 
		),
		array (
				'name' => 'group.product',
				'data' => 'data' 
		),
		array (
				'name' => 'group.frequency',
				'data' => 'frequency' 
		) 
];
?>
@extends('core.fo')

@section('funtionName')
SAFETY DATA CAPTURE
@stop

@section('content')

<script type="text/javascript">
$(function(){

	$('#buttonSave').click(function(){
		_safety.clickSubmit();
	});	

 	actions.loadUrl = '/fo/loadSafety';
 	actions.loadSuccess =  function(_data){
 	 	
 		if($.fn.dataTable.isDataTable( '#table_Safety' ))
			tableSafety.destroy();
		
		_safety.loadTitle(_data[0]);	    
		_safety.loadData(_data[0]);
		
		//$("#containerSafety").width((_data[0]['totalWidth'])+"px");
 	};
	  
});  

var _safety = {
		
	isChange : 1,
		
	isDataChange : 0,

	columnName : '',
	
	loadCbo : function (data, valueDefault, columnName, width){
		var cbo = '';	  
		cbo += ' <select style = "width:'+(width+30)+'px;" class="'+columnName+'" onchange="_safety.onchangeTextValue(this); _safety.setChange(this);">';
		cbo += ' <option value></option>';	
		for(var i = 0; i < data.length; i++){
			if(valueDefault === data[i].ID){
				cbo += ' <option value="'+data[i].ID+'" selected="selected">'+data[i].NAME+'</option>';	
			}else{
				cbo += ' <option value="'+data[i].ID+'">'+data[i].NAME+'</option>';
			}						
		}	
		cbo += ' </select> ';
		
		return cbo;
	},

	loadData : function (result){
		var safe = result.search;
		var size = result.thead;
	  	var severity = result.severity;
	  	var str = '';
	  	var $sclass = ''
		for(var i = 0; i < safe.length; i++){
			if(i % 2==0){
				bgColor="#f8f8f8";
			}else{ 
				bgColor="#FFFFFF";
			}
			var index = 0;
			var columnName = '';
			str += '<tr class="row-data" height="30" style="background-color:'+bgColor+'">';			
			for(var column in safe[i]){
				var width = size[index]['FDC_WIDTH'];

				if(column == "NAME"){
					str += '<td data-order="'+i+'" style="background:none;color:black;width: '+width+'px;"><div>' + _safety.checkValue(safe[i][column],'') + '</div></td>';
				}else{
					if(column == "SEVERITY_ID"){
						str += '<td data-order="'+i+'" style="background:none;color:black;width: '+width+'px;">'+_safety.loadCbo(severity, _safety.checkValue(safe[i][column], ''), column, width)+' </td>';
					}else{
						if(column == "XID"){
							str += ' <input type="hidden" value="'+safe[i][column]+'" class="'+column+'" />';
						}else{
							str += '<td style="background:none;color:black;width: '+width+'px;"><input style="width: 100%" onkeypress="_safety.onchangeTextValue(this);" onkeydown="_safety.setChange(this);" type="text" value="' + _safety.checkValue(safe[i][column],'') +'" class= "'+column+'"></td>';
						}
					}
				}
				index = index + 1;
				columnName += column+",";
			}	
			_safety.columnName = columnName.substring(0,columnName.length-1);
			str += ' <input type="hidden" value="0" class="changed" />';
			str += '</tr>';
		}
		
		$('#body_Safety').html(str);

		$(".COUNT").keydown(function(e){
			return inputNumber(e);
		});
		
		_safety.freezeSafety();
  	},

  	loadTitle : function(result){
  		var str = '';
  		var safe = result.thead;
		for(var i = 0; i < safe.length-1; i++ ){// ko lay ptu cuoi cung
			str += '<th style="font-size:9pt;text-align:left;white-space: nowrap;"><div style="width:'+(safe[i].FDC_WIDTH>1?safe[i].FDC_WIDTH : 100)+'px">'+ (safe[i].LABEL?safe[i].LABEL:safe[i].COLUMN_NAME) +'</div></th>';
		}
		
  		$('#title_thead').html(str);
  	},
	
	onchangeTextValue : function(textbox) {
						
		$(textbox).removeClass('error-input');
		$(textbox).addClass('ts-txt-changed');	
		_safety.isDataChange = 1;
	},
	
	setChange : function(element){
		var rowIndex = $(element).closest("tr").index();
		 $("#body_Safety tr.row-data:eq(" + rowIndex +") .changed").val(_safety.isChange);
	},

	clickSubmit : function(){
		
		var facility_id = $('#Facility').val(); 
		var created_date = $('#date_begin').val();
		var sData = [];	
		$('#body_Safety tr.row-data').each(function(){
			var rowIndex = $(this).index();
			var getIsChange = $("#body_Safety tr.row-data:eq(" + rowIndex +") .changed").val();
			
			if(getIsChange == _safety.isChange){
				var columnName = _safety.columnName.split(',');		
				var record = {};		
				for(var j = 1; j < columnName.length; j++){
					var key = columnName[j];
					var value = $("#body_Safety tr.row-data:eq(" + rowIndex +") ."+columnName[j]+"").val();
					record[key] = value;
					record['FACILITY_ID'] = facility_id;
					record['CREATED_DATE'] = created_date;
				}
				sData.push(record); 
			} 
		});

		var tk = $('#token').val();
				
		param = {
			_token : tk,
			_sData : sData
		}
		
	  	$.ajax({
	    	url: '/fo/saveSafety',
	    	type: "post",
	    	dataType: 'json',
	    	data: param,
	    	success: function(_data){
		    	if(_data === 1){
			    	alert('save successful');
	    			actions.doLoad();
		    	}
			}
		});
	}, 

	checkValue : function(sValue, valueDefault){
		var result = sValue;

		if(sValue === null || sValue === undefined){
			result = valueDefault;
		}
			
		return result;
	},

	freezeSafety : function()
	{

		if($.fn.dataTable.isDataTable( '#table_Safety' ))
			tableSafety.destroy();
		
		tableSafety = $('#table_Safety').DataTable({
			scrollY:        "275px",
			scrollX:        true,
			scrollCollapse: true,
			paging:         false,
			searching:		false,
			info:			false,
			columnDefs: [
			             { orderable: false, targets: -1 },
			             { orderable: false, targets: -2 },
			             { orderable: false, targets: -3 }
			          ]
		});
	}
}
</script>

<style>
.error-input {
	color: #DD0000 !important;
}

.ts-txt-changed {
	color: #16A086 !important;
}
</style>

<div style="width: 1010px; margin-top: 20px;">
		<div id="containerSafety" style="overflow-x:hidden">
		<table border="0" cellpadding="3" id="table_Safety" class="fixedtable nowrap display compact">
			<thead>
                <tr style="height:26" id="title_thead"></tr>
			</thead>
			<tbody id="body_Safety">
			</tbody>
		</table>
	</div>	
	
</div>
@stop
