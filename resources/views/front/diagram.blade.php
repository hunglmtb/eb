<?php
$currentSubmenu = '/diagram';

$cur_diagram_id = 0;
?>

@extends('core.bsdiagram')

@section('content')

<script type="text/javascript">

$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	});

	$('#cboProdUnit').on('change', function() {
		diagram.change('cboProdUnit');
// 		diagram.change('cboArea');
// 		diagram.change('cboFacility');
	});

	$('#cboArea').on('change', function() {
		diagram.change('cboArea');
	});

	$('select').on('change', function() {
		if(this.id+"" === "cboFacility"){
			diagram.change('cboFacility');
		}
	});

	$('#surveillanceSetting').css('height', 'auto');

	$("#tdShowHideToolBox").click(function(){
		if($("#tdToolBox").is(":visible"))
		{
			$("#tdToolBox").hide();
			$("#imgShowHideToolBox").attr("src","/images/arrow_right.png");
			$("#graph").css("width",$(window).width()-30);
		}
		else
		{
			$("#tdToolBox").show();
			$("#imgShowHideToolBox").attr("src","/images/arrow_left.png");
			$("#graph").css("width",$(window).width()-$("#tdToolBox").width()-30);
		}
	});

	$("#Qoccurdate" ).datepicker({
		changeMonth	:true,
		changeYear	:true,
		dateFormat	:jsFormat
	}); 

	$("[name=RR]").change(function()
		{
			var visibleLabel=$("#fp2").prop('checked');
			
			//Get all cells
			var cells=ed.graph.model.cells; //ed.graph.model.getChildVertices(ed.graph.getDefaultParent());
			for(c in cells)
			{
				var id=cells[c].getId();
				if(id.substr(0,5)=='label')
					cells[c].setVisible(visibleLabel);
			}
			ed.graph.refresh();
	});

	$('.sur_tabs_holder').skinableTabs({
		effect: 'basic_display',
		skin: 'skin4',
		position: 'top'
	});

	$("#sur_flow_phase").html($("#Qflowphase").html());	

	$("#outlineContainer").css("height",150);	
});

var diagram = {

		change: function(id){
			var table = ""
			var cboSet = "";
			var value = -1;
			var keysearch = "";
				
			if(id == "cboProdUnit"){
				table = "LoArea";
				cboSet = "cboArea";
				keysearch = 'PRODUCTION_UNIT_ID';
				value =  $('#'+id).val();
			}

			if(id == "cboArea"){
				table = "Facility";
				cboSet = "cboFacility";
				keysearch = 'AREA_ID';
				value =  $('#'+id).val();
			}

			if(id == "cboFacility" || id == "cboObjType"){			
				table = $("#cboObjType option:selected").text();
				cboSet = "cboObjs";
				keysearch = 'FACILITY_ID';
				value =  $('#cboFacility').val();

				$('#txtObjType').text(table);
			}		

			if(table != ""){
				param = {
						'TABLE' : table,
						'value': value,
						'keysearch' : keysearch
				}
				
				$("#"+cboSet).prop("disabled", true);  
				sendAjax('/onChangeObj', param, function(data){
					diagram.loadCbo(cboSet, data);
					if(cboSet=="cboArea") diagram.change("cboArea");
					else if(cboSet=="cboFacility") diagram.change("cboFacility"); 
				});
			}
		},
		
		loadCbo : function(id, data){
			var cbo = '';
			$('#'+id).html(cbo);
			for(var v in data){
				cbo +='<option value="'+data[v].ID+'">'+data[v].NAME+'</option>';
			}

			$('#'+id).html(cbo);
			$("#"+id).prop("disabled", false);  

			if(id == "cboObjs"){
				if(currentObjectMapping.getAttribute('object_id') > 0){
					$("#cboObjs").val(currentObjectMapping.getAttribute('object_id'));
				}
			}
		},

		loadSurveillanceSetting : function(data){
			var strCbo = '';
			var strCheck = '';
			var strConnection = '';
			var strTag = '';
			var cfgFieldProps = data.cfgFieldProps;
			var intConnection = data.intConnection;
			var tags = data.tags;

			$('#sur_fields').html(strCheck);
			$('#sur_fields_select').html(strCbo);
			$('#cboConnection').html(strConnection);
			if(tags.length > 0){
				$('#sur_tag_content').html(strTag);
			}else{
				$('#sur_tag_content').html(data.strMessage);
			}

			if(cfgFieldProps.length > 0){
				for(var v in cfgFieldProps){
					strCheck +='<input type="checkbox" style="width:18px; height:15px;" surveilance_settings="' + cfgFieldProps[v].TABLE_NAME + '/' + cfgFieldProps[v].COLUMN_NAME +'" '+ cfgFieldProps[v].CHECK+' value="'+checkValue(cfgFieldProps[v].LABEL, cfgFieldProps[v].TABLE_NAME + '/' + cfgFieldProps[v].COLUMN_NAME) + '">'+ cfgFieldProps[v].TABLE_NAME +'.<font color="#378de5"><b>'+cfgFieldProps[v].COLUMN_NAME+'</b></font>('+checkValue(cfgFieldProps[v].LABEL,'')+') <br>';
					strCbo +='<option value="' + checkValue(cfgFieldProps[v].TABLE_NAME + '/' + cfgFieldProps[v].COLUMN_NAME, '') +'">'+ checkValue(cfgFieldProps[v].TABLE_NAME + '/' + cfgFieldProps[v].COLUMN_NAME, '') +'</option>';
				}
			}

			if(intConnection.length > 0){
				for(var z in intConnection){
					strConnection +='<option value="' + intConnection[z].ID +'">'+ intConnection[z].NAME +'</option>';
				}
			}

			if(tags.length > 0){
				for(var x in tags){
					strTag +='<input type="checkbox" style="width:18px; height:15px;" surveilance_settings="'+ tags[x].TAG_ID +'"'+ tags[x].CHECK+' value='+tags[x].TAG_ID +'>'+tags[x].TAG_ID +'<br>';
				}
			}

			$('#sur_fields').html(strCheck);
			$('#sur_fields_select').html(strCbo);
			$('#cboConnection').html(strConnection);
			
			if(tags.length > 0){
				$('#sur_tag_content').html(strTag);
			}else{
				$('#sur_tag_content').html(data.strMessage);
			}

			$('#openSurveillanceSetting').click(function(){
				$('#surveillanceSetting').dialog('close');
				showObjectMapping();
			})

			var phase_config=currentObjectMapping.getAttribute('sur_phase_config');
			var conn_id=currentObjectMapping.getAttribute('conn_id');

			if(conn_id != 0){
				$('#cboConnection').val(conn_id);
			}
			if(phase_config+""!="undefined"){
				var cfgs=phase_config.split("!!");
				if(cfgs.length>1){
					$("#sur_fields_select").val(cfgs[1]);
				}
				cfgs=cfgs[0].split("@@");
				$('#sur_phase_list').html('');
				for (i = 0; i < cfgs.length; i++) {
					var attrs=cfgs[i].split("^^");
					if(attrs.length>=4){
						var phase_id=attrs[0], phase_name=attrs[1], prefix=attrs[2], subfix=attrs[3];
						$('#sur_phase_list').append('<div class="sur_phase_item" sur="'+phase_id+'^^'+phase_name+'^^'+prefix+'^^'+subfix+'" id="sur_phase_id_'+phase_id+'">['+prefix+'] '+phase_name+' ['+subfix+'] &nbsp;&nbsp;&nbsp;(<a href="javascript:diagram.del_sur_phase('+phase_id+')">Remove</a>)</div>');

					}
				}
			}

			$('#btnTagsMapping').click(function(){
				window.open("/tagsMapping", '_blank');
			});
			
		},

		add_sur_phase : function(){
			var phase_id, phase_name, prefix, subfix;
			phase_id=$('#sur_flow_phase').val();
			if($('#sur_phase_id_'+phase_id).length){
				$('#sur_phase_id_'+phase_id).effect("highlight", {}, 2000);
				return;
			}
			phase_name=$("#sur_flow_phase option:selected").text();
			prefix=$('#txt_sur_phase_prefix').val();
			subfix=$('#txt_sur_phase_subfix').val();
			$('#sur_phase_list').append('<div class="sur_phase_item" sur="'+phase_id+'^^'+phase_name+'^^'+prefix+'^^'+subfix+'" id="sur_phase_id_'+phase_id+'">['+prefix+'] '+phase_name+' ['+subfix+'] &nbsp;&nbsp;&nbsp;(<a href="javascript:diagram.del_sur_phase('+phase_id+')">Remove</a>)</div>');
		},
		
		del_sur_phase : function(phase_id){
			$('#sur_phase_id_'+phase_id).remove();
		},

		exportImage : function(type){
			saveSvgAsPng($('#graph svg')[0], 'diagram.png');
		}
}

