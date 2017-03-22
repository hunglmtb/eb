<?php
	$code_data_method 	= App\Models\CodeDataMethod::where(['ACTIVE'=>1])->orderBy('ORDER')->get(['ID', 'NAME']);
	$cfg_input_type 	= App\Models\CfgInputType::all('ID', 'NAME');
?>
<script type="text/javascript">

var _fieldconfig = {

		data_source_change : function(){
			param = {
				'table' : $("#data_source").val()	
			}
			
			sendAjaxNotMessage('/getColumn', param, function(data){
				_fieldconfig.listField(data);
			});
		},

		listField : function(data){
			var getFields = data.getFields;
			var getFieldsEffected = data.getFieldsEffected;

			$("#data_field").html('');
			for(var i = 0; i < getFields.length; i++){
				var str = '<option value="'+getFields[i]['COLUMN_NAME']+'"><b>'+getFields[i]['COLUMN_NAME']+'</b></option>';
				$("#data_field").append(str);
			}

			$("#data_field_effected").html('');
			for(var i = 0; i < getFieldsEffected.length; i++){
				var str = '<option value="'+getFieldsEffected[i]['COLUMN_NAME']+'"><b>'+getFieldsEffected[i]['COLUMN_NAME']+'</b></option>';
				$("#data_field_effected").append(str);
			}
		},
		
		change_field : function(){
			if($('#change_field').text()=='OK')
			{
				$(this).text('Change field');
				$("#add, #remove, #up, #down").css("visibility", "hidden");
				$("#data_field").attr("disabled", true);
				//$("#data_field_effected").attr("multiple", false);
				$("#save, #reset").css("visibility", "visible");
				
				//Get all value effected field
				var fields=document.getElementById('data_field_effected');
				var fields_str;
				for(var i=0; i<fields.length; i++)
				{
					if(fields_str)
						fields_str+=',' + fields.options[i].value;
					else
						fields_str=fields.options[i].value;
				}
				//Submit field
				
				param = {
					'object' : "field",
					'table' : $("#data_source").val(),
					'data' : fields_str
				}
				
				sendAjaxNotMessage('/saveconfig', param, function(data){
					if(data!="ok")
					   alert("Err: "+data);
					else
					{
						$("#tbl_detail").show("slide",250);
						$("#data_field, #set, #unset").prop("disabled", true);
						$("#done").text('Edit');
						
						$("#data_field_effected").click(function(){
							//data_field_effected_click();
						});
						
						$("#data_field_effected").change(function(){
							//data_field_effected_click();
						});
						$("#save, #reset").button();
					}
				});
				$('#change_field').text('Change field');
				
			}else{
				$('#change_field').text('OK');
				$("#add, #remove, #up, #down").css("visibility", "visible");
				$("#data_field").attr("disabled", false);
				$("#save, #reset").css("visibility", "hidden");
			} 
		},
		add:function(){
			var fields=$("#data_field").val();
			for(var i=0; i<fields.length; i++)
			{
				$("#data_field option[value='"+fields[i]+"']").remove();
				$("#data_field_effected").append("<option value='"+fields[i]+"'>"+fields[i]+"</option>");
			}
		},
		remove : function(){
			var fields=$("#data_field_effected").val();
			for(var i=0; i<fields.length; i++)
			{
				$("#data_field_effected option[value='"+fields[i]+"']").remove();
				$("#data_field").append("<option value='"+fields[i]+"'>"+fields[i]+"</option>");
			}			
		},
		up : function(){
			$('#data_field_effected option:selected').each(function(){
				$(this).insertBefore($(this).prev());
			});
		},
		down : function(){
			$('#data_field_effected option:selected').each(function(){
				$(this).insertAfter($(this).next());
			});
		},
		data_field_effected_change : function()
		{
			var fields=$("#data_field_effected").val();
			if (fields.length>1) {
				if (fields.length>4)
					$("#caption").html("["+fields.length+" fields selected]");
				else
					$("#caption").html(""+fields+"");
				$("#friendly_name").attr("disabled", "disabled"); 
				$("#formula").attr("disabled", "disabled"); 
				$("#data_method").val("");
				$("#input_type").val("");

				return;
			} 
	
			//$("#tbl_detail").html("");
			$("#save, #reset").hide();

			param = {
					'table' : $("#data_source").val(),
			   		'field_effected' : ""+fields+""	
			}
			
			sendAjaxNotMessage('/getprop', param, function(respondData){
				_fieldconfig.setData(respondData);
			});
		},
		setData : function(respondData){
			var data = respondData.data;
			$('#caption').text(data[0].COLUMN_NAME);
			$('#friendly_name').val(data[0].LABEL);
			$('#data_method').val(data[0].DATA_METHOD);
			$('#VALUE_FORMAT').val(data[0].VALUE_FORMAT);
			if(data[0].IS_MANDATORY == "1"){
				$('#is_mandatory').prop('checked', true);
			}else{
				$('#is_mandatory').prop('checked', false); 
			}
			$('#FDC_WIDTH').val(data[0].FDC_WIDTH);
			$('#INPUT_TYPE').val(data[0].INPUT_TYPE);
			$('#FORMULA').val(data[0].FORMULA);
			$('#VALUE_FORMAT').val(data[0].VALUE_FORMAT);
			$('#VALUE_MAX').val(data[0].VALUE_MAX);
			$('#VALUE_MIN').val(data[0].VALUE_MIN);
			$('#VALUE_WARNING_MAX').val(data[0].VALUE_WARNING_MAX);
			$('#VALUE_WARNING_MIN').val(data[0].VALUE_WARNING_MIN);
			$('#RANGE_PERCENT').val(data[0].RANGE_PERCENT);

			if(data[0].USE_FDC == "1"){
				$('#us_data').prop('checked', true);
			}else{
				$('#us_data').prop('checked', false);
			}

			if(data[0].USE_GRAPH == "1"){
				$('#us_gr').prop('checked', true);
			}else{
				$('#us_gr').prop('checked', false);
			}

			if(data[0].USE_DIAGRAM == "1"){
				$('#us_sr').prop('checked', true);
			}else{
				$('#us_sr').prop('checked', false);
			}

			$("#objectExtension").html("");
			$("#addObjectBtn").remove();
			var objectExtensionSource = respondData.objectExtension;
			if(objectExtensionSource.length>0){
				var objectExtension	= data[0].OBJECT_EXTENSION;
				_fieldconfig.objectExtensionTarget = respondData.objectExtensionTarget;
				if(objectExtension!=null&&objectExtension!=""){
					var objects = $.parseJSON(objectExtension);
					$.each(objects, function( index, value ) {
						_fieldconfig.addObjectExtension(objectExtensionSource,value,index);
					});
				}
				
				var addObjectBtn = $("<img id='addObjectBtn'></img>");
				addObjectBtn.attr("src","/img/plus.png");
				addObjectBtn.addClass("xclose floatRight");
				addObjectBtn.click(function() {
					_fieldconfig.addObjectExtension(objectExtensionSource,[],{});
				});
				addObjectBtn.appendTo($("#extensionView"));
			}
			
			$("#save, #reset").show();
		},
		objectExtensionTarget	: [],
		addObjectExtension : function(objects,targets,objectId){
			var li = $("<li></li>");
			var del = $("<img></img>");
			del.attr("src","../img/x.png");
			del.addClass("xclose");
			del.click(function() {
				li.remove();
			});
			del.appendTo(li);
			
			var select = $("<select></select>");
			$.each(objects, function( oindex, ovalue ) {
				var option = $("<option></option>");
				option.val(ovalue.ID);
				option.text(ovalue.NAME);
				option.appendTo(select);
			});
			select.css("width","85px");
			select.val(objectId);
			select.appendTo(li);

			var objectExtensionTarget = _fieldconfig.objectExtensionTarget;

			if(targets.OVERWRITE !=true && targets.OVERWRITE !="true") {
				targets.basic				= {
		        	VALUE_MAX			: $("#VALUE_MAX").val()			,
		        	VALUE_MIN			: $("#VALUE_MIN").val()			,
		        	VALUE_WARNING_MAX	: $("#VALUE_WARNING_MAX").val()	,
		        	VALUE_WARNING_MIN	: $("#VALUE_WARNING_MIN").val()	,
		        	RANGE_PERCENT		: $("#RANGE_PERCENT").val()		,
				};
				targets.OVERWRITE			= false;
			}
			else targets.OVERWRITE			= true;
			
			var basic = $("<span></span>");
			basic.addClass("linkViewer");
			basic.appendTo(li);
			basic.editable({
				type		: 'address',
				onblur		: 'ignore',
				placement	: 'left',
				mode		: "popup",		
				value		: targets,
		    });
			basic.on('save', function(e, params) {
				var cellColor 	= params.newValue.advance.COLOR;
				cellColor		= cellColor==""?"transparent":"#"+cellColor;
				select.css("background-color",cellColor);
			});
			var scolor 	= typeof targets.advance == "object"?targets.advance.COLOR:"";
			scolor		= scolor==""?"transparent":"#"+scolor;
			select.css("background-color",scolor);
			li.appendTo($("#objectExtension"));
		},
		buildObjectExtension	:  function(){
			var objects = {};
			$('#objectExtension li').each(function(i){
				var objectId = $(this).find( "select" ).eq(0).val();
				if(objectId!=null&&objectId!=""){
					var targets 	= $(this).find( "span" ).eq(0).editable('getValue',true);
					if(typeof(objects[objectId]) == "undefined") 
						objects[objectId] = targets;
					else 
						objects[objectId] = arrayUnique(objects[objectId].concat(targets),
												function(e1,e2){
														return typeof(e1) == "object" && typeof(e2) == "object" && Object.keys(e1)[0] == Object.keys(e2)[0];
												});
				}
			});
			return objects;
		},
		reset : function()
		{
			$('#cfg_field_prop')[0].reset();
		},
		saveprop : function()
		{		
			var table 	= $("#data_source").val();
			var field	=""+$("#data_field_effected").val();
			if(typeof _fieldconfig.getTableField=="function") {
				var tableField	= _fieldconfig.getTableField(table,field);
				table = tableField.table;
				field = tableField.field_effected;
			}
			
			param = {
				'table' 				: table,
				'field' 				: field,
				'data_method' 			: $("#data_method").val(),
				'VALUE_FORMAT' 			: $("#VALUE_FORMAT").val(),
				'INPUT_TYPE' 			: $("#INPUT_TYPE").val(),
				'FORMULA' 				: $("#FORMULA").val(),
				'VALUE_MAX' 			: $("#VALUE_MAX").val(),
				'VALUE_MIN' 			: $("#VALUE_MIN").val(),
				'VALUE_WARNING_MAX' 	: $("#VALUE_WARNING_MAX").val(),
				'VALUE_WARNING_MIN' 	: $("#VALUE_WARNING_MIN").val(),
				'RANGE_PERCENT' 		: $("#RANGE_PERCENT").val(),
				'FDC_WIDTH' 			: $("#FDC_WIDTH").val(),
				'us_data' 				: $("#us_data").is(':checked') ? 1 : 0,
				'us_gr' 				: $("#us_gr").is(':checked') ? 1 : 0,
				'us_sr' 				: $("#us_sr").is(':checked') ? 1 : 0,
				'is_mandatory' 			: $("#is_mandatory").is(':checked') ? 1 : 0,
				'friendly_name' 		: $("#friendly_name").val(),
				objectExtension			: _fieldconfig.buildObjectExtension(),
			}
			
			sendAjax('/saveprop', param, function(data){
				if(data == "OK"){
					$("#x_status").html('Save successfully');
					alert('Save successfully');
				}else{
					alert(data);
				}
			});
		}
}
</script>
            <form name="cfg_field_prop" action="saveconfig.php" method="post" id="cfg_field_prop">
              <table border="0" id="tbl_detail" style="padding:4px;width:500px;">
                <tr>
                  <td colspan="2"><b style="font-size:1.1em" id="caption">CAPTION</b></td>
                </tr>
                <tr>
                  <td class="field">Label</td>
                  <td>
                  	<input type="text" name="friendly_name" id="friendly_name" size="50" style="width: 198px;">
                  	Input width<input type='text' name='FDC_WIDTH' id='FDC_WIDTH' size='5' value=''>
                  </td>
                </tr>
                <tr>
                  <td class="field">Data method</td>
                  <td>
                  	<select size="1" name="data_method" style="width:332" id="data_method">
                  	<option value="0"></option>
                  	 @foreach($code_data_method as $re)
						<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> 
					 @endforeach
                    </select>
                  </td>
                </tr>
                <tr>
                  <td class="field">Input type</td>
                  <td>
                  	<select size="1" name="INPUT_TYPE" style="width:332" id="INPUT_TYPE">
                  	<option value="0"></option>
                  	@foreach($cfg_input_type as $re)
						<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option> 
					@endforeach
                    </select>
                  </td>
                </tr>
                <tr>
                  <td class="field">Mandatory</td>
                  <td>
                  	<input type='checkbox' name='is_mandatory' id="is_mandatory">
                  </td>
                </tr>
                <tr>
                  <td class="field">Formula</td>
                  <td>
                  <textarea name="FORMULA" id="FORMULA" style="width:332" rows="2"></textarea>
                  </td>
                </tr>
                <tr>
                  <td class="field">Data format</td>
                  <td><input type="text" name="VALUE_FORMAT" id="VALUE_FORMAT" size="50"></td>
                </tr>
                <tr>
                  <td class="field">Error Max Value</td>
                  <td><input type="text" name=VALUE_MAX id="VALUE_MAX" size="50"></td>
                </tr>
                <tr>
                  <td class="field">Error Min value</td>
                  <td><input type="text" name="VALUE_MIN" id="VALUE_MIN" size="50"></td>
                </tr>
                <tr>
                  <td class="field">Warning Max Value</td>
                  <td><input type="text" name="VALUE_WARNING_MAX" id="VALUE_WARNING_MAX" size="50"></td>
                </tr>
                <tr>
                  <td class="field">Warning Min value</td>
                  <td><input type="text" name="VALUE_WARNING_MIN" id="VALUE_WARNING_MIN" size="50"></td>
                </tr>
                <tr>
                  <td class="field">Range %</td>
                  <td><input type="text" name="RANGE_PERCENT" id="RANGE_PERCENT" size="50"></td>
                </tr>
                <tr style="height:30px">
                  <td class="field">Use for:</td>
                  <td>
	                  <div class="floatLeft" style="width: 27%;">
	                  	<input type="checkbox" name="us_data" id="us_data">Data capture<br>
	                    <input type="checkbox" name="us_gr" id="us_gr">Graph<br>
	                    <input type="checkbox" name="us_sr" id="us_sr">Surveillance
	                  </div>
	                  <div class="floatLeft" id ="extensionView" style="width: 73%;">
		                  <ul id="objectExtension" style="list-style-type: none;margin: 0;padding-left: 7px;">
		                  </ul>
	                  </div>
                  </td>
                </tr>
              </table>
            </form>
			<div style="margin-left:100px">
            	<input type="button" id="save" onClick="_fieldconfig.saveprop()" style="width:100px;" value="Save">
                <input type="reset" id="reset" onClick="_fieldconfig.reset()" style="width:100px;" value="Reset">
			</div>
	
	<link href="/jqueryui-editable/css/jqueryui-editable.css" rel="stylesheet"/>
	<script src="/jqueryui-editable/js/jqueryui-editable.js"></script>
	<script src="/common/js/extendFieldConfig.js"></script>
	
	<link rel="stylesheet" media="screen" type="text/css" href="/common/colorpicker/css/colorpicker.css" />
	<script type="text/javascript" src="/common/colorpicker/js/colorpicker.js"></script>
	
	<style>
 		._colorpicker{
	 		border:none;
	 		cursor:pointer;
	 		z-index: 10000;
 		}
 		.colorpicker{
	 		z-index: 10000;
 		}
 		.field{
 			white-space: nowrap;
 		}
 		.editable-address {
		    display: block;
		    margin-bottom: 5px; 
		    white-space: nowrap;
		}
		
		.editable-address span {
		    width: 140px; 
		    display: inline-block;
		}
	</style>
