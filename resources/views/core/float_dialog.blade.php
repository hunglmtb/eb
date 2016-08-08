<?php
	if (!isset($floatContents)) $floatContents = ['editBoxContentview'/* ,'historyContent' */];
 ?>
<script>
	var floatContents = <?php echo json_encode($floatContents); ?>;
	if(typeof(editBox) == "undefined"){
		editBox = {	fields : [],
				enableRefresh:false,
				hidenFields : [],
				};

		editBox.closeEditWindow = function() {
			$('#floatBox').dialog('close');
			$.each(editBox.fields, function( index, value ) {
				delete actions.editedData[value];
		    });
		};
		
		editBox.initExtraPostData = function (id,rowData){
		 		return 	{id:id};
		 	}
		
		editBox.showDialog = function (option,success,error){
				title 		= option.title;
				postData 	= option.postData;
				url 		= option.url;
				viewId 		= option.viewId;
				
				var dialogOptions = {
							height: 350,
							width: 900,
							position: { my: 'top', at: 'top+150' },
							modal: true,
							title: title,
							create: function() {
								if (typeof(editBox.saveDetail) == "function") {
							        var saveBtn = $("<a id='savebtn' href='#' style='right: 20px;display:none'>Save</a>")
													.button({/* icons:{primary: "ui-icon-plus"}, */text: true});
										saveBtn.insertBefore('.ui-dialog-titlebar-close').click(function(e){
											   e.preventDefault();
	// 										   alert("click");
											   editBox.saveDetail(postData.id);
										});
								}
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
			
				$.ajax({
					url: url,
					type: "post",
					data: postData,
					success:function(data){
						$("#history_container").css("display","block");
						$("#savebtn").css("display","table");
						$("#box_loading").css("display","none");
						
						console.log ( "send "+url+"  success : "/* +JSON.stringify(data) */);
						if (typeof(success) == "function") {
							success(data);
						}
					},
					error: function(data) {
						console.log ( "extensionHandle error: "/*+JSON.stringify(data)*/);
						$("#box_loading").html("not availble");
						if (typeof(error) == "function") {
							error(data);
						}
					}
				});
			}
				
			editBox.editRow = function (id,rowData){
				if (typeof(editBox.preEditHandleAction) == "function") {
					editBox.preEditHandleAction(id,rowData);
				}
				
		 		success = function(data){
					editBox.editGroupSuccess(data,id);
				}
		    	option = {
					    	title 		: rowData.CODE,
					 		postData 	: editBox.initExtraPostData(id,rowData),
					 		url 		: editBox.loadUrl,
					 		viewId 		: 'editBoxContentview',
		    	    	};
		 		
				editBox.showDialog(option,success);
		    }
		
			editBox.renderSumRow = function (api,columns){
			$.each(columns, function( i, column ) {
		        total = 0;
		        $.each(api.columns(column).data()[0], function( index, value ) {
		        	total += intVal(value);
				});
		        // Update footer
		        $( api.columns(column).footer() ).html(total.toFixed(3));
			});
		}
	}
</script>
@yield('editBoxParams')

<div id="floatBox" style="display:none;">
		@foreach($floatContents as $key => $content )
			<div id="{{$content}}"  style="display:none;width:100%;border:none;height: 100%; margin-top: 0">
				@yield($content)
			</div>
	 	@endforeach
		<div id="box_loading" >Loading...</div>
	</div>
