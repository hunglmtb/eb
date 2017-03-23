<?php
$xmenu	= [];
$xmenu["production"]=[
	"text"  =>"Production Management",
	"display" => 1,
	"sub" =>[
		["text"  =>"Flow Stream","desc" => "","url" => "/dc/flow"],
		["text"  =>"Energy Unit","desc" => "","url" => "/dc/eu"],
		["text"  =>"Tank & Storage","desc" => "","url" => "/dc/storage"],
		["text"  =>"Ticket","desc" => "","url" => "/dc/ticket"],
		["text"  =>"Well Test","desc" => "","url" => "/dc/eutest"],
		["text"  =>"Deferment & MMR","desc" => "","url" => "/dc/deferment"],
		["text"  =>"Quality","desc" => "","url" => "/dc/quality"]
	]
];
$xmenu["operation"]=[
	"text"  =>"Field Operations",
	"display" => 1,
	"sub" =>[
		["text"  =>"Safety","desc" => "","url" => "/fo/safety"],
		["text"  =>"Comments","desc" => "","url" => "/fo/comment"],
		["text"  =>"Equipment","desc" => "","url" => "/fo/equipment"],
		["text"  =>"Chemical","desc" => "","url" => "/fo/chemical"],
		["text"  =>"Personnel","desc" => "","url" => "/fo/personnel"]
	]
];
$xmenu["visual"]=[
	"text"  =>"Data Visualization",
	"display" => 1,
	"sub" =>[
		["text"  =>"Network Model","desc" => "","url" => "/diagram"],
		["text"  =>"Data View","desc" => "","url" => "/dataview"],
		["text"  =>"Report","desc" => "","url" => "/workreport"],
		["text"  =>"Advanced Graph","desc" => "","url" => "/graph"],
		["text"  =>"Workflow","desc" => "","url" => "/workflow"],
		["text"  =>"Choke Model","desc" => "","url" => "/fp/choke"],
		["text"  =>'Dashboard',"desc"=>"","url" => "/dashboard"],
		["text"  =>"Task Manager","desc" => "","url" => "/dv/taskman"],
		["text"  =>"Storage Display","desc" => "","url" => "/pd/storagedisplay"],
	]
];
$xmenu["allocation"]=[
	"text"  =>"Allocation",
	"display" => 1,
	"sub" =>[
		["text"  =>"Run Allocation","desc" => "","url" => "/allocrun"],
		["text"  =>"Config Allocation","desc" => "","url" => "/allocset"]
	]
];
$xmenu["forecast"]=[
	"text"  =>"Forecast & Planning",
	"display" => 1,
	"sub" =>[
		["text"  =>"WELL FORECAST","desc" => "","url" => "/fp/forecast"],
		["text"  =>"PREoS","desc" => "","url" => "../fp/preos"],
		["text"  =>"MANUAL ALLOCATE PLAN","desc" => "","url" => "/fp/allocateplan"],
		["text"  =>"MANUAL ALLOCATE FORECAST","desc" => "","url" => "/fp/allocateforecast"],
		["text"  =>"LOAD PLAN/FORECAST","desc" => "","url" => "/fp/loadplanforecast"],
	]
];
$xmenu["delivery"]=[
	"text"  =>"Product Delivery",
	"display" => 1,
	"sub" =>[
		["text"  =>"CONTRACT ADMIN","desc" => "","url" => "/pd/contractdata"],
		["text"  =>"CARGO ADMIN","desc" => "","url" => "/pd/cargoentry"],
		["text"  =>"CARGO ACTION","desc" => "","url" => "/pd/cargovoyage"],
		["text"  =>"CARGO MANAGEMENT","desc" => "","url" => "/pd/demurrageebo"],
		["text"  =>"CARGO MONITORING","desc" => "","url" => "/pd/liftaccdailybalance"],
	]
];
$xmenu["greenhouse"]=[
	"text"  =>"Greenhouse Gas",
	"display" => 1,
	"sub" =>[
		["text"  =>"EMISSION SOURCES","desc" => "","url" => "../ghg/index.php/emission"],
		["text"  =>"EMISSION ENTRY","desc" => "","url" => "../ghg/index.php/emissionEntry"],
		["text"  =>"EMISSION RELEASED","desc" => "","url" => "../ghg/index.php/emissionReleased"],
		["text"  =>"EMISSION ALLOCATION","desc" => "","url" => "../ghg/index.php/emissionAllocation"],
		["text"  =>"EMISSION REPORT","desc" => "","url" => "../ghg/index.php/emissionReport"]
	]
];
$xmenu["admin"]=[
	"text"  =>"Administrator",
	"display" => 1,
	"sub" =>[
		["text"  =>"VALIDATE DATA","desc" => "","url" => "/am/validatedata"],
		["text"  =>"APPROVE DATA","desc" => "","url" => "/am/approvedata"],
		["text"  =>"LOCK DATA","desc" => "","url" => "/am/lockdata"],
		["text"  =>"ROLES","desc" => "","url" => "/am/roles"],
		["text"  =>"USERS","desc" => "","url" => "/am/users"],
		["text"  =>"Audit Trail","desc" => "","url" => "/am/audittrail"],
		["text"  =>"USERS LOG","desc" => "","url" => "/am/userlog"],
		["text"  =>"HELP EDITOR","desc" => "","url" => "/am/helpeditor"]
	]
];
$xmenu["config"]=[
	"text"  =>"System Configuration",
	"display" => 1,
	"sub" =>[
		["text"  =>"Fields Config","desc" => "","url" => "/fieldsconfig"],
		["text"  =>"Tables Data","desc" => "","url" => "/loadtabledata"],
		["text"  =>"Tags Mapping","desc" => "","url" => "/tagsMapping"],
		["text"  =>"Formula Editor","desc" => "","url" => "/formula"],
		["text"  =>"View Config","desc" => "","url" => "/viewconfig"],
		["text"  =>"Dashboard Config","desc" => "","url" => "/config/dashboard"],
		["text"  =>"Objects Manager","desc" => "","url" => "/objectsmanager"],
	]
];
$xmenu["interfaces"]=[
	"text"  =>"Interface",
	"display" => 1,
	"sub" =>[
		["text"  =>"IMPORT DATA","desc" => "Import Tags Spreadsheet","url" => "/importdata"],
		["text"  =>"SOURCE CONFIG","desc" => "","url" => "/sourceconfig"],
		["text"  =>"DATA LOADER","desc" => "","url" => "/dataloader"]
	]
];
	

