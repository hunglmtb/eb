<?php
include_once('../lib/db.php');

$RIGHT_CODE="VIS_WORKFLOW";
checkRight($RIGHT_CODE);

$cur_diagram_id = 0;
?>
<html>
<head>
<title>Diagram Test</title>
<meta charset='utf-8'/>
<link rel="stylesheet" href="../common/css/jquery-ui.css">
<link rel="stylesheet" href="../common/css/style.css">
<script src="../common/js/jquery-1.9.1.js"></script>
<script src="../common/js/jquery-ui.js"></script>
<script src="../common/utils.js"></script>
<script src="../common/js/svgtopng.js"></script>
<style type="text/css" media="screen">
	#edit_buttons input{}
    td {font-size:8pt}
    .tabselected,.tabnormal { cursor:pointer;font-weight:bold;font-size:7.5pt;color:#fff;padding:5px;text-align:center}
    .tabselected {background:#959596;}
    .tabnormal {background:none;}
    .abutton{
        cursor:pointer;
        padding:0px;
        font-size:8pt;
        background:#ffffff;
        border:1px solid #666;
        text-align:center;
        color:#666;
        -moz-border-radius: 2px;
        border-radius: 2px;
    }
    .abutton:hover{background:#88BCD9;}
    .abutton:active{background:#6CADD0;}
    .xbutton{
        cursor:pointer;
        padding:0px;
        font-size:8pt;
        background:#666;
        border:0px solid #666;
        text-align:center;
        color:#ffffff;
        -moz-border-radius: 3px;
        border-radius: 3px;
    }
    .xbutton:hover{background:#88BCD9;}
    .xbutton:active{background:#6CADD0;}
    .ebutton{
        cursor:pointer;
		width:22px;
        padding:0px;
        font-size:8pt;
        background:white;
        border:1px solid #666;
        text-align:center;
        color:#ffffff;
        -moz-border-radius: 3px;
        border-radius: 3px;
    }
    .ebutton:hover{background:#eeeeee;}
    .ebutton:active{background:#e0e0e0;}
	#Qoccurdate, #Qflowphase{
		background:#eee;
	}
	.dark_bg{position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5)}
	td.mxWindowPane button {color:black}
	.skin4.top .tab_content {padding:0px}
</style>
<script type="text/javascript">
    var mxBasePath = '';
    var urlParams = (function(url)
    {
        var result = new Object();
        var params = window.location.search.slice(1).split('&');

        for (var i = 0; i < params.length; i++)
        {
            idx = params[i].indexOf('=');

            if (idx > 0)
            {
                result[params[i].substring(0, idx)] = params[i].substring(idx + 1);
            }
        }

        return result;
    })(window.location.href);

    var mxLanguage = urlParams['lang'];
</script>
<script type="text/javascript" src="js/mxClient.js?2"></script>
<script type="text/javascript" src="js/mxApplication.js"></script>
<script type="text/javascript">
var ed;
// Program starts here. The document.onLoad executes the
// mxApplication constructor with a given configuration.
// In the config file, the mxEditor.onInit method is
// overridden to invoke this global function as the
// last step in the editor constructor.
function onInit(editor){
    ed=editor;
    // Enables rotation handle
    mxVertexHandler.prototype.rotationEnabled = true;

    // Enables guides
    mxGraphHandler.prototype.guidesEnabled = true;

    // Alt disables guides
    mxGuide.prototype.isEnabledForEvent = function(evt)
    {
        return !mxEvent.isAltDown(evt);
    };

    // Enables snapping waypoints to terminals
    mxEdgeHandler.prototype.snapToTerminals = true;

    // Defines an icon for creating new connections in the connection handler.
    // This will automatically disable the highlighting of the source vertex.
    mxConnectionHandler.prototype.connectImage = new mxImage('images/connector.gif', 16, 16);

    // Enables connections in the graph and disables
    // reset of zoom and translate on root change
    // (ie. switch between XML and graphical mode).
    editor.graph.setConnectable(true);

    editor.graph.setPanning(true);
    //editor.graph.panningHandler.useLeftButtonForPanning = true;


    // Clones the source if new connection has no target
    editor.graph.connectionHandler.setCreateTarget(false);
    editor.graph.setAllowDanglingEdges(false);
	
	mxGraph.prototype.multigraph=false;
	mxGraph.prototype.alreadyConnectedResource="Already connected";

    var cellAddedListener = function(sender, evt)
    {
        var cells = evt.getProperty('cells');
        var cell = cells[0];
        if(editor.graph.isSwimlane(cell)){
            var DiagramName = mxUtils.prompt('Enter swimlane name', 'Swimlane');
            if(!DiagramName)
            {
                editor.graph.removeCells([cell]);
                return;
            }
            cell.setAttribute("label",DiagramName);
            addSubnetworkListItem(cell);
        }
    };

    var cellRemovedListener=function(sender, evt){
        updateSubnetworksList();
    }
    editor.graph.addListener(mxEvent.CELLS_ADDED, cellAddedListener);
    editor.graph.addListener(mxEvent.CELLS_REMOVED, cellRemovedListener);
    editor.graph.selectionModel.addListener(mxEvent.CHANGE, function(){
		if(ed.graph.selectionModel.cells.length==1 && ed.graph.selectionModel.cells[0].isEdge()==false){
			onObjectSelected(ed.graph.selectionModel.cells[0]);
		}
	});

    //outlineContainer
    if(!outline){
        outline = document.getElementById('outlineContainer');
        if (mxClient.IS_IE)
        {
            new mxDivResizer(outline);
        }
        var outln = new mxOutline(editor.graph, outline);
    }
}

function addSubnetworkListItem(cell){
    var DiagramName=cell.getAttribute("label");
    var list=document.getElementById("listSubnetwork");
    var entry = document.createElement('option');
    entry.appendChild(document.createTextNode(DiagramName));
    entry.setAttribute("cell_id",cell.id);
    entry.addEventListener('click',function(){
        //alert(cell.id);

        for(var i=0;i<entry.parentElement.children.length;i++)
        {
            var cID=entry.parentElement.children[i].getAttribute("cell_id");
            if(cID!=cell.id)
            {
                ed.graph.setCellStyles("highlight", "0", [ed.graph.model.getCell(cID)]);
            }
        }

        currentSubnetworkID=cell.id;
        justclicksubnetwork=true;
        ed.graph.setCellStyles("highlight", true, [cell]);
    },false);
    list.appendChild(entry);
}

function updateSubnetworksList(){
    var elements = document.getElementById("listSubnetwork").options;
    for(var i = 0; i < elements.length; i++)
    {
        var cID=elements[i].getAttribute("cell_id");
        if(!ed.graph.model.getCell(cID))
        {
            document.getElementById("listSubnetwork").remove(i);
        }
    }
}

function clearGraph(){
    ed.graph.removeCells(ed.graph.getChildVertices(ed.graph.getDefaultParent()));
}
var outline;
var defaultDiagramName="[Untitled Diagram]";
var defaultDiagramId= 0;
var currentDiagramName=defaultDiagramName;
var currentDiagramId = defaultDiagramId;

function setCurrentDiagramName(s){
    currentDiagramName=s;
    document.getElementById("diagramName").innerHTML=currentDiagramName;
}

function setCurrentDiagramId(i){
    currentDiagramId = i;
}

function saveDiagram(saveAs){
    try{
		$("#listSavedDiagrams").html("Loading...");
		if(currentDiagramName=="" || !currentDiagramName || currentDiagramName ==defaultDiagramName || saveAs){
			postRequest('../taskman/ajaxs/form/frm_add_wf.php',{id:0},function(data){
				$( "#boxSavedDiagrams" ).dialog({
					height: 400,
					width: 550,
					modal: true,
					title: "Add New Workflow",
					buttons: {
						Save: function() {
							currentDiagramName=$('#txt_name').val();
							currentDiagramIntro=$('#txt_intro').val();
							if($('#opt_yes').is(':checked')){
								currentDiagramStatus='yes';
							}else{
								currentDiagramStatus='no';
							}
							if(currentDiagramName=="" || !currentDiagramName){
								return;
							}
							var isbegin=0; var isend=0;
							for (var c in ed.graph.model.cells){
								var Cell=ed.graph.model.cells[c];
								if(Cell.style=='endpoint'){
									isend++;
									Cell.setAttribute('isend',1);
								} 
								if(Cell.style=='beginpoint'){
									isbegin++;
									Cell.setAttribute('isbegin',1);
								}
								if(Cell.isEdge()){
									_source=Cell.source.getAttribute('task_id');
									_target=Cell.target.getAttribute('task_id');
									//-----------------------------------------------
									if(Cell.target.getAttribute('prev_task_config')!=undefined && Cell.target.getAttribute('prev_task_config').indexOf(_source+',')>-1){
										
									}else{
										if(Cell.target.getAttribute('prev_task_config'))
										_source=Cell.target.getAttribute('prev_task_config')+_source;
										Cell.target.setAttribute('prev_task_config',_source+',');
									}
									//-----------------------------------------------
									if(Cell.source.getAttribute('next_task_config')!=undefined && Cell.source.getAttribute('next_task_config').indexOf(_target+',')>-1){
										
									}else{
										if(Cell.source.getAttribute('next_task_config'))
										_target=Cell.source.getAttribute('next_task_config')+_target;
										Cell.source.setAttribute('next_task_config',+_target+',');
									}
								}
			
							}
							var enc = new mxCodec();
							var node = enc.encode(ed.graph.getModel());
							var currentXML=mxUtils.getPrettyXml(node);
							
							if(isbegin==0 || isend==0){
								alert('Can not found begin node or end node!');
								return false;
							}
							if(isbegin>1){
								alert('Can not larger than tow begin node')
								return false;
							}
							postRequest( 
								"../taskman/ajaxs/workflowSave.php",
								{name:currentDiagramName,intro:currentDiagramIntro,isrun:currentDiagramStatus,key:currentXML},
								function(data) {
									console.log(data);
									//setCurrentDiagramId(data);
									setCurrentDiagramName(currentDiagramName);
									$("#listSavedDiagrams").html('');
								}
							);
							$( this ).dialog( "close" );
						},
						Close: function() {
						  $( this ).dialog( "close" );
						}
					},
				});
				$("#listSavedDiagrams").html(data);
			});
		}else{
			postRequest('../taskman/ajaxs/form/frm_edit_wf.php',{wfid:currentDiagramId},function(data){
				$( "#boxSavedDiagrams" ).dialog({
					height: 400,
					width: 550,
					modal: true,
					title: 'Edit Workflow: '+currentDiagramName,
					buttons: {
						Save: function() {
							currentDiagramName=$('#txt_name').val();
							currentDiagramId=$('#txt_id').val();
							currentDiagramIntro=$('#txt_intro').val();
							if($('#opt_yes').is(':checked')){
								currentDiagramStatus='yes';
							}else{
								currentDiagramStatus='no';
							}
							if(currentDiagramName=="" || !currentDiagramName){
								return false;
							}
							var isbegin=0; var isend=0;
							for (var c in ed.graph.model.cells){
								var Cell=ed.graph.model.cells[c];
								if(Cell.style=='endpoint'){
									isend++;
									Cell.setAttribute('isend',1);
								} 
								if(Cell.style=='beginpoint'){
									isbegin++;
									Cell.setAttribute('isbegin',1);
								}
								if(Cell.isEdge()){
									_source=Cell.source.getAttribute('task_id');
									_target=Cell.target.getAttribute('task_id');
									//-----------------------------------------------
									if(Cell.target.getAttribute('prev_task_config')!=undefined && Cell.target.getAttribute('prev_task_config').indexOf(_source+',')>-1){
										
									}else{
										if(Cell.target.getAttribute('prev_task_config'))
										_source=Cell.target.getAttribute('prev_task_config')+_source;
										Cell.target.setAttribute('prev_task_config',_source+',');
									}
									//-----------------------------------------------
									if(Cell.source.getAttribute('next_task_config')!=undefined && Cell.source.getAttribute('next_task_config').indexOf(_target+',')>-1){
										
									}else{
										if(Cell.source.getAttribute('next_task_config'))
										_target=Cell.source.getAttribute('next_task_config')+_target;
										Cell.source.setAttribute('next_task_config',+_target+',');
									}
								}
			
							}
							var enc = new mxCodec();
							var node = enc.encode(ed.graph.getModel());
							var currentXML=mxUtils.getPrettyXml(node);
							
							if(isbegin==0 || isend==0){
								alert('Can not found begin node or end node!');
								return false;
							}
							if(isbegin>1){
								alert('Can not larger than tow begin node')
								return false;
							}
							 
							
							postRequest( 
								"../taskman/ajaxs/workflowSave.php",
								{id:currentDiagramId,name:currentDiagramName,intro:currentDiagramIntro,isrun:currentDiagramStatus,key:currentXML},
								function(data) {
									console.log(data);
									setCurrentDiagramName(currentDiagramName);
									$("#listSavedDiagrams").html('');
									hideBoxDiagrams();
								}
							);
							$( this ).dialog( "close" );
						},
						Close: function() {
						  $( this ).dialog( "close" );
						}
					},
				});
				$("#listSavedDiagrams").html(data);
			});
		}
		
    }
    catch(err){
        alert(err.message);
    }
}


function changeLineColor(color)
{
    ed.graph.model.beginUpdate();
    try
    {
        var c;
        for (c in ed.graph.selectionModel.cells){
            if(ed.graph.selectionModel.cells[c].isEdge()){
                if (color=='') color='black';
                ed.graph.setCellStyles("strokeColor", color, [ed.graph.selectionModel.cells[c]]);
            }
        }
    }
    finally
    {
        ed.graph.model.endUpdate();
    }
}
function buttonActionClick(act)
{
	if(act=="print"){
		var pageCount=1;
		var scale = mxUtils.getScaleForPageCount(pageCount, ed.graph);
		var preview = new mxPrintPreview(ed.graph, scale);
		var oldRenderPage = mxPrintPreview.prototype.renderPage;
	
		var title=$("#diagramName").text();
		var sur_date=$("#Qoccurdate").val()+"";
		preview.title=title+(sur_date!=""?" - Surveillance date: "+sur_date:"");
		preview.print();
		preview.close();
	}
    else if(act=="rotate"){
		ed.graph.toggleCellStyles(mxConstants.STYLE_HORIZONTAL,"1",ed.graph.selectionModel.cells);
	}
    else 
		ed.execute(act);
}

function highlightContainer(a)
{
    //alert(graph);
    ed.graph.model.beginUpdate();
    try
    {
        var c;
        for (c in ed.graph.model.cells)
        {
            if(ed.graph.isSwimlane(ed.graph.model.cells[c]))
            {
                ed.graph.setCellStyles("highlight", a?"1":"0", [ed.graph.model.cells[c]]);

            }
        }
    }
    catch(err)
    {
        alert(err.message);
    }
    finally
    {
        ed.graph.model.endUpdate();
    }
}
var currentSubnetworkID;
var justclicksubnetwork=false;
function listSubnetworkClick()
{
    if(justclicksubnetwork){justclicksubnetwork=false; return;}
    if(currentSubnetworkID)
    {
        ed.graph.setCellStyles("highlight", "0", [ed.graph.model.getCell(currentSubnetworkID)]);
    }
    var elements = document.getElementById("listSubnetwork").options;
    for(var i = 0; i < elements.length; i++){
        elements[i].selected = false;
    }
}

function loadDiagram(){
	$("#listSavedDiagrams").html("Loading...");
	showBoxDiagrams();
	postRequest( 
		 "../taskman/ajaxs/getListWorkFlow.php",
		 {id:"~~GETLIST"},
		 function(result) {
			//var s=data;
			$("#listSavedDiagrams").html(result);
			$('#workflow_List tr td.show_wf').click(function(){
				var wf_data=$(this).parent().attr('wf_data');
				wf_data=jQuery.parseJSON(wf_data);
				setCurrentDiagramName(wf_data.name);
				setCurrentDiagramId(wf_data.id);
				postRequest('../taskman/ajaxs/getXMLCodeWF.php?wfid='+wf_data.id,{},function(xmlcode){
					var doc = mxUtils.parseXml(xmlcode);
					var dec = new mxCodec(doc);
					clearGraph();
					dec.decode(doc.documentElement, ed.graph.getModel());
					ed.graph.refresh();
				})
				/* var xmlcode=$(this).parent().attr('xmlcode');
				var doc = mxUtils.parseXml(xmlcode);
				var dec = new mxCodec(doc);
				clearGraph();
				dec.decode(doc.documentElement, ed.graph.getModel());
				ed.graph.refresh();*/
				hideBoxDiagrams(); 
			});
		 }
	  );
}

function newDiagram(){
    clearGraph();
	setCurrentDiagramId(0);
    setCurrentDiagramName(defaultDiagramName);
}
function showBoxDiagrams(){
	$( "#boxSavedDiagrams" ).dialog({
		height: 400,
		width: 550,
		modal: true,
		title: "Select a network diagram",
	});
}
function hideBoxDiagrams(){
	$("#boxSavedDiagrams").dialog("close");
}
var curent_task=0;
var curent_object=null;
function onObjectSelected(obj){
	alert('selected');
	var style=obj.style;
	if(obj.style=='swimlane'){
		$('#taskconfig').html('');
		alert("Please select once Task node");
	}else{
		curent_object=obj;
		if(obj.getAttribute('task_id'))
		curent_task=obj.getAttribute('task_id');
		
		if(obj.getAttribute('task_id')!=undefined){
			if(obj.style!='beginpoint' && obj.style!='endpoint'){
				$('#taskconfig').html('Loadding...');
				$('#taskconfig').load('../taskman/ajaxs/form/frm_task_config.php?style='+style+'&taskid='+curent_task);
			}
		}else{
			$.get('../common/getkey.php',function(taskid){
				curent_task=taskid;
				obj.setAttribute('task_id',curent_task);
				if(obj.style!='beginpoint' && obj.style!='endpoint'){
					$('#taskconfig').html('Loadding...');
					$('#taskconfig').load('../taskman/ajaxs/form/frm_edit_task.php?style='+style+'&taskid='+curent_task);
				}
			});
		}
	}
}
function showBoxTaskConfig(){
	if(ed.graph.selectionModel.cells.length==1 && ed.graph.selectionModel.cells[0].isEdge()==false && curent_object.style!='swimlane' && curent_object.style!='beginpoint' && curent_object.style!='endpoint'){
		$( "#boxtaskconfig" ).dialog({
			width: 550,
			modal: false,
			title: "Config a task",
			buttons: {
				Save: function() {
					var encoder = new mxCodec();
					var node = encoder.encode(ed.graph.getModel());
					var xml=mxUtils.getPrettyXml(node);
					var node_task=$(xml).find('mxCell[vertex=1]');
					var relation_task=$(xml).find('mxCell[edge=1]');
					var n=relation_task.length;
					var id=curent_object.id;
					var next_config='';
					var prev='';
					var _task={};
					for(var i=0;i<n;i++){
						var this_r=$(relation_task[i]);
						if(id==this_r.attr('source')){
							var _target=find_taskid_byid(node_task,this_r.attr('target'));
							if(_target)
							next_config+=_target+',';
						}
					}
					curent_object.setAttribute('next_task_config',next_config);
					_task['next_task_config']=next_config;
					
					for(var i=0;i<n;i++){
						var this_r=$(relation_task[i]);
						if(id==this_r.attr('target')){
							var _source=find_taskid_byid(node_task,this_r.attr('source'));
							if(_source)
							prev+=_source+',';
						}
					}
					curent_object.setAttribute('prev_task_config',prev);
					_task['prev_task_config']=prev;
					
					_task['id']=$('#txt_task_id').val();
					_task['name']=$('#txt_task_name').val();
					_task['runby']=$('#task_run_type').val();
					_task['user']=$('#txt_user').val();
					_task['task_group']=$('#choice_task_group').val();
					_task['task_code']=$('#choice_task').val();
					
					task_config={};
					if(_task['task_code']=='ALLOC_CHECK' || _task['task_code']=='ALLOC_RUN'){
						task_config['network']=$('#cbo_network').val();
						task_config['jobid']=$('#cbo_jobs').val();
						task_config['type']=$('#cbo_datetype').val();
						if(task_config['type']=='date'){
							task_config['from']=$('#txt_from').val();
							task_config['to']=$('#txt_to').val();
						}
						task_config['email']=$('#txt_email').val();
					}else if(_task['task_code']=='VIS_REPORT'){
						task_config['reportid']=$('#cbo_Reports').val();
						task_config['facility']=$('#cbo_Facility').val();
						task_config['type']=$('#cbo_datetype').val();
						if(task_config['type']=='date'){
							task_config['from']=$('#txt_from').val();
							task_config['to']=$('#txt_to').val();
						}
						task_config['email']=$('#txt_email').val();
					}else{
						
					}
					task_config=JSON.stringify(task_config);
					curent_object.setAttribute('task_config',task_config);
					
					task=JSON.stringify(_task);
					if(currentDiagramName=="" || !currentDiagramName || currentDiagramName ==defaultDiagramName){
						
					}else{
						$.get('../taskman/ajaxs/workflowSaveTask.php?wfid='+currentDiagramId+'&taskdata='+task+'&taskconfig='+task_config,function(data){
							console.log(data);
						});
					}
					
					curent_object.setAttribute('task_data',task);
					curent_object.setAttribute('label',$('#txt_task_name').val());
					ed.graph.refresh();
					$( this ).dialog( "close" );
				},
				Close: function() {
					curent_object=null;
					$( this ).dialog( "close" );
				}
			},
		});
	}else{
		alert('Select once task for config!');
	}
}
function find_taskid_byid(node_task,id){
	for(var j=0;j<node_task.length;j++){
		if($(node_task[j]).parent().attr('id')==id){
			return $(node_task[j]).parent().attr('task_id');
		}
	}
	return false;
}
function hideBoxTaskConfig(){
	$("#boxtaskconfig").dialog("close");
	edit=false;
}
function showXML(){
	var enc = new mxCodec();
	var node = enc.encode(ed.graph.getModel());
	var currentXML=mxUtils.getPrettyXml(node);
	showBoxDiagrams();
	currentXML=currentXML.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	$("#listSavedDiagrams").html("<pre>"+currentXML+"</pre>");
}
window.onbeforeunload = function() { return mxResources.get('changesLost'); };
</script>
</head>
<body onLoad="new mxApplication('config/diagrameditor.xml');" style="margin:0px;background:#eeeeee;">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
    <td height="120" valign="top">
        <div id="pageheader"></div>
    </td>
</tr>
<tr>
<td valign="top" align="center">
<!-- Object mapping -->
<div id="objectMapping" style="display:none">
<br>
<table border="0" cellpadding="3" id="table2">
        <tr>
            <td style=""><b style="">
                    <font size="2">Production Unit</font></b></td>
            <td style=""><b style="">
                    <font size="2">Area</font></b></td>
            <td style=""><b style="">
                    <font size="2">Facility</font></b></td>
        </tr>
        <tr>
            <td width="140" style="">
                <select style="width:100%; " id="cboProdUnit" size="1" name="cboProdUnit"></select></td>
            <td width="140" style="">
                <select style="width:100%; " id="cboArea" size="1" name="cboArea"></select></td>
            <td width="140" style="">
                <select style="width:100%; " id="cboFacility" size="1" name="cboFacility"></select></td>
        </tr>
    </table>
    <br>
    <table border="0" cellpadding="0" cellspacing="4" width="400" id="table1">
        <tr>
            <td>Object type</td>
            <td width="250">
                <select style="width:240px;" id="cboObjType" size="1" name="cboObjType">
                    <option value="FLOW">Flow</option>
                    <option value="ENERGY_UNIT">Energy Unit</option>
                    <option value="ENERGY_UNIT_GROUP">Energy Unit Group</option>
                    <option value="TANK">Tank & Storage</option>
                    <option value="EQUIPMENT">Equipment</option>
                </select></td>
        </tr>
        <tr>
            <td id="txtObjType">Flow</td>
            <td>
                <select style="width:240px;" id="cboObjs" size="1" name="cboObjs">
                </select></td>
        </tr>
        <tr id="flow_direction_tr" style="display:none">
            <td>Flow Direction</td>
            <td><input type="radio" value="in" name="flow_direction" id="fpdir1">In &nbsp;
            <input type="radio" value="out" name="flow_direction" id="fpdir2" checked>Out</td>
        </tr>
    </table>
</div>
<!-- End object mapping -->    

<!-- Surveillance setting -->
<div id="surveillanceSetting" style="display:none;padding:0;margin:5px">
	<div class="sur_tabs_holder">
		<ul>
			<li><a href="#sur_fields">Fields</a></li>
			<li><a href="#sur_tag">Tags</a></li>
		</ul>
		<div class="sur_tab_content_holder" style="margin:5px">
			<div id="sur_fields" style="overflow-y:auto;height:320px;line-height:18px">
			</div>
			<div id="sur_tag" style="height:320px">
				<div id="sur_tag_content" style="overflow-y:auto;height:250px;line-height:18px"></div>
				<div style="position:absolute;left:0px;bottom:0px;width:100%;height:70px;background:#e8e8e8;padding:10px 5px;box-sizing:border-box">
					<table>
					<tr><td width="100"><b>Connection</b></td><td><select id="cboConnection" style="min-width:185px"></select></td></tr>
					<tr><td><b>Other tags</b></td><td><input type="text" id="txt_sur_other_tag" style="width:370px"></td></tr>
					</table>
				</div>
			</div>
		</div>
    </div>
</div>

<table border="0" cellpadding="0" cellspacing="0" id="table1" width="100%">
    <tr>
        <td style="display:none" height="20">
            <div style="display:none" id="header_">&nbsp;</div>



            <div id="mainActions"			style="display:none;width:100%;padding-top:8px;padding-left:24px;padding-bottom:8px;">
            </div>
            <div style="display:none;float:right;padding-right:36px;">
                <input id="source" type="checkbox"/>Source
            </div>
            <div id="selectActions" style="display:none;width:100%;padding-left:54px;padding-bottom:4px;">
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <table border="0" cellpadding="0" cellspacing="0" id="table2" width="100%">
                <tr>
                    <td style="border:none;width:280px;">
                        <span style="font-size:10pt;padding-left:10px;" id="diagramName">[Untitled Diagram]</span></td>
                    <td>

                        <table border="0" cellpadding="0" id="table17" cellspacing="4" height="30">
                            <tr>
                                <td onClick="newDiagram()" width="60" class="xbutton">
                                    New</td>
                                <td onClick="loadDiagram()" width="60" class="xbutton">
                                    Load</td>
                                <td id="buttonSave" onMouseOut="$('#buttonSaveAs').hide();" onMouseOver="$('#buttonSaveAs').show();" onClick="saveDiagram()" width="60" class="xbutton">
                                    <span id="buttonSave_text">Save</span>
                                    <div class="xbutton" style="padding:5px; display:none; position: absolute; width: 64px; z-index: 101; margin-left:0px; margin-top:4px;border:2px solid #666" id="buttonSaveAs">
                                        <span onClick="saveDiagram('a')" >Save As</span></div>
                                </td>
                                <td onClick="buttonActionClick('print')" width="60" class="xbutton">Print</td>
                                <td style="display:none" onClick="buttonActionClick('exportImage')" width="60" class="xbutton">Export</td>
                                <td align="right" width="70">
											<span style="font-size: 8pt">
											Line color</span></td>
                                <td onClick="changeLineColor('red')" width="40" class="xbutton" style="background-color: #FF0000">
                                    </td>
                                <td onClick="changeLineColor('blue')" width="40" class="xbutton" style="background-color: #0066CC">
                                    </td>
                                <td onClick="changeLineColor('#008800')" width="40" class="xbutton" style="background-color: #008800">
                                    </td>
                                <td onClick="changeLineColor('#CC6600')" width="40" class="xbutton" style="background-color: #CC6600">
                                    </td>
                                <td style="display:; text-align:center" width="100" class="xbutton" onClick="$('#boxSubnetworks').toggle();">
                                    <span>Swimlanes</span>
                                    <div style="display:none; position: absolute; width: 174px; height: 133px; z-index: 100; margin-left:-0px; margin-top:5px;border:2px solid #666" id="boxSubnetworks">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" id="table22" height="100%">
                                            <tr>
                                                <td bgcolor="#c0c0c0" style="border:1px solid #666">
                                                    <select onclick="listSubnetworkClick()" id="listSubnetwork" style="width:100%;height:100%;border:0px solid #ffffff;overflow:auto;background:#c0c0c0;font-family:Verdana;font-size:8pt;color:#000" name="sometext" multiple="multiple">
                                                    </select></td>
                                            </tr>
                                        </table>
                                    </div></td>
                                <td width="50">&nbsp;</td>
								<td class="ebutton" onClick="buttonActionClick('copy')"><img src="images/copy.gif"></td>
								<td class="ebutton" onClick="buttonActionClick('cut')"><img src="images/cut.gif"></td>
								<td class="ebutton" onClick="buttonActionClick('paste')"><img src="images/paste.gif"></td>
								<td class="ebutton" onClick="buttonActionClick('delete')"><img src="images/delete.gif"></td>
								<td class="ebutton" onClick="buttonActionClick('undo')"><img src="images/undo.gif"></td>
								<td class="ebutton" onClick="buttonActionClick('redo')"><img src="images/redo.gif"></td>
								<td class="ebutton" onclick="buttonActionClick('rotate')"><img src="images/rotate.png"></td>
                                <td width="70" align="right">Export as</td>
								<td class="xbutton" width="30" onClick="exportImage()">PNG</td>
								<td class="xbutton" width="50" onClick="showBoxTaskConfig();">Config</td>
								<td class="xbutton" width="30" onClick="showXML();">XML</td>
								<script>
								function exportImage(type){
									saveSvgAsPng($('#graph svg')[0], 'diagram.png');
								}
								</script>
                            </tr>
                        </table>

                    </td>
                    <td>&nbsp;

                        </td>
                </tr>
			</table>
            <table border="0" cellpadding="0" cellspacing="0" id="table21" width="100%">
                <tr>
                    <td>&nbsp;</td>
                    <td id="tdToolBox" width="260" valign="top" style="border:none">
<script>
	function showIcons()
	{
		$("#buttonShowIcons").attr('class','tabselected');
		$("#buttonShowProperties").attr('class','tabnormal');
		$("#properties").hide();
		$("#icons").show();
	}
	function showProperties()
	{
		$("#buttonShowIcons").attr('class','tabnormal');
		$("#buttonShowProperties").attr('class','tabselected');
		$("#icons").hide();
		$("#properties").show();
	}
	function showObjectMapping(){
		$( "#objectMapping" ).dialog({
			height: 300,
			width: 550,
			modal: true,
			title: "Object mapping",
			buttons: {
				"OK": function(){ mappingObject();$("#objectMapping").dialog("close");},
				"Cancel": function(){
					$("#objectMapping").dialog("close");
				}
			}
		});
	}
	function mappingObject()
	{
		if(currentObjectMapping)
		{
			currentObjectMapping.setAttribute('object_id',$("#cboObjs").val());
			currentObjectMapping.setAttribute('object_type',$("#cboObjType").val());
		}
	}
	var currentObjectMapping,currentObjectID;
	function objectMapping()
	{
		ed.graph.model.beginUpdate();
		try
		{
			var c;
			for (c in ed.graph.selectionModel.cells)
			{
				currentObjectMapping=ed.graph.selectionModel.cells[c];
				currentObjectID=currentObjectMapping.getAttribute('object_id');

				$("#flow_direction").val(currentObjectMapping.getAttribute('flow_direction'));
				var objtype=currentObjectMapping.getAttribute('object_type');

				if (true || objtype=='ENERGY_UNIT' || objtype=='FLOW' || objtype=='TANK' || objtype=='EQUIPMENT' || objtype=='ENERGY_UNIT_GROUP')
				{
					//$("#flow_direction_tr").hide();
					$("#cboObjType").val(objtype);
					$("#cboObjType").change();
					showObjectMapping();
				}
				else if (objtype=='FLOW')
				{
					//$("#flow_direction_tr").show();
					$("#cboObjType").prop('selectedIndex',0);
					$("#cboObjType").change();
					showObjectMapping();
				}
				else if (objtype=='SUR')
				{
					$("#surMapping").show();
				}
				break;
			}
		}
		finally
		{
			ed.graph.model.endUpdate();
		}
	}
	function surveillanceSetting()
	{
		$('#sur_fields').html('Loading...');								
		$('#sur_tag_content').html('Loading...');								
		$("#txt_sur_other_tag").val("");
		$( "#surveillanceSetting" ).dialog({
			height: 480,
			width: 520,
			modal: true,
			title: "Surveillance settings",
			buttons: {
				"Apply": applySurveillance,
				"Cancel": function(){
					$("#surveillanceSetting").dialog("close");
				}
			}
		});
		var c;
		//Get all medel selsected
		for (c in ed.graph.selectionModel.cells)
		{
			currentObjectMapping=ed.graph.selectionModel.cells[c];
		
			var sur=currentObjectMapping.getAttribute('surveillance');
			var object_type=currentObjectMapping.getAttribute('object_type');
			var object_id=currentObjectMapping.getAttribute('object_id');
			var conn_id=currentObjectMapping.getAttribute('conn_id');
			
			$("#cboConnection").val(conn_id);
			//Get property in database
			postRequest("getSS.php",			//Get surveillance seeting property
				   {
					   'sur':sur,
					   'object_type':object_type,
					   'object_id':object_id
				   },
				   function(data){
					   var ss=data.split("!@#$");
					   $("#sur_fields").html(ss[0]);
					   $("#sur_tag_content").html(ss[1]);
					   $("#txt_sur_other_tag").val(ss[2]);
				   }
			);
			break;
		}
	}
	function applySurveillance(){
		var s="";
		var l=""; //lable display for cell (yellow cell)
		$("#sur_fields :checked").each(function(){
			s+=(s==""?"":"-")+$(this).attr("surveilance_settings");
			l+=(l==""?"":"\n")+$(this).val()
		});
		$("#sur_tag_content :checked").each(function(){
			s+=(s==""?"":"-")+$(this).attr("surveilance_settings");
			l+=(l==""?"":"\n")+$(this).val()
		});
		var other_tag=$("#txt_sur_other_tag").val().trim();
		if(other_tag!=""){
			s+=(s==""?"":"-")+"@TAG:"+other_tag;
			l+=(l==""?"":"\n")+other_tag
		}
		var rowlabel=$("#surveillanceSetting :checked").length;
		
		if(currentObjectMapping)
			currentObjectMapping.setAttribute('surveillance',s);
		if(currentObjectMapping)
			currentObjectMapping.setAttribute('conn_id',$("#cboConnection").val());
		
		//Create label or edit label
		var cell = ed.graph.getSelectionCell();
		var id=cell.getId();
		
		var cellX = Number(cell.getGeometry().x);
		var cellY = Number(cell.getGeometry().y-50);
		
		var doc = mxUtils.createXmlDocument();
		var node = doc.createElement('MyNode')
		node.setAttribute('label', l);
		//node.setAttribute('width', '125');
		//graph.insertVertex(graph.getDefaultParent(), null, node, 40, 40, 80, 30);
		
		//var parent = ed.graph.getDefaultParent();
		var parent = cell.parent;
		var model = ed.graph.model;
		
		//Check cell existing
		var label = model.getCell('label_'+id);
		if(typeof label==='undefined')
		{
			model.beginUpdate();
			try
			{
				var v1=ed.graph.insertVertex(parent, 'label_'+id, node, cellX, cellY, 160, 30);
				v1.setStyle('text_sur');
				if(rowlabel==0)
					v1.setVisible(false)
				else
				{
					v1.setVisible(true);
					v1.geometry.height=8+rowlabel*12;
				}
				//v1.setGeometry({'width':v1.getGeometry().width, 'height':8+rowlabel*12, 'x':v1.getGeometry().x, 'y':v1.getGeometry().y});
				var v2=cell;
				e=ed.graph.insertEdge(parent, 'edge_'+id, '', v1, v2);
				e.setStyle('dashed=1');
			}
			finally
			{
			  model.endUpdate();
			}
		}
		else
		{
			label.setAttribute('label', l);
			//label.setGeometry({'width':label.getGeometry().width, 'height':8+rowlabel*12, 'x':label.getGeometry().x, 'y':label.getGeometry().y});
			if(rowlabel==0)
				label.setVisible(false)
			else
			{
				label.setVisible(true);
				label.geometry.height=8+rowlabel*12;
			}
		}
		ed.graph.refresh();
		$("#surveillanceSetting").dialog("close");
	}
	function Qaddlabel(){
		var cell = ed.graph.getSelectionCell();
		cell.setGeometry('100', '200', '250', '84');
		ed.graph.refresh();
	}
</script>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" id="table3" height="100%">
                            <tr>
                                <td height="20" bgcolor="#666">
                                    <table border="0" cellpadding="0" width="100%" id="table10" cellspacing="1" height="100%">
                                        <tr>
                                            <td id="buttonShowIcons" width="46" onClick="showIcons()" class="tabselected" bgcolor="#959596">
                                                Toolbar</td>
                                            <td id="buttonShowProperties" class="tabnormal" onClick="showProperties()" width="79">&nbsp;
                                                Properties</td>
                                            <td>&nbsp;
                                                </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="300" style="border:1px solid #666" bgcolor="#C0C0C0" valign="top">
                                    <div id="properties" style="display:none;">
                                    </div>
                                    <div id="icons" style="width:260px;height:100%;overflow:auto;">
                                        <div style="padding:10px;" id="toolbar" ></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td height="10"></td>
                            </tr>
                            <tr>
                                <td>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="table20" height="100%">
                                        <tr>
                                            <td height="20" bgcolor="#666">
                                                <table border="0" cellpadding="0" width="100%" id="table21" cellspacing="1">
                                                    <tr>
                                                        <td>
                                                            <p align="left"><font size="1" color="#F8F8F8">
                                                                    &nbsp;<b>Zoom</b></font></td>
                                                        <td act="zoomIn" id="buttonZoomIn" onClick="buttonActionClick('zoomIn')" width="30" height="15" class="abutton">
                                                            in</td>
                                                        <td act="zoomOut" onClick="buttonActionClick('zoomOut')" width="30" height="15" class="abutton">
                                                            out</td>
                                                        <td act="actual" onClick="buttonActionClick('actualSize')" width="30" height="15" class="abutton">
                                                            1:1</td>
                                                        <td act="fit" onClick="buttonActionClick('fit')" width="30" height="15" class="abutton">
                                                            fit</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="display:;background:#fff;border:1px solid #666">
                                                <div id="outlineContainer" style="background:#fff;width:248px;height:99px;">
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="10" style="cursor:pointer" id="tdShowHideToolBox"><img id="imgShowHideToolBox" width=10 src='images/arrow_left.png'></td>
                    <td width="1000">
                        <div id="graph" style="position:relative;height:450px;width:1000px;cursor:default;overflow:hidden;border:1px solid #666;background-image:url('images/bg.png')">
                            <!-- Graph Here -->
                            <center id="splash" style="padding-top:230px;">
                                <img src="images/loading.gif">
                            </center>
                        </div></td>
                    <td>&nbsp;
                        </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</td>
</table>
<script>
    $('#cboProdUnit').change(function(e){
        $('#cboArea').html('');   // clear the existing options
        postRequest(
            "../common/getcodelist.php",
            {table:"LO_AREA",id:"",parent_field:"production_unit_id",parent_value:$(this).val()},
            function(data) {
                $('#cboArea').html(data);
                $('#cboArea').change();
            }
        );
    });
    $('#cboArea').change(function(e){
        $('#cboFacility').html('');   // clear the existing options
        postRequest(
            "../common/getcodelist.php",
            {table:"FACILITY",id:"",parent_field:"area_id",parent_value:$(this).val()},
            function(data) {
                $('#cboFacility').html(data);
                $('#cboFacility').change();
            }
        );
    });

    $('#cboFacility').change(function(e){
		currentObjectID=null;
        $('#cboObjType').change();
    });

    $('#cboObjType').change(function(e){
        $('#cboObjs').html('');   // clear the existing options
        $('#txtObjType').html($("#cboObjType option:selected").text());
        postRequest(
            "../common/getcodelist.php",
            {objtype:$(this).val(), facility_id:$('#cboFacility').val(),current_id:currentObjectID},
            function(data) {
				var arrs=data.split("!@#$");
				if(arrs.length>1)
				{
	                $('#cboObjs').html(arrs[0]);
					$('#cboFacility').html(arrs[1]);
					if(arrs.length>2) $('#cboArea').html(arrs[2]);
					if(arrs.length>3) $('#cboProdUnit').html(arrs[3]);
				}
				else
	                $('#cboObjs').html(data);
            }
        );
    });
</script>
<div id='boxtaskconfig' style="display:none">
	<div id="taskconfig" style="overflow-y:auto;height:330px; padding:5px;">
	</div>
</div>
<div id="boxSavedDiagrams" style="display:none">
	<div id="listSavedDiagrams" style="overflow-y:auto;height:330px; padding:5px;">
	</div>
</div>
<div style="text-align:center;padding:0px;color:#666"><font face="Arial" size="1">Copyright &copy; 2016 eDataViz LLC</font></div>
</div>
<script>
var func_code="<?php echo $RIGHT_CODE; ?>";
$(document).ready(function(){
	$("#pageheader").load("../home/header.php?menu=visual");
	$("#tdShowHideToolBox").click(function(){
		if($("#tdToolBox").is(":visible"))
		{
			$("#tdToolBox").hide();
			$("#imgShowHideToolBox").attr("src","images/arrow_right.png");
			$("#graph").css("width",$(window).width()-30);
		}
		else
		{
			$("#tdToolBox").show();
			$("#imgShowHideToolBox").attr("src","images/arrow_left.png");
			$("#graph").css("width",$(window).width()-$("#tdToolBox").width()-30);
		}
	});

	$("#outlineContainer").css("height",$(window).height()-$("#tdToolBox").height()-70);
	$("#graph").css("width",$(window).width()-$("#tdToolBox").width()-30);
	$("#graph").css("height",$(window).height()-170);
})	
</script>
</body>
</html>
