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
    $.post(target,variables,function(data){hideWaiting();completedFunc(data);},
    		function(data){hideWaiting();});
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
var _alert;
(function() {
    _alert = window.alert;       // <-- Reference
    window.alert = function(str) {
		showMessageAutoHide(str);
        //_alert(str +'@');                      // Suits for this case
    };
})();