$lang = session()->get('locale', "en");
foreach($xmenu as $index => $object ){
	$smenu = $object["sub"];
	foreach($smenu as  $cindex => $menuItem ){
		$menuItem["text"]	= Lang::has("front/site.".$menuItem["text"], $lang)?
									trans("front/site.".$menuItem["text"]):$menuItem["text"];
		$menuItem["desc"]		= Lang::has("front/site.".$menuItem["desc"], $lang)?
									trans("front/site.".$menuItem["desc"]):$menuItem["desc"];
		$smenu[$cindex]	= $menuItem;
	}
	$xmenu[$index]["sub"] = $smenu;
	$xmenu[$index]["text"] = \Helper::translateText($lang,$xmenu[$index]["text"]);
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Energy Builder</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <meta name="keywords" content="oil,gas,energy,production" />
        <meta name="author" content="edataviz" />
		<meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
		<link rel="shortcut icon" href="../favicon.ico"> 
        <link rel="stylesheet" type="text/css" href="../css/style8.css" />
		<link rel="stylesheet" href="../css/hexagon.css?3">
		<link rel="stylesheet" href="../common/css/jquery-ui.css">
		<link rel="stylesheet" href="../common/css/style.css">

	    <script src="../common/js/jquery-1.9.1.js"></script> 
		<script src="../common/js/jquery-ui.js"></script>

		<script type="text/javascript" src="../common/utils.js"></script>
</head>
<body style="background-image:url('/img/bg2.png')">
<script>var func_code="ROOT";</script>
<style>
#boxUserInfo{display:none}
#bee{
	z-index:1000;
	display:block;
	position: absolute;
	width: 56px;
	height: 54px;
	left:50%;
	margin-left:1px;
	top:490px;
	transition: left 1500ms ease-in, top 1500ms ease-out;
}
</style>
@include('partials.user')

<div id="hex_logo">
	<img border="0" src="../img/eb2.png?1" >
</div>

<img id="bee" border="0" src="../img/bee.png">

