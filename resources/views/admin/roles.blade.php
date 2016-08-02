<?php
$currentSubmenu ='/am/roles';
$listControls = [ 
		'UserRole' => array (
				'label' => 'User Roles',
				'ID' => 'UserRole' 
		) 
];
?>

@extends('core.am', ['listControls' => $listControls])

@section('group')
<div id="controlSearch">
	<div class="role_title">
		<b>Role </b>
	</div>
	<div class="div_roleselect">
		<select id="Roles" onchange="_roles.loadData();"> @foreach($userRole as $role)
			<option value="{!! $role->ID !!}">{!! $role->NAME !!}</option>
			@endforeach
		</select>
	</div>

	<div class="div_actRole">
		<a hreft="#" onclick="_roles.editRole();"><b> Rename </b></a>| <a
			hreft="#" onclick="_roles.deleteRole();"> <b>Delete </b></a> | <a
			hreft="#" onclick="_roles.addRole();"> <b>New role </b></a>
	</div>

</div>
<br>
@stop 
@section('content')

<script type="text/javascript">

var ebtoken = $('meta[name="_token"]').attr('content');

$.ajaxSetup({
	headers: {
		'X-XSRF-Token': ebtoken
	}
});
$(function(){	
	$('#controlSearch').css('width','902px');
	_roles.loadData();
});

var _roles = {
		editRole : function(){
			$("#d_group_name").val($("#Roles option:selected").text());
			var id=$("#Roles").val();

			$( "#dialog" ).dialog({
				width: 370,
				modal: true,
				title: "Rename Role",
				buttons: {
					"Rename": function(){
						var group_name=$("#d_group_name").val();
																				
						param = {
								'ID' : id,
								'NAME' : group_name
						}
						$.ajax({
					    	url: '/am/editRoles',
					    	type: "post",
					    	data: param, 
					    	success: function(_data){
					    		$("#dialog").dialog("close");
					    		$("#Roles option:selected").text(group_name);		
							}
						});
					
					},
					"Cancel": function (){
						$("#dialog").dialog("close");
					}
				}
			});
		},
		addRole : function(){
			$( "#dialog" ).dialog({
				width: 370,
				modal: true,
				title: "add new Role",
				buttons: {
					"New role": function(){
						var group_name=$("#d_group_name").val();
																				
						param = {
								'NAME' : group_name
						}

						sendAjax('/am/addRoles', param, function(data){
							_roles.loadCbo(data.userRole);
				    		$("#dialog").dialog("close");
						});						
					},
					"Cancel": function (){
						$("#dialog").dialog("close");
					}
				}
			});
		},

		deleteRole : function(){
			var id=$("#Roles").val();
			if(!confirm("Are you sure to delete this group and all formula belong to it?")) return;
			param = {
					'ID' : id
			}

			sendAjax('/am/deleteRoles', param, function(data){
				_roles.loadCbo(data.userRole);
				_roles.loadData();
	    		$("#dialog").dialog("close");
			});				
		},
		loadCbo : function(data){
			var str = '';
			$('#Roles').val(str);
			for(var i = 0; i < data.length; i++){		
				str += '<option value="'+ data[i].ID +'">'+ data[i].NAME +'</option>'
			}
			$('#Roles').html(str);
		},
		
		loadData : function(){
			var roleID = $("#Roles").val();

			param = {
				'ROLE_ID' : roleID
			}

			sendAjax('/am/loadRightsList', param, function(data){
				_roles.showData(data);
			});	
		},
		showData : function(data){
			var roleLeft = data.roleLeft;
			var roleRight = data.roleRight;
			var strLeft = '';
			var strRight = '';

			$('#body_left').html(strLeft);
			for(var i = 0; i < roleLeft.length; i++){
				var cssClass = "row1";
				if(i%2 == 0){
					cssClass = "row2";
				} 
				strLeft +='<tr class="'+cssClass+'">';
				strLeft +='<td >' + roleLeft[i].NAME + '</td>';
				strLeft +='<td ><a hreft="#" onclick="_roles.removeOrGrant('+roleLeft[i].ID+',1);">Remove</a></td>';
				strLeft +='</tr>';
			}
			$('#body_left').html(strLeft);

			$('#body_right').html(strRight);
			for(var j = 0; j < roleRight.length; j++){
				
				var cssClass = "row1";
				if(j%2 == 0){
					cssClass = "row2";
				} 
				strRight +='<tr class="'+cssClass+'">';;
				strRight +='<td >' + roleRight[j].NAME + '</td>';
				strRight +='<td ><a hreft="#" onclick="_roles.removeOrGrant('+roleRight[j].ID+',0);">Grant</a></td>';
				strRight +='</tr>';
			}
			$('#body_right').html(strRight);
		},
		removeOrGrant :function(right_id, romove){
			var roleID = $("#Roles").val();
			param = {
					'ROLE_ID' : roleID,
					'RIGHT_ID' : right_id,
					'TYPE' : romove
				}

				sendAjax('/am/removeOrGrant', param, function(data){
					_roles.showData(data);
				});	
		}
}
</script>

<div id="dialog" style="display: none; height: 35px">
	<div id="chart_change">
		<table>
			<tr>
				<td>Group name:</td>
				<td><input type="text" size="" value="" id="d_group_name"
					style="width: 250px"></td>
			</tr>
		</table>
	</div>
</div>

<div class="boxContext">
	<div class="boxContext_left">
		<table class="roleTable" >
			<thead>
				<tr>
					<td><b>Rights list</b></td>
					<td></td>
				</tr>
				<tr>
					<td class="roleColumn1"><b>Name</b></td>
					<td class="roleColumn2"></td>
				</tr>
			</thead>
			<tbody id="body_left">
			</tbody>
		</table>
	</div>
	<div class="boxContext_left">
		<table class="roleTable">
			<thead>
				<tr>
					<td><b>Available rights</b></td>
					<td></td>
				</tr>
				<tr>
					<td class="roleColumn1"><b>Name</b></td>
					<td class="roleColumn2"></td>
				</tr>
			</thead>
			<tbody id="body_right">
			</tbody>
		</table>
		
		
	</div>
</div>

@stop
