var $bgBox;
var timerWaiting;
function addStyleSheet(css)
{
	var style = document.createElement('style');
	style.type = 'text/css';
	style.innerHTML = css;
	document.getElementsByTagName('head')[0].appendChild(style);
}
function showEBMessage(msg)
{
	
}
function _showWaiting()
{
	if(!$bgBox)
	{
		var style = '@keyframes slidedown{from{top:-40px;}}@keyframes fadein{from{opacity:0;}}@keyframes rotate{from{transform: rotate(0deg);}to{transform: rotate(360deg);}}#_box_notice{animation: slidedown 0.7s;position:absolute;background:yellow;box-sizing: border-box;box-shadow: 0px 0px 30px #000000;width:300px;height:40px;padding:10px;border-radius: 0px 0px 10px 10px;top:0px;left:50%;margin-left:-150px;color:#666666;text-align:center;font-size:10pt;}#_refresh {width:20px;height:20px;display:inline-block;box-sizing: border-box;margin-left:-110px;border:4px solid gray;animation: rotate 1s linear infinite;}';
		addStyleSheet(style);
		
		var waitingHTML='<div id="_box_notice"><div id="_refresh"></div><div id="_content" style="position:absolute;top:12px;width:100%;font-size:11pt">Loading...</div></div>';
		$bgBox = $('<div>')
			.attr('id', '_waiting')
			.attr('style',"animation: fadein 0.5s;position: fixed;top:0px;left:0px; width: 100%; height: 100%; z-index: 999;text-align:center;background:rgba(0,0,0,0.1)")
			.html(waitingHTML)
			.appendTo('body');
	}
	$bgBox.show();
}
function showWaiting()
{
	if(timerWaiting) clearTimeout(timerWaiting);
	timerWaiting=setTimeout(function(){ _showWaiting(); },1000);
}
function hideWaiting()
{
	if(timerWaiting) clearTimeout(timerWaiting);
	if($bgBox) $bgBox.fadeOut();
}
var $msgBox;
var timerMsgBox;
function showMessageAutoHide(s,t)
{
	if(!$msgBox)
	{
		var style = '@keyframes slidedown{from{top:-40px;}}#_box_notice_msg{animation: slidedown 0.7s;background:yellow;box-sizing: border-box;box-shadow: 0px 0px 30px #000000;min-width:200px;max-width:800px;padding:15px 20px;border-radius: 0px 0px 10px 10px;top:0px;position: absolute;z-index:10000;top: 0px;left:50%;transform: translate(-50%,0%); color:black;text-align:center;font-size:10pt;}';
		addStyleSheet(style);
		
		$msgBox = $('<div id="_box_notice_msg" style=""></div>').appendTo('body');
		$msgBox.click(function(){___hideMessage();});
	}
	$("#_box_notice_msg").html(s);
	$msgBox.show();
	if(timerMsgBox) clearTimeout(timerMsgBox);
	timerMsgBox=setTimeout('___hideMessage()',!t?5000:t);
}
function ___hideMessage()
{
	if(timerMsgBox) clearTimeout(timerMsgBox);
	if($msgBox) $msgBox.fadeOut();
}
function postRequest(target,variables,completedFunc,container)
{
    showWaiting(container);
    $.post(target,variables,function(data){hideWaiting();completedFunc(data);});
}

function sendAjax(url,param, func,error)
{
    return $.ajax({
  		beforeSend: function(){
  			showWaiting();
  		},
    	url: url,
    	type: "post",
    	data: param,
    	dataType: 'json',
    	success: function(_data){
    		hideWaiting(); 
    		func(_data);
		},
		error: function(_data){
    		hideWaiting();
    		if(typeof(error) == "function") func(error);
    		else alert(_data + 'error');
		}
	});    
}

function sendAjaxNotMessage(url,param, func)
{
    return $.ajax({
    	url: url,
    	type: "post",
    	data: param,
    	dataType: 'json',
    	success: function(_data){
    		func(_data);
		}
	});    
}
function zeroFill( number, width )
{
  width -= number.toString().length;
  if ( width > 0 )
  {
    return new Array( width + (/\./.test( number ) ? 2 : 1) ).join( '0' ) + number;
  }
  return number + ""; // always return a string
}

