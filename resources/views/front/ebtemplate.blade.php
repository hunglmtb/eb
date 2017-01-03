<?php
// include_once('../lib/db.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Energy Builder</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <meta name="description" content="Creative CSS3 Animation Menus" />
        <meta name="keywords" content="menu, navigation, animation, transition, transform, rotate, css3, web design, component, icon, slide" />
        <meta name="author" content="Codrops" />
		<meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
		<link rel="shortcut icon" href="../favicon.ico"> 
        <link rel="stylesheet" type="text/css" href="../css/style8.css" />
		<link rel="stylesheet" href="../css/hexagon.css?r=2">
		<link rel="stylesheet" href="../index.htm_files/css3menu0/style.css" type="text/css" />
		<link rel="stylesheet" href="../common/css/jquery-ui.css">
		<link rel="stylesheet" href="../common/css/style.css">

	    <script src="../common/js/jquery-1.9.1.js"></script> 
		<script src="../common/js/jquery-ui.js"></script>

		<script type="text/javascript" src="../common/js/jquery.reveal.js"></script>
		<script type="text/javascript" src="../common/utils.js"></script>
</head>
    <body style="background:#222222">
	<script>var func_code="ROOT";</script>
	@include('partials.user')

<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table2" height="100%">
	<tr>
		<td height="120" valign="top">
		<div id="pageheader">
			<div id="hex_logo">
<div style="display:none;position: absolute; width: 56px; height: 54px; z-index: 1; left:470px;top:443px" id="bee">
					<img border="0" src="../img/bee.png"></div>						
				<img border="0" src="../img/eb2.png?1" >
				<div style="display:none;position: absolute; width: 78px; height: 26px; z-index: 1; left: 76px; top: 187px" id="menu_holder">
<ul id="css3menu0" class="topmenu">
	<li class="topmenu"><a href="#" style="width:30px;height:10px;line-height:10px;"><p align=center>...</p></a>
	</li>
</ul>
			</div>
			</div>
			<p></div>
		</td>
	</tr>
	<tr>
		<td valign="top">
<!-- 		<a href='http://www.centralpetroleum.com.au' target='_blank'>
			<img src='../img/cplogo.png' style='position: absolute;top: 50%;left:50%;transform: translate(390px,-120px);width:180px'>
		</a> -->
		<div id="poweredBy">	

	<div class="hex" style="background:#ffffff">		
		<div class="inner" style="color:#333">
		Powered by<br>
		<img width="110" src="../img/edataviz_logo.png" style="margin:5px 0px" alt="eDataViz"><br>
		<font size="1">Copyright &copy; 2016 eDataViz LLC</font>
		</div>		
		<a target="_blank" href="http://www.edataviz.com"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
			<p>&nbsp;</div>
&nbsp;</td>
	</tr>
