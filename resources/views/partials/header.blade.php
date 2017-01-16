<?php 
		$current_username = '';
		if((auth()->user() != null)) $current_username = auth()->user()->username;
	?>
<link href="/common/css/header_menu.css" rel="stylesheet"/>

<div id="menu-wrapper">
	 <a href="/"><img src="/img/eb2.png" height="40" style="position:absolute;z-index:2;top:10px;left:20px"></a>
	<ul class="nav" id = "menu_nav"></ul>
</div>

	 <div id="user_box" style="position:absolute;top:8px;right:10px;text-align:right">
	 <span style="cursor:pointer" onclick="location.href='/me/setting';"><font color="#33b5e8"><span id="textUsername">{{$current_username}}</span></font></span>
	 <img src="/img/settings.png" onclick="showWorkflow()" height="24" width="24" id="uf_settings" title="Settings" style="">
	 <img src="/img/help.png" onclick="showHelp()" title="Help on this function" style="">
	 <a href="/auth/logout"><img src="/img/logout.png" title="Log out" style=""></a>
<div id="wf_notify_box" onclick="showWorkflow()" style="display:;position:absolute;right:58px;top:23px;width:16px;height:16px;font-family:Arial;background:red;border:2px solid white;border-radius:12px;font-size:6pt;font-weight:bold;color:white;cursor:pointer;text-align:center;line-height:12px;letter-spacing: -1px;text-indent:-1px;box-sizing: border-box;">
<span id="wf_notify" style="padding-left:3px;text-align:center;font-size:7pt">1</span>
</div></div>