function inputNumber(keyEvent){
	if (keyEvent.shiftKey) {
		return false;
	}
	
	var number = [ 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 109, 110];
	var control = [/*backspace*/8, /*del*/ 46, /*tab*/9, /*esc*/ 27, /*enter*/13,/*arrow*/ 37, 38, 39, 40];
	var keyCode = keyEvent.charCode || keyEvent.keyCode || 0;
	
	if ($.inArray(keyCode, number.concat(control)) < 0){
		return false;
	}
	
	return true;
}

function preventDecimalInput(keyEvent, isNeg, left, right) {
	if (keyEvent.shiftKey) {
		return false;
	}
	var number = [ 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 109, 189, 190, 110];
	var control = [/*backspace*/8, /*del*/ 46, /*tab*/9, /*esc*/ 27, /*enter*/13,/*arrow*/ 37, 38, 39, 40];
	var keyCode = keyEvent.charCode || keyEvent.keyCode || 0;

	if ($.inArray(keyCode, number.concat(control)) < 0){
		return false;
	}
	if ($.inArray(keyCode, control) >= 0){
		return true;
	}
	var ctrId = keyEvent.target.id + "";
	
	var index = document.getElementById(ctrId).selectionStart;
	var current =  ($("#" + ctrId).val() + "").replace(",","");
	var willStr = "";
	var charDw = "";
	if (keyCode == 189 || keyCode == 109) {
		charDw = "-";
	} else if (keyCode == 190 || keyCode == 110) {
		charDw = ".";
	} else {
		if((current.length - (current.indexOf("-") + 1)) == left && current.indexOf(".") < 0) {
			return false;
		}
		if (parseInt(keyCode) >= 96 && parseInt(keyCode) <= 105) {
			charDw = (parseInt(keyCode) - 96) + '';
		} else {
			charDw = String.fromCharCode(keyCode);
		}
	}
	if (index > 0) {
		willStr = current.substring(0, index) + charDw + current.substring(index, current.length);
	} else {
		willStr = charDw + current;
	}
	var regexStr = "^";
	if (isNeg) {
		regexStr += "\\-?";
	}
	regexStr += "\\d{0," + left + "}";
	if (right > 0) {
		regexStr += "\\.?\\d{0," + right + "}";
	}
	//regexStr += "$";
	var RE = new RegExp(regexStr);
	if (RE.test(willStr)) {
		return true;
	} else {
		return false;
	}
}

function checkValue(sValue, valueDefault){
	var result = sValue;

	if(sValue === null || sValue === undefined){
		result = valueDefault;
	}
		
	return result;
}

function formatDate(dateString){
	var date = dateString!=""? moment.utc(dateString,configuration.time.DATETIME_FORMAT_UTC)
									.format(configuration.time.DATE_FORMAT)
							:dateString;
	return date;
}

function formatDateTime(dateString){
	var date = dateString!=""? moment.utc(dateString,configuration.time.DATETIME_FORMAT_UTC)
									.format(configuration.time.DATETIME_FORMAT)
							:dateString;
	return date;
}
function formatDateTimeUTC(dateString){
	var date = dateString!=""? moment.utc(dateString,configuration.time.DATETIME_FORMAT)
									.format(configuration.time.DATETIME_FORMAT_UTC)
							:dateString;
	return date;
}


function validateNumber(selector) {
	var regex = /^-?[0-9]{1,5}$/;
	if(!regex.test($(selector).val())) {
		return false;
	}
	else return true;
}

var _alert;
(function() {
    _alert = window.alert;       // <-- Reference
    window.alert = function(str) {
		showMessageAutoHide(str);
        //_alert(str +'@');                      // Suits for this case
    };
})();

function arrayUnique(array,equalFunction) {
    var a = array.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j] || (typeof equalFunction == "function" && equalFunction(a[i],a[j])))
                a.splice(j--, 1);
        }
    }

    return a;
}