</table>
<div class="hex_container" style="z-index:100" id="boxLogin">
	<div class="hex_disabled hex-gap" id="cell1">		
		<div class="inner">
		</div>		
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>
	
	<div class="hex_disabled" id="cell2">		
		<div class="inner">
		</div>		
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>


	<div class="hex_disabled" id="cell3">		
		<div class="inner">
		</div>		
		<a href="../diagram/index.htm"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex_disabled" id="cell7">		
		<div class="inner">
		</div>		
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex_disabled" style="background:#666">		
		<div class="inner">
		<h4>Username</h4>
		<input class="r_textbox" type="text" style="width:120px;" id="username" name="username" value="" />
		<div style="margin-left:13px;width:120px;height:3px;border:1px solid #d08924;border-top:none"></div>
		</div>		
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex_disabled" style="background:#666">		
		<div class="inner">
		<h4>Password</h4>
		<input class="r_textbox" type="password" style="width:120px;" value="" id="password" name="password" />
		<div style="margin-left:13px;width:120px;height:3px;border:1px solid #d08924;border-top:none"></div>
		</div>		
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>
	
	<div class="hex_disabled" id="cell4">		
		<div class="v_top">
		</div>		
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex_disabled hex-gap" id="cell6">		
		<div class="inner">
		</div>		
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	
	<div class="hex hex-login">		
		<div class="inner">
		<div style="position: absolute; width: 100px; height: 100px; z-index: 200; left:57px;top:61px">
					<img border="0" src="../img/bee.png"></div>		
				<h4>LOG IN</h4>
		</div>		
		<a href="javascript:logineb()"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>

	
	<div class="hex_disabled" id="cell5">		
		<div class="inner">
		</div>		
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
</div>
<div class="hex_container" style="display:none" id="boxMenu">
	<div class="hex_disabled hex-gap" id="menuName">		
		<div class="inner">
		<h4>MENU NAME</h4>
		</div>		
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>
	
	<div class="hex hex-1" id="menu4">		
		<div class="inner">
		<h4>MENU 6</h4>
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>

	<div class="hex hex-1" id="menu3">		
		<div class="inner">
		<h4>MENU 5</h4>
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex hex-1" id="menu7">		
		<div class="inner">
		<h4>MENU 7</h4>
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex hex-1" id="menu0">		
		<div class="inner">
		<h4>MENU 0</h4>
		</div>		
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex hex-1" id="menu1">		
		<div class="inner">
		<h4>MENU 1</h4>
		</div>		
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>
	
	<div class="hex hex-1" id="menu2">		
		<div class="inner">
		<h4>MENU 2</h4>
		</div>		
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex hex-3 hex-gap" id="menuBack" code="back">		
		<div class="inner">
		<h4><img src='../img/back.png' style="vertical-align:middle"> &nbsp;&nbsp;HOME&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h4>
		</div>		
		<a href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex hex-1" id="menu6">		
		<div class="inner">
		<h4>MENU 4</h4>
		</div>		
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex hex-1" id="menu5">		
		<div class="inner">
		<h4>MENU 3</h4>
		</div>		
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>
	
</div>
<div class="hex_container" style="display:none" id="boxFunctions">
	<div class="hex hex-1 hex-gap" id="func1" code="production">		
		<div class="inner">
				<h4>PRODUCTION<br>MANAGEMENT </h4>
		</div>		
		<a id="pm" href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>
	
	<div class="hex hex-2" id="func2" code="operation">		
		<div class="inner">
				<h4>FIELD<br>OPERATIONS</h4>
		</div>		
		<a id="fo" href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>


	<div class="hex hex-3" id="func3" code="visual">		
		<div class="inner">
				<h4>DATA<br>VISUALIZATION</h4>
<!--				
				<hr>
				<p>We Open Everyday</p>
-->				
		</div>		
		<a id="dv" href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex hex-2" id="func4" code="allocation">		
		<div class="inner">
		<h4>ALLOCATION</h4>
		</div>		
		<a id="allocation" href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex hex-3" id="func5" code="forecast">		
		<div class="inner">
		<h4>FORECAST & PLANNING </h4>
		</div>		
		<a id="fp" href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
<?php
if(true){
?>
	<div class="hex hex-1" id="func6" code="delivery">		
		<div class="inner">
		<h4>PRODUCT<br>DELIVERY</h4>
		</div>		
		<a id="pd" href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>
	<div class="hex hex-2" id="func7" code="greenhouse">		
		<div class="inner">
		<h4>GREENHOUSE<br>GAS</h4>
		</div>		
		<a id="gg"href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
<?php
}
else
{
?>
	<div class="hex hex-1" id="func6" code="delivery">		
		<div class="inner">
		<h4>PRODUCT<br>DELIVERY</a></h4>
		</div>		
		<a href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	<div class="hex hex-2" id="func7" code="greenhouse">		
		<div class="inner">
		<h4>GREENHOUSE<br>GAS</h4>
		</div>		
		<a href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
<?php
}
?>
	
	<div class="hex hex-1 hex-gap" id="func8" code="admin">		
		<div class="inner">
		<h4>ADMINISTRATOR</h4>
		</div>		
		<a id="administrator" href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex hex-2" code="config">		
		<div class="inner">
		<h4>SYSTEM<br>CONFIGURATION</h4>
		</div>		
		<a id="sc" href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>	
	
	<div class="hex hex-3" code="interface">		
		<div class="inner">
				<h4>INTERFACES</h4>
		</div>		
		<a id="interfaces" href="#" onclick="func(this)"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>		
	</div>
	
