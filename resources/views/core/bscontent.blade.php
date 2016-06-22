<?php
if (!isset($subMenus)) $subMenus = [];
if (!isset($active)) $active =1;
?>
@extends('core.bsmain',['subMenus' => $subMenus])

@section('content')
	@if(isset($tables))
			<div id="tabs">
			<ul>
				@foreach($tables as $key => $table )
					<li id="{{$key}}"><a href="#tabs-{{$key}}"><font size="2">{{$table['name']}}</font></a></li>
		 		@endforeach
			</ul>
			<div id="tabs_contents">
				@foreach($tables as $key => $table )
					<div id="tabs-{{$key}}">
						<div id="container_{{$key}}" style="overflow-x:hidden">
							<table border="0" cellpadding="3" id="table_{{$key}}" class="fixedtable nowrap display">
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
							active:{{$active}},
							activate: function(event, ui) {
						        actions.loadNeighbor(event, ui);
						    }
						});
					});
			</script>
		@stop
	@endif
@stop

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
		options = {tableOption :{searching: true},
					invisible:[]};
		var tbl = actions.initTableOption(tab,data,options,actions.renderFirsColumn,actions.createdFirstCellColumn);

		actions.afterDataTable(tbl,tab);
		actions.updateView(postData);

		if($( window ).width()>$('#table_'+tab).width()){
	 		$('#container_'+tab).css('width',$('#table_'+tab).width());
		}
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

	actions.saveSuccess =  function(data){
		var postData = data.postData;
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
							$.each($(row.node()).find('td'), function( index, td) {
					        	$(td).css('color', '');
					        });
						}
						else{
							value['DT_RowId'] = actions.getExistRowId(value,key);
							table.row.add(value).draw( false );
						}
					}
		        });
				actions.afterGotSavedData(data,table,key);
			}
		}
		actions.editedData = {};
		actions.deleteData = {};
		alert(JSON.stringify(data.updatedData));
		if(data.hasOwnProperty('lockeds')){
			alert(JSON.stringify(data.lockeds));
		}
 	};
</script>
@stop
