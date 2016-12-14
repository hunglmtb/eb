<?php
	$isFilterModify	= isset($isFilterModify)	? $isFilterModify	:false;
?>

@if(isset($isAction)&&$isAction)
	@section('script')
		@parent
		<script src="/common/js/eb_table_action.js"></script>
	@stop
@endif

@section('extraAdaptData')
@parent
	@section('floatWindow')
		@yield('editBox')
		@include('core.edit_dialog')
	@stop
	
	@if(isset($isFilterModify)&&$isFilterModify)
		<script>
			filters.afterRenderingDependences	= function(id){
				var partials 		= id.split("_");
				var prefix 			= partials.length>1?partials[0]+"_":"";
				var model 			= partials.length>1?partials[1]:id;
				var currentObject	= "#"+prefix;
				
				if(model=="ObjectName") 
					$('#title_'+id).text($(currentObject+"IntObjectType").find(":selected").text());
				else if(model=="ObjectDataSource")
					filters.preOnchange(prefix+"ObjectDataSource");
				
			};
			filters.preOnchange		= function(id, dependentIds,more){
				var partials 		= id.split("_");
				var prefix 			= partials.length>1?partials[0]+"_":"";
				var model 			= partials.length>1?partials[1]:id;
				var selectedObject	= "#container_"+prefix;
				var currentObject	= "#"+prefix;
				switch(model){
					case "IntObjectType":
						if($(currentObject+model).find(":selected").attr( "name")=="ENERGY_UNIT"||
								$(currentObject+model).find(":selected").attr( "name")=="EU_TEST") 
							$(selectedObject+'CodeFlowPhase').css("display","block");
						else {
		// 					$(selectedObject+'CodeFlowPhase').css("display","none");
							$(selectedObject+'CodeFlowPhase').hide();
							$(selectedObject+'CodeForecastType').hide();
							$(selectedObject+'CodePlanType').hide();
							$(selectedObject+'CodeAllocType').hide();
						}
						break;
					case "ObjectDataSource":
						var objectDataSource = $(currentObject+'ObjectDataSource').val();
						if(objectDataSource!=null){
							objectDataSource=='EnergyUnitDataAlloc'?$(selectedObject+'CodeAllocType').show():$(selectedObject+'CodeAllocType').hide();
							objectDataSource.endsWith("Plan")?$(selectedObject+'CodePlanType').show():$(selectedObject+'CodePlanType').hide();
							objectDataSource.endsWith("Forecast")?$(selectedObject+'CodeForecastType').show():$(selectedObject+'CodeForecastType').hide();
							if($(currentObject+"CodeFlowPhase").is(":visible")) {
								$(selectedObject+'CodePlanType').removeClass("clearBoth");
								$(selectedObject+'CodeAllocType').removeClass("clearBoth");
								$(selectedObject+'CodeForecastType').removeClass("clearBoth");
							}
							else {
								$(selectedObject+'CodePlanType').addClass("clearBoth");
								$(selectedObject+'CodeAllocType').addClass("clearBoth");
								$(selectedObject+'CodeForecastType').addClass("clearBoth");
							}
						}
						if(prefix=="") $("#tdObjectContainer").css({'height':($("#filterFrequence").height()+'px')});
						break;
				}
			};
		
			
			filters.moreDependence	= function(dependentIds,model,currentValue,prefix){
				if(model=="ObjectDataSource"&&$("#"+prefix+"IntObjectType").val()=="KEYSTORE"){
					if(isFirstDisplay&&prefix!="") {
						dependentIds = [{"name":"ObjectName","source":"ObjectDataSource"}];
						isFirstDisplay = false;
					}
					else dependentIds.push({"name":"ObjectName","source":"ObjectDataSource"});
				}
				return dependentIds;
			};
		</script>
		
		<script>
		// 	editBox.fields = ['deferment'];
			editBox.loadUrl = "/code/filter";
			/* editBox['size'] = {	height : 420,
								width : 900,
								}; */
			var currentSpan = null;
			editBox.initExtraPostData = function (span,rowData){
			 						isFirstDisplay = false;
		 							currentSpan = span;
		 							var postData	= typeof span.data == "function"?span.data():{};
		 							return 	postData;
		 	};
		 	isFirstDisplay = false;
		 	editBox.editGroupSuccess = function(data,span){
		 		$("#editBoxContentview").html(data);
		 		filters.afterRenderingDependences("secondary_ObjectName");
		 		filters.preOnchange("secondary_IntObjectType");
		 		filters.preOnchange("secondary_ObjectDataSource");
		 		isFirstDisplay = true;
		 		if($("#secondary_IntObjectType").val()=="KEYSTORE") $("#secondary_ObjectDataSource").change();
		 		if(typeof editBox.updateExtraFilterData == "function")
					editBox.updateExtraFilterData(span.data());
			};
		
			editBox.editSelectedObjects = function (dataStore,resultText,x){
				if(currentSpan!=null) {
					currentSpan.data(dataStore);
					currentSpan.text(resultText);
					var li = currentSpan.closest( "li" );
					editBox.updateObjectAttributes(li,dataStore,x);
				}
			};
		
			editBox.addObjectItem 	= function (color,dataStore,texts,x){
				var li 				= $("<li class='x_item'></li>");
				var sel				= "<select class='x_chart_type' style='width:100px'><option value='line'>Line</option><option value='spline'>Curved line</option><option value='column'>Column</option><option value='area'>Area</option><option value='areaspline'>Curved Area</option><option value='pie'>Pie</option></select>";
				var inputColor 		= "<input type='text' maxlength='6' size='6' style='background:"+color+";color:"+color+";' class='_colorpicker' value='"+(color=="transparent"?"7e6de3":color.replace("#", ""))+"'>";
				var select			= $(sel);
				var colorSelect		= $(inputColor);
				var span 			= $("<span></span>");
				var del				= $('<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png">');
		
				if(dataStore.hasOwnProperty('chartType')) select.val(dataStore.chartType);
				select.appendTo(li);
				colorSelect.appendTo(li);
				span.appendTo(li);
				del.appendTo(li);
				
				currentSpan 		= span;
				span.click(function() {
					editBox.editRow(span,span);
				});
				span.addClass("clickable");
				var rstext 			= typeof texts =="string"? texts:editBox.renderOutputText(texts);
				editBox.editSelectedObjects(dataStore,rstext,x);
				
				li.appendTo($("#chartObjectContainer"));
				setColorPicker();
			}
		
			editBox.updateObjectAttributes = function (li,dataStore,x){
				if(typeof x !="string")
					x = editBox.getObjectValue(dataStore);
				li.attr("object_value",x);
			};
		
			editBox.getObjectValue = function (dataStore){
				var s3	="";
				var d0 	= dataStore.IntObjectType;
				if(d0=="ENERGY_UNIT") s3+=":"+dataStore.CodeFlowPhase;
				var x	= 	d0+":"+
							dataStore.ObjectName+":"+
							dataStore.ObjectDataSource+":"+
							dataStore.ObjectTypeProperty+
							s3+"~"+
							dataStore.CodeAllocType+"~"+
							dataStore.CodePlanType+"~"+
							dataStore.CodeForecastType+"~"+
							dataStore.cboYPos+"~"+
							dataStore.txt_y_unit;
				return x;
			};
		</script>
	@endif
@stop
