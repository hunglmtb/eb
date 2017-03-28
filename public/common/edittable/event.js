(function ($) {
    "use strict";
    
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
        	this.$input 	= this.$tpl.find('input');
        	this.$select 	= this.$tpl.find('select');
        	this.$span 		= this.$tpl.find('span');
        	this.$rows 		= this.$tpl.find('.DATAROW');
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
        	case "TASK" :
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
					this.renderDatetimeValue('STARTTIME',value.STARTTIME);
					this.renderDatetimeValue('ENDTIME',value.ENDTIME);
					this.renderValue('.WEEKDAYROW',value.WEEKDAY);
					this.renderValue('.DAYROW',value.MONTHDAY);
					this.renderValue('.MONTHROW',value.MONTH);
				break;
				case "TASK" :
					var networkSelect = this.$select.filter('[name="NETWORK"]');
				   this.networks		= typeof value.networks == "object" ? value.networks: this.networks;
				   if(typeof this.networks == "object"){
					   $.each(this.networks, function(key, value) {   
						   networkSelect.append($("<option></option>")
								   .attr("value",value.ID)
								   .text(value.NAME)); 
					   });
					   networkSelect.val(value.NETWORK);
				   }
					this.renderDatetimeValue('DATE',value.DATE);
					this.$input.filter('[name="SENDLOG"]').val(value.SENDLOG);
					this.$select.filter('[name="JOB"]').val(value.JOB);
					this.$select.filter('[name="JOB"]').attr("originValue",value.JOB);

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
					var startTime= this.getDatetimeValue('STARTTIME');
					var endTime	= this.getDatetimeValue('ENDTIME');
					value	= {
							FREQUENCEMODE	: this.$select.filter('[name="FREQUENCEMODE"]').val(),
							INTERVALDAY		: this.$input.filter('[name="INTERVALDAY"]').val(),
							STARTTIME		: startTime,
							ENDTIME			: endTime,
							WEEKDAY			: weekdays,
							MONTHDAY		: days,
							MONTH			: months,
					};
				break;
				case "TASK" :
					value	= {
							NETWORK			: this.$select.filter('[name="NETWORK"]').val(),
							JOB				: this.$select.filter('[name="JOB"]').val(),
							DATE			: this.getDatetimeValue('DATE'),
							SENDLOG			: this.$input.filter('[name="SENDLOG"]').val(),
							name			: this.$select.filter('[name="JOB"]').find(":selected").text(),
					};
				break;
			}
			return value;
       },
       
       getDatetimeValue: function(filterName) {
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
    	   var datetime	= moment.utc(value,configuration.time.DATETIME_FORMAT_UTC);
    	   if(datetime.isValid()) this.$span.filter('[name="'+filterName+'"]').text(datetime.format(configuration.time.DATETIME_FORMAT));
    	   
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
    	   this.renderDateTimePicker('[name="STARTTIME"],[name="ENDTIME"]');
       },
       renderDateTimePicker: function(filterQuery) {
    	   var  editable = {
    			   title			: 'edit',
    			   clear			: false,
    			   emptytext		: '',
    			   onblur			: 'submit',
    			   showbuttons		: false,
    			   mode				: 'popup',
    			   type				: 'datetime',
    			   format			: configuration.picker.DATETIME_FORMAT_UTC,
    			   viewformat		: configuration.picker.DATETIME_FORMAT,
    			   datetimepicker	: {
    				   minuteStep :5,
    				   showMeridian : true,
    			   },
    	   };
    	   var datetimeInputs	= this.$span.filter(filterQuery);
    	   datetimeInputs.editable(editable);
    	   
       },
       activateJobEvent: function() {
    	   this.renderDateTimePicker('[name="DATE"]');
    	   this.$select.filter('[name="NETWORK"]').change();
       },
        /**
        Activates input: sets focus on the first field.
        
        @method activate() 
       **/        
       activate: function() {
			switch(this.options.configType){
			case "EVENT" :
				this.activateTimeEvent();
				break;
			case "TASK" :
				this.activateJobEvent();
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
        tpl: '<table class="eventTable" style="width:inherit;"><tbody><tr><td><label><span>Recurring</span></label></td><td><select class="editable-event" name="FREQUENCEMODE"><option value="ONCETIME">ONCETIME</option><option value="DAILY">DAILY</option><option value="WEEKLY">WEEKLY</option><option value="MONTHLY">MONTHLY</option></select></td></tr>'+
   	 		 '<tr class="INTERVALROW" ><td><label><span>Recur every</span></label></td><td><input class="editable-event" type="number" name="INTERVALDAY"><label><span> day(s)</span></label></td></tr>'+
        	 '<tr class="STARTTIMEROW" ><td><label><span>Start Time</span></label></td><td><span class="editable-event clickable" name="STARTTIME">set datetime</span></td></tr>'+
        	 '<tr class="ENDTIMEROW" ><td><label><span>End Time</span></label></td><td><span class="editable-event clickable" type="text" name="ENDTIME">set datetime</span></td></tr>'+
        	 '<tr class="DATAROW WEEKDAYROW" ><td><label><span> Week days</span></label></td><td colspan="3"> <label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="1">Monday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="2">Tuesday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="3">Wednesday</label> <label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="4">Thursday </label><br><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="5">Friday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="6">Saturday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="0">Sunday</label></td></tr>'+
        	 '<tr class="DATAROW MONTHROW" ><td><label><span> Month</span></label></td><td colspan="3"> <label><input type="checkbox" name="chk_month[]" value="1"> 1</label><label><input type="checkbox" name="chk_month[]" value="2"> 2</label><label><input type="checkbox" name="chk_month[]" value="3"> 3</label><label><input type="checkbox" name="chk_month[]" value="4"> 4</label><label><input type="checkbox" name="chk_month[]" value="5"> 5</label><label><input type="checkbox" name="chk_month[]" value="6"> 6</label><label><input type="checkbox" name="chk_month[]" value="7"> 7</label><label><input type="checkbox" name="chk_month[]" value="8"> 8</label><label><input type="checkbox" name="chk_month[]" value="9"> 9</label><label><input type="checkbox" name="chk_month[]" value="10"> 10</label><label><input type="checkbox" name="chk_month[]" value="11"> 11</label><label><input type="checkbox" name="chk_month[]" value="12"> 12</label></td></tr>'+
        	 '<tr class="DATAROW DAYROW" ><td> <label><span>Day</span></label></td><td colspan="3"> <label><input type="checkbox" name="chk_day[]" value="1"> 1</label><label><input type="checkbox" name="chk_day[]" value="2"> 2</label><label><input type="checkbox" name="chk_day[]" value="3"> 3</label><label><input type="checkbox" name="chk_day[]" value="4"> 4</label><label><input type="checkbox" name="chk_day[]" value="5"> 5</label><label><input type="checkbox" name="chk_day[]" value="6"> 6</label><label><input type="checkbox" name="chk_day[]" value="7"> 7</label><label><input type="checkbox" name="chk_day[]" value="8"> 8</label><label><input type="checkbox" name="chk_day[]" value="9"> 9</label><label><input type="checkbox" name="chk_day[]" value="10"> 10</label><label><input type="checkbox" name="chk_day[]" value="11"> 11</label><label><input type="checkbox" name="chk_day[]" value="12"> 12</label><label><input type="checkbox" name="chk_day[]" value="13"> 13</label><label><input type="checkbox" name="chk_day[]" value="14"> 14</label><label><input type="checkbox" name="chk_day[]" value="15"> 15</label><label><input type="checkbox" name="chk_day[]" value="16"> 16</label><br>'+
        	 '<label><input type="checkbox" name="chk_day[]" value="17"> 17</label><label><input type="checkbox" name="chk_day[]" value="18"> 18</label><label><input type="checkbox" name="chk_day[]" value="19"> 19</label><label><input type="checkbox" name="chk_day[]" value="20"> 20</label><label><input type="checkbox" name="chk_day[]" value="21"> 21</label><label><input type="checkbox" name="chk_day[]" value="22"> 22</label><label><input type="checkbox" name="chk_day[]" value="23"> 23</label><label><input type="checkbox" name="chk_day[]" value="24"> 24</label><label><input type="checkbox" name="chk_day[]" value="25"> 25</label><label><input type="checkbox" name="chk_day[]" value="26"> 26</label><label><input type="checkbox" name="chk_day[]" value="27"> 27</label><label><input type="checkbox" name="chk_day[]" value="28"> 28</label><label><input type="checkbox" name="chk_day[]" value="29"> 29</label><label><input type="checkbox" name="chk_day[]" value="30"> 30</label><label><input type="checkbox" name="chk_day[]" value="31"> 31</label></td></tr>'+
            '</tbody></table>',
        inputclass	: '',
        configType	: 'EVENT',
    });
    $.fn.editabletypes.EVENT = EVENT;
}(window.jQuery));