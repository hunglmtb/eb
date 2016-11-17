<?php
$currentSubmenu 		= '/fieldsconfig';
$objectExtension 		= isset($objectExtension)?$objectExtension:[];
?>

@extends('core.bsconfig')
<script src="/common/js/jquery-2.1.3.js"></script>
<script src="/common/js/js.js"></script>
<script type="text/javascript">
$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	})
	
	$("#add").button({ icons: { primary: "ui-icon-arrowreturnthick-1-e"} });
	$("#remove").button({ icons: { primary: "ui-icon-arrowreturnthick-1-w"} });
	$("#up").button({ icons: { primary: "ui-icon-arrowthick-1-n"} });
	$("#down").button({ icons: { primary: "ui-icon-arrowthick-1-s"} });
	$("#change_field, #save, #reset").button();
	
	$("#data_field").attr("disabled", true);

	$("#data_source").change();

	$("#chk_vie, #chk_tbl").change(function(){
		if($("#chk_tbl").prop('checked')==false && $("#chk_vie").prop('checked')==false)
		{
			$("#data_source").html('');
			$("#data_source").change()			
			return;
		}

		param = {
			'chk_tbl'  :$("#chk_tbl").prop('checked'),
			'chk_vie' : $("#chk_vie").prop('checked')
		}
		
		sendAjaxNotMessage('/chckChange', param, function(data){
			$("#data_source").html('');
			$("#data_source").html(data);
			$("#data_source").change();
		});
	});

	$("#data_field_effected").change(function(){
		_fieldconfig.data_field_effected_change();
	});

	
});

function setColorPicker(element,target){
	element.ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex);
			$(el).ColorPickerHide();