<div id="poweredBy">	
	<div class="hex" style="background:#ffffff">
	<div class="inner" style="color:#333">
	Powered by
	<img width="110" src="../img/edataviz_logo.png" style="margin:5px 0px" alt="eDataViz">
	<font size="1">Copyright &copy; 2016 eDataViz LLC</font>
	</div>
	<a target="_blank" href="http://www.edataviz.com"></a>
	<div class="corner-1"></div>
	<div class="corner-2"></div>
	</div>	
</div>

<div class="hex_container" style="z-index:100" id="boxLogin">
	<div class="hex hex_disabled hex-gap" id="cell1">
		<div class="inner">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>
	
	<div class="hex hex_disabled" id="cell2">
		<div class="inner">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>


	<div class="hex hex_disabled" id="cell3">
		<div class="inner">
		</div>
		<a href="../diagram/index.htm"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
	<div class="hex hex_disabled" id="cell7">
		<div class="inner">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
	<div class="hex hex_disabled" style="background:#666">
		<div class="inner">
		<h4>Username</h4>
		<input class="r_textbox" type="text" style="width:120px;" id="username" name="username" value="" />
		<div style="margin-left:13px;width:120px;height:3px;border:1px solid #d08924;border-top:none"></div>
		</div>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
	<div class="hex hex_disabled" style="background:#666">
		<div class="inner">
		<h4>Password</h4>
		<input class="r_textbox" type="password" style="width:120px;" value="" id="password" name="password" />
		<div style="margin-left:13px;width:120px;height:3px;border:1px solid #d08924;border-top:none"></div>
		</div>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>
	
	<div class="hex hex_disabled" id="cell4">
		<div class="v_top">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
	<div class="hex hex_disabled hex-gap" id="cell6">
		<div class="inner">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
	
	<div class="hex hex-login">
		<div class="inner">
			<img style="display:none;position: absolute; z-index: 200;top:80px;left:85px;" width="47" border="0" src="../img/bee.png">
			<h4>LOG IN</h4>
		</div>
		<a href="javascript:logineb()"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>

	
	<div class="hex hex_disabled" id="cell5">
		<div class="inner">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
</div>

<div class="hex_container" style="display:none" id="boxFunctions">
<?php
$i = 0;
foreach($xmenu as $code => $object ){
	$i++;
	$text = $object["text"];
	$enabled = $object["display"] == 1;
	$class = "hex";
	if($i == 1 || $i == 6 || $i == 8)
		$class .= " hex-1";
	else if($i == 2 || $i == 4 || $i == 7 || $i == 9)
		$class .= " hex-2";
	else
		$class .= " hex-3";
	if($i == 1 || $i == 8)
		$class .= " hex-gap";
?>
	<div class="menu {{$class}}" base_class="{{$class}}" base_text="{{$text}}" url="" id="func_{{$code}}" index="{{$i}}" code="{{$code}}" onclick="func(this)">
		<div class="inner" id="{{$code}}">
			<h4><span id="menu_text">{{$text}}</span><span id="menu_back"></span></h4>
		</div>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>
<?php
}
?>
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
		func("#func_"+submenu);
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
		$("#bee").css({left:-100, top: -100});
/* 		$( "#bee" ).stop().animate({
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
 */	}
	else $( "#bee" ).hide();
}
function layoutUserLoggedOut()
{
	window.location.reload();
}
var menuBox;
var submenu_idx={};
submenu_idx["1"] = [4,5,2,6,8,9,3,10,7];
submenu_idx["2"] = [5,6,1,3,9,4,7,8,10];
submenu_idx["3"] = [6,7,2,5,10,9,1,4,8];
submenu_idx["4"] = [5,8,1,6,9,2,7,10,3];
submenu_idx["5"] = [4,1,8,6,2,9,7,3,10];
submenu_idx["6"] = [5,2,9,7,3,10,1,8,4];
submenu_idx["7"] = [6,10,3,2,9,5,1,8,4];
submenu_idx["8"] = [4,5,9,6,1,2,10,7,3];
submenu_idx["9"] = [5,6,8,10,2,4,7,1,3];
submenu_idx["10"] = [6,7,9,5,3,2,8,1,4];
var menu = <?php echo json_encode($xmenu); ?>;
function showMainMenu(){
/* 	$( "#boxFunctions" ).fadeIn( 500, function() {
	});
	$( "#boxMenu" ).fadeOut( 500, function() {
	});
 */
	$(".menu").each(function(){
		$(this).attr("class","menu "+$(this).attr("base_class"));
		$(this).find("#menu_text").html($(this).attr("base_text"));
		//$(this).find("a").attr("href","#");
		$(this).attr("url","");
		//$(this).css("opacity","1");
	});
}

