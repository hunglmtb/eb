<meta name="_token"
	content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
<link rel="stylesheet" href="/common/css/admin.css">
<link rel="stylesheet" href="/common/css/jquery-ui.css">

<script src="/common/js/jquery-1.9.1.js"></script>
<script src="/common/js/jquery-ui.js"></script>
<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="/common/utils.js"></script>

<?php
$listControls = [ 
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
		) 
];
?>

<script type="text/javascript">
var ebtoken = $('meta[name="_token"]').attr('content');

$.ajaxSetup({
	headers: {
		'X-XSRF-Token': ebtoken
	}
});

$(function(){	
	
	var listControl = <?php echo json_encode($listControls);?>;
	
	//obj.init(listControl);
	
	$("select").change(function() {   
			var id = this.id;
			var table = ""
			var cboSet = ""; 
				
			if(id == "ProductionUnit"){
				table = "LoArea";
				cboSet = "Area"
			}

			if(id == "Area"){
				table = "Facility";
				cboSet = "Facility";
			}

			if(table != ""){
				value =  $('#'+id).val();
				obj.cboOnchange(cboSet, value, table);
			}
		});

	$( "#txtExpireDate" ).datepicker({
	    changeMonth:true,
	     changeYear:true,
	     dateFormat:"mm/dd/yy"
	});

	$('#btnSave').click(function(){
		obj.addUser();		
	});

	$('#btnClose').click(function(){
		parent.$('#boxWorkflow234').dialog('close');
	});	
}); 
var obj= {	
	reloadCbo : function(id, data){
		$('#'+id).empty();
		
		var _data = data.result;
		var cbo = '';

		cbo += ' 		<option value="0">All</option>';
		for(var v in _data){
			cbo += ' 		<option value="' + _data[v].ID + '">' + _data[v].NAME + '</option>';
		}

		$('#'+id).html(cbo);
	},

	cboOnchange : function(cboSet, value, table){
		param = {
			'ID' :value,
			'TABLE' : table
		};

		$.ajax({
	    	url: '/am/selectedID',
	    	type: "post",
	    	dataType: 'json',
	    	data: param,
	    	success: function(_data){
	    		obj.reloadCbo(cboSet, _data);
			}
		});

	},

	addUser : function(){
		var roles = '';
		var active = 0;
		
		$('input:checkbox[name=chkRoles]:checked').each(function(){
			 roles += $(this).val()+',';				       
		});

		if($('#chkActive').prop('checked')) {
			active = 1;
		}

		var user = $('#txtUsername').val();

		if(user == ""){
			alert("Username is not null");
			$('#txtUsername').focus();
			return;
		}
		
		 param = {
			'username' : $('#txtUsername').val(),
			'pass' : $('#txtPassword').val(),
			'lastname' : $('#txtLastName').val(),
			'middlename' : $('#txtMiddleName').val(),
			'firstname' : $('#txtFirstName').val(),
			'email' : $('#txtEmail').val(),
			'expireDate' : $('#txtExpireDate').val(),
			'roles' : roles.substring(0, roles.length-1),
			'pu_id' : $('#ProductionUnit').val(),
			'area_id' : $('#Area').val(),
			'fa_id' : $('#Facility').val(),
			'active' : active
		}	 		
		
		$.ajax({
	    	url: '/am/save',
	    	type: "post",
	    	data: param, 
	    	success: function(_data){
	    		alert(_data.Message);
			}
		});
	}
}

</script>

<div id="boxEditUser" class="context_iframe">
	<form name="frmUser" id="frmUser" action="" method="POST">
		<div id="divleft" class="div_left">
			<table border="0" cellpadding="0" id="table7" style="width: 100%">
				<tbody>
					<tr>
						<td>User ID</td>
						<td><input id="txtUsername" style="width: 174px" type="text"
							name="txtUsername" value="" size="20"></td>
					</tr>
					<tr>
						<td>Password</td>
						<td><input id="txtPassword" style="width: 174px" type="password"
							name="txtPassword" value="" size="20"></td>
					</tr>
					<tr>
						<td>Last name</td>
						<td><input id="txtLastName" style="width: 174px; height: 22px;"
							type="text" name="txtLastName" value="" size="20"></td>
					</tr>
					<tr>
						<td>Middle name</td>
						<td><input id="txtMiddleName" style="width: 174px; height: 22px;"
							type="text" name="txtMiddleName" value="" size="20"></td>
					</tr>
					<tr>
						<td>First name</td>
						<td><input id="txtFirstName" style="width: 174px; height: 22px;"
							type="text" name="txtFirstName" value="" size="20"></td>
					</tr>
					<tr>
						<td>Email address</td>
						<td><input id="txtEmail" style="width: 174px; height: 22px;"
							type="text" name="txtEmail" value="" size="20"></td>
					</tr>

					<tr>
						<td>Expire date</td>
						<td><input id="txtExpireDate" style="width: 174px; height: 22px;"
							type="text" name="txtExpireDate" value="" size="20"></td>
					</tr>
					<tr>
						<td>Active</td>
						<td><input name="chkActive" id="chkActive" type="checkbox"></td>
					</tr>
					<tr>
						<td width="100"><strong>Data scope</strong></td>
						<td>
							<div id="controlEdit">
								<div class="filter">
									<div>
										Production Unit
									</div>
									<select id="ProductionUnit" name="ProductionUnit">
										<option value="0">All</option> @foreach($loProductionUnit as
										$unit)
										<option value="{!!$unit->ID!!}">{!!$unit->NAME!!}</option>
										@endforeach
									</select>
								</div>
								<div class="filter">

									<div>
										Area
									</div>
									<select id="Area" name="Area">
										<option value="0">All</option>
									</select>
								</div>
								<div class="filter">
									<div>
										Facility
									</div>
									<select id="Facility" name="Facility">
										<option value="0">All</option>
									</select>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div id="divright" style="width: 300px; float: left;">
			<table style="width: 100%">
				<tbody id="listRole">
					@foreach($userRole as $role)
					<tr>
						<td style="width: 30"><input name="chkRoles" type="checkbox"
							value="{!!$role->ID!!}"></td>
						<td>{!! $role->NAME !!}</td>
					</tr>

					@endforeach
				</tbody>
			</table>
		</div>
	</form>
</div>

<div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"
	style="margin-top: 40px;">
	<div class="div_footer">
		<button type="button" id="btnSave" class="class_button">
			<span>Save</span>
		</button>		
		
		<button type="button" id="btnClose" class="class_button">
			<span>Close</span>
		</button>
	</div>
</div>