// 			target.css("background-color","#"+hex);
			$(el).css("background-color","#"+hex);
			$(el).css("color","#"+hex);
		},
		onBeforeShow: function () {
			$(this).ColorPickerSetColor($(this).val());
		}
	});
}

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
			$('#FDC_WIDTH').val(data[0].FDC_WIDTH);
			$('#data_method').val(data[0].DATA_METHOD);
			$('#input_type').val(data[0].INPUT_TYPE);

			if(data[0].IS_MANDATORY == "1"){
				$('#is_mandatory').prop('checked', true);
			}else{
				$('#is_mandatory').prop('checked', false); 
			}
			$('#formula').val(data[0].FORMULA);
			$('#data_format').val(data[0].VALUE_FORMAT);
			$('#max_value').val(data[0].VALUE_MAX);
			$('#min_value').val(data[0].VALUE_MIN);

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
					_fieldconfig.addObjectExtension(objectExtensionSource,[],-1);
				});
				addObjectBtn.appendTo($("#extensionView"));
			}
			
			$("#save, #reset").show();
		},
		objectExtensionTarget	: [],
		addObjectExtension : function(objects,targets,value){
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
			select.val(value);
			select.appendTo(li);

			var colorObjects = $.grep(targets, function(e){ 
  	 			return typeof(e) == "object" && typeof( e.color) != "undefined";
   			});

			if (colorObjects.length > 0){
				var cellColor = colorObjects[0]["color"];
				select.css("background-color","#"+cellColor);
				select.attr("cell-color",cellColor);
			}

			var objectExtensionTarget = _fieldconfig.objectExtensionTarget;
 			
			var span = $("<span></span>");
			span.addClass("linkViewer");
			span.editable({
		    	type 		: 'checklist',
		    	onblur		: 'ignore',
		        value		: targets,
		        source		: objectExtensionTarget,
		        display		: function(value, sourceData) {
				        	   //display checklist as comma-separated values
				        	   var html = [],
				        	       checked = $.fn.editableutils.itemsByValue(value, sourceData);
				        	       
				        	   if(checked.length) {
				        	       $.each(checked, function(i, v) { html.push($.fn.editableutils.escape(v.text)); });
				        	       $(this).text(html.join(', '));
				        	   } else {
				        	       $(this).text("pick rules"); 
				        	   }
				        	},
		    });
			span.on("shown", function(e, editable) {
					var div = $("<div></div>");
					var color = $("<input class='inputColor' type='text' maxlength='6' size='6' style='padding:2px;background: rgb(219, 68, 219);' >");
					color.addClass("_colorpicker");
					var evalue = span.editable('getValue',true);
					var colorObjects = $.grep(evalue, function(e){ 
          	 			return typeof(e) == "object" && typeof( e.color) != "undefined";
           			});

					if (colorObjects.length == 0){
						color.css("color","#000000");
						color.val("pick color");
					}
					else{
						var cellColor = colorObjects[0]["color"];
						color.css("background-color","#"+cellColor);
						color.css("color","#"+cellColor);
						color.val(cellColor);
					}
					
					setColorPicker(color,span);
					color.appendTo(div);
					div.insertAfter($(editable.container.$form.get(0)).find(".editable-buttons").eq(0));
					span.on('save', function(e, params) {
						var cellColor = color.val();
						select.css("background-color","#"+cellColor);
						select.attr("cell-color",cellColor);
						if(cellColor!="pick color"){
							var ovalue = params.newValue;
							var result = $.grep(ovalue, function(e){ 
				              	 			return typeof(e) == "object" && typeof( e.color) != "undefined";
				               			});
							if (result.length == 0) 
								ovalue.push({color	: cellColor});
							else
								result[0]["color"] = cellColor;
						}

					});
	    	});
			span.appendTo(li);
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
			var field=""+$("#data_field_effected").val()+"";
			var table = $("#data_source").val();
			
			param = {
				'table' : $("#data_source").val(),
				'field' : field,
				'data_method' : $("#data_method").val(),
				'formula' : $("#formula").val(),
				'input_type' : $("#input_type").val(),
				'data_format' : $("#data_format").val(),
				'max_value' : $("#max_value").val(),
				'min_value' : $("#min_value").val(),
				'fdc_width' : $("#FDC_WIDTH").val(),
				'us_data' : $("#us_data").is(':checked') ? 1 : 0,
				'us_gr' : $("#us_gr").is(':checked') ? 1 : 0,
				'us_sr' : $("#us_sr").is(':checked') ? 1 : 0,
				'is_mandatory' : $("#is_mandatory").is(':checked') ? 1 : 0,
				'friendly_name' : $("#friendly_name").val(),
				objectExtension	: _fieldconfig.buildObjectExtension(),
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

@section('content')
<link href="/common/css/style_field_config.css" rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<body style="margin:0; overflow-x:hidden">
<div id="wraper">
	<table bgcolor="#E6E6E6" width="531px">
    	<tr><td><b>Table name</b></td></tr>
        <tr><td>
            <select size="1" name="data_source" id="data_source" style="width:200px" onchange="_fieldconfig.data_source_change();_fieldconfig.reset();">
               @foreach($cfg_data_source as $re)
					<option value="{!!$re['NAME']!!}">{!!$re['NAME']!!}</option> 
				@endforeach
            </select> 
            <input type="checkbox" checked id="chk_tbl" value="1">Tables
            <input type="checkbox" checked id="chk_vie" value="0">Views
        </td></tr>
    </table><br>
    <table bgcolor="#E6E6E6" style="border:0px; border-collapse:collapse">
    	<tr>
        	<td><b>Field</b></td>
            <td></td>
            <td><b>Effected field</b></td>
            <td width="40px" bgcolor="#FFFFFF"></td>
            <td valign="top" rowspan="2" align="left" height="400">
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
                  	<select size="1" name="input_type" style="width:332" id="input_type">
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
                  <textarea name="formula" id="formula" style="width:332" rows="2"></textarea>
                  </td>
                </tr>
                <tr>
                  <td class="field">Data format</td>
                  <td><input type="text" name="data_format" id="data_format" size="50"></td>
                </tr>
                <tr>
                  <td class="field">Max value</td>
                  <td><input type="text" name="max_value" id="max_value" size="50"></td>
                </tr>
                <tr>
                  <td class="field">Min value</td>
                  <td><input type="text" name="min_value" id="min_value" size="50"></td>
                </tr>
                <tr style="height:30px">
                  <td class="field">Use for:</td>
                  <td>
	                  <div class="floatLeft" style="width: 25%;">
	                  	<input type="checkbox" name="us_data" id="us_data">Data capture<br>
	                    <input type="checkbox" name="us_gr" id="us_gr">Graph<br>
	                    <input type="checkbox" name="us_sr" id="us_sr">Surveillance
	                  </div>
	                  <div class="floatLeft" id ="extensionView" style="width: 75%;">
		                  <ul id="objectExtension" style="list-style-type: none;margin: 0;padding-left: 7px;">
			              	@foreach($objectExtension as $extension)
				              	<select >
					              	@foreach($extension["object"] as $object)
						              	<option value='{{$object->ID}}'>{{$object->NAME}}</option>
									@endforeach
								</select>
							@endforeach
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
            </td>
        </tr>
        <tr>
        	<td valign="top">
            	<select size="20" multiple name="data_field" id="data_field" style="width:220px">
                </select>
            </td>
            <td><button id="up" value="Up" onclick="_fieldconfig.up();" style="visibility:hidden">Up</button><br>
            	<button id="down" value="Down" onclick="_fieldconfig.down();" style="visibility:hidden">Down</button><br>
            	<button id="add" value="Set" onclick="_fieldconfig.add();" style="visibility:hidden">Add</button><br>
                <button id="remove" onclick="_fieldconfig.remove();" style="visibility:hidden" value="Unset">Remove</button><br>
                <button id="change_field" value="Done" onclick="_fieldconfig.change_field();">Change field</button>
            </td>
            <td valign="top">
            	<select size="20" name="data_field_effected" id="data_field_effected" multiple style="width:220px">
              	</select>
            </td>
            <td bgcolor="#FFFFFF"><br>
            </td>
        </tr>
    </table>
</div>
</body>
@stop


@section('script')
	<!-- <link href="/common/css/bootstrap.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-responsive.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-editable.css" rel="stylesheet"/>
 	<script src="/common/js/bootstrap.js"></script>
	<script src="/common/js/bootstrap-editable.js"></script> -->
	
	<link href="/jqueryui-editable/css/jqueryui-editable.css" rel="stylesheet"/>
	<script src="/jqueryui-editable/js/jqueryui-editable.js"></script>
	
	<link rel="stylesheet" media="screen" type="text/css" href="/common/colorpicker/css/colorpicker.css" />
	<script type="text/javascript" src="/common/colorpicker/js/colorpicker.js"></script>
	
	<style>
 		._colorpicker{border:none;cursor:pointer}
	</style>
@stop