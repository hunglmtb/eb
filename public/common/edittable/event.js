var firstTime = true;
function onAfterGotDependences(elementId,element,currentId){
   if(elementId.indexOf("AllocJob") !== -1){
	   if(firstTime) {
		   var originValue = element.attr("originValue");
		   element.val(originValue);
		   firstTime = false;
	   }
   }
}

(function ($) {
    "use strict";
    
    var timeConfigEditableTpl	= '<table class="eventTable EVENT_TABLE TIMECONFIG" style="width:inherit;"><tbody><tr><td><label><span>Recurring</span></label></td><td><select class="editable-event" name="FREQUENCEMODE"><option value="ONCETIME">ONCETIME</option><option value="DAILY">DAILY</option><option value="WEEKLY">WEEKLY</option><option value="MONTHLY">MONTHLY</option></select></td></tr>'+
	 '<tr class="INTERVALROW" ><td><label><span>Recur every</span></label></td><td><input class="editable-event" type="number" name="INTERVALDAY"><label><span> day(s)</span></label></td></tr>'+
	 '<tr class="STARTTIMEROW" ><td><label><span>Start Time</span></label></td><td><span class="editable-event clickable" name="StartTime">set datetime</span></td></tr>'+
	 '<tr class="ENDTIMEROW" ><td><label><span>End Time</span></label></td><td><span class="editable-event clickable" type="text" name="EndTime">set datetime</span></td></tr>'+
	 '<tr class="DATAROW WEEKDAYROW" ><td><label><span> Week days</span></label></td><td colspan="3"> <label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="1">Monday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="2">Tuesday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="3">Wednesday</label> <label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="4">Thursday </label><br><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="5">Friday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="6">Saturday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="0">Sunday</label></td></tr>'+
	 '<tr class="DATAROW MONTHROW" ><td><label><span> Month</span></label></td><td colspan="3"> <label><input type="checkbox" name="chk_month[]" value="1"> 1</label><label><input type="checkbox" name="chk_month[]" value="2"> 2</label><label><input type="checkbox" name="chk_month[]" value="3"> 3</label><label><input type="checkbox" name="chk_month[]" value="4"> 4</label><label><input type="checkbox" name="chk_month[]" value="5"> 5</label><label><input type="checkbox" name="chk_month[]" value="6"> 6</label><label><input type="checkbox" name="chk_month[]" value="7"> 7</label><label><input type="checkbox" name="chk_month[]" value="8"> 8</label><label><input type="checkbox" name="chk_month[]" value="9"> 9</label><label><input type="checkbox" name="chk_month[]" value="10"> 10</label><label><input type="checkbox" name="chk_month[]" value="11"> 11</label><label><input type="checkbox" name="chk_month[]" value="12"> 12</label></td></tr>'+
	 '<tr class="DATAROW DAYROW" ><td> <label><span>Day</span></label></td><td colspan="3"> <label><input type="checkbox" name="chk_day[]" value="1"> 1</label><label><input type="checkbox" name="chk_day[]" value="2"> 2</label><label><input type="checkbox" name="chk_day[]" value="3"> 3</label><label><input type="checkbox" name="chk_day[]" value="4"> 4</label><label><input type="checkbox" name="chk_day[]" value="5"> 5</label><label><input type="checkbox" name="chk_day[]" value="6"> 6</label><label><input type="checkbox" name="chk_day[]" value="7"> 7</label><label><input type="checkbox" name="chk_day[]" value="8"> 8</label><label><input type="checkbox" name="chk_day[]" value="9"> 9</label><label><input type="checkbox" name="chk_day[]" value="10"> 10</label><label><input type="checkbox" name="chk_day[]" value="11"> 11</label><label><input type="checkbox" name="chk_day[]" value="12"> 12</label><label><input type="checkbox" name="chk_day[]" value="13"> 13</label><label><input type="checkbox" name="chk_day[]" value="14"> 14</label><label><input type="checkbox" name="chk_day[]" value="15"> 15</label><label><input type="checkbox" name="chk_day[]" value="16"> 16</label><br>'+
	 '<label><input type="checkbox" name="chk_day[]" value="17"> 17</label><label><input type="checkbox" name="chk_day[]" value="18"> 18</label><label><input type="checkbox" name="chk_day[]" value="19"> 19</label><label><input type="checkbox" name="chk_day[]" value="20"> 20</label><label><input type="checkbox" name="chk_day[]" value="21"> 21</label><label><input type="checkbox" name="chk_day[]" value="22"> 22</label><label><input type="checkbox" name="chk_day[]" value="23"> 23</label><label><input type="checkbox" name="chk_day[]" value="24"> 24</label><label><input type="checkbox" name="chk_day[]" value="25"> 25</label><label><input type="checkbox" name="chk_day[]" value="26"> 26</label><label><input type="checkbox" name="chk_day[]" value="27"> 27</label><label><input type="checkbox" name="chk_day[]" value="28"> 28</label><label><input type="checkbox" name="chk_day[]" value="29"> 29</label><label><input type="checkbox" name="chk_day[]" value="30"> 30</label><label><input type="checkbox" name="chk_day[]" value="31"> 31</label></td></tr>'+
	 '</tbody></table>';
    
    var event			= [{	type		: "select",
								name		: "FREQUENCEMODE",
								label		: "Recurring",
								collection	: [	{ID	: "ONCETIME"	, NAME	: "ONCETIME" },
									          	{ID	: "DAILY"		, NAME	: "DAILY" },
									          	{ID	: "WEEKLY"		, NAME	: "WEEKLY" },
									          	{ID	: "MONTHLY"		, NAME	: "MONTHLY" },
									          	],
								display		: true,
							},
							{	type		: "input",
					    		name		: "INTERVALDAY",
					    		width		: "200px",
					    		label		: "Recur every day(s)"
			    			}
    						];
    var datetimeValues	= [
                      	   	{ID	: "THIS_DAY"			, NAME	: "THIS DAY" },
				          	{ID	: "MONTH_BEGIN_DAY"		, NAME	: "MONTH_BEGIN_DAY" },
				          	{ID	: "MONTH_END_DAY"		, NAME	: "MONTH_END_DAY" },
				          	{ID	: "WEEK_BEGIN_DAY"		, NAME	: "WEEK_BEGIN_DAY" },
				          	{ID	: "WEEK_END_DAY"		, NAME	: "WEEK_END_DAY" },
				          	{ID	: "SPECIFIC_DAY"		, NAME	: "SPECIFIC_DAY" },
				      	   ];
    
    var startDate	= {	type		: "datetime",
			    		name		: "STARTTIME",
			    		collection	: "datetimeValues",
			    		label		: "Begin time"};
    var endDate		= {	type		: "datetime",
			    		name		: "ENDTIME",
			    		collection	: "datetimeValues",
			    		label		: "End time"};
    var sendLog		= {	type		: "input",
			    		name		: "SENDLOG",
			    		width		: "200px",
			    		label		: "emails"};
    var facility	= {	type		: "select",
			    		name		: "FACILITY",
			    		label		: "Facility",
			    		collection	: "facilities",
						display		: true,
	    				};
    var network		= {
						type		: "select",
						name		: "Network",
						id			: "run_Network",
						label		: "Network",
						dependence	: "AllocJob",
			    		collection	: "networks",
					};
    var allocJob	= {
						type		: "select",
						name		: "AllocJob",
						id			: "run_AllocJob",
						label		: "Job",
						display		: true,
					};
    var runAllocation	= [	network,
                  	   	allocJob,
						startDate,
						endDate,
						sendLog
						];
    var checkAllocation	= [	
                       	   	jQuery.extend(jQuery.extend({},network), {id	: "check_Network"}),
                       	   	jQuery.extend(jQuery.extend({},allocJob), {id	: "check_AllocJob"}),
    						startDate,
    						endDate,
    						sendLog
    						];
    var codeReadingFrequency = {
								type		: "select",
								name		: "CodeReadingFrequency",
								label		: "Record Frequency",
					    		collection	: "codeReadingFrequency",
					    		hasAll		: true,
						    };
    var codeFlowPhase = {
    		type		: "select",
    		name		: "CodeFlowPhase",
    		label		: "Phase Type",
    		hasAll		: true,
    		collection	: "codeFlowPhase",
	    };
    var codeEventType = {
    		type		: "select",
    		name		: "CodeEventType",
    		label		: "Event Type",
    		hasAll		: true,
    		collection	: "codeEventType",
	    };
    var types = {
//		EVENT			: event,
		ALLOC_CHECK		: checkAllocation,
    	ALLOC_RUN		: runAllocation,
    	VIS_WORKFLOW	: [facility,
					    	{
					    		type		: "select",
					    		name		: "TmWorkflow",
					    		label		: "Workflow",
					    		collection	: "tmWorkflows",
								display		: true,
					    	},
					    	startDate,
					    	endDate,
					    	sendLog
				    	],
				    	
    	FDC_EU			:   [	facility,
	 					    	{
						    		type		: "select",
						    		name		: "EnergyUnitGroup",
						    		label		: "Eu group",
						    		collection	: "energyUnitGroup",
						    		hasAll		: true,
									display		: true,
							    },
							    codeReadingFrequency,
							    codeFlowPhase,
							    codeEventType,
							    {
						    		type		: "select",
						    		name		: "CodeAllocType",
						    		label		: "Alloc Type",
						    		collection	: "codeAllocType",
						    		hasAll		: true,
							    },
							    {
						    		type		: "select",
						    		name		: "CodePlanType",
						    		label		: "Plan Type",
						    		collection	: "codePlanType",
						    		hasAll		: true,
							    },
							    {
						    		type		: "select",
						    		name		: "CodeForecastType",
						    		label		: "Forecast Type",
						    		collection	: "codeForecastType",
						    		hasAll		: true,
							    },
						    	startDate,
						    	endDate,
						    	sendLog
							], 
    	FDC_EU_TEST			:   [	facility,
    	 					    	{
							    		type		: "select",
							    		name		: "EnergyUnit",
							    		label		: "Energy Unit",
							    		collection	: "energyUnit",
							    		hasAll		: true,
										display		: true,
								    },
							    	startDate,
							    	endDate,
							    	sendLog
								], 
    	FDC_FLOW  		:   [	facility,
							    codeReadingFrequency,
							    codeFlowPhase,
						    	startDate,
						    	endDate,
						    	sendLog
							], 
    	FDC_STORAGE 		:   [	facility,
    	            		     	{
							    		type		: "select",
							    		name		: "CodeProductType",
							    		label		: "Product",
							    		collection	: "codeProductType",
							    		hasAll		: true,
								    },
    						    	startDate,
    						    	endDate,
    						    	sendLog
    							], 
    };
    
    var builInputElement = function (type,element) {
    	var input = "";
    	var idAttr = typeof element.id == "string" ? 'id="'+element.id+'"':'';
    	if(type=="select"||type=="datetime")
    		input = '<select '+idAttr+' class="editable-event" name="'+element.name+'"></select>';
    	else if(type=="input")
    		input = '<input class="editable-event eventTaskInput" name="'+element.name+'"></input>';
    	if(type=="datetime")
    		input += '<span class="editable-event clickable" name="'+element.name+'_PICKER"></span>';
    	return input;
    };
    
    var buildEditableTemplate = function (types) {
    	var html = timeConfigEditableTpl;
    	for (var name in types) {
    		var dependences	= [];
    		html+='<table class="eventTable '+name+'_TABLE TASKCONFIG" style="width:inherit;"><tbody>';
    		var preRow 			= '<tr>';
    		var afterRow 		= '</tr>';
    		var columnNumber	= 1;
    		if(types[name].length>5){
    			columnNumber = 2;
    		}
    		$.each(types[name], function(key, element) {
    	    	var widthAttr = typeof element.width == "string" ? 'width="'+element.width+'"':'';
//    			html+='<tr><td><label><span>'+element.label+'</span></label></td><td '+widthAttr+'>'+builInputElement(element.type,element)+'</td></tr>';
    			if(columnNumber>1){
    				if(key%columnNumber==0){
        				preRow		=  '<tr>';
        				afterRow	=  '';
        			}
    				else if(key%columnNumber==(columnNumber-1)){
    					preRow		=  '';
    					afterRow	=  '</tr>';
    				}
    				else{
    					preRow		=  '';
    					afterRow	=  '';
    				}
    			}
    			html+=preRow+'<td><label><span>'+element.label+'</span></label></td><td '+widthAttr+'>'+builInputElement(element.type,element)+'</td>'+afterRow;
    			if(typeof element.dependence != "undefined" && typeof element.id != "undefined" )
    				dependences.push({	source	: element.id,
    									targets	: element.dependence});
    		});
    		html+='</tbody></table>';
    		for (var i = 0; i < dependences.length; i++) {
    			html+="<script>registerOnChange('"+dependences[i].source+"',['"+dependences[i].targets+"'])<\/script>";
			}
		}
    	return html;
    };
    
    var tpl	= buildEditableTemplate(types);
    
    var EVENT = function (options) {
        this.init('EVENT', options, EVENT.defaults);
    };

    //inherit from Abstract input
    $.fn.editableutils.inherit(EVENT, $.fn.editabletypes.abstractinput);

    $.extend(EVENT.prototype, {
        /**
        Renders input from tpl

        @method render() 
        **/        
        render: function() {
        	this.$input 		= this.$tpl.find('input');
        	this.$select 		= this.$tpl.find('select');
        	this.$span 			= this.$tpl.find('span');
        	this.$rows 			= this.$tpl.find('.DATAROW');
        	this.$tables 		= this.$tpl.filter("table");
        	this.datetimeValues	= datetimeValues;
        },
        /**
        Default method to show value in element. Can be overwritten by display option.
        
        @method value2html(value, element) 
        **/
        value2html: function(value, element) {
        	if(!value) {
                $(element).empty();
                return; 
            }
        	var text = "";
        	switch(this.options.configType){
        	case "EVENT" :
        		text = value.FREQUENCEMODE;
        		break;
        	default:
        		text = value.name;
        		break;
        	}
        	text	= typeof text == "string" && text!=""?text:"config";
        	var html = '<b>' + text+ '</b>';
            $(element).html(html); 
        },
        
        /**
        Gets value from element's html
        
        @method html2value(html) 
        **/        
        html2value: function(html) {        
          /*
            you may write parsing method to get value by element's html
            e.g. "Moscow, st. Lenina, bld. 15" => {city: "Moscow", street: "Lenina", building: "15"}
            but for complex structures it's not recommended.
            Better set value directly via javascript, e.g. 
            editable({
                value: {
                    city: "Moscow", 
                    street: "Lenina", 
                    building: "15"
                }
            });
          */ 
          return null;  
        },
      
       /**
        Converts value to string. 
        It is used in internal comparing (not for sending to server).
        
        @method value2str(value)  
       **/
       value2str: function(value) {
           var str = '';
           if(value) {
               /*for(var k in value) {
                   str = str + k + ':' + value[k] + ';';  
               }*/
        	   str = JSON.stringify(value);
           }
           return str;
       }, 
       
       /*
        Converts string to value. Used for reading value from 'data-value' attribute.
        
        @method str2value(str)  
       */
       str2value: function(str) {
           /*
           this is mainly for parsing value defined in data-value attribute. 
           If you will always set value by javascript, no need to overwrite it
           */
           return str;
       },                
       
       /**
        Sets value of input.
        
        @method value2input(value) 
        @param {mixed} value
       **/         
       value2input: function(value) {
			if(typeof value == "undefined" || !value) return;
			switch(this.options.configType){
				case "EVENT" :
					this.$select.filter('[name="FREQUENCEMODE"]').val(value.FREQUENCEMODE);
					this.$input.filter('[name="INTERVALDAY"]').val(value.INTERVALDAY);
					this.originRenderDateTimePicker('StartTime',value.StartTime);
					this.originRenderDateTimePicker('EndTime',value.EndTime);
					this.renderValue('.WEEKDAYROW',value.WEEKDAY);
					this.renderValue('.DAYROW',value.MONTHDAY);
					this.renderValue('.MONTHROW',value.MONTH);
				break;
				default :
					var elements = types[this.options.configType];
					for (var int = 0; int < elements.length; int++) {
						this.renderElement(value,elements[int]);
					}
					break;
				break;
			}
       },       
       renderElement: function(value,element) {
    	   switch(element.type){
			case "select" :
				var select 		= this.$select.filter('[name="'+element.name+'"]');
				var collection 	= [];
				if(typeof this.collection =="undefined" ) this.collection = [];
				this.collection[element.collection]	= typeof value[element.collection] == "object" ? value[element.collection]: this.collection[element.collection];
				if(element.hasAll==true)
					select.append($("<option></option>")
						.attr("value",0)
						.text("(All)"));
				if(typeof this.collection[element.collection] == "object"){
					collection	= this.collection[element.collection];
				}
				$.each(collection, function(key, item) {   
					select.append($("<option></option>")
							.attr("value",item.ID)
							.text(item.NAME)); 
				});
				select.val(value[element.name]);
				select.attr("originValue",value[element.name]);
				break;
			case "datetime" :
				this.renderDatetimeInput(element.name,value[element.name]);
				break;
			case "input" :
				this.$input.filter('[name="'+element.name+'"]').val(value[element.name]);
				break;
    	   }
       },
       /**
        Returns value of input.
        
        @method input2value() 
       **/          
       input2value: function() {
			var value	= {};
			switch(this.options.configType){
				case "EVENT" :
					var weekdays = this.extractValue('.WEEKDAYROW');
					var months 	= this.extractValue('.MONTHROW');
					var days 	= this.extractValue('.DAYROW');
					var startTime= this.originGetDatetimeValue('StartTime');
					var endTime	= this.originGetDatetimeValue('EndTime');
					value	= {
							FREQUENCEMODE	: this.$select.filter('[name="FREQUENCEMODE"]').val(),
							INTERVALDAY		: this.$input.filter('[name="INTERVALDAY"]').val(),
							StartTime		: startTime,
							EndTime			: endTime,
							WEEKDAY			: weekdays,
							MONTHDAY		: days,
							MONTH			: months,
					};
				break;
				default:
					var elements = types[this.options.configType];
					for (var int = 0; int < elements.length; int++) {
						value[elements[int].name] = this.getElementValue(elements[int]);
						if(elements[int].display==true) 
							value.name = this.$select.filter('[name="'+elements[int].name+'"]:visible').find(":selected").text();
					}
					break;
			}
			return value;
       },
       
       getElementValue: function(element) {
    	   var value = "";
    	   switch(element.type){
			case "select" :
				value = this.$select.filter('[name="'+element.name+'"]:visible').val();
				break;
			case "datetime" :
				value = {	
						type	: this.$select.filter('[name="'+element.name+'"]:visible').val(),
						value	: this.getDatetimeValue(element.name)
				}
				break;
			case "input" :
				value = this.$input.filter('[name="'+element.name+'"]:visible').val();
				break;
    	   }
    	   return value;
       },
       
       getDatetimeValue: function(filterName) {
    	   var timeText	= this.$span.filter('[name="'+filterName+'_PICKER"]').text();
    	   var datetime	= moment.utc(timeText,configuration.time.DATETIME_FORMAT);
    	   var value= datetime.isValid()?datetime.format(configuration.time.DATETIME_FORMAT_UTC):null;
           return value;
       },
       originGetDatetimeValue: function(filterName) {
    	   var timeText	= this.$span.filter('[name="'+filterName+'"]').text();
    	   var datetime	= moment.utc(timeText,configuration.time.DATETIME_FORMAT);
    	   var value= datetime.isValid()?datetime.format(configuration.time.DATETIME_FORMAT_UTC):null;
           return value;
       },
       extractValue: function(filterName) {
    	   var weekdays =$.grep(this.$rows.filter(filterName).find('td input'), function(object){ 
             	 return $(object).is(":checked");
    	   });
    	   var value = $.map( weekdays, function( object,key ) {
    		   return object.value;
    	   });
    	   
           return value;
       },
       renderDatetimeValue: function(filterName,value) {
    	   var datetime	= moment.utc(value,configuration.time.DATE_FORMAT_UTC);
    	   if(datetime.isValid()) this.$span.filter('[name="'+filterName+'_PICKER"]').text(datetime.format(configuration.time.DATE_FORMAT));
    	   
       },
       originRenderDatetimeValue: function(filterName,value) {
    	   var datetime	= moment.utc(value,configuration.time.DATE_FORMAT_UTC);
    	   if(datetime.isValid()) this.$span.filter('[name="'+filterName+'"]').text(datetime.format(configuration.time.DATE_FORMAT));
    	   
       },
       renderDatetimeInput: function(filterName,datetimeValue) {
    	   this.datetimeValues		= typeof value.datetimeValues == "object" ? value.datetimeValues: this.datetimeValues;
		   if(typeof this.datetimeValues == "object"){
			   var select = this.$select.filter('[name="'+filterName+'"]');
			   $.each(this.datetimeValues, function(key, value) {   
				   select.append($("<option></option>")
						   .attr("value",value.ID)
						   .text(value.NAME)); 
			   });
			   var selectValue = typeof datetimeValue == "object" && datetimeValue!=null?datetimeValue.type:"";
			   select.val(selectValue);
			   var spans = this.$span;
			   if(selectValue=="SPECIFIC_DAY") this.renderDatetimeValue(filterName,datetimeValue.value);
			   select.change(function(e){
				   if(this.value=="SPECIFIC_DAY") spans.filter('[name="'+filterName+'_PICKER"]:visible').click();
				   else spans.filter('[name="'+filterName+'_PICKER"]:visible').text("");
			   });
		   }
		   this.renderDateTimePicker(filterName);
       },
       renderValue: function(filterName,value) {
    	   if(typeof value== 'object'){
    		   $.grep(this.$rows.filter(filterName).find('td input'), function(object){
    			   var checked	= $.inArray($(object).val(),value)>-1;
    			   $(object).prop('checked', checked);
    			   return checked;
    		   });
    	   }
       },
       activateTimeEvent: function() {
    	   var editableInputs 	= this.$input;
    	   var frequenceMode = this.$select.filter('[name="FREQUENCEMODE"]');
    	   frequenceMode.change(function() {
    		   $(".INTERVALROW").show();
    		   $(".MONTHROW").hide();
    		   $(".DAYROW").hide();
    		   $(".WEEKDAYROW").hide();
    		   
    		   switch ($(this).val()){
    		   case	"ONCETIME":
    			   $(".INTERVALROW").hide();
    			   break;
    		   case	"DAILY":
    			   break;
    		   case	"WEEKLY":
    			   $(".WEEKDAYROW").show();
    			   break;
    		   case	"MONTHLY":
    			   $(".DAYROW").show();
    			   $(".WEEKDAYROW").show();
    			   $(".MONTHROW").show();
    			   break;
    		   }
    	   });
    	   frequenceMode.change();
       },
       renderDateTimePicker: function(filterQuery) {
    	   var  editable = {
    			   title			: 'edit',
    			   clear			: false,
    			   emptytext		: '',
    			   onblur			: 'submit',
    			   showbuttons		: true,
    			   mode				: 'popup',
    			   placement 		: "bottom",
    			   type				: 'date',
    			   format			: configuration.picker.DATE_FORMAT_UTC,
    			   viewformat		: configuration.picker.DATE_FORMAT,
    			   /*datetimepicker	: {
    				   minuteStep :5,
    				   showMeridian : true,
    			   },*/
    	   };
    	   var datetimeInputs	= this.$span.filter('[name="'+filterQuery+'_PICKER"]');
    	   datetimeInputs.editable(editable);
    	   
       },
       originRenderDateTimePicker: function(filterQuery,value) {
    	   var  editable = {
    			   title			: 'edit',
    			   clear			: false,
    			   emptytext		: '',
    			   onblur			: 'submit',
    			   showbuttons		: true,
    			   mode				: 'popup',
    			   placement 		: "bottom",
    			   type				: 'date',
    			   format			: configuration.picker.DATE_FORMAT_UTC,
    			   viewformat		: configuration.picker.DATE_FORMAT,
    	   };
    	   var datetimeInputs	= this.$span.filter('[name="'+filterQuery+'"]');
    	   datetimeInputs.editable(editable);
    	   this.originRenderDatetimeValue(filterQuery,value);
    	   
       },
       activateJobEvent: function() {
//    	   this.renderDateTimePicker('[name="DATE"]');
    	   this.$select.filter('[name="Network"]').change();
       },
        /**
        Activates input: sets focus on the first field.
        
        @method activate() 
       **/        
       activate: function() {
		   this.$tables.css("display","none");
    	   this.$tables.filter('.'+this.options.configType+"_TABLE").css("display","block");
			switch(this.options.configType){
			case "EVENT" :
				this.activateTimeEvent();
				break;
			case "TASK" :
			case "ALLOC_CHECK" :
			case "ALLOC_RUN" :
				this.activateJobEvent();
				break;
			case "WORKFLOW" :
				break;
			}
	       $( ".editable-container" ).draggable();
       },  
       
       /**
        Attaches handler to submit form in case of 'showbuttons=false' mode
        
        @method autosubmit() 
       **/       
       autosubmit: function() {
           this.$input.keydown(function (e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
           });
       }       
    });

    
    EVENT.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: tpl,
        inputclass	: '',
        configType	: 'EVENT',
    });
    $.fn.editabletypes.EVENT = EVENT;
}(window.jQuery));