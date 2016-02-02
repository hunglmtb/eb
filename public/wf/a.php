
<html>
<head>
<title>Workflow Display</title>
<link rel="stylesheet" href="../common/css/style.css">
<script src="../common/js/jquery-1.9.1.js"></script>
<script src="../common/utils.js"></script>
<script src="../common/js/base64.js"></script>
<script src="js/wfshow.js"></script>
<span id="wf_xml" style="display:none">
PG14R3JhcGhNb2RlbD4KICA8cm9vdD4KICAgIDxEaWFncmFtIGxhYmVsPSJUZXN0IERpYWdyYW0iIGhyZWY9IiIgaWQ9IjAiPgogICAgICA8bXhDZWxsLz4KICAgIDwvRGlhZ3JhbT4KICAgIDxMYXllciBsYWJlbD0iRGVmYXVsdCBMYXllciIgaWQ9IjEiPgogICAgICA8bXhDZWxsIHBhcmVudD0iMCIvPgogICAgPC9MYXllcj4KICAgIDxTaGFwZSBsYWJlbD0iQmVnaW4iIGhyZWY9IiIgdGFza19pZD0iNjAyIiB0YXNrX2NvZGU9IiIgaXNydW49IjEiIGlzYmVnaW49IjEiIG5leHRfdGFza19jb25maWc9IjYwMywiIGlkPSIyIj4KICAgICAgPG14Q2VsbCBzdHlsZT0iYmVnaW5wb2ludCIgdmVydGV4PSIxIiBwYXJlbnQ9IjEiPgogICAgICAgIDxteEdlb21ldHJ5IHg9IjEzMCIgeT0iMTMwIiB3aWR0aD0iODAiIGhlaWdodD0iNDAiIGFzPSJnZW9tZXRyeSIvPgogICAgICA8L214Q2VsbD4KICAgIDwvU2hhcGU+CiAgICA8UmVjdCBsYWJlbD0iZmxvdyIgaHJlZj0iIiB0YXNrX2lkPSI2MDMiIHRhc2tfY29kZT0iRkRDX0ZMT1ciIGlzcnVuPSIyIiBuZXh0X3Rhc2tfY29uZmlnPSI2MDQsIiBwcmV2X3Rhc2tfY29uZmlnPSI2MDIsIiB0YXNrX2NvbmZpZz0ie30iIHRhc2tfZGF0YT0ieyZxdW90O25leHRfdGFza19jb25maWcmcXVvdDs6JnF1b3Q7NjA0LCZxdW90OywmcXVvdDtwcmV2X3Rhc2tfY29uZmlnJnF1b3Q7OiZxdW90OzYwMiwmcXVvdDssJnF1b3Q7aWQmcXVvdDs6JnF1b3Q7NjAzJnF1b3Q7LCZxdW90O25hbWUmcXVvdDs6JnF1b3Q7ZmxvdyZxdW90OywmcXVvdDtydW5ieSZxdW90OzomcXVvdDsyJnF1b3Q7LCZxdW90O3VzZXImcXVvdDs6JnF1b3Q7dHVuZyxhYmMsJnF1b3Q7LCZxdW90O3Rhc2tfZ3JvdXAmcXVvdDs6JnF1b3Q7RkRDJnF1b3Q7LCZxdW90O3Rhc2tfY29kZSZxdW90OzomcXVvdDtGRENfRkxPVyZxdW90O30iIGlkPSIzIj4KICAgICAgPG14Q2VsbCBzdHlsZT0icmVjdCIgdmVydGV4PSIxIiBwYXJlbnQ9IjEiPgogICAgICAgIDxteEdlb21ldHJ5IHg9IjMyMCIgeT0iMTMwIiB3aWR0aD0iODAiIGhlaWdodD0iNDAiIGFzPSJnZW9tZXRyeSIvPgogICAgICA8L214Q2VsbD4KICAgIDwvUmVjdD4KICAgIDxSb3VuZHJlY3QgbGFiZWw9IlJvdW5kZWQiIGhyZWY9IiIgdGFza19pZD0iNjA0IiB0YXNrX2NvZGU9IkZEQ19FVSIgaXNydW49IjAiIHByZXZfdGFza19jb25maWc9IjYwMywiIG5leHRfdGFza19jb25maWc9IjYwNSwiIGlkPSI0Ij4KICAgICAgPG14Q2VsbCBzdHlsZT0icm91bmRlZCIgdmVydGV4PSIxIiBwYXJlbnQ9IjEiPgogICAgICAgIDxteEdlb21ldHJ5IHg9IjUyMCIgeT0iMTMwIiB3aWR0aD0iODAiIGhlaWdodD0iNDAiIGFzPSJnZW9tZXRyeSIvPgogICAgICA8L214Q2VsbD4KICAgIDwvUm91bmRyZWN0PgogICAgPFNoYXBlIGxhYmVsPSJFbmQiIGhyZWY9IiIgdGFza19pZD0iNjA1IiB0YXNrX2NvZGU9IiIgaXNydW49IjAiIGlzZW5kPSIxIiBwcmV2X3Rhc2tfY29uZmlnPSI2MDQsIiBpZD0iNSI+CiAgICAgIDxteENlbGwgc3R5bGU9ImVuZHBvaW50IiB2ZXJ0ZXg9IjEiIHBhcmVudD0iMSI+CiAgICAgICAgPG14R2VvbWV0cnkgeD0iNzMwIiB5PSIxMTAiIHdpZHRoPSI4MCIgaGVpZ2h0PSI0MCIgYXM9Imdlb21ldHJ5Ii8+CiAgICAgIDwvbXhDZWxsPgogICAgPC9TaGFwZT4KICAgIDxDb25uZWN0b3IgbGFiZWw9IiIgaHJlZj0iIiBpZD0iNiI+CiAgICAgIDxteENlbGwgZWRnZT0iMSIgcGFyZW50PSIxIiBzb3VyY2U9IjIiIHRhcmdldD0iMyI+CiAgICAgICAgPG14R2VvbWV0cnkgcmVsYXRpdmU9IjEiIGFzPSJnZW9tZXRyeSIvPgogICAgICA8L214Q2VsbD4KICAgIDwvQ29ubmVjdG9yPgogICAgPENvbm5lY3RvciBsYWJlbD0iIiBocmVmPSIiIGlkPSI3Ij4KICAgICAgPG14Q2VsbCBlZGdlPSIxIiBwYXJlbnQ9IjEiIHNvdXJjZT0iMyIgdGFyZ2V0PSI0Ij4KICAgICAgICA8bXhHZW9tZXRyeSByZWxhdGl2ZT0iMSIgYXM9Imdlb21ldHJ5Ii8+CiAgICAgIDwvbXhDZWxsPgogICAgPC9Db25uZWN0b3I+CiAgICA8Q29ubmVjdG9yIGxhYmVsPSIiIGhyZWY9IiIgaWQ9IjgiPgogICAgICA8bXhDZWxsIGVkZ2U9IjEiIHBhcmVudD0iMSIgc291cmNlPSI0IiB0YXJnZXQ9IjUiPgogICAgICAgIDxteEdlb21ldHJ5IHJlbGF0aXZlPSIxIiBhcz0iZ2VvbWV0cnkiLz4KICAgICAgPC9teENlbGw+CiAgICA8L0Nvbm5lY3Rvcj4KICA8L3Jvb3Q+CjwvbXhHcmFwaE1vZGVsPgo=
</span>
<script type="text/javascript" src="js/mxClient.js"></script>
<script type="text/javascript" src="js/mxApplication.js"></script></head>
<body onload="new mxApplication('config/diagrameditor.xml');" style="margin:0px;background:white;overflow:hidden">
<div id='wflist' style='margin-top:5px;width:100%;height:30px;border-top:0px solid #aaaaaa;padding-top:0px;background:white'>
<b> Workflow </b> <select id='cbo_wflist' style='min-width:200px;height:30px;margin:0 2px;border-color:#378de5'><option value='23' selected>tung tesstss</option></select>
<input type='button' value='Refresh' style="margin:0 2px;height:30px;width:80px" onclick="refreshPage()">
<button type='text' id='cmd_open_task' onclick="openTask()" style='display:none;margin:0 2px;width:100px;height:30px' >Open Task</button>
<button type='text' id='cmd_finish_task' style='display:none;margin:0 2px;width:120px;height:30px' >Finish Task</button>
</div>
<center>
<div id="graph" style="margin-top:15px;position:relative;height:calc(100% - 60px);width:100%;box-sizing: border-box;cursor:default;overflow:hidden;border:0px solid #a0a0a0;">
</div>
</center>
<div id='task_process' style='display:none;text-align:center;padding-top:0px'>
<span id='task_info' style="display:none">Task Name:<span class='name' task_id='0'></span></span>
</div>
<div style="display:none; padding:10px;" id="toolbar" ></div>
<script>
$(document).ready(function(){
	$('#cbo_wflist').change(function(){
		var wfid=$(this).val();
		loadSavedDiagram(wfid);		
	});
	$('#cmd_finish_task').click(function(){
		task_id=$('#task_process .name').attr('task_id');
		if(task_id>0){
			$.get('../taskman/ajaxs/finish_workflowtask.php?id='+task_id,function(data){
				parent.loadTasksCounting();
				loadWorkflow();
				_alert("Task finished successfully ("+data+")");
			})
		}
	})
});
</script>
</body>
</html>
