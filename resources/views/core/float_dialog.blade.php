<?php
	if (!isset($floatContents)) $floatContents = ['editBoxContentview'/* ,'historyContent' */];
 ?>
<script>
	var floatContents = <?php echo json_encode($floatContents); ?>;
	if(typeof(editBox) == "undefined"){
		editBox = {
					fields 			: [],
					enableRefresh	:false,
					hidenFields 	: [],
					size			: {	height : 350,
											width : 900,
										},
					isNotSaveGotData	: function (url,viewId){
				 		return true;
				 	},
				 	gotData			: false,
				};

		editBox.closeEditWindow = function(close) {
			if(close) $('#floatBox').dialog('close');
		};
		
		editBox.initExtraPostData = function (id,rowData,url){
		 		return 	{id:id};
		 }

		editBox.getSaveDetailUrl = function (url,editId,viewId){
	 		return 	editBox.saveUrl;
	 	}
	 	
		editBox['getSaveButton'] = function (id){
			return $("<a id ='"+id+"' class='savebtn' href='#' style='right: 60px;display:block;position: absolute;'>Save</a>")
			.button({/* icons:{primary: "ui-icon-plus"}, */text: true});
	 	};
		editBox.showDialog = function (option,success,error){
				title 		= option.title;
				postData 	= option.postData;
				url 		= option.url;
				viewId 		= option.viewId;
				editId		= postData.id;
				var dSize	= typeof option.size=='object'?option.size: editBox.size;
				var dialogOpenFunction	= typeof editBox.dialogOpenFunction=='function'?editBox.dialogOpenFunction: function( event, ui ) {
							    			$(".savebtn").remove();
											var saveUrl = editBox.getSaveDetailUrl(url,editId,viewId);
									    	if (typeof(editBox.saveDetail) == "function" && typeof saveUrl == "string") {
										        	var saveBtn = editBox.getSaveButton(viewId+"_"+editId);
										        	saveBtn.click(function(e){
														   e.preventDefault();
														   editBox.saveDetail(editId,editBox['saveFloatDialogSucess'],saveUrl);
													});
													saveBtn.insertBefore('.ui-dialog-titlebar-close');
											}
										};
				var dialogOptions = {
							editId	: editId,
							height	: dSize.height,
							width	: dSize.width,
							position: { my: 'top', at: 'top+150' },
							modal	: true,
							title	: title,
							close	: function(event) {
										$.each(editBox.fields, function( index, value ) {
											delete actions.editedData[value];
									    });
										if(editBox.enableRefresh) {
											actions.doLoad(true);
											editBox.enableRefresh = false;
										} 
								   	 },
						    open	: dialogOpenFunction,
							create	: function() {
								
						    }
						};
				$("#floatBox").dialog(dialogOptions);
				$("#box_loading").html("Loading...");
				$("#box_loading").css("display","block");
				$("#savebtn").css("display","none");
			
				$.each(floatContents, function( index, value ) {
					$("#"+value).css("display","none");
			     });
			    
				$("#"+viewId).css("display","block");
				if (typeof(editBox.preSendingRequest) == "function") {
					editBox.preSendingRequest();
				}

				if(typeof(url) != "undefined" && url!=null && url!=""){
					successFn = function(data){
						if(typeof editBox.gotData != "object") editBox.gotData = {};
						editBox.gotData[viewId]	= data;
						$("#history_container").css("display","block");
						$("#savebtn").css("display","block");
						$("#box_loading").css("display","none");
						
						console.log ( "send "+url+"  success : "/* +JSON.stringify(data) */);
						if (typeof(success) == "function") {
							success(data);
						}
					};
					
					if(editBox.isNotSaveGotData(url,viewId)){
						$.ajax({
							url			: url,
							type		: "post",
							data		: postData,
							success		: successFn,
							error		: function(data) {
								console.log ( "extensionHandle error: "/*+JSON.stringify(data)*/);
								$("#box_loading").html("not availble");
								if (typeof(error) == "function") {
									error(data);
								}
							}
						});
					}
					else successFn(editBox.gotData[viewId]);
				}
			}
				
		editBox.editRow = function (id,rowData,url,viewId){
			var editUrl = typeof url 	== "string"? url	: editBox.loadUrl;
			var vId 	= typeof viewId == "string"? viewId	:'editBoxContentview';
			if (typeof(editBox.preEditHandleAction) == "function") {
				editBox.preEditHandleAction(id,rowData);
			}
			
	 		success = function(data){
				editBox.editGroupSuccess(data,id,editUrl);
			}
	    	option = {
				    	title 		: rowData.CODE,
				 		postData 	: editBox.initExtraPostData(id,rowData,editUrl),
				 		url 		: editUrl,
				 		viewId 		: vId,
	    	    	};
			editBox.showDialog(option,success);
	    }
		
		editBox.renderSumRow = function (api,columns,fixed){
			fixed = typeof(fixed) == "undefined"?3:fixed;
	        total = 0;
			$.each(columns, function( i, column ) {
		        $.each(api.columns(column).data()[0], function( index, value ) {
		        	total += intVal(value);
				});
		        // Update footer
		        $( api.columns(column).footer() ).html(total.toFixed(fixed));
			});
			return total;
		}

		editBox.renderOutputText = function (texts){
			return 	texts.ObjectName +"("+
					texts.IntObjectType+"."+
					texts.ObjectDataSource+"."+
					(texts.hasOwnProperty('CodeFlowPhase')? 		(texts["CodeFlowPhase"]+".")	:"")+
					(texts.hasOwnProperty('CodeAllocType')? 		(texts["CodeAllocType"]+".")	:"")+
					(texts.hasOwnProperty('CodePlanType')? 			(texts["CodePlanType"]+".")		:"")+
// 					(texts.hasOwnProperty('CodeProductType')? 		(texts["CodeProductType"]+".")	:"")+
					(texts.hasOwnProperty('CodeForecastType')? 		(texts["CodeForecastType"]+".")	:"")+
					(texts.hasOwnProperty('ObjectTypeProperty')? 	texts["ObjectTypeProperty"]		:"")+
					")";
		};
	}
</script>
@yield('editBoxParams')

<div id="floatBox" style="display:none;">
		@foreach($floatContents as $key => $content )
			<div id="{{$content}}"  style="display:none;border:none; margin-top: 0;height: 100%;">
				@yield($content)
			</div>
			@yield("extra_".$content)
	 	@endforeach
		<div id="box_loading" >Loading...</div>
</div>

@section('floatMoreBox')
	<div id="floatMoreBox" style="display:none;">
		@yield('floatMoreBoxContent')
	</div>
@stop
@yield('floatMoreBox')


