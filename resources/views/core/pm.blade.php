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
		postData = data.postData;
		var tab = postData['{{config("constants.tabTable")}}'];
		actions.loadedData[tab] = postData;
// 		alert("len nao "+tab);
		var exclude = [0];
		var uoms = data.uoms;
		$.each(uoms, function( index, value ) {
			var collection = value['data'];
			exclude.push(uoms[index]["targets"]);
			uoms[index]["render"] = function ( data, type, row ) {
							                var result = $.grep(collection, function(e){ 
								                return e['ID'] == data;
								                });
	                						return result[0]['CODE'];
                					};
            $.each(collection, function( i, vl ) {
            	vl['value']=vl['ID'];
            	vl['text']=vl['CODE'];
            });
//             uoms[index]["width"] = 150;
            uoms[index]["createdCell"] = function (td, cellData, rowData, row, col) {
//					var tdf = $(td).attr("id","newId");
//				var hd = $(td).column();
				var $th = $(td).closest('table').find('th').eq($(td).index());
//		      	if ( cellData < 1 ) {
	        	$(td).editable({
	        	    type: 'select',
	        	    title: 'edit',
	        	    emptytext: '',
	        	    value:cellData,
	        	    showbuttons:false,
	        	    source: collection,
	        	    success: function(response, newValue) {
			        	/* var result = $.grep(actions.editedData, function(e){ 
										               	 return e['ID'] == rowData['ID'];
										                });
			        	var table = $('#table_'+tab).DataTable();
			        	var columnName = table.settings()[0].aoColumns[col].data;
			        	if (result.length == 0) {
				        	var editedData = {"ID":rowData['ID']};
				        	editedData[columnName] = newValue;
			        		actions.editedData.push(editedData);
			        	}
			        	else{
			        		result[0][columnName] = newValue;
			        	} */
			        	
			        	$(td).css('color', 'red');
			        	
	        	    }
	        	});
   			}
		});

		var original = Array.apply(null, Array(data.properties.length)).map(function (_, i) {return i;});
		var finalArray = $(original).not(exclude).get();

		var cell = {"targets": finalArray,
			    	"createdCell": function (td, cellData, rowData, row, col) {
// 			    						var tdf = $(td).attr("id","newId");
//  										var hd = $(td).column();
//  										var $th = $(td).closest('table').find('th').eq($(td).index());
// 								      	if ( cellData < 1 ) {
								        	$(td).editable({
								        	    type : 'number',
								        	    step: 'any',
								        	    title: 'edit',
								        	    onblur: 'cancel',
								        	    emptytext: '',
								        	    showbuttons:false,
								        	    validate: function(value) {
								        	        if($.trim(value) == '') {
								        	            return 'This field is required';
								        	        }
								        	    },
								        	    success: function(response, newValue) {
										        	var result = $.grep(actions.editedData, function(e){ 
																	               	 return e['ID'] == rowData['ID'];
																	                });
									                /*
										        	var hd = table.columns( $(td).index() ).header();
										        	var columns = table.settings().init().columns;
										        	var title = $(hd).html();
										        	var dt = $(hd).data(); */
										        	var table = $('#table_'+tab).DataTable();
										        	var columnName = table.settings()[0].aoColumns[col].data;
										        	if (result.length == 0) {
											        	var editedData = {"ID":rowData['ID']};
											        	editedData[columnName] = newValue;
										        		actions.editedData.push(editedData);
										        	}
										        	else{
										        		result[0][columnName] = newValue;
										        	}
										        	
										        	$(td).css('color', 'red');
										        	
								        	    }
								        	});
// 								      	}
								    }
			  		};
		uoms.push(cell);

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

		var hdt;	
		var tbl = $('#table_'+tab).DataTable( {
 	          data: data.dataSet,
	          columns: data.properties,
	          destroy: true,
	          "columnDefs": uoms,
 	          "scrollX": true,
 	         "autoWidth": false,
	         "headerCallback": function( thead, data, start, end, display ) {
	        	  	hdt = $(thead).find('th').eq(0);
	        	  	},
	       	"scrollY":        "300px",
// 	                "scrollCollapse": true,
			"paging":         false
// 	           paging: false,
// 	          searching: false 
	    } );
		actions.updateView(postData);

		if($( window ).width()>tblWdth){
	 		$('#table_'+tab+'_wrapper').css('width',(tblWdth)+'px');
		}
 		var tbbody = $('#table_'+tab);
 		tbbody.tableHeadFixer({"left" : 1,head: false,});

 		var hehe = hdt.parent().parent();
 		hehe.tableHeadFixer({"left" : 1,head: false,});
 		var scrtbl = $('#container_'+tab +' .dataTables_scroll .dataTables_scrollBody');
 		$('.dataTables_scrollBody').on("scroll", function(e) {
  			hdt.css({'left': $(this).scrollLeft()});
 		});

 		/* $('#table_'+tab).on( 'order.dt', function () {
 		    // This will show: "Ordering on column 1 (asc)", for example
 	 		tbbody.tableHeadFixer({"left" : 1,head: false,});
 		} );

 		$('#table_'+tab).on( 'search.dt', function () {
 	 		tbbody.tableHeadFixer({"left" : 1,head: false,});
 		} ); */
 	};

	actions.shouldLoad = function(data){
		var activeTabID = getActiveTabID();
		var postData = actions.loadedData[activeTabID];
		var noData = jQuery.isEmptyObject(postData);
		var dataNotMatching = false;
		if (!noData) {
			
		}
		
		var shouldLoad = actions.readyToLoad&&(noData||dataNotMatching);
		return shouldLoad;
	};
</script>
@stop
