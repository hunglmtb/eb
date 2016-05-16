<?php
$subMenus = [array('title' => 'FLOW STREAM', 'link' => 'flow'),
		array('title' => 'ENERGY UNIT', 'link' => 'eu'),
		array('title' => 'STORAGE', 'link' => 'storage'),
		array('title' => 'TICKET', 'link' => 'ticket'),
		array('title' => 'WELL TEST', 'link' => 'eutest'),
		array('title' => 'DEFERMENT', 'link' => 'deferment'),
		array('title' => 'QUALITY', 'link' => 'quality')
];
?>
@extends('core.bscontent',['subMenus' => $subMenus])
@section('adaptData')
@parent
<script>
	actions.loadSuccess =  function(data){
		$('#buttonLoadData').attr('value', 'Refresh');
		postData = data.postData;
		var tab = postData['{{config("constants.tabTable")}}'];
		actions.loadedData[tab] = postData;
		var exclude = [0];
		var uoms = data.uoms;

		$.each(uoms, function( index, value ) {
			var collection = value['data'];
			exclude.push(uoms[index]["targets"]);
			uoms[index]["render"] = function ( data, type, row ) {
							                var result = $.grep(collection, function(e){ 
								                return e['ID'] == data;
								                });
											if(typeof(result) !== "undefined" && typeof(result[0]) !== "undefined" &&result[0].hasOwnProperty('NAME')){
		                						return value['COLUMN_NAME']=="ALLOC_TYPE"?result[0]['NAME']:result[0]['NAME'];
											}
											return data;
								                
                					};
            $.each(collection, function( i, vl ) {
            	vl['value']=vl['ID'];
            	vl['text']=vl['NAME'];
            });
            uoms[index]["createdCell"] = function (td, cellData, rowData, row, col) {
                if(data.properties[col].DATA_METHOD==1&&data.properties[col].DATA_METHOD=='1'){
	 				$(td).addClass( "editInline" );
		        	$(td).editable({
		        	    type: 'select',
		        	    title: 'edit',
		        	    emptytext: '',
		        	    value:cellData,
		        	    showbuttons:false,
		        	    source: collection,
		        	    success: actions.getEditSuccessfn(tab,td, cellData, rowData, row, col),
		        	});
                }
   			}
		});

		var original = Array.apply(null, Array(data.properties.length)).map(function (_, i) {return i;});
		var finalArray = $(original).not(exclude).get();

		$.each(finalArray, function( i, cindex ) {
			var type = typetoclass(data.properties[cindex].INPUT_TYPE);
			var cell = actions.getCellProperty(data,tab,type,cindex);
    		uoms.push(cell);
        });
		
		var phase = {"targets": 0,
					"render": actions.renderFirsColumn
		  			};
		uoms.push(phase);

		var  marginLeft = 0;
		var  tblWdth = 0;
		$.each(data.properties, function( ip, vlp ) {
 			if(ip==0){
//  				vlp['className']= 'headcol';
 				marginLeft = vlp['width'];
 			}
 			var iw = (vlp['width']>1?vlp['width']:100);
 			tblWdth+=iw;
 			vlp['width']= iw+"px";
        });
		$('#table_'+tab).css('width',(tblWdth)+'px');

		var tbl = $('#table_'+tab).DataTable( {
 	          data: data.dataSet,
	          columns: data.properties,
	          destroy: true,
	          "columnDefs": uoms,
 	          "scrollX": true,
 	         "autoWidth": false,
	       	"scrollY":        "270px",
// 	                "scrollCollapse": true,
			"paging":         false
// 	           paging: false,
// 	          searching: false 
	    } );
		actions.updateView(postData);

		if($( window ).width()>$('#table_'+tab).width()){
	 		$('#container_'+tab).css('width',$('#table_'+tab).width()+18);
		}
 		var tbbody = $('#table_'+tab);
 		tbbody.tableHeadFixer({"left" : 1,head: false,});

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
					row = table.row( '#'+value[actions.type.saveKeyField(key)] );
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
		        });
			}
		}

		actions.editedData = {};
		alert(JSON.stringify(data.updatedData));
		if(data.hasOwnProperty('lockeds')){
			alert(JSON.stringify(data.lockeds));
		}
 	};
</script>
@stop
