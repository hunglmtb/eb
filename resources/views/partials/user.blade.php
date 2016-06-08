<script>
	function loadjscssfile(filename, filetype){
	    if (filetype=="js"){ //if filename is a external JavaScript file
	        var fileref=document.createElement('script')
	        fileref.setAttribute("type","text/javascript")
	        fileref.setAttribute("src", filename)
	    }
	    else if (filetype=="css"){ //if filename is an external CSS file
	        var fileref=document.createElement("link")
	        fileref.setAttribute("rel", "stylesheet")
	        fileref.setAttribute("type", "text/css")
	        fileref.setAttribute("href", filename)
	    }
	    if (typeof fileref!="undefined")
	        document.getElementsByTagName("head")[0].appendChild(fileref)
	}
	if(!$.ui){
		loadjscssfile("/common/js/jquery-ui.js", "js");
		loadjscssfile("/common/css/jquery-ui.css?2", "css");
	}
	if($.ui){
		$.ui.dialog.prototype._makeDraggable = function() { 
		this.uiDialog.draggable({
			containment: false,
			});
		};	
	}
	/*
	if (!$("link[href='../common/css/style.css']").length)
		loadjscssfile("../common/css/style.css", "css");
	    //$('<link href="../common/css/style.css" rel="stylesheet">').appendTo("head");
	*/
	</script>
	
	<?php 
		$current_username = '';
		if((auth()->user() != null)) $current_username = auth()->user()->username;
	?>
	<div id="boxUserInfo" style='position:fixed;z-index:2;display:;top:10px;right:10px;font-size:10pt;overflow:none;padding:3px 6px 3px 10px;background:#555555;border:1px solid #505050;border-radius:3px;color:#bbbbbb;font-size:10pt'>
	User <span style="cursor:pointer" onclick="location.href='/user/settings.php';"><font color="#33b5e8"><span id="textUsername">{{$current_username}}</span></font></span> &nbsp;|&nbsp; <div style="display:none;width:50px;cursor:pointer;padding:2px;color:#33b5e8;margin:2px;font-size:8pt">Alert: 0</div>
	<a style="color:#33b5e8;text-decoration:none" href="/auth/logout">logout</a> &nbsp;&nbsp;
	
	<img atl="Workflow" src='/img/gear.png' height=16 onclick="showWorkflow()" style="float:right;margin:0px 2px;cursor:pointer">
	<div id="wf_notify_box" onclick="showWorkflow()" style="position:absolute;right:-5px;top:-5px;width:16px;height:16px;font-family:Arial;background:red;border:2px solid white;border-radius:12px;font-size:6pt;font-weight:bold;color:white;cursor:pointer;text-align:center;line-height:12px;letter-spacing: -1px;text-indent:-1px;box-sizing: border-box;">
	<span id="wf_notify">
	<?php
		/* $sql="SELECT count(*) FROM tm_workflow_task WHERE (isrun in (2,3) AND (user LIKE '%,$current_username,%' or user LIKE '$current_username,%')) AND wf_id in (SELECT id FROM tm_workflow WHERE isrun='yes')";
		$re=mysql_query($sql);
		$row=mysql_fetch_array($re);
		echo $row[0];
		if(!$row[0]) echo "<script>$('#wf_notify_box').hide();</script>"; */
	?>
	</span>
	</div>
	<img atl="Help" src='/img/help.png' height=16 onclick="showHelp()" style="float:right;cursor:pointer">
	<script>
	function show_wf_loading(){
		$("#wf_loading_box").show();
	}
	function hide_wf_loading(){
		$("#wf_loading_box").fadeOut("fast");
	}
	function showWorkflow(){
		$( "#boxWorkflow" ).dialog({
			height: 520,
			width: 900,
			modal: true,
			position: ['right-4.5', 'top+20'],
			title: "Your tasks",
			close: function( event, ui ) {
				//loadTasksCounting();
			}
		});
	
		$("#iframeWorkflow").attr("src","data:text/html;charset=utf-8," + escape(''));
		show_wf_loading();
		$("#iframeWorkflow").attr("src","loadWfShow");
	}
	var help="";
	function showHelp(){
		if(!$("#boxHelp").is(":visible"))
			$( "#boxHelp" ).dialog({
				height: 450,
				width: 710,
				modal: false,
				position: ['right-4.5', 'top+20'],
				title: "Help on this screen",
			});
		if(help==""){
			if (typeof func_code == 'undefined'){
				$("#boxHelp").html("No data");
				return;
			}
			$("#boxHelp").html('<img class="center_content" src="/wf/images/loading.gif">');
			$.get("/common/act.php?act=get_help&func_code="+func_code,function(data){
				help="1";
				if(data=="") data="No help!";
				$("#boxHelp").html(data);
			});
		}
	}
	function showTaskLog(task_id){
		if(task_id<=0) return;
		if(!$("#boxTaskLog").is(":visible"))
			$( "#boxTaskLog" ).dialog({
				height: 500,
				width: 900,
				modal: true,
				//position: ['right-4.5', 'top+20'],
				title: "Task log"
			});
		$("#boxTaskLog").html('<img class="center_content" src="/wf/images/loading.gif">');
		$.get("/common/act.php?act=get_tasklog&task_id="+task_id,function(data){
			$("#boxTaskLog").html(data);
		});
	}
	var taskCountingTimer;
	var username= '{{$current_username}}';
	function _loadTasksCounting(){
		//alert("_loadTasksCounting");
		$.get("/taskman/ajaxs/WorkflowTaskCounter.php?user="+username,function(data){
				$("#wf_notify").html(data);
				if(data=="0") 
					$("#wf_notify_box").hide();
				else
					$("#wf_notify_box").show();
				taskCountingTimer=setTimeout(_loadTasksCounting,30000);
			});
	}
	function loadTasksCounting(){
		if(taskCountingTimer) {
			clearTimeout(taskCountingTimer);
			taskCountingTimer=null;
		}
		if(username!="")
			_loadTasksCounting();
	}
	taskCountingTimer=setTimeout(_loadTasksCounting,30000);
	</script>
	</div>
		<div id="boxWorkflow" style="display:none;width:100%;height:100%;background:#ffffff;overflow:hidden;">
		    <iframe id="iframeWorkflow" onload="loadTasksCounting()" style="border:none;padding:0px;width:100%;height:100%;box-sizing: border-box;"></iframe>
				<div id="wf_loading_box" style="position:absolute;left:0px;top:0px;width:100%;height:100%;background:white;opacity:0.8"><center id="notify_splash"><img class="center_content" src="/wf/images/loading.gif"></center></div>
		</div>
	<!-- <div id="boxWorkflow" onclick="showWorkflow()" style="z-index:1;display:none;position:fixed;top:0px;left:0px;width:100%;height:100%;background:rgba(0,0,0,0.3)">
		<div id="boxWorkflow_content" style="z-index:100;padding:0px;position:absolute;top: 99px;left:50%;transform: translateX(-50%);width:900px;height:520px;background:#ffffff;border:1px solid #333333;border-radius:0px;box-shadow:0px 5px 20px rgba(0,0,0,0.4)">
			<iframe id="iframeWorkflow" style="border:none;padding:0px;width:100%;height:100%;box-sizing: border-box;"></iframe>
			<div id="wf_loading_box" style="position:absolute;left:0px;top:0px;width:100%;height:100%;background:white;opacity:0.5"><center id="notify_splash"><img style="position: absolute;top: 50%;left:50%;transform: translate(-50%,-50%);" src="/wf/images/loading.gif"></center></div>
		</div>
	</div> -->
	<div id="boxHelp" style="display:none;width:100%;height:100%"><img class="center_content" src="/wf/images/loading.gif"></div>
	<div id="boxTaskLog" style="display:none;z-index:100;width:100%;height:100%"></div>



