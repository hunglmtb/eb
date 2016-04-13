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
		var exclude = [0];
		var uoms = data.uoms;

// 		var tabindex = 0;
		var getEditSuccessfn  = function(td, cellData, rowData, row, col) {
			/* 
			var enterHander = function(eInner) {
		        if (eInner.keyCode == 13) //if its a enter key
		        {
		        	var tabindex = $(this).attr('tabindex');
		            $('[tabindex=' + tabindex + ']').trigger( "click" );
		            
		            /* var e = jQuery.Event("keyup"); // or keypress/keydown
				    e.keyCode = 27; // for Esc
				    $(td).trigger(e); // trigger it on document
		            var tabindex = $(this).attr('tabindex');
		            tabindex++; //increment tabindex
		            $('[tabindex=' + tabindex + ']').focus(); *//*
// 		            $('#Msg').text($(this).attr('id') + " tabindex: " + tabindex + " next element: " + $('*').attr('tabindex').id);


		            // to cancel out Onenter page postback in asp.net
		            return false;
		        }
		    };
		    
			$(td).bind('keypress', enterHander);

			$( td ).blur(function() {
 				 e.keyCode = 27; // for Esc
 				 $(td).trigger(e); // trigger it on document
			});
			 */
			return function(response, newValue) {
		    	if (!(tab in actions.editedData)) {
		    		actions.editedData[tab] = [];
		    	}
		    	var eData = actions.editedData[tab];
	        	var result = $.grep(eData, function(e){ 
								               	 return e['FLOW_ID'] == rowData['FLOW_ID'];
								                });
	        	var table = $('#table_'+tab).DataTable();
	        	var columnName = table.settings()[0].aoColumns[col].data;
	        	if (result.length == 0) {
		        	var editedData = {"FLOW_ID":rowData['X_FL_ID']};
		        	editedData[columnName] = newValue;
	        		eData.push(editedData);
	        	}
	        	else{
	        		result[0][columnName] = newValue;
	        	}
	        	rowData[columnName] = newValue;
 				table.row( '#'+rowData['DT_RowId'] ).data(rowData);
	        	$(td).css('color', 'red');
	        	 /* var tabindex = $(this).attr('tabindex');
	            $('[tabindex=' + (tabindex +1)+ ']').focus(); */
		    };
		}

	    
		$.each(uoms, function( index, value ) {
			var collection = value['data'];
			exclude.push(uoms[index]["targets"]);
			uoms[index]["render"] = function ( data, type, row ) {
							                var result = $.grep(collection, function(e){ 
								                return e['ID'] == data;
								                });
											if(typeof(result) !== "undefined" && typeof(result[0]) !== "undefined" &&result[0].hasOwnProperty('CODE')){
		                						return result[0]['CODE'];
											}
											return data;
								                
                					};
            $.each(collection, function( i, vl ) {
            	vl['value']=vl['ID'];
            	vl['text']=vl['CODE'];
            });
            uoms[index]["createdCell"] = function (td, cellData, rowData, row, col) {
                if(data.properties[col].DATA_METHOD==1&&data.properties[col].DATA_METHOD=='1'){
		        	$(td).editable({
		        	    type: 'select',
		        	    title: 'edit',
		        	    emptytext: '',
		        	    value:cellData,
		        	    showbuttons:false,
		        	    source: collection,
		        	    success: getEditSuccessfn(td, cellData, rowData, row, col),
		        	});
                }
   			}
		});

		var original = Array.apply(null, Array(data.properties.length)).map(function (_, i) {return i;});
		var finalArray = $(original).not(exclude).get();

		var cell = {"targets": finalArray,
					"render": function ( data, type, row ) {
									var number = data;
									if(data!=null){
						        		number = parseFloat(data).toFixed(2);
						        	}
									return number;
								},
			    	"createdCell": function (td, cellData, rowData, row, col) {
// 			    						var tdf = $(td).attr("id","newId");
//  										var hd = $(td).column();
//  										var $th = $(td).closest('table').find('th').eq($(td).index());
// 								      	if ( cellData < 1 ) {
// 											$(td).attr('tabindex', tabindex++);
               				 			if(!data.locked&&actions.isEditable(data.properties[col],rowData,data.rights)){
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
								        	    success: getEditSuccessfn(td, cellData, rowData, row, col),
								        	});

								        	$(td).on("shown", function(e, editable) {
								        		  editable.input.$input.get(0).select();
								        	});
               				 			}
								        	/* var enterHander = function(eInner) {
										        if (eInner.keyCode == 13) //if its a enter key
										        {
										        	var tabindex = $(this).attr('tabindex');
										            $('[tabindex=' + tabindex + ']').trigger( "click" );
										            return false;
										        }
										    };
										    
 											$(td).bind('keypress', enterHander); */
// 								      	}
								    }
			  		};

		var phase = {"targets": 0,
					"render": function ( data, type, rowData ) {
								var html = data+"<div class='phase "+rowData['PHASE_CODE']+"'>"+
			        						rowData['PHASE_NAME']+"</div>" ;
								return html;
							}
		  			};
		uoms.push(cell);
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
				dataNotMatching = actions.loadPostParams[key]!=postData[key];
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
					row = table.row( '#'+value['FLOW_ID'] );
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
		alert(JSON.stringify(postData));
		if(data.hasOwnProperty('lockeds')){
			alert(JSON.stringify(data.lockeds));
		}
 	};
</script>
@stop
