<?php
$enableHeader	= false;
$enableFooter	= false;
?>

@extends('core.bsdiagram')

@section('main')
<script  type="text/javascript"  src="/common/js/base64.js"></script>
<script type="text/javascript">

var diagram_xml="";
<?php
	if($diagram_id>0) echo "diagram_xml='".base64_encode($xml)."';\r\n";
?>

function loadDiagramFromXML(){
	if(diagram_xml=="") return;
	var doc = mxUtils.parseXml(Base64.decode(diagram_xml));
	var dec = new mxCodec(doc);
	clearGraph();
	dec.decode(doc.documentElement, ed.graph.getModel());
	lastObj=null;

	ed.graph.setCellsLocked(ed.graph.model.cells);
	ed.graph.fit(0,false);
	var scale=ed.graph.view.getScale();
	scale=0.5/scale/scale;
	//alert(0.5/scale);
	ed.graph.center(true,true,scale,scale);
	//console.log(xmlcode);
	ed.graph.refresh();
}

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
var ed;
function onInit(editor)
{
    ed=editor;
    editor.graph.setPanning(true);
    editor.graph.panningHandler.useLeftButtonForPanning = true;
	editor.graph.setConnectable(false);
	editor.graph.swimlaneSelectionEnabled=false;
	//mxGraphHandler.prototype.highlightEnabled=true;

	//editor.graph.setEnabled(false);
	//mxGraph.setCellsSelectable(false);
	mxGraph.prototype.maxFitScale=1;
	mxGraph.prototype.isCellSelectable = function(cell)
	{
		var task_code=cell.getAttribute('task_code',"");
		var task_id=cell.getAttribute('task_id',0);
		var isrun=cell.getAttribute('isrun',0);
		//return (task_code!="" || isrun==2);
		return (task_id>0);
	};

	//mxPopupMenu.prototype.showMenu = function(){}
	
	editor.graph.selectionModel.addListener(mxEvent.CHANGE, function(){
		if(ed.graph.selectionModel.cells.length==1){
			onObjectSelected(ed.graph.selectionModel.cells[0]);
		}
	});

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
	loadDiagramFromXML();
	display();
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

function clearGraph()
{
    ed.graph.removeCells(ed.graph.getChildVertices(ed.graph.getDefaultParent()));
}

function display()
{
	//Get all cells
	var cells=ed.graph.model.cells; //getChildVertices(ed.graph.getDefaultParent());
	var occur_date="<?php echo $occur_date; ?>";
	var flow_phase=2;
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
		if(typeof conn_id =="undefined") conn_id = -1; 

		var v = {
			'ID' 				: cell.getId(),
			'OBJECT_TYPE' 		: cell.getAttribute('object_type'),
			'OBJECT_ID' 		: cell.getAttribute('object_id'),
			'CONN_ID' 			: conn_id,
			'SUR_PHASE_CONFIG' 	: sur_phase_config,
			'SU' 				: su
		}

		sData.push(v); 
	}
	
	if(sData!="")
	{
		postRequest('/getValueSurveillance',
		   {
			   'vparam':sData,
			   'occur_date':occur_date,
			   'flow_phase':flow_phase
		   },
		   function(data){
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
		   }
		);
	}
}
window.onbeforeunload = function() { return mxResources.get('changesLost'); };
</script>

<div id="graph" style="margin-top:15px;position:relative;height:calc(100% - 20px);width:100%;box-sizing: border-box;cursor:default;overflow:hidden;border:0px solid #a0a0a0;">
<?php if(!($diagram_id>0)) echo '<p class="center_content">No diagram</p>'; ?>
</div>
<div style="display:none; padding:10px;" id="toolbar" ></div>

<script>
$(document).ready(function(){
	<?php 
 			if($diagram_id>0) echo "new mxApplication('/config/diagrameditor.xml?3');"; 
// 			if(!($diagram_id>0)) echo '<p class="center_content">No diagram</p>';
	?>
});
</script>
@stop