var ed;
function onInit(editor)
{
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
    mxConnectionHandler.prototype.connectImage = new mxImage('/images/connector.gif', 16, 16);

    // Enables connections in the graph and disables
    // reset of zoom and translate on root change
    // (ie. switch between XML and graphical mode).
    editor.graph.setConnectable(true);

    editor.graph.setPanning(true);
    //editor.graph.panningHandler.useLeftButtonForPanning = true;


    // Clones the source if new connection has no target
    editor.graph.connectionHandler.setCreateTarget(false);
    editor.graph.setAllowDanglingEdges(false);

    var cellAddedListener = function(sender, evt)
    {
        var cells = evt.getProperty('cells');
        var cell = cells[0];
        if(editor.graph.isSwimlane(cell)){
            var DiagramName = mxUtils.prompt('Enter subnetwork name', 'Subnetwork');
            if(!DiagramName)
            {
                editor.graph.removeCells([cell]);
                return;
            }
            cell.setAttribute("label",DiagramName);
            addSubnetworkListItem(cell);
        }
    };

    var cellRemovedListener=function(sender, evt)
    {
        updateSubnetworksList();
    }
    editor.graph.addListener(mxEvent.CELLS_ADDED, cellAddedListener);
    editor.graph.addListener(mxEvent.CELLS_MOVED, cellMovedListener);
    editor.graph.addListener(mxEvent.CELLS_REMOVED, cellRemovedListener);

    // Updates the title if the root changes
    var title = document.getElementById('title');

    if (title != null)
    {
        var f = function(sender)
        {
            title.innerHTML = 'mxDraw - ' + sender.getTitle();
        };

        editor.addListener(mxEvent.ROOT, f);
        f(editor);
    }

    // Changes the zoom on mouseWheel events
    mxEvent.addMouseWheelListener(function (evt, up)
    {
        if (!mxEvent.isConsumed(evt))
        {
            if (up)
            {
                editor.execute('zoomIn');
            }
            else
            {
                editor.execute('zoomOut');
            }

            mxEvent.consume(evt);
        }
    });

    // Defines a new action to switch between
    // XML and graphical display
    var textNode = document.getElementById('xml');
    var graphNode = editor.graph.container;
    var sourceInput = document.getElementById('source');
    sourceInput.checked = false;

    var funct = function(editor)
    {
        if (sourceInput.checked)
        {
            graphNode.style.display = 'none';
            textNode.style.display = 'inline';

            var enc = new mxCodec();
            var node = enc.encode(editor.graph.getModel());

            textNode.value = mxUtils.getPrettyXml(node);
            textNode.originalValue = textNode.value;
            textNode.focus();
        }
        else
        {
            graphNode.style.display = '';

            if (textNode.value != textNode.originalValue)
            {
                var doc = mxUtils.parseXml(textNode.value);
                var dec = new mxCodec(doc);
                dec.decode(doc.documentElement, editor.graph.getModel());
            }

            textNode.originalValue = null;

            // Makes sure nothing is selected in IE
            if (mxClient.IS_IE)
            {
                mxUtils.clearSelection();
            }

            textNode.style.display = 'none';

            // Moves the focus back to the graph
            textNode.blur();
            editor.graph.container.focus();
        }
    };

    editor.addAction('switchView', funct);

    // Defines a new action to switch between
    // XML and graphical display
    mxEvent.addListener(sourceInput, 'click', function()
    {
        editor.execute('switchView');
    });

    // Create select actions in page
    var node = document.getElementById('mainActions');
    var buttons = ['group', 'ungroup', 'cut', 'copy', 'paste', 'delete', 'undo', 'redo'];

    // Only adds image and SVG export if backend is available
    // NOTE: The old image export in mxEditor is not used, the urlImage is used for the new export.
    if (editor.urlImage != null)
    {
        // Client-side code for image export
        var exportImage = function(editor)
        {
            var graph = editor.graph;
            var scale = graph.view.scale;
            var bounds = graph.getGraphBounds();

            // New image export
            var xmlDoc = mxUtils.createXmlDocument();
            var root = xmlDoc.createElement('output');
            xmlDoc.appendChild(root);

            // Renders graph. Offset will be multiplied with state's scale when painting state.
            var xmlCanvas = new mxXmlCanvas2D(root);
            xmlCanvas.translate(Math.floor(1 / scale - bounds.x), Math.floor(1 / scale - bounds.y));
            xmlCanvas.scale(scale);

            var imgExport = new mxImageExport();
            imgExport.drawState(graph.getView().getState(graph.model.root), xmlCanvas);

            // Puts request data together
            var w = Math.ceil(bounds.width * scale + 2);
            var h = Math.ceil(bounds.height * scale + 2);
            var xml = mxUtils.getXml(root);

            // Requests image if request is valid
            if (w > 0 && h > 0)
            {
                var name = 'export.png';
                var format = 'png';
                var bg = '&bg=#FFFFFF';

                new mxXmlRequest(editor.urlImage, 'filename=' + name + '&format=' + format +
                    bg + '&w=' + w + '&h=' + h + '&xml=' + encodeURIComponent(xml)).
                    simulate(document, '_blank');
            }
        };

        editor.addAction('exportImage', exportImage);

        // Client-side code for SVG export
        var exportSvg = function(editor)
        {
            var graph = editor.graph;
            var scale = graph.view.scale;
            var bounds = graph.getGraphBounds();

            // Prepares SVG document that holds the output
            var svgDoc = mxUtils.createXmlDocument();
            var root = (svgDoc.createElementNS != null) ?
                svgDoc.createElementNS(mxConstants.NS_SVG, 'svg') : svgDoc.createElement('svg');

            if (root.style != null)
            {
                root.style.backgroundColor = '#FFFFFF';
            }
            else
            {
                root.setAttribute('style', 'background-color:#FFFFFF');
            }

            if (svgDoc.createElementNS == null)
            {
                root.setAttribute('xmlns', mxConstants.NS_SVG);
            }

            root.setAttribute('width', Math.ceil(bounds.width * scale + 2) + 'px');
            root.setAttribute('height', Math.ceil(bounds.height * scale + 2) + 'px');
            root.setAttribute('xmlns:xlink', mxConstants.NS_XLINK);
            root.setAttribute('version', '1.1');

            // Adds group for anti-aliasing via transform
            var group = (svgDoc.createElementNS != null) ?
                svgDoc.createElementNS(mxConstants.NS_SVG, 'g') : svgDoc.createElement('g');
            group.setAttribute('transform', 'translate(0.5,0.5)');
            root.appendChild(group);
            svgDoc.appendChild(root);

            // Renders graph. Offset will be multiplied with state's scale when painting state.
            var svgCanvas = new mxSvgCanvas2D(group);
            svgCanvas.translate(Math.floor(1 / scale - bounds.x), Math.floor(1 / scale - bounds.y));
            svgCanvas.scale(scale);

            var imgExport = new mxImageExport();
            imgExport.drawState(graph.getView().getState(graph.model.root), svgCanvas);

            var name = 'export.svg';
            var xml = encodeURIComponent(mxUtils.getXml(root));

            new mxXmlRequest(editor.urlEcho, 'filename=' + name + '&format=svg' + '&xml=' + xml).simulate(document, "_blank");
        };

        editor.addAction('exportSvg', exportSvg);

        buttons.push('exportImage');
        buttons.push('exportSvg');
    };

    //Begin: Them combo thay doi stroke color
    var colors = ['','red','green','blue'];
    var selectTag = document.createElement('select');
    node.appendChild(selectTag);
    var factoryColor = function(){
        return function(){
            var color = selectTag.options[selectTag.selectedIndex].value;
            editor.graph.model.beginUpdate();
            try
            {
                //alert(editor.graph.model.cells.);
                //if(!editor.graph.selectionModel.cells[0].isEdge()) return;
                var c;
                for (c in editor.graph.selectionModel.cells)
                {
                    if(editor.graph.selectionModel.cells[c].isEdge())
                    {
                        if (color=='') color='black';
                        //mxUtils.setCellStyles(editor.graph.model, [editor.graph.selectionModel.cells[c]],"strokeColor", color);
                        editor.graph.setCellStyles("strokeColor", color, [editor.graph.selectionModel.cells[c]]);

                    }
                }
                /*
                 if (color!='')
                 editor.graph.setCellStyles("strokeColor", color);
                 else
                 editor.graph.setCellStyles("strokeColor", 'black');
                 */
            }
            finally
            {
                editor.graph.model.endUpdate();
            }
        };
    };
    mxEvent.addListener(selectTag, 'change', factoryColor());
    for (var i=0;i<colors.length;i++)
    {
        var colorOption = document.createElement('option');
        mxUtils.write(colorOption, mxResources.get(colors[i]));
        colorOption.style.background = colors[i]
        colorOption.innerHTML="<span>"+colors[i]+"</span>";
        selectTag.appendChild(colorOption);
    }
    //End: Them combo thay doi stroke color

    for (var i = 0; i < buttons.length; i++)
    {
        var button = document.createElement('button');
        mxUtils.write(button, mxResources.get(buttons[i]));
        button.innerHTML=buttons[i];

        var factory = function(name)
        {
            return function()
            {
                //alert(name);
                editor.execute(name);
            };
        };

        mxEvent.addListener(button, 'click', factory(buttons[i]));
        node.appendChild(button);
    }

    // Create select actions in page
    var node = document.getElementById('selectActions');
    /*
     mxUtils.write(node, 'Select: ');
     mxUtils.linkAction(node, 'All', editor, 'selectAll');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'None', editor, 'selectNone');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'Vertices', editor, 'selectVertices');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'Edges', editor, 'selectEdges');
     */

    // Create select actions in page
    /*
     var node = document.getElementById('zoomActions');
     mxUtils.write(node, 'Zoom: ');
     mxUtils.linkAction(node, 'In', editor, 'zoomIn');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'Out', editor, 'zoomOut');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'Actual', editor, 'actualSize');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'Fit', editor, 'fit');
     */

    //load diagram
    //loadSavedDiagram();

    //outlineContainer
    if(!outline)
    {
        outline = document.getElementById('outlineContainer');
        if (mxClient.IS_IE)
        {
            new mxDivResizer(outline);
        }

        // Creates the outline (navigator, overview) for moving
        // around the graph in the top, right corner of the window.
        var outln = new mxOutline(editor.graph, outline);
    }

    //loadListSavedDiagrams();
}

