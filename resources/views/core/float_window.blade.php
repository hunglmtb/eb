<div style="background:#eee;border:2px solid #666;display:none;position: fixed; width: 950px; height: 430px; z-index: 1; left:50%; margin-left:-450px; top:145px" id="divEditGroup">
	<div onClick="saveEditGroup()" style="cursor:pointer; position: absolute; right:72px;top:-27px;border:2px solid #666;background:#eee; width: 82px; height: 23px;line-height:23px; z-index: 1" id="layer1">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font size="2">Save</font></div>
	<div onClick="closeEditWindow()" style="cursor:pointer;position: absolute; right:-2px;top:-27px;border:2px solid #666;background:#eee; width: 75px; height: 23px;line-height:23px; z-index: 1" id="layer1">&nbsp;&nbsp;&nbsp;&nbsp;
		<font size="2">Close</font>
	</div>
	<div id="contentview" style="width:100%;height:100%">
		<table border='0' cellpadding='0' style='width:100%;height:100%'>
			<caption style='background:gray;color:white;height:20px;font-size:10.5pt' id = 'cationEditGroup'></caption>
			<tr>
				<td valign='top'>
					<div id="table_editrowoil_containerdiv" class="secondaryTable" style='height:400px;overflow:auto'>
						<table id="table_editrowoil" class="fixedtable nowrap display">
							<tfoot>
								<tr>
									<td style="text-align:left">Sum:</td>
									<td style="text-align:left"></td>
									<td style="text-align:left" colspan="1"></td>
								</tr>
							</tfoot>
						</table>
					</div>
				</td>
				<td valign='top' width="10">
					<div class="paddingOfTable" style='width:10px;overflow:auto'>
					</div>
				</td>
				<td valign='top'>
					<div id="table_editrowgas_containerdiv" class="secondaryTable" style='height:400px;overflow:auto'>
						<table id="table_editrowgas" class="fixedtable nowrap display">
						<tfoot>
							<tr>
								<td style="text-align:left">Sum:</td>
								<td style="text-align:left"></td>
								<td style="text-align:left"></td>
								<td style="text-align:left" colspan="3"></td>
							</tr>
						</tfoot>
					</table>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div id="tableEditGroup" style="width:100%;height:100%"></div>
</div>