</div>
<script>
$.ajaxSetup({
    headers: {
        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
    }
});

$(document).ready(function () {
	var submenu = '{{$menu}}';
	if(typeof(submenu) !== "undefined" && submenu!=''){
		func($("#"+submenu));
	};
});

function logout()
{
	window.location.href="../auth/logout";
}
var is_logging_in=false;
	var curCell=0;
	function randomCell()
	{
		if(curCell>0)
		{
			$( "#cell"+curCell ).css('background-color', '#ccc');
		}
		if(!is_logging_in) { return; }
		curCell++; if(curCell>7) curCell=1;
		$( "#cell"+curCell ).css('background-color', '#d08924');
/*
		$( "#cell"+curCell ).animate({
		backgroundColor: "#aaa"
		}, 200 );
*/
		setTimeout('randomCell()',200);
	}

$('#username').select();
$('#username').focus();
$("#password").keyup(function(e){ 
    var code = e.which; // recommended to use e.which, it's normalized across browsers
    if(code==13)e.preventDefault();
    if(code==32||code==13||code==188||code==186){
        logineb();
    } 
});

function layoutUserLoggedIn(ani)
{
	$('#boxUserInfo').show();
	$("#boxFunctions").show();
	$("#boxLogin").hide();
	//$("#menu_holder").show();
	$("#bee").show();
	if(ani)
	{
		$( "#bee" ).stop().animate({
				left: "-=875",
				top: "-=900"
			}, 2000, function() { //animation complete, then rotate
				$("#bee").animate(
				  {rotation:90},
				  {
				    duration: 1000,
				    step: function(now, fx) {
				      $(this).css({"transform": "rotate("+now+"deg)"});
				    }
				  }
				);					
			});
	}
	else
		$( "#bee" ).hide();
	//	$( "#bee" ).css({"top": 3, "left": 55,"transform":"rotate(90deg)" });
}
function layoutUserLoggedOut()
{
	window.location.reload();
	return;
	$("#boxLogin").show();
	$("#menu_holder").hide();
	$("#bee").hide();
	$("#bee").css({top: 443, left: 470,rotation:0});
}
var menuBox;
var menu={};
menu["production"]=[
		{menutext:"Flow Stream",desc:"",url:"/dc/flow"},
		{menutext:"Energy Unit",desc:"",url:"/dc/eu"},
		{menutext:"Tank & Storage",desc:"",url:"/dc/storage"},
		{menutext:"Ticket",desc:"",url:"/dc/ticket"},
		{menutext:"Well Test",desc:"",url:"/dc/eutest"},
		{menutext:"Deferment & MMR",desc:"",url:"/dc/deferment"},
		{menutext:"Quality",desc:"",url:"/dc/quality"}
	];
menu["operation"]=[
		{menutext:"Safety",desc:"",url:"/fo/safety"},
		{menutext:"Comments",desc:"",url:"/fo/comment"},
		{menutext:"Equipment",desc:"",url:"/fo/equipment"},
		{menutext:"Chemical",desc:"",url:"/fo/chemical"},
		{menutext:"Personnel",desc:"",url:"/fo/personnel"}
	];