function buttonActionClick(act){
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
    else{
    	if(act=="rotate"){
    		ed.graph.toggleCellStyles(mxConstants.STYLE_HORIZONTAL,"1",ed.graph.selectionModel.cells);
    	}else{ 
        	ed.execute(act);
    	}
    } 
}

function changeLineColor(color)
{
    ed.graph.model.beginUpdate();
    try
    {
        var c;
        for (c in ed.graph.selectionModel.cells)
        {
            if(ed.graph.selectionModel.cells[c].isEdge())
            {
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

function cellMovedListener(sender, evt){
	var cells = evt.getProperty('cells');
 	for (i = 0; i < cells.length; i++) {
	  	var cell=cells[i];
	  	if(cell.id!==null)
	  	if(cell.id.substr(0,8)=='sur_val_'){
	   		updateSurPhaseCellPosition(cell);
	  	}
 	}
	 //ed.graph.model.endUpdate();
	 ed.graph.refresh();
}

function updateSurPhaseCellPosition(baseCell){
	var ind=Number(baseCell.getAttribute("sur_phase_index"));
	var id1=baseCell.id.substr(0,baseCell.id.lastIndexOf('_')+1);
	for(i=0;i<30;i++){
		var cell=ed.graph.model.getCell(id1+i);
		if(typeof cell!=='undefined'){
			var ind2=Number(cell.getAttribute("sur_phase_index"));
			if(ind2!=ind){
				cell.geometry.y=baseCell.geometry.y+(ind2-ind)*baseCell.geometry.height;
				cell.geometry.x=baseCell.geometry.x;
			}
		}
	}
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

function addSubnetworkListItem(cell)
{
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

function updateSubnetworksList()
{
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

function clearGraph()
{
    ed.graph.removeCells(ed.graph.getChildVertices(ed.graph.getDefaultParent()));
}

var outline;
function deleteDiagram(sId)
{
    if(!confirm("Are you really want to delete this diagram?")) return;

    param = {
			'ID' : sId
	}

	sendAjax('/deletediagram', param, function(data){
		var str = "";
		if(data.length > 0){
			$("#listSavedDiagrams").html(str);
			for(var v in data){
				str+="<a href=\"javascript:loadSavedDiagram('"+data[v].NAME+"','"+data[v].ID+"')\">"+data[v].NAME+"</a>&nbsp;<a href=\"javascript:deleteDiagram('"+data[v].ID+"')\"><font size='1' color='#ff0000'>[Delete]</font></a>&nbsp;<br>";
			}

			$("#listSavedDiagrams").html(str);
		}
	});
}

function hideBoxDiagrams()
{
	$("#listSavedDiagrams").dialog("close");
}

function loadSavedDiagram(sName,sId)
{
    hideBoxDiagrams();
	if(showWaiting) showWaiting();
	
    mxUtils.get("loaddiagram/" + sId, 
		function(req){
			if(hideWaiting) hideWaiting();
			clearGraph();
			var node = req.getDocumentElement();

			var dec = new mxCodec(node.ownerDocument);
			dec.decode(node, ed.graph.getModel());

			ed.graph.refresh();
			updateSubnetworksList();

			setCurrentDiagramName(sName);
			setCurrentDiagramId(sId);

			var c;
			for (c in ed.graph.model.cells)
			{
				var cell=ed.graph.model.cells[c];
				if(ed.graph.isSwimlane(cell))
				{
					addSubnetworkListItem(cell);
				}
			}

		},
		function(){
			if(hideWaiting) hideWaiting();
			alert('Error loading saved diagram!');
		}
	);
}

var defaultDiagramName="[Untitled Diagram]";
var defaultDiagramId= 0;
var currentDiagramName=defaultDiagramName;
var currentDiagramId = defaultDiagramId;

function setCurrentDiagramName(s)
{
    currentDiagramName=s;
    document.getElementById("diagramName").innerHTML=currentDiagramName;
}

function setCurrentDiagramId(i)
{
    currentDiagramId = i;
}

function display()
{
	//Get all cells
	var cells=ed.graph.model.cells; //getChildVertices(ed.graph.getDefaultParent());
	var occur_date=$("#Qoccurdate").val();
	var flow_phase=$("#Qflowphase").val();
	var vparam="";
	var sData = [];	
	for(c in cells)
	{
		var cell=cells[c];
		if(cell.getId().substr(0,5)=='label') continue;			//skip label
		
		var su=cell.getAttribute('surveillance')+"";
		su=su.trim();
		var sur_phase_config=cell.getAttribute('sur_phase_config')+"";
		sur_phase_config=sur_phase_config.trim();
		if(sur_phase_config=="undefined") sur_phase_config="";
		if(su=="undefined") su="";

		if(su+sur_phase_config=="") continue;
		var conn_id=cell.getAttribute('conn_id');
		if(conn_id=="undefined") conn_id=""; 

		var v = {
			'ID' : cell.getId(),
			'OBJECT_TYPE' : cell.getAttribute('object_type'),
			'OBJECT_ID' : cell.getAttribute('object_id'),
			'CONN_ID' : conn_id,
			'SUR_PHASE_CONFIG' : sur_phase_config,
			'SU' : su
		}

		sData.push(v); 
		//vparam += (vparam==""?"":"#")+cell.getId()+"~"+su+"~"+cell.getAttribute('object_type')+"~"+cell.getAttribute('object_id')+"~"+conn_id+"~"+sur_phase_config;
	}
	if(sData!="")
	{
		param = {
			'vparam':sData,
			'occur_date':occur_date,
			'flow_phase':flow_phase
		}

		sendAjax('/getValueSurveillance', param, function(data){
			if(data.substr(0,2)!='ok')
			{
				alert(data);
				return;
			}
			var arrs=data.substr(2).split("#");
			var i = 0;
			for (; i < arrs.length; i++)
			{ 
				var vs=arrs[i].split("^");
				if(vs.length>1){
					if(vs[1]=="%SF"){
						if(vs.length>2){
							var arrfs=vs[2].split("%SV");
							for(var j=0;j<arrfs.length-1;j+=2){
								label=ed.graph.model.getCell('sur_val_'+vs[0]+'_'+arrfs[j]);
								if(typeof label!=='undefined'){
									var phase_name=label.getAttribute('phase_name', '');
									var prefix=label.getAttribute('prefix', '');
									var subfix=label.getAttribute('subfix', '');
									label.setAttribute('label', (prefix==""?phase_name:prefix)+': '+arrfs[j+1]+' '+subfix);
								}
							}
						}
					}
					else{
						var label=ed.graph.model.getCell('label_'+vs[0]);
						if(typeof label!=='undefined'){
							if(vs[1]!="%SF")
								label.setAttribute('label', vs[1]);
						}
					}
				}
			}
			ed.graph.refresh();
		   }); 
	}
}

function showBoxDiagrams(){
	$( "#listSavedDiagrams" ).dialog({
			height: 400,
			width: 450,
			modal: true,
			title: "Select a network diagram",
		});
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

function loadDiagram()
{
	$("#listSavedDiagrams").html("Loading...");
	showBoxDiagrams();

	param = {
			'ID' : -100
	}

	sendAjax('/getdiagram', param, function(data){
		var str = "";
		if(data.length > 0){
			$("#listSavedDiagrams").html(str);
			for(var v in data){
				str+="<a href=\"javascript:loadSavedDiagram('"+data[v].NAME+"','"+data[v].ID+"')\">"+data[v].NAME+"</a>&nbsp;<a href=\"javascript:deleteDiagram('"+data[v].ID+"')\"><font size='1' color='#ff0000'>[Delete]</font></a>&nbsp;<br>";
			}

			$("#listSavedDiagrams").html(str);
		}
	});
}



function newDiagram()
{
    clearGraph();
	setCurrentDiagramId(0);
    setCurrentDiagramName(defaultDiagramName);
}

function saveDiagram(saveAs)
{
    try
    {
		if(saveAs) currentDiagramId=0;
        if(currentDiagramName=="" || !currentDiagramName || currentDiagramName ==defaultDiagramName || saveAs)
            currentDiagramName=mxUtils.prompt('Enter Diagram name', 'Untitled Diagram');
        if(currentDiagramName=="" || !currentDiagramName)
        {
            return;
        }
        setCurrentDiagramName(currentDiagramName);
        document.getElementById("buttonSave_text").innerHTML="Saving...";
        var enc = new mxCodec();
        var node = enc.encode(ed.graph.getModel());

        param = {	
            'ID':currentDiagramId,
            'NAME':currentDiagramName,
            'KEY':encodeURIComponent(mxUtils.getPrettyXml(node))
         }

    	sendAjax('/savediagram', param, function(data){
    		document.getElementById("buttonSave_text").innerHTML="Save";
			currentDiagramId=data;
    	});	
    }
    catch(err)
    {
        alert(err.message);
    }
}

window.onbeforeunload = function() { return mxResources.get('changesLost'); };
</script>
<body onLoad="new mxApplication('/config/diagrameditor.xml?6');"
	style="margin: 0px; background: #eeeeee;">
	
	
	<div id="box_cell_image" style="display: none">
			<span id="box_cell_image_input"> <br> Input image URL <input
				type="text" id="txt_cell_image_url" style="width: 470px"> <br> <br>
				or Upload from your computer <input type="file" name="files[]" multiple id="file_cell_image_url" style="width: 390px"> <br> <br> or <input
				type="button" onclick="pick_cell_image()"
				value="Pick available image">
			</span>
			<div id="box_pick_cell"	style="display: none; width: 100%; height: 100%"></div>
	</div>

	<div id="surveillanceSetting"
		style="display: none; padding: 0; margin: 5px">
		<div class="sur_tabs_holder">
			<ul>
				<li><a href="#sur_fields">Fields</a></li>
				<li><a href="#sur_tag">Tags</a></li>
				<li><a href="#sur_phase">Phase config</a></li>
			</ul>
			<div class="sur_tab_content_holder" style="margin: 5px">
				<div id="sur_fields"
					style="overflow-y: auto; height: 318px; line-height: 18px"></div>
				<div id="sur_tag" style="height: 320px">
					<div id="sur_tag_content"
						style="overflow-y: auto; height: 250px; line-height: 18px"></div>
					<div
						style="position: absolute; left: 0px; bottom: 0px; width: 100%; height: 70px; background: #e8e8e8; padding: 10px 5px; box-sizing: border-box">
						<table>
							<tr>
								<td width="100"><b>Connection</b></td>
								<td><select id="cboConnection" style="min-width: 185px"></select></td>
							</tr>
							<tr>
								<td><b>Other tags</b></td>
								<td><input type="text" id="txt_sur_other_tag"
									style="width: 370px; height: 18px;"></td>
							</tr>
						</table>
					</div>
				</div>
				<div id="sur_phase"
					style="overflow-y: auto; height: 300px; line-height: 18px; padding: 10px;">
					Data field <select id="sur_fields_select"></select>
					<table>
						<tr>
							<td>Flow phase</td>
							<td>Prefix</td>
							<td>Subfix</td>
							<td></td>
						</tr>
						<tr>
							<td><select id="sur_flow_phase"></select></td>
							<td><input id="txt_sur_phase_prefix"
								style="width: 100px; height: 18px;"></td>
							<td><input id="txt_sur_phase_subfix"
								style="width: 100px; height: 18px;"></td>
							<td><input type="button" style="width: 60px; height: 16px;"
								value="Add" onclick="diagram.add_sur_phase()"></input></td>
						</tr>
					</table>
					<hr>
					<b>Added flow phases:</b>
					<div id="sur_phase_list"></div>
				</div>
			</div>
		</div>
	</div>
	<td valign="top" align="center">
		<!-- Object mapping -->
		<div id="objectMapping" style="display: none">

			<br>
			<table border="0" cellpadding="3" id="table2">
				<tr>
					<td style=""><b style=""> <font size="2">Production Unit</font></b></td>
					<td style=""><b style=""> <font size="2">Area</font></b></td>
					<td style=""><b style=""> <font size="2">Facility</font></b></td>
				</tr>
				<tr>
					<td width="140" style=""><select style="width: 100%;"
						id="cboProdUnit" size="1" name="cboProdUnit">
							@foreach($loProductionUnit as $lo)
							<option value="{!!$lo->ID!!}">{!!$lo->NAME!!}</option>
							@endforeach
					</select></td>
					<td width="140" style=""><select style="width: 100%;" id="cboArea"
						onchange="diagram.change('cboArea');" size="1" name="cboArea">
							@foreach($loArea as $area)
							<option value="{!!$area->ID!!}">{!!$area->NAME!!}</option>
							@endforeach
					</select></td>
					<td width="140" style=""><select style="width: 100%;"
						onchange="diagram.change('cboFacility');" id="cboFacility"
						size="1" name="cboFacility"> @foreach($facility as $fa)
							<option value="{!!$fa->ID!!}">{!!$fa->NAME!!}</option>
							@endforeach
					</select></td>
				</tr>
			</table>
			<br>
			<table border="0" cellpadding="0" cellspacing="4" width="400"
				id="table1">
				<tr>
					<td>Object type</td>
					<td width="250"><select style="width: 240px;" id="cboObjType"
						size="1" name="cboObjType" onchange="diagram.change('cboObjType')"> @foreach($intObjectType as $iot)
							<option value="{!!$iot->CODE!!}">{!!$iot->NAME!!}</option>
							@endforeach
					</select></td>
				</tr>
				<tr>
					<td id="txtObjType">Flow</td>
					<td><select style="width: 240px;" id="cboObjs" size="1"
						name="cboObjs"> @foreach($type as $t)
							<option value="{!!$t->ID!!}">{!!$t->NAME!!}</option> @endforeach
					</select></td>
				</tr>
				<tr id="flow_direction_tr" style="display: none">
					<td>Flow Direction</td>
					<td><input type="radio" value="in" name="flow_direction"
						id="fpdir1">In &nbsp; <input type="radio" value="out"
						name="flow_direction" id="fpdir2" checked>Out</td>
				</tr>
			</table>
		</div>

		<table border="0" cellpadding="0" cellspacing="0" id="table1"
			width="100%">
			<tr>
				<td style="display: none" height="20">
					<div style="display: none" id="header_">&nbsp;</div>



					<div id="mainActions"
						style="display: none; width: 100%; padding-top: 8px; padding-left: 24px; padding-bottom: 8px;">
					</div>
					<div style="display: none; float: right; padding-right: 36px;">
						<input id="source" type="checkbox" />Source
					</div>
					<div id="selectActions"
						style="display: none; width: 100%; padding-left: 54px; padding-bottom: 4px;">
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="0" cellspacing="0" id="table2"
						width="100%">
						<tr>
							<td style="border: none; width: 280px;"><span
								style="font-size: 10pt; padding-left: 10px;" id="diagramName">[Untitled
									Diagram]</span></td>
							<td>

								<table border="0" cellpadding="0" id="table17" cellspacing="4"
									height="30">
									<tr>
										<td onClick="newDiagram()" width="60" class="xbutton">New</td>
										<td onClick="loadDiagram()" width="60" class="xbutton">Load</td>
										<td id="buttonSave" onMouseOut="$('#buttonSaveAs').hide();"
											onMouseOver="$('#buttonSaveAs').show();" width="60"
											class="xbutton">
											<span class="xbutton" id="buttonSave_text" onClick="saveDiagram()">Save</span>
											<div class="xbutton"
												style="padding: 5px; display: none; position: absolute; width: 64px; z-index: 101; margin-left: 0px; margin-top: 4px; border: 2px solid #666"
												id="buttonSaveAs">
												<span class="xbutton" onClick="saveDiagram('a')">Save As</span>
											</div></td>
										<td onClick="buttonActionClick('print')" width="60"
											class="xbutton">Print</td>
										<td style="display: none"
											onClick="buttonActionClick('exportImage')" width="60"
											class="xbutton">Export</td>
										<td align="right" width="70"><span style="font-size: 8pt">
												Flowline</span></td>
										<td onClick="changeLineColor('red')" width="40"
											class="xbutton" style="background-color: #FF0000">Gas</td>
										<td onClick="changeLineColor('blue')" width="40"
											class="xbutton" style="background-color: #0066CC">Water</td>
										<td onClick="changeLineColor('#CC6600')" width="40"
											class="xbutton" style="background-color: #CC6600">Oil</td>
										<td style="display:; text-align: center" width="100"
											class="xbutton"><span
											onClick="$('#boxSubnetworks').toggle();">Subnetworks</span>
											<div
												style="display: none; position: absolute; width: 174px; height: 133px; z-index: 100; margin-left: -0px; margin-top: 5px; border: 2px solid #666"
												id="boxSubnetworks">
												<table border="0" cellpadding="0" cellspacing="0"
													width="100%" id="table22" height="100%">
													<tr>
														<td bgcolor="#c0c0c0" style="border: 1px solid #666"><select
															onclick="listSubnetworkClick()" id="listSubnetwork"
															style="width: 100%; height: 100%; border: 0px solid #ffffff; overflow: auto; background: #c0c0c0; font-family: Verdana; font-size: 8pt; color: #000"
															name="sometext" multiple="multiple">
														</select></td>
													</tr>
												</table>
											</div></td>
										<td width="50">&nbsp;</td>
										<td class="ebutton" onClick="buttonActionClick('copy')"><img
											src="/images/copy.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('cut')"><img
											src="/images/cut.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('paste')"><img
											src="/images/paste.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('delete')"><img
											src="/images/delete.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('undo')"><img
											src="/images/undo.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('redo')"><img
											src="/images/redo.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('rotate')"><img
											src="/images/rotate.png"></td>
										<td width="70" align="right">Export as</td>
										<td class="xbutton" width="30" onClick="diagram.exportImage()">PNG</td>
									</tr>
								</table>

							</td>
							<td>&nbsp;</td>
						</tr>
					</table>
					<table border="0" cellpadding="0" cellspacing="0" id="table21"
						width="100%">
						<tr>
							<td>&nbsp;</td>
							<td id="tdToolBox" width="260" valign="top" style="border: none">
<script>
	var filesToUpload = [];
	$('input[type=file]').on('change', prepareUpload);
	function prepareUpload(event)
	{
	  var files = event.target.files || event.originalEvent.dataTransfer.files;
	    // Itterate thru files (here I user Underscore.js function to do so).
	    // Simply user 'for loop'.
	    $.each(files, function(key, value) {
	        filesToUpload.push(value);
	    });
	}
	function pick_cell_image(){
		$("#box_cell_image_input").hide();
		$("#box_pick_cell").show();
		$("#box_pick_cell").html("Loading...");
		
		$("#box_pick_cell").html('No available image');
	}
	function set_cell_image(cell,url){
		cell.style="shape=image;html=1;verticalLabelPosition=bottom;verticalAlign=top;imageAspect=1;image="+url;
		ed.graph.refresh();
	}
	function setCellImage(cell){
		$("#box_cell_image_input").show();
		$("#box_pick_cell").hide();
		$("#txt_cell_image_url").val("");
		$("#file_cell_image_url").val("");
		$( "#box_cell_image" ).dialog({
			height: 300,
			width: 600,
			modal: true,
			title: "Set Image",
			buttons: {
				"OK": function(){
					var url=$("#txt_cell_image_url").val().trim();
					if(url!=""){
						set_cell_image(cell,url);
						$("#box_cell_image").dialog("close");
					}else{			
						if(filesToUpload){
						    var formData = new FormData();

						    // Add selected files to FormData which will be sent
						    if (filesToUpload) {
						        $.each(filesToUpload, function(key, value){
						            formData.append(key, value);
						        });        
						    }
						    
						    showWaiting("Uploading image...");
							$.ajax({
						        type: "POST",
						        url: '/uploadImg',
						        data: formData,
						        processData: false,
						        contentType: false,
						        dataType: 'json',
						        cache: false,
						        success: function(data, textStatus, jqXHR)
								{
									hideWaiting();
									if(typeof data.error === 'undefined')
									{
										// Success so call function to process the form
										set_cell_image(cell,data.files);
										filesToUpload = [];
										$("#box_cell_image").dialog("close");
									}
									else
									{
										// Handle errors here
										alert('Error: ' + data.error);
										console.log('ERRORS: ' + data.error);
									}
								},
								error: function(jqXHR, textStatus, errorThrown)
								{
									filesToUpload = [];
									hideWaiting();
									// Handle errors here
									alert('Error: ' + textStatus);
									console.log('ERRORS: ' + textStatus);
									// STOP LOADING SPINNER
								}
						        
						    });
						}
					}
				},
				"Cancel": function(){
					$("#box_cell_image").dialog("close");
					filesToUpload = [];
				}
			}
		});
	}
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
			width: 450,
			modal: true,
			title: "Object mapping",
			buttons: {
				"OK": function(){ 
					mappingObject();
					$("#objectMapping").dialog("close");
				},
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
				currentObjectMapping = ed.graph.selectionModel.cells[c];
				currentObjectID = checkValue(currentObjectMapping.getAttribute('object_id'),'');

				$("#flow_direction").val(currentObjectMapping.getAttribute('flow_direction'));
				var objtype = checkValue(currentObjectMapping.getAttribute('object_type'),'');

				if (true || objtype=='ENERGY_UNIT' || objtype=='FLOW' || objtype=='TANK' || objtype=='EQUIPMENT' || objtype=='ENERGY_UNIT_GROUP')
				{
					if(objtype != ''){
						$("#cboObjType").val(objtype);
					}
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
			height: 485,
			width: 660,
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

			param = {
				'SUR':sur,
				'OBJECT_ID' : object_id,
			   	'OBJECT_TYPE' : object_type			   	
			}
			
			sendAjax('/getSurveillanceSetting', param, function(data){
				diagram.loadSurveillanceSetting(data);
			});
			
			break;
		}
	}
	function applySurveillance(){
		var s="";
		var phase_config="";
		$("#sur_phase_list .sur_phase_item").each(function(){
			phase_config+=(phase_config==""?"":"@@")+$(this).attr('sur');
		});
		phase_config+="!!"+$("#sur_fields_select").val();
		var l=""; //lable display for cell (yellow cell)
		$("#sur_fields :checked").each(function(){
			s+=(s==""?"":"@")+$(this).attr("surveilance_settings");
			l+=(l==""?"":"\n")+$(this).val()
		});
		$("#sur_tag_content :checked").each(function(){
			s+=(s==""?"":"@")+"TAG:"+$(this).attr("surveilance_settings");
			l+=(l==""?"":"\n")+$(this).val()
		});
		var other_tag=$("#txt_sur_other_tag").val().trim();
		if(other_tag!=""){
			s+=(s==""?"":"@")+"TAG:"+other_tag;
			l+=(l==""?"":"\n")+other_tag
		}
		var rowlabel=$("#surveillanceSetting input:checked").length;
		
		if(currentObjectMapping){
			currentObjectMapping.setAttribute('surveillance',s);
			currentObjectMapping.setAttribute('sur_phase_config',phase_config);
			currentObjectMapping.setAttribute('conn_id',$("#cboConnection").val());
		}
		
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
		
		//Phase value cells
		var parr=[];
		for(i=0;i<30;i++){
			var c=ed.graph.model.getCell('sur_val_'+id+'_'+i);
			if(typeof c!=='undefined'){
				parr.push(i);
			}
		}

		if(phase_config!=""){
			var cfgs=phase_config.split("!!")[0].split("@@");
			var s_ind=0;
			for (i = 0; i < cfgs.length; i++) {
				var attrs=cfgs[i].split("^^");
				if(attrs.length>=4){
					var phase_id=attrs[0], phase_name=attrs[1], prefix=attrs[2], subfix=attrs[3];
					var i2=parr.indexOf(Number(phase_id));
					if(i2>=0){
						parr.splice(i2, 1);
					}
					s_ind++;
					var label = model.getCell('sur_val_'+id+'_'+phase_id);
					if(typeof label==='undefined'){
						model.beginUpdate();
						try
						{
							var n2 = doc.createElement('MyNode');
							n2.setAttribute('label', (prefix+""==""?phase_name:prefix) + ": -- " + subfix);
							var v1=ed.graph.insertVertex(parent, 'sur_val_'+id+'_'+phase_id, n2, cellX, cell.geometry.y+cell.geometry.height+20+(s_ind-1)*20, 100, 20);
							var fillColor="gray";
							if(phase_id==1) fillColor="#CC6600";
							if(phase_id==2) fillColor="#FF0000";
							if(phase_id==3) fillColor="#0066CC";
							v1.setStyle('text_sur;dashed=0;fontColor=white;strokeColor=black;fillColor='+fillColor+';');
							v1.setVisible(true);
							v1.setAttribute('sur_phase_index', s_ind);
							v1.setAttribute('phase_id', phase_id);
							v1.setAttribute('phase_name', phase_name);
							v1.setAttribute('prefix', prefix);
							v1.setAttribute('subfix', subfix);
							if(s_ind==1){
								var v2=cell;
								e=ed.graph.insertEdge(parent, 'sur_edge_'+id+'_'+phase_id, '', v1, v2);
								e.setStyle('dashed=1');
							}
						}
						finally
						{
						  model.endUpdate();
						}
					}
					else{
						label.setAttribute('label', (prefix+""==""?phase_name:prefix) + ": -- " + subfix);
					}
				}
			}
		}
		for (i2 = 0; i2 < parr.length; i2++){
			var c = model.getCell('sur_val_'+id+'_'+parr[i2]);
			if(typeof c!=='undefined'){
				ed.graph.removeCells([c]);
			}
		}

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
								<table border="0" cellpadding="0" cellspacing="0" width="100%"
									id="table3" height="100%">
									<tr>
										<td height="20" bgcolor="#666">
											<table border="0" cellpadding="0" width="100%" id="table10"
												cellspacing="1" height="100%">
												<tr>
													<td id="buttonShowIcons" width="46" onClick="showIcons()"
														class="tabselected" bgcolor="#959596">Icons</td>
													<td id="buttonShowProperties" class="tabnormal"
														onClick="showProperties()" width="79">&nbsp; Properties</td>
													<td>&nbsp;</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td height="300" style="border: 1px solid #666"
											bgcolor="#C0C0C0" valign="top">
											<div id="properties" style="display: none;">
												<div style="margin: 10px auto; width: 94%; height: 100%;">
													<b>Display surveillance setting</b>
													<table>
														<tr>
															<td style="width: 35%">Date</td>
															<td colspan="2"><input type="text" id="Qoccurdate"
																style="width: 150px"></td>
														</tr>
														<tr>
															<td>Flow phase</td>
															<td colspan="2"><select id="Qflowphase"
																style="width: 150px"> @foreach($codeFlowPhase as
																	$flowphase)
																	<option value="{!!$flowphase->ID!!}">{!!$flowphase->NAME
																		!!}</option> @endforeach
															</select></td>
														</tr>
														<tr>
															<td></td>
															<td style="width: 50px"></td>
															<td class="xbutton" style="height: 25px"
																onClick="display()"><span>Display</span></td>
														</tr>
													</table>
													<script>
												//$("#display").button();
											
											</script>
												</div>
											</div>
											<div id="icons"
												style="width: 260px; height: 100%; overflow: auto;">
												<div style="padding: 10px;" id="toolbar"></div>
											</div>
										</td>
									</tr>
									<tr>
										<td height="10"></td>
									</tr>
									<tr>
										<td>
											<table border="0" cellpadding="0" cellspacing="0"
												width="100%" id="table20" height="100%">
												<tr>
													<td height="15px" bgcolor="#666">
														<table border="0" cellpadding="0" width="100%"
															id="table21" cellspacing="1">
															<tr style="height: 10px;">
																<td><font size="1" color="#F8F8F8"> &nbsp;<b>Zoom</b></font></td>
																<td act="zoomIn" id="buttonZoomIn"
																	onClick="buttonActionClick('zoomIn')" width="30"
																	height="15" class="abutton">in</td>
																<td act="zoomOut" onClick="buttonActionClick('zoomOut')"
																	width="30" height="15" class="abutton">out</td>
																<td act="actual"
																	onClick="buttonActionClick('actualSize')" width="30"
																	height="15" class="abutton">1:1</td>
																<td act="fit" onClick="buttonActionClick('fit')"
																	width="30" height="15" class="abutton">fit</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td
														style="display:; background: #fff; border: 1px solid #666">
														<div id="outlineContainer"
															style="background: #fff; width: 248px; height: 99px;"></div>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table></td>
							<td width="10" style="cursor: pointer" id="tdShowHideToolBox"><img
								id="imgShowHideToolBox" width=10 src='/images/arrow_left.png'></td>
							<td width="1000">
								<div id="graph"
									style="position: relative; height: 507px; width: 1051px; cursor: default; overflow: hidden; border: 1px solid #666; background-image: url('/images/bg.png')">
									<!-- Graph Here -->
									<center id="splash" style="padding-top: 230px;">
										<img src="/images/loading.gif">
									</center>
								</div>
							</td>
							<td>&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
	</div>

	<div id="listSavedDiagrams" style="overflow-y: auto; display: none;"></div>

</body>
@stop