function func(menu_item)
{
	var menu_item = $(menu_item);
	if(menu_item.hasClass("hex_dim"))
		return;
	if(menu_item.attr("back")=='1')
	{
		menu_item.attr("back","");
		menu_item.find("#menu_back").html("");
		showMainMenu();
		return;
	}
	
	var url = menu_item.attr("url");
	if(typeof url == "string" && url.length > 0){
		window.location = url;
		return;
	}

	var menuCode=menu_item.attr("code");
	if(menu[menuCode] == undefined) return ;
	if(menu[menuCode].sub == undefined) return ;
	var a=menu[menuCode].sub;
	
	if(a.length > 0){
		var menu_item_index=menu_item.attr("index");
		menu_item.attr("back","1");
		menu_item.find("#menu_back").html("<hr>HOME");
		
		for(var i=0;i<submenu_idx[menu_item_index].length;i++){
			var m = $("#func_"+submenu_idx[menu_item_index][i]);
			if(i < a.length){
				m.removeClass("hex-1").removeClass("hex-2").removeClass("hex-3").addClass("hex-m");
				m.find("#menu_text").html(a[i]["text"]);
				m.attr("url",a[i]["url"]);
			}
			else{
				m.removeClass("hex-1").removeClass("hex-2").removeClass("hex-3").addClass("hex_dim");
			}
			//menu.css("opacity","1");
		}
	}
}

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
function shadeBlendConvert(p, from, to) {
    if(typeof(p)!="number"||p<-1||p>1||typeof(from)!="string"||(from[0]!='r'&&from[0]!='#')||(typeof(to)!="string"&&typeof(to)!="undefined"))return null; //ErrorCheck
    if(!this.sbcRip)this.sbcRip=function(d){
        var l=d.length,RGB=new Object();
        if(l>9){
            d=d.split(",");
            if(d.length<3||d.length>4)return null;//ErrorCheck
            RGB[0]=i(d[0].slice(4)),RGB[1]=i(d[1]),RGB[2]=i(d[2]),RGB[3]=d[3]?parseFloat(d[3]):-1;
        }else{
            if(l==8||l==6||l<4)return null; //ErrorCheck
            if(l<6)d="#"+d[1]+d[1]+d[2]+d[2]+d[3]+d[3]+(l>4?d[4]+""+d[4]:""); //3 digit
            d=i(d.slice(1),16),RGB[0]=d>>16&255,RGB[1]=d>>8&255,RGB[2]=d&255,RGB[3]=l==9||l==5?r(((d>>24&255)/255)*10000)/10000:-1;
        }
        return RGB;}
    var i=parseInt,r=Math.round,h=from.length>9,h=typeof(to)=="string"?to.length>9?true:to=="c"?!h:false:h,b=p<0,p=b?p*-1:p,to=to&&to!="c"?to:b?"#000000":"#FFFFFF",f=sbcRip(from),t=sbcRip(to);
    if(!f||!t)return null; //ErrorCheck
    if(h)return "rgb("+r((t[0]-f[0])*p+f[0])+","+r((t[1]-f[1])*p+f[1])+","+r((t[2]-f[2])*p+f[2])+(f[3]<0&&t[3]<0?")":","+(f[3]>-1&&t[3]>-1?r(((t[3]-f[3])*p+f[3])*10000)/10000:t[3]<0?f[3]:t[3])+")");
    else return "#"+(0x100000000+(f[3]>-1&&t[3]>-1?r(((t[3]-f[3])*p+f[3])*255):t[3]>-1?r(t[3]*255):f[3]>-1?r(f[3]*255):255)*0x1000000+r((t[0]-f[0])*p+f[0])*0x10000+r((t[1]-f[1])*p+f[1])*0x100+r((t[2]-f[2])*p+f[2])).toString(16).slice(f[3]>-1||t[3]>-1?1:3);
}
$(".menu").each(function(){
	$(this).hover(function(){
			if($(this).hasClass("hex_dim")) return;
			$(this).css("background-color", shadeBlendConvert(0.25,$(this).css("background-color")));
		}, function(){
			$(this).css("background-color", "");
	});
});
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