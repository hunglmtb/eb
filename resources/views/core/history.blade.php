<script src="/common/js/highcharts.js"></script>
<script src="/common/js/highcharts_exporting.js?1"></script>
<script src="/common/js/highcharts-offline-exporting.js?1"></script>

<script>
var xchart;
var currentHistory	= {
		tab			:	false,
		columnName	:	false,
		rowData		:	false
};
function limitChange(limit){
	parent.actions.loadHistory(currentHistory.tab,currentHistory.columnName,currentHistory.rowData,limit);
}
function changeChartType(type){
	xchart.series[0].update({
                type: type
            });
}
</script>

<div id="boxHistory" style="display:none;">
	<div id="frameChart" style="width:100%;border:none;height: 100%; margin-top: 0">
		<div style="position:absolute;left:230px;top:10px;z-index:100">
		Chart type <select id="cboChartType" onchange="changeChartType($(this).val())" style="width:100px"><option value="line">Line</option><option value="spline">Curved line</option><option value="column">Column</option><option value="area">Area</option><option value="areaspline">Curved Area</option></select>
		<!--
		  <div id="radio">
		    <input type="radio" id="radio1" name="radio" value="line" checked="checked"><label for="radio1"><img src='../img/chart-line.png' height=16></label>
		    <input type="radio" id="radio2" name="radio" value="area"><label for="radio2"><img src='../img/chart-area.png' height=16></label>
		    <input type="radio" id="radio3" name="radio" value="column"><label for="radio3"><img src='../img/chart-bar.png' height=16></label>
		  </div>
		  -->
		</div>
		<table>
		<tr><td width=200 valign='top' style="border-right:1px solid #dddddd"><b>Last <select id="cboLimit" onchange="limitChange($(this).val())">
		<option>5</option>
		<option selected>10</option>
		<option>15</option>
		<option>20</option>
		<option>30</option>
		<option>50</option>
		</select> values:</b><br><br><div id="history_list" style="max-height:230px;overflow:auto"></div></td>
		<td><div id="history_container" style="width: 650px; height: 300px; margin: 0 auto"></div></td></tr>
		</table>
	</div>
	<div id="history_loading">Loading...</div>
	
</div>