menu["visual"]=[
		{menutext:"Network Model",desc:"",url:"/diagram"},
		{menutext:"Data View",desc:"",url:"/dataview"},
		{menutext:"Report",desc:"",url:"/workreport"},
		{menutext:"Advanced Graph",desc:"",url:"/graph"},
		{menutext:"Workflow",desc:"",url:"/workflow"},
		{menutext:"Choke Model",desc:"",url:"/fp/choke"},
		{menutext:'{{ trans("front/site.Dashboard") }}',desc:"",url:"/dashboard"},
		{menutext:"Task Manager",desc:"",url:"/taskman/?com=task"},
	];
menu["allocation"]=[
		{menutext:"Run Allocation",desc:"",url:"/allocrun"},
		{menutext:"Config Allocation",desc:"",url:"/allocset"}
	];
menu["forecast"]=[
		{menutext:"WELL FORECAST",desc:"",url:"/fp/forecast"},
		{menutext:"PREoS",desc:"",url:"../fp/preos"},
		{menutext:"MANUAL ALLOCATE<br>PLAN",desc:"",url:"/fp/allocateplan"},
		{menutext:"LOAD<br>PLAN/FORECAST",desc:"",url:"/fp/loadplanforecast"},
	];
<?php
// if($current_username=="CP_User"){}
// else{
?>
menu["delivery"]=[
		{menutext:"CONTRACT ADMIN",desc:"",url:"/pd/contractdata"},
		{menutext:"CARGO ADMIN",desc:"",url:"/pd/cargoentry"},
		{menutext:"CARGO ACTION",desc:"",url:"/pd/cargovoyage"},
		{menutext:"CARGO MANAGEMENT",desc:"",url:"/pd/demurrageebo"},
		{menutext:"CARGO<br>MONITORING",desc:"",url:"/pd/liftaccdailybalance"},
//		{menutext:"VOYAGE GROUND",desc:"",url:"../pd/?func=voyageground"},
//		{menutext:"VOYAGE PIPELINE",desc:"",url:"../pd/?func=voyagepipeline"}
	];
menu["greenhouse"]=[
		{menutext:"EMISSION<br>SOURCES",desc:"",url:"../ghg/index.php/emission"},
		{menutext:"EMISSION<br>ENTRY",desc:"",url:"../ghg/index.php/emissionEntry"},
		{menutext:"EMISSION<br>RELEASED",desc:"",url:"../ghg/index.php/emissionReleased"},
		{menutext:"EMISSION<br>ALLOCATION",desc:"",url:"../ghg/index.php/emissionAllocation"},
		{menutext:"EMISSION<br>REPORT",desc:"",url:"../ghg/index.php/emissionReport"}
	];
<?php
// }
?>
menu["admin"]=[
		{menutext:"VALIDATE DATA",desc:"",url:"	/am/validatedata"},
		{menutext:"APPROVE DATA",desc:"",url:"/am/approvedata"},
		{menutext:"LOCK DATA",desc:"",url:"/am/lockdata"},
		{menutext:"ROLES",desc:"",url:"/am/roles"},
		{menutext:"USERS",desc:"",url:"/am/users"},
		{menutext:"Audit Trail",desc:"",url:"/am/audittrail"},
		{menutext:"USERS LOG",desc:"",url:"/am/userlog"},
		{menutext:"HELP EDITOR",desc:"",url:"/am/helpeditor"}
	];
menu["config"]=[
		{menutext:"Fields Config",desc:"",url:"/fieldsconfig"},
		{menutext:"Tables Data",desc:"",url:"/loadtabledata"},
// 		{menutext:"PD Tables",desc:"",url:"/pdtabledata"},
		{menutext:"Tags Mapping",desc:"",url:"/tagsMapping"},
		{menutext:"Formula Editor",desc:"",url:"/formula"},
		{menutext:"View Config",desc:"",url:"/viewconfig"},
	];
menu["interface"]=[
		{menutext:"IMPORT DATA",desc:"Import Tags Spreadsheet",url:"/importdata"},
		{menutext:"SOURCE CONFIG",desc:"",url:"/sourceconfig"},
		{menutext:"DATA LOADER",desc:"",url:"/dataloader"}
	];
