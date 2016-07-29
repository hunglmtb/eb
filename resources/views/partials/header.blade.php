<?php 
		$current_username = '';
		if((auth()->user() != null)) $current_username = auth()->user()->username;
	?>
<link href="/common/css/header_menu.css" rel="stylesheet"/>

<div id="menu-wrapper">
	<img src="/img/eb2.png" height="40" style="position:absolute;z-index:2;top:10px;left:20px">
	<ul class="nav" id = "menu_nav"></ul>
</div>

	 <div id="user_box" style="position:absolute;top:8px;right:10px;text-align:right">
	 <span>{{$current_username}}
	 </span>
	 <img src="/img/settings.png" onclick="showWorkflow()" height="24" width="24" id="uf_settings" title="Settings" style="">
	 <img src="/img/help.png" onclick="showHelp()" title="Help on this function" style="">
	 <a href="/auth/logout"><img src="/img/logout.png" title="Log out" style=""></a>
<div id="wf_notify_box" onclick="showWorkflow()" style="display:;position:absolute;right:58px;top:23px;width:16px;height:16px;font-family:Arial;background:red;border:2px solid white;border-radius:12px;font-size:6pt;font-weight:bold;color:white;cursor:pointer;text-align:center;line-height:12px;letter-spacing: -1px;text-indent:-1px;box-sizing: border-box;">
<span id="wf_notify" style="padding-left:3px;text-align:center;font-size:7pt">1</span>
</div></div>