<script>
var xmenu=
[
	{"name":"PRODUCTION","link":"/home/pm",background_img:"oilgas.png","columns":
		[
			{"blocks":
				[
					{"code":"","name":"Production management","title":"","link":"/home/pm","menus":
						[
							{"name":"Flow stream","title":"Flow Data Capture","link":"/dc/flow"},
							{"name":"Energy Unit","title":"Energy Unit Data Capture","link":"/dc/eu"},
							{"name":"Tank & Storage","title":"Tank & Storage","link":"/dc/storage"},
							{"name":"Tank Ticket","title":"Tank Ticket Data Capture","link":"/dc/ticket"},
							{"name":"Well Test","title":"Well Test Data Capture","link":"/dc/eutest"},
							{"name":"Deferment & MMR","title":"Deferment & MMR Data Capture","link":"/dc/deferment"},
							{"name":"Quality Data","title":"Quality Data","link":"/dc/quality"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Field Operations","title":"","link":"/home/fo","menus":
						[
							{"name":"Safety","title":"Safety","link":"/fo/safety"},
							{"name":"Comments","title":"Comments","link":"/fo/comment"},
							{"name":"Equipment","title":"Equipment","link":"/fo/equipment"},
							{"name":"Chemical","title":"Chemical","link":"/fo/chemical"},
							{"name":"Personnel","title":"Personnel","link":"/fo/personnel"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Allocation","title":"Allocation","link":"/home/allocation","menus":
						[
							{"name":"Run Allocation","title":"Run Allocation","link":"/allocrun"},
						]
					},
				]
			}
		]
	},
	{"name":"Data Visualization","link":"/home/dv",background_img:"oilgas.png","columns":
		[
			{"blocks":
				[
					{"code":"","name":"Data Visualization","title":"","link":"/home/dv","menus":
						[
							{"name":"Network Models","title":"Network Models","link":"/diagram"},
							{"name":"Data Views","title":"Data Views","link":"/dataview"},
							{"name":"Reports","title":"Reports","link":"/workreport"},
							{"name":"Advanced Graph","title":"Advanced Graph","link":"/graph"},
							{"name":"Workflow","title":"Workflow","link":"/workflow"},
							{"name":'{{ trans("front/site.Dashboard") }}',"title":'{{ trans("front/site.Dashboard") }}',"link":"/dashboard"},
							{"name":"Choke Model","title":"Choke Model","link":"/fp/choke"},
							{"name":"Task Manager","title":"Task Manager","link":"/dv/taskman"},
							{"name":"Storage Display","title":"Storage Display","link":"/pd/storagedisplay"},
						]
					},
				]
			}
		]
	},
	{"name":"PRODUCT DELIVERY","link":"/home/pd",background_img:"delivery2.png","columns":
		[
			{"blocks":
				[
					{"code":"","name":"Contract Admin","title":"","link":"/home/pd","menus":
						[
							{"name":"Contract Data","title":"Contract Data","link":"/pd/contractdata"},
							{"name":"Contract Calculation","title":"Contract Calculation","link":"/pd/contractcalculate"},
							{"name":"Contract Template","title":"Contract Template","link":"/pd/contracttemplate"},
							{"name":"Cargo Program","title":"Cargo Program","link":"/pd/contractprogram"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Cargo Admin","title":"","link":"/home/pd","menus":
						[
							{"name":"Cargo Entry","title":"Cargo Entry","link":"/pd/cargoentry"},
							{"name":"Cargo Nomination","title":"Cargo Nomination","link":"/pd/cargonomination"},
							{"name":"Cargo Schedule","title":"Cargo Schedule","link":"/pd/cargoschedule"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Cargo Action","title":"","link":"/home/pd","menus":
						[
							{"name":"Cargo Voyage","title":"Cargo Voyage","link":"/pd/cargovoyage"},
							{"name":"Cargo Load","title":"Cargo Load","link":"/pd/cargoload"},
							{"name":"Cargo Unload","title":"Cargo Unload","link":"/pd/cargounload"},
							{"name":"Voyage Marine","title":"Voyage Marine","link":"/pd/voyagemarine"},
							{"name":"Voyage Ground","title":"Voyage Ground","link":"/pd/voyageground"},
							{"name":"Voyage Pipeline","title":"Voyage Pipeline","link":"/pd/voyagepipeline"},
							{"name":"BL/MR","title":"BL/MR","link":"/pd/shipblmr"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Cargo Management","title":"","link":"/home/pd","menus":
						[
							{"name":"Demurrage/EBO","title":"Demurrage/EBO","link":"/pd/demurrageebo"},
							{"name":"Cargo Documents","title":"Cargo Documents","link":"/pd/cargodocuments"},
							{"name":"Cargo Status","title":"Cargo Status","link":"/pd/cargostatus"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Cargo Monitoring","title":"","link":"/home/pd","menus":
						[
							{"name":"LIFTING ACCT DAILY BALANCE","title":"LIFTING ACCT DAILY BALANCE","link":"/pd/liftaccdailybalance"},
							{"name":"LIFTING ACCT MONTHLY DATA","title":"LIFTING ACCT MONTHLY DATA","link":"/pd/liftaccmonthlyadjust"},
							{"name":"CARGO PLANNING","title":"CARGO PLANNING","link":"/pd/cargoplanning"},
						]
					},
				]
			},
		]
	},
	{"name":"GHG","link":"/home/gg",background_img:"ghg2.png","columns":
		[
			{"blocks":
				[
					{"code":"","name":"Emission Sources","title":"","link":"/home/gg","menus":
						[
							{"name":"Combustion","title":"","link":"#"},
							{"name":"Indirect","title":"","link":"#"},
							{"name":"Events","title":"","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Emission Entry","title":"","link":"/home/gg","menus":
						[
							{"name":"Combustion","title":"","link":"#"},
							{"name":"Indirect","title":"","link":"#"},
							{"name":"Events","title":"","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Emission Release","title":"","link":"/home/gg","menus":
						[
							{"name":"Combustion","title":"","link":"#"},
							{"name":"Indirect","title":"","link":"#"},
							{"name":"Events","title":"","link":"#"},
						]
					},
				]
			},
		]
	},
	{"name":"ADMIN","link":"/home/administrator",background_img:"config.png","columns":
		[
			{"blocks":
				[
					{"code":"","name":"Transaction Data","title":"","link":"/home/administrator","menus":
						[
							{"name":"Validate Data","title":"VALIDATE DATA","link":"/am/validatedata"},
							{"name":"Approve Data","title":"APPROVE DATA","link":"/am/approvedata"},
							{"name":"Lock Data","title":"DATA LOCKING","link":"/am/lockdata"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Administrator","title":"","link":"/home/administrator","menus":
						[
							{"name":"Users","title":"Users Management","link":"/am/users"},
							{"name":"Roles","title":"Roles Settings","link":"/am/roles"},
							{"name":"User Logs","title":"USERS LOG","link":"/am/userlog"},
							{"name":"Audit Trail","title":"Audit Trail","link":"/am/audittrail"},
// 							{"name":"User Settings","title":"","link":"#"},
							{"name":"Password & Preferences","title":"Password & Preferences","link":"/me/setting"},
							{"name":"Help Editor","title":"HELP EDITOR","link":"/am/helpeditor"},
						]
					},
				]
			},
		]
	},
	{"name":"CONFIG","link":"/home/sc",background_img:"config.png","columns":
		[
			{"blocks":
				[
					{"code":"","name":"System Configuration","title":"","link":"/home/sc","menus":
						[
							{"name":"Fields Config","title":"Fields Config","link":"/fieldsconfig"},
							{"name":"Table Data","title":"Table Data","link":"/loadtabledata"},
							{"name":"PD Table","title":"PD Table","link":"/pdtabledata"},
							{"name":"Tags Mapping","title":"TAG MAPPING CONFIG","link":"/tagsMapping"},
							{"name":"View Config","title":"View Config","link":"/viewconfig"},
							{"name":"Formula Editor","title":"Formula Editor","link":"/formula"},
							{"name":"Menu Config","title":"Menu Config","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Interface","title":"","link":"/home/interfaces","menus":
						[
							{"name":"Source Config","title":"Source Config","link":"/sourceconfig"},
							{"name":"Import Data","title":"Import Data","link":"/importdata"},
							{"name":"Data Loader","title":"Data Loader","link":"/dataloader"},
							{"name":"Dashboard Config","title":"Dashboard Config","link":"/config/dashboard"},
						]
					},
					{"code":"","name":"Allocation","title":"","link":"/home/allocation","menus":
						[
							{"name":"Allocation Config","title":"CONFIG ALLOCATION","link":"/allocset"},
						]
					},
				]
			}
		]
	},
	{"name":"Forecast & Planning","link":"/home/fp",background_img:"oilgas.png","columns":
		[
			{"blocks":
				[
					{"code":"","name":"Forecast & Planning","title":"","link":"/home/fp","menus":
						[
							{"name":"Well Forecast","title":"WELL FORECAST","link":"/fp/forecast"},
							{"name":"PREoS","title":"PENG-ROBINSON EQUATION OF STATE","link":"/fp/preos"},
							{"name":"Manual Allocate Plan","title":"MANUAL ALLOCATE PLAN","link":"/fp/allocateplan"},
							{"name":"Manual Allocate Forecast","title":"MANUAL ALLOCATE FORECAST","link":"/fp/allocateforecast"},
							{"name":"Load Plan/Forecast","title":"LOAD PLAN/FORECAST DATA","link":"/fp/loadplanforecast"},
						]
					},
				]
			}
		]
	},
];
var active_link='{{$currentSubmenu}}';
var html_group="";
var html_col="";
var html_block="";
var html_menu="";
activeTitle = "";
for(var i = 0; i < xmenu.length; i++) {
	var group=xmenu[i];
	var is_group_active=false;
	if(group.columns!==null){
		html_col='';
		for(var i2 = 0; i2 < group.columns.length; i2++) {
			var col=group.columns[i2];
			if(col.blocks!==null){
				html_block="";
				for(var i3 = 0; i3 < col.blocks.length; i3++) {
					var block=col.blocks[i3];
					var is_block_active=false;
					html_menu="";
					if(block.menus!==null){
						for(var i4 = 0; i4 < block.menus.length; i4++) {
							var menu=block.menus[i4];
							var is_menu_active=false;
							if(menu.link==active_link){
								is_menu_active=true;
								is_block_active=true;
								is_group_active=true;
							}
							html_menu+=is_menu_active?'<li class="activex" ><a href="#">'+menu.name+'</a></li>':
								'<li><a href="'+menu.link+'">'+menu.name+'</a></li>';
							activeTitle = is_menu_active?menu.title:activeTitle;
						}
					}
					html_block+='<div class="nav-cell'+(is_block_active?' activex':'')+'"><a href="'+block.link+'"><h3>'+block.name+'</h3></a><ul>'+html_menu+'</ul></div>';
				}
				html_col+='<div class="nav-column">'+html_block+'</div>';
			}
		}
	}
	if(group.link==active_link) is_group_active=true;
	html_group+='<li'+(is_group_active?' class="activex"':'')+'><a href="'+group.link+'">'+group.name+'</a>';
	if(group.columns!==null)
		html_group+='<div'+(group.background_img!==""?' style="background-image:url(/img/'+group.background_img+');background-repeat: no-repeat;background-position: right bottom;"':'')+'>'+html_col+'</div>';
	html_group+='</li>';
}
menuHtml ='<li class="active_item">'+activeTitle+'</li>'+html_group;

var element = document.getElementById("menu_nav");
element.innerHTML += menuHtml; 
</script>

<div style="display:none;">
	@include('partials.user')
</div>