function func(o)
{
	var menuID=$(o).parent().attr("code");
	if(menuID=='back')
	{
		$( "#boxFunctions" ).fadeIn( 500, function() {
			//$( "span" ).fadeIn( 100 );
		});
		$( "#boxMenu" ).fadeOut( 500, function() {
			//$( "span" ).fadeIn( 100 );
		});
		//$("#boxMenu").hide();
		//$("#boxFunctions").show();
		return;
	}
	var a=menu[menuID];
	if(typeof(a) == "undefined") return ;
	
	if(a.length==0)
	{
		//alert("This function is not available");
		//$('#myModal').foundation('reveal', 'open');
	}
	//else if(a.length==1)
	//	document.location.href=a[0]["url"];
	else
	{
		$("#menuName h4").html($(o).parent().find("h4").html());
		var i;
		for(i=0;i<a.length;i++)
		{
 			$("#menu"+i+" h4").html(a[i]["menutext"].toUpperCase());
// 			$("#menu"+i+" h4").html(a[i]["menutext"]);
			$("#menu"+i+" a").attr("href",a[i]["url"]);
			$("#menu"+i).attr("class",$("#menu"+i).attr("old_class"));
			$("#menu"+i).css("opacity","none");
		}
		for(i=a.length;i<8;i++)
		{
			//$("#menu"+i).hide();
			$("#menu"+i+" h4").html("");
			$("#menu"+i+" a").attr("href","#");
			$("#menu"+i).attr("class","hex_dim");
			//$("#menu"+i).css("opacity",0.5);
		}
		
		$( "#boxFunctions" ).fadeOut( 500, function() {
			//$( "span" ).fadeIn( 100 );
		});
		$( "#boxMenu" ).fadeIn( 500, function() {
			//$( "span" ).fadeIn( 100 );
		});
		//$("#boxMenu").show();
		//$("#boxFunctions").hide();
	}
}
$("#boxMenu").children().each(function(){
	$(this).attr("old_class",$(this).attr("class"));
	//alert($(this).attr("old_class"));
});



<?php
/* $is_logged_in = false;
if($is_logged_in)
{
	echo "layoutUserLoggedIn();";
}
else
	echo "$('#boxUserInfo').hide();"; */

// echo "\r\nvar _redirect='".($_REQUEST['redirect']?base64_decode($_REQUEST['redirect']):"")."';";
?>


function logineb(){

	if(!$('#username').val())
	{
		alert('Please input username');
		$('#username').select();
		$('#username').focus();
		return;
	}
	if(!$('#password').val())
	{
		alert('Please input password');
		$('#password').select();
		$('#password').focus();
		return;
	}
	is_logging_in=true;
	randomCell();
	
  var usn = $('input[name=username]').val();
  var pw = $('input[name=password]').val();
  var tk = $('input[name=_token]').val();
          
  $.ajax({
    url: '/auth/eblogin',
    type: "post",
    data: {username:usn,password:pw},
    success: function(data){
//       alert(data);
		is_logging_in=false;
// 		var _redirect = '/login/success';
		var _redirect = false;
      if(_redirect) 
			window.location.href=_redirect;
		else
		{
			username=$('#username').val();
			layoutUserLoggedIn(true);
			$('#textUsername').html(username);
			loadTasksCounting();
			language	= data.language;
			oldLanguage	= "{{session('locale')}}";
			if(typeof language == 'string' &&language!=oldLanguage) location.reload();
		}
    },
    error: function(data) {
		is_logging_in=false;
    	alert(data.responseText);
    }
  });    
}
	</script>

	@if((session('statut') != null) && (session('statut') != '') && session('statut') != 'visitor')
		<script type="text/javascript">
			layoutUserLoggedIn();
		</script>
	@else
		<script type="text/javascript">
			$('#boxUserInfo').hide();
		</script>
	@endif

</body>
</html>