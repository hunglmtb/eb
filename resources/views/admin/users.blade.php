<?php
$currentSubmenu = 'users';
$listControls = [ 
		'UserRole' => array (
				'label' => 'User Roles',
				'ID' => 'UserRole',
				'default' => 'All' 
		),
		'LoProductionUnit' => array (
				'label' => 'Production Unit',
				'ID' => 'LoProductionUnit',
				'default' => 'All' 
		),
		
		'LoArea' => array (
				'label' => 'Area',
				'ID' => 'LoArea',
				'fkey' => 'production_unit_id',
				'default' => 'All' 
		),
		
		'Facility' => array (
				'label' => 'Facility',
				'ID' => 'Facility',
				'default' => 'All',
				'fkey' => 'area_id' 
		) ,
		'loadData' => array(
				'label' => 'Load Data',
				'ID' => 'LoadData',
				'TYPE' => 'BUTTON',
				'onclick' => '_users.loadData()'
		),
];
?>

@extends('core.am', ['listControls' => $listControls])

@section('title')
<div class="title">Users Management</div>
@stop @section('content')

<script type="text/javascript">
$(function(){		


});

var _users = {
		loadData : function (){
			param = {
				'ROLES_ID' : $('#UserRoles').val(),
				'PRODUCTION_ID' : $('#ProductionUnit').val(),
				'AREA_ID' : $('#Area').val(),
				'FACILITY' : $('#Facility').val()
			}

			sendAjax('/am/loadUserList', param, function(data){
				_users.listData(data.result);
			});	
		},

		onclickLink : function(id){
			$("#iframeWorkflow234").contents().find("body").html('');
			$( "#boxWorkflow234" ).dialog({
				height: 480,
				width: 700,
				modal: true,
				title: "Edit User",
				close: function( event, ui ) {	
					_users.loadData();
				}
			});

			$("#iframeWorkflow234").attr("src","data:text/html;charset=utf-8," + escape(''));
			$("#iframeWorkflow234").attr("src", "/am/editUser/"+id);

			
		},

		onclickLinkAddNew : function(){
			$("#iframeWorkflow234").contents().find("body").html('');
			$( "#boxWorkflow234" ).dialog({
				height: 480,
				width: 700,
				modal: true,
				title: "Add User",
				close: function( event, ui ) {
					_users.loadData();					
				}
			});

			$("#iframeWorkflow234").attr("src","data:text/html;charset=utf-8," + escape(''));
			$("#iframeWorkflow234").attr("src", "/am/new/");			
		},

		onclickDelete : function(id){

			if(!confirm("Are you sure to delete this user?")) return;
			param = {
				'ID' : id
			}
			$.ajax({
		    	url: '/am/delete',
		    	type: "GET",
		    	data: param, 
		    	success: function(_data){
		    		alert(_data.Message);
		    		_users.loadData();		    		
				}
			});
		},
		
		listData : function(data){
			var str = '';
			$('#bodyUsersList').html('');
			
			for(var i = 0; i< data.length; i++){
				var id = data[i].ID;
				var cssClass = "row1";
				if(i%2 == 0){
					cssClass = "row2";
				} 
				str += '<tr class='+ cssClass +'>';
				str += '	<td><span>' + checkValue(data[i].USERNAME,'') + '</span></td>';
				str += '	<td >'+ checkValue(data[i].ROLE,'') +'</td>';
				str += '	<td >'+ checkValue(data[i].PU_NAME,'') +'</td>';
				str += '	<td >'+ checkValue(data[i].AREA_NAME,'') +'</td>';
				str += '	<td >'+ checkValue(data[i].FACILITY_NAME,'') +'</td>'; 
				str += '	<td >'+ checkValue(data[i].STATUS,'') +'</td>';
				str += '	<td >'+ checkValue(data[i].EXPIRE_DATE,'') +'</td>';
				str += '	<td >'+ checkValue(data[i].PASSWORD_CHANGED,'') +'</td>';
				str += '	<td class="linkA">&nbsp;';
				str += '		<a href="#" onclick="_users.onclickDelete('+data[i].ID+')">delete </a> | ';
				str += '		<a href="#" onclick="_users.onclickLink('+data[i].ID+')">Edit </a>';
				str += '	</td>';
				str += '</tr>';
			}

			$('#bodyUsersList').html(str);

			

			$('#bodyUsersList tr').click(function () {
		        if(this.style.background == "" || this.style.background =="white") {
		            $(this).css('background', 'red');
		        }
		        else {
		            $(this).css('background', 'white');
		        }
		    });
		}
}
</script>

<div id="boxUsersList">
	<strong>&nbsp;Users list</strong> ( <a href="#" onclick="_users.onclickLinkAddNew()">Add
		User</a> )<br>
	<table>
		<thead id="table5">
			<tr>
				<td class="column160">Username</td>
				<td class="column160">Roles</td>
				<td class="column160">Production Unit</td>
				<td class="column120">Area</td>
				<td class="column140">Falicity</td>
				<td class="column120">Status</td>
				<td class="column120">Expire date</td>
				<td class="column160">Password changed</td>
				<td class="column100">&nbsp;</td>
			</tr>
		</thead>
		<tbody id="bodyUsersList">
		</tbody>
	</table>
</div>

<div class="footerList"></div>

<div id="boxWorkflow234"
	style="display: none; width: 100%; height: 100%; background: #ffffff; overflow: hidden;">
	<iframe id="iframeWorkflow234"
		style="border: none; padding: 0px; margin-left:-21px; width: 110%; height: 100%; box-sizing: border-box;"></iframe>
</div>
@stop
