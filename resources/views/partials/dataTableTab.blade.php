@if(isset($tables))
	<div id="tabs">
		<ul id="ebTabHeader">
			@foreach($tables as $key => $table )
			<li id="{{$key}}"><a href="#tabs-{{$key}}"><font size="2">{{$table['name']}}</font></a></li>
			@endforeach
			<div id="more_actions"></div>
		</ul>
		<div id="tabs_contents">
			@foreach($tables as $key => $table )
			<div id="tabs-{{$key}}">
				<div id="container_{{$key}}" style="overflow-x: hidden">
					<table border="0" cellpadding="3" id="table_{{$key}}"
						class="fixedtable nowrap display" style="width: inherit;position:relative;">
					</table>
				</div>
			</div>
			@endforeach
		</div>
		@yield('secondaryContent')
	</div>
	@section('script') 
	@parent
		<script>
				$(document).ready(function () {
					$("#tabs").tabs({
						active	:{{$active}},
						create	: function(event,ui){
						        actions.loadNeighbor(event, ui);
					    },
						activate: function(event, ui) {
					        actions.loadNeighbor(event, ui);
					    }
					});
				});
		</script>
	@stop 
@endif
@yield('extraContent')

@section('adaptData')
<script>
	actions.initData = function(){
		var activeTabID = getActiveTabID();
		var tab = {'{{config("constants.tabTable")}}':activeTabID}
		return tab;
	}

	actions.loadSuccess =  function(data){
		$('#buttonLoadData').attr('value', 'Refresh');
		postData = data.postData;
		var tab = postData['{{config("constants.tabTable")}}'];
		actions.loadedData[tab] = postData;
		options = actions.getTableOption(data,tab);
		var render1stCoumnFn = actions.getRenderFirsColumnFn(tab);
		var tbl = actions.initTableOption(tab,data,options,render1stCoumnFn,actions.createdFirstCellColumn);

		actions.afterDataTable(tbl,tab);
		if(actions.enableUpdateView(tab,postData)) actions.updateView(postData);

		if($( window ).width()>$('#table_'+tab).width()){
	 		$('#container_'+tab).css('width',$('#table_'+tab).width());
		}
		var disableLeftFixer = typeof(options["tableOption"]) !== "undefined" && 
								typeof(options["tableOption"]["disableLeftFixer"]) !== "undefined" &&
								options["tableOption"]["disableLeftFixer"] == true;

		if(!disableLeftFixer){
	 		var tbbody = $('#table_'+tab);
	 		if(data.dataSet!=null&&(data.dataSet.length>0)) tbbody.tableHeadFixer({"left" : 1,head: false,});
	
			var hdt;	
	 		var tblh = $('#container_'+tab ).find('table').eq(0);
		  	hdt = $(tblh).find('th').eq(0);
	 		var tblHeader = hdt.parent().parent();
	 		tblHeader.tableHeadFixer({"left" : 1,head: false,});
	 		var tblScroll = $('#container_'+tab ).find('div.dataTables_scrollBody').eq(0);
	 		tblScroll.on("scroll", function(e) {
	  			hdt.css({'left': $(this).scrollLeft()});
	 		});
		};

		if(actions.tableIsDragable(tab)){
			$('#table_'+tab +" tbody").sortable();
	 		$('#table_'+tab +" tbody").disableSelection();
		}

	}
	actions.shouldLoad = function(data){
		var activeTabID = getActiveTabID();
		var postData = actions.loadedData[activeTabID];
		var noData = jQuery.isEmptyObject(postData);
		var dataNotMatching = false;
		if (!noData&&actions.loadPostParams) {
			for (var key in actions.loadPostParams) {
				if($('.'+key).css('display') != 'none'){
					dataNotMatching = actions.loadPostParams[key]!=postData[key];
				} 
				if(dataNotMatching) break;
			}
		}
		
		var shouldLoad = actions.readyToLoad&&(noData||dataNotMatching);
		return shouldLoad;
	};

	actions.reloadAfterSave	= false;
	
	actions.saveSuccess =  function(data,noDelete){
		var postData = data.postData;
		if(!jQuery.isEmptyObject(data.updatedData)){
			for (var key in data.updatedData) {
				if($('#table_'+key).children().length>0){
					table = $('#table_'+key).DataTable();
					$.each(data.updatedData[key], function( index, value) {
						if(actions.isShownOf(value,postData)) {
							row = table.row( '#'+actions.getExistRowId(value,key));
							var tdata = row.data();
							if( typeof(tdata) !== "undefined" && tdata !== null ){
								for (var pkey in value) {
									if(tdata.hasOwnProperty(pkey)){
										tdata[pkey] = value[pkey];
									}
								}
 								row.data(tdata).draw();
 								var otd =  null;
								$.each($(row.node()).find('td'), function( index, td) {
						        	$(td).css('color', '');
						        	otd = td;
						        });
								actions.createdFirstCellColumnByTable(table,tdata,otd,key);
							}
							else{
								value['DT_RowId'] = actions.getExistRowId(value,key);
								table.row.add(value).draw( false );
							}
						}
			        });
					if(typeof(noDelete) === "undefined" || !noDelete ) actions.afterGotSavedData(data,table,key);
				}
				if(typeof(noDelete) === "undefined" || !noDelete ) delete actions.editedData[key];
			}
		}
		else if(typeof(postData) !== "undefined" && (postData.hasOwnProperty('deleteData'))){
			for (var key in postData.deleteData) {
				if(typeof(noDelete) === "undefined" || !noDelete ) {
					table = $('#table_'+key).DataTable();
					actions.afterGotSavedData(data,table,key);
					delete actions.deleteData[key];
				}
			}
		}
		else if(typeof data.dataSets != "undefined") {
			$.each(data.dataSets, function( index, value) {
				actions.loadSuccess(value);
			});
		}
		/* actions.editedData = {};
		actions.deleteData = {}; */
// 		alert(JSON.stringify(data.updatedData));
		msg = 'done\n';
		if(data.hasOwnProperty('lockeds')){
			msg+=JSON.stringify(data.lockeds);
// 			alert();
		}

		alert(msg);
		if(actions.reloadAfterSave) actions.doLoad(true);
 	};
</script>
@stop
