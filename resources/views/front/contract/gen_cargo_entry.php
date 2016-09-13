<!DOCTYPE html>
<!-- <script src="/common/js/numericInput.min.js"></script> -->
<div style="padding:10px">
<b><font size="2">Cargo No. Nomenclature</font></b>
<table cellpadding="5">
<tr>
<td width="90">1st code</td>
<td><input type="text" style="width:100px" name="txt_1stcode" id="txt_1stcode"></td>
<td>Year</td>
<td><input type="text" style="width:60px" name="txt_year1" id="txt_year1"></td>
<td width="90">2nd code</td>
<td><input type="text" style="width:60px" name="txt_2ndcode" id="txt_2ndcode"></td>
<td>Year</td>
<td><input type="text" style="width:60px" name="txt_year" id="txt_year"></td>
<td>Month</td>
<td><input type="text" style="width:60px" name="txt_month" id="txt_month"></td>
<td>Day</td>
<td><input type="text" style="width:60px" name="txt_day" id="txt_day"></td>
<td width="100">Seq. No. start</td>
<td><input type="text" style="width:60px" name="txt_seqno" id="txt_seqno"></td>
</tr>
</table>
<hr>
<table id="tableInfo" cellpadding="3">
<tr>
	<td>Liffting Account</td>
	<td>Priority</td>
	<td>Quantity Type</td>
	<td>1st Cargo Date</td>
	<td>Avg. Qty. per Cargo</td>
	<td>UOM</td>
	<td>Adjust Time</td>
	<td>Tolerance</td>
</tr>
<tr>
	<td><?php Helper::filter(['id'=>'PdLiftingAccount']);?>
	</td>
	<td><?php Helper::filter(['id'=>'PdCodeCargoPriority']);?>
	</td>
	<td><?php Helper::filter(['id'=>'PdCodeCargoQtyType']);?>
	</td>
	<td><?php Helper::selectDate(['id'=>'txtCargoDate','sName'=>'txtCargoDate']);?>
	</td>
	<td><input type="text" style="width:100px" name="txtQty" id="txtQty" class="_numeric"></td>
	<td><?php Helper::filter(['id'=>'PdCodeMeasUom']);?>
	</td>
	<td><?php Helper::filter(['id'=>'PdCodeTimeAdj']);?>
	</td>
	<td><?php Helper::filter(['id'=>'PdCodeQtyAdj']);?>
	</td>
</tr>
</table>
<br>
<div>
	Contract attribute
	<?php Helper::filter(['id'=>'PdContractQtyFormula']);?>
	Value
<input type="text" style="width:100px" name="txtAttrValue" id="txtAttrValue" class="_numeric"> 
</div>
<br>
<br>
<button onclick="gen()" style="width:200px;height:40px;">Generate Cargo Entry</button>
</div>
<script>
function calAttrValue(){
	var formula_id=$("#PdContractQtyFormula").val();
	if(formula_id>0){
		showWaiting();
	    postData = {
						contract_id				:'<?php echo $contract_id;?>',
						PdContractQtyFormula	:formula_id
					 };
	    $.ajax({
			url: '/gen_cargo_entry/calculate',
			type: "post",
			data: postData,
			success:function(data){
				console.log ( "send calAttrValue  success "/* +JSON.stringify(data) */);
				$("#txtAttrValue").val(data);
// 				alert(JSON.stringify(data));
				hideWaiting();
			},
			error: function(data) {
				console.log ( "calAttrValue error ");
				hideWaiting();
				alert("can not calculate attribute value");
			}
		});
	}
}
function gen(){
	/*
	var n=$("#txtNumCargo").val();
	if(!(n>0)){
		alert("Please input number of cargo");
		$("#txtNumCargo").focus();
		return;
	}
	*/
	
	if($("#txt_1stcode").val().trim()==""){
		alert("Please input 1st code");
		$("#txt_1stcode").focus();
		return;
	}
	if($("#txt_seqno").val().trim()==""){
		alert("Please input Seq. No. start");
		$("#txt_seqno").focus();
		return;
	}
	if($("#txtCargoDate").val().trim()==""){
		alert("Please input 1st Cargo Date");
		$("#txtCargoDate").focus();
		return;
	}
	
	var avgqty=parseInt($("#txtQty").val().replace(',','.'));
	if(!(avgqty>0)){
		alert("Please input average quantity");
		$("#txtQty").focus();
		return;
	}
	var qty=parseInt($("#txtAttrValue").val());
	if(!(qty>0)){
		alert("Please input quantity");
		$("#txtAttrValue").focus();
		return;
	}
	var n=Math.round(qty/avgqty);
	if(n>10){
		if(!confirm(n+" cargo entries will be generated. Do you want to continue?"))
			return;
	}
	else{
		if(!confirm("Do you want to create cargo entries?"))
			return;
	}
	postRequest( 
	             "gen_cargo_entry.php?act=gen",
	             {
					 code1st:$("#txt_1stcode").val(),
					 year1:$("#txt_year1").val(),
					 code2nd:$("#txt_2ndcode").val(),
					 year:$("#txt_year").val(),
					 month:$("#txt_day").val(),
					 day:$("#txt_month").val(),
					 seq:$("#txt_seqno").val(),
					 liftacc:$("#PdLiftingAccount").val(),
					 priority:$("#PdCodeCargoPriority").val(),
					 qtytype:$("#PdCodeCargoQtyType").val(),
					 date1st:$("#txtCargoDate").val(),
					 avgqty:$("#txtQty").val().replace(',','.'),
					 uom:$("#PdCodeMeasUom").val(),
					 adjtime:$("#PdCodeTimeAdj").val(),
					 tolerance:$("#PdCodeQtyAdj").val(),
					 contract_id:'<?php echo $contract_id;?>',
					 storage_id:'<?php echo $storage_id;?>',
					 qty:$("#txtAttrValue").val().replace(',','.')
				 },
	             function(data) {
					 _alert(data);
	             }
	          );
	
}

if(configuration.number.DECIMAL_MARK=='comma') 
	$("._numeric").attr('pattern',"^[-]?[0-9]+([,][0-9]{1,20})?");
else
	$("._numeric").attr('pattern',"^[-]?[0-9]+([\.][0-9]{1,20})?");

$('#PdContractQtyFormula').on('change', function() {
	calAttrValue();
});
$("#PdContractQtyFormula").change();
</script>