<script>
var xmenu=
[
	{"name":"DASHBOARD","link":"/",background_img:"","columns":null},
	{"name":"PRODUCTION","link":"#",background_img:"oilgas.png","columns":
		[
			{"blocks":
				[
					{"code":"","name":"Production management","title":"","link":"#","menus":
						[
							{"name":"Flow stream","title":"Flow Data Capture","link":"/dc/flow"},
							{"name":"Energy Unit","title":"Energy Unit Data Capture","link":"/dc/eu"},
							{"name":"Tank & Storage","title":"Tank & Storage Data Capture","link":"/dc/storage"},
							{"name":"Tank Ticket","title":"Tank Ticket Data Capture","link":"/dc/ticket"},
							{"name":"Well Test","title":"Well Test Data Capture","link":"/dc/eutest"},
							{"name":"Deferment","title":"Deferment Data Capture","link":"/dc/deferment"},
							{"name":"Quality Data","title":"Quality Data","link":"/dc/quality"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Field Operations","title":"","link":"#","menus":
						[
							{"name":"Safety","title":"Safety","link":"/fo/safety"},
							{"name":"Comments","title":"Comments","link":"/fo/comment"},
							{"name":"Equipment","title":"Equipment","link":"/fo/equipment"},
							{"name":"Chemical","title":"Chemical","link":"/fo/chemical"},
							{"name":"Personel","title":"Personel","link":"/fo/personnel"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Data Visualization","title":"","link":"#","menus":
						[
							{"name":"Network Models","title":"","link":"#"},
							{"name":"Data Views","title":"","link":"#"},
							{"name":"Reports","title":"","link":"#"},
							{"name":"Graph Plotting","title":"","link":"#"},
							{"name":"Workflow","title":"","link":"#"},
							{"name":"Task Manager","title":"","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Allocation","title":"","link":"#","menus":
						[
							{"name":"Run Allocation","title":"","link":"#"},
							{"name":"Allocation Config","title":"","link":"#"},
						]
					},
					{"code":"","name":"Interface","title":"","link":"#","menus":
						[
							{"name":"Import Data","title":"","link":"#"},
							{"name":"Data Loader","title":"","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Forecast & Planning","title":"","link":"#","menus":
						[
							{"name":"Well Forecast","title":"WELL FORECAST","link":"/fp/forecast"},
							{"name":"PREoS","title":"PENG-ROBINSON EQUATION OF STATE","link":"/fp/preos"},
							{"name":"Manual Allocate Plan","title":"MANUAL ALLOCATE PLAN","link":"/fp/allocateplan"},
							{"name":"Load Plan/Forecast","title":"LOAD PLAN/FORECAST DATA","link":"/fp/loadplanforecast"},
						]
					},
				]
			},
		]
	},
	{"name":"PRODUCT DELIVERY","link":"#",background_img:"delivery2.png","columns":
		[
			{"blocks":
				[
					{"code":"","name":"Contract Admin","title":"","link":"#","menus":
						[
							{"name":"Contract Data","title":"","link":"#"},
							{"name":"Contract Calculation","title":"","link":"#"},
							{"name":"Contract Template","title":"","link":"#"},
							{"name":"Cargo Program","title":"","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Cargo Admin","title":"","link":"#","menus":
						[
							{"name":"Cargo Entry","title":"Cargo Entry","link":"/pd/cargoentry"},
							{"name":"Cargo Nomination","title":"Cargo Nomination","link":"/pd/cargonomination"},
							{"name":"Cargo Schedule","title":"Cargo Schedule","link":"/pd/cargoschedule"},
							{"name":"Storage Display","title":"Storage Display","link":"/pd/cargodisplay"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Cargo Action","title":"","link":"#","menus":
						[
							{"name":"Cargo Voyage","title":"","link":"#"},
							{"name":"Cargo Load","title":"","link":"#"},
							{"name":"Cargo Unload","title":"","link":"#"},
							{"name":"Voyage Marine","title":"","link":"#"},
							{"name":"Voyage Ground","title":"","link":"#"},
							{"name":"Voyage Pipeline","title":"","link":"#"},
							{"name":"BL/MR","title":"","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Cargo Management","title":"","link":"#","menus":
						[
							{"name":"Demurrage/EBO","title":"","link":"#"},
							{"name":"Cargo Documents","title":"","link":"#"},
							{"name":"Cargo Status","title":"","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Cargo Monitoring","title":"","link":"#","menus":
						[
							{"name":"LIFTING ACCT DAILY BALANCE","title":"","link":"#"},
							{"name":"LIFTING ACCT MONTHLY DATA","title":"","link":"#"},
						]
					},
				]
			},
		]
	},
	{"name":"GHG","link":"#",background_img:"ghg2.png","columns":
		[
			{"blocks":
				[
					{"code":"","name":"Emission Sources","title":"","link":"#","menus":
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
					{"code":"","name":"Emission Entry","title":"","link":"#","menus":
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
					{"code":"","name":"Emission Release","title":"","link":"#","menus":
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
	{"name":"CONFIG","link":"#",background_img:"config.png","columns":
		[
			{"blocks":
				[
					{"code":"","name":"Transaction Data","title":"","link":"#","menus":
						[
							{"name":"Validate Data","title":"","link":"#"},
							{"name":"Approve Data","title":"","link":"#"},
							{"name":"Lock Data","title":"","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Administrator","title":"","link":"#","menus":
						[
							{"name":"Users","title":"","link":"#"},
							{"name":"Roles","title":"","link":"#"},
							{"name":"User Logs","title":"","link":"#"},
							{"name":"Audit Trail","title":"","link":"#"},
							{"name":"Help Editor","title":"","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"System Configuration","title":"","link":"#","menus":
						[
							{"name":"Fields Config","title":"","link":"#"},
							{"name":"Table Data","title":"","link":"#"},
							{"name":"Tags Mapping","title":"TAG MAPPING CONFIG","link":"/tagsMapping"},
							{"name":"View Config","title":"","link":"#"},
							{"name":"Formula Editor","title":"","link":"#"},
							{"name":"Menu Config","title":"","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"Interface","title":"","link":"#","menus":
						[
							{"name":"Source Config","title":"","link":"#"},
						]
					},
					{"code":"","name":"Allocation","title":"","link":"#","menus":
						[
							{"name":"Allocation Config","title":"","link":"#"},
						]
					},
				]
			},
			{"blocks":
				[
					{"code":"","name":"User","title":"","link":"#","menus":
						[
							{"name":"Dashboard Config","title":"","link":"#"},
							{"name":"User Settings","title":"","link":"#"},
							{"name":"Change Password","title":"Change Password","link":"/me/setting"},
						]
					},
				]
			},
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
					html_block+='<div class="nav-cell'+(is_block_active?' activex':'')+'"><h3>'+block.name+'</h3><ul>'+html_menu+'</ul></div>';
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
