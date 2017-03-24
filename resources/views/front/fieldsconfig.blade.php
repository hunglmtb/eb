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
</script>

@section('content')
<link href="/common/css/style_field_config.css" rel="stylesheet">
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
            <label><input type="checkbox" style="margin-left:20px" id="chk_dc" value="0">Disable in Data Capture</label>
        </td></tr>
    </table><br>
    <table bgcolor="#E6E6E6" style="border:0px; border-collapse:collapse">
    	<tr>
        	<td><b>Field</b></td>
            <td></td>
            <td><b>Effected field</b></td>
            <td width="40px" bgcolor="#FFFFFF"></td>
            <td valign="top" rowspan="2" align="left" height="400">
				@include('core.fields_config')
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
