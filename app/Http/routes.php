<?php
Route::get('/', [
		'uses' => 'HomeController@index',
		'as' => 'home'
]);
Route::get('language/{lang}', 'HomeController@language')->where('lang', '[A-Za-z_-]+');


// Admin
Route::get('admin', [
	'uses' => 'AdminController@admin',
	'as' => 'admin',
	'middleware' => 'admin'
]);

Route::get('medias', [
	'uses' => 'AdminController@filemanager',
	'as' => 'medias',
	'middleware' => 'redac'
]);


// Blog
Route::get('blog/order', ['uses' => 'BlogController@indexOrder', 'as' => 'blog.order']);
Route::get('articles', 'BlogController@indexFront');
Route::get('blog/tag', 'BlogController@tag');
Route::get('blog/search', 'BlogController@search');

Route::put('postseen/{id}', 'BlogController@updateSeen');
Route::put('postactive/{id}', 'BlogController@updateActive');

Route::resource('blog', 'BlogController');

// Comment
Route::resource('comment', 'CommentSampleController', [
	'except' => ['create', 'show']
]);

Route::put('commentseen/{id}', 'CommentController@updateSeen');
Route::put('uservalid/{id}', 'CommentController@valid');


// Contact
Route::resource('contact', 'ContactController', [
	'except' => ['show', 'edit']
]);


// User
/* Route::get('user/sort/{role}', 'UserController@indexSort');

Route::get('user/roles', 'UserController@getRoles');
Route::post('user/roles', 'UserController@postRoles');

Route::put('userseen/{user}', 'UserController@updateSeen');

Route::resource('user', 'UserController'); */

// Authentication routes...
Route::get('auth/login', 'HomeController@index');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::post('auth/eblogin', 'Auth\AuthController@postEBLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');
Route::get('auth/confirm/{token}', 'Auth\AuthController@getConfirm');
Route::get('login/success', 'HomeController@loginSuccess');

Route::get('home/{menu?}', 'HomeController@index');

// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');


//-----EB
Route::post('code/list', 'CodeController@getCodes');

Route::get('dc/flow',['uses' =>'ProductManagementController@flow','middleware' => 'checkRight:FDC_FLOW']);
Route::post('code/load',['uses' =>'FlowController@load','middleware' => 'saveWorkspace']);
Route::post('code/save', 'FlowController@save');
Route::post('code/history', 'FlowController@history');

Route::get('dc/eu', 'ProductManagementController@eu');
Route::get('dc/eu',['uses' =>'ProductManagementController@eu','middleware' => 'checkRight:FDC_EU']);
Route::post('eu/load',['uses' =>'EuController@load','middleware' => 'saveWorkspace']);
Route::post('eu/save', 'EuController@save');
Route::post('eu/history', 'EuController@history');

Route::get('dc/storage',['uses' =>'ProductManagementController@storage','middleware' => 'checkRight:FDC_STORAGE']);
Route::post('storage/load',['uses' =>'StorageController@load','middleware' => 'saveWorkspace']);
Route::post('storage/save', 'StorageController@save');
Route::post('storage/history', 'StorageController@history');

Route::get('dc/eutest', 			'ProductManagementController@eutest');
Route::get('dc/eutest',				['uses' =>'ProductManagementController@eutest','middleware' => 'checkRight:FDC_EU_TEST']);
Route::post('eutest/load',			['uses' =>'EuTestController@load','middleware' => 'saveWorkspace']);
Route::post('eutest/save', 			'EuTestController@save');
Route::post('eutest/history', 		'EuTestController@history');

Route::get('dc/quality',['uses' =>'ProductManagementController@quality','middleware' => 'checkRight:FDC_QUALITY']);
Route::post('quality/load',['uses' =>'QualityController@load','middleware' => 'saveWorkspace']);
Route::post('quality/save', 'QualityController@save');
Route::post('quality/loadsrc', 'QualityController@loadsrc');
Route::post('quality/edit', 'QualityController@edit');
Route::post('quality/edit/saving', 'QualityController@editSaving');
Route::post('quality/history', 		'QualityController@history');

Route::get('dc/deferment',['uses' =>'ProductManagementController@deferment','middleware' => 'checkRight:FDC_DEFER']);
Route::post('deferment/load',['uses' =>	'DefermentController@load','middleware' => 'saveWorkspace']);
Route::post('deferment/save', 			'DefermentController@save');
Route::post('deferment/loadsrc', 		'DefermentController@loadsrc');
Route::post('deferment/edit', 			'DefermentController@edit');
Route::post('deferment/edit/saving', 	'DefermentController@editSaving');
Route::post('deferment/history', 		'DefermentController@history');

Route::get('dc/ticket',			['uses' =>'ProductManagementController@ticket','middleware' => 'checkRight:FDC_TICKET']);
Route::post('ticket/load',		['uses' =>	'TicketController@load','middleware' => 'saveWorkspace']);
Route::post('ticket/save', 		'TicketController@save');
Route::post('ticket/history', 	'TicketController@history');

//---------
Route::get('fo/safety',			['uses' =>'FOController@safety','middleware' => 'checkRight:FOP_SAFETY']);
Route::post('safety/load',		['uses' =>	'SafetyController@load','middleware' => 'saveWorkspace']);
Route::post('safety/save', 		'SafetyController@save');

Route::get('fo/comment',			['uses' =>'FOController@comment','middleware' => 'checkRight:FOP_COMMENT']);
Route::post('comment/load',			['uses' =>	'CommentController@load','middleware' => 'saveWorkspace']);
Route::post('comment/save', 		'CommentController@save');

Route::get('fo/equipment',			['uses' =>'FOController@equipment','middleware' => 'checkRight:FOP_EQUIP']);
Route::post('equipment/load',		['uses' =>	'EquipmentController@load','middleware' => 'saveWorkspace']);
Route::post('equipment/save', 		'EquipmentController@save');

Route::get('fo/chemical',			['uses' =>'FOController@chemical','middleware' => 'checkRight:FOP_CHEMICAL']);
Route::post('chemical/load',		['uses' =>	'ChemicalController@load','middleware' => 'saveWorkspace']);
Route::post('chemical/save', 		'ChemicalController@save');

Route::get('fo/personnel',			['uses' =>'FOController@personnel','middleware' => 'checkRight:FOP_PERSONNEL']);
Route::post('personnel/load',		['uses' =>	'PersonnelController@load','middleware' => 'saveWorkspace']);
Route::post('personnel/save', 		'PersonnelController@save');
Route::post('personnel/loadsrc', 	'PersonnelController@loadsrc');


Route::get('tagsMapping',			['uses' =>'SystemConfigController@tagsmapping','middleware' => 'checkRight:CONFIG_TAGS_MAPPING']);
Route::post('tagsMapping/load',		['uses' =>	'TagsMappingController@load','middleware' => 'saveWorkspace']);
Route::post('tagsMapping/save', 	'TagsMappingController@save');
Route::post('tagsMapping/loadsrc', 	'TagsMappingController@loadsrc');

Route::get('fp/forecast',			['uses' =>'ForecastPlanningController@forecast','middleware' => 'checkRight:FP_WELLFORECAST']);
Route::post('forecast/load',		['uses' =>	'EnergyUnitForecastController@load','middleware' => 'saveWorkspace']);
Route::post('forecast/run', 		'EnergyUnitForecastController@run');

Route::get('fp/preos',			['uses' =>'ForecastPlanningController@preos','middleware' => 'checkRight:FP_PREOS']);
Route::post('preos/load',		['uses' =>	'PreosController@load','middleware' => 'saveWorkspace']);
Route::post('preos/run', 		'PreosController@run');

Route::get('fp/allocateplan',		['uses' =>'ForecastPlanningController@allocateplan','middleware' => 'checkRight:FP_ALLOCATE_PLAN']);
Route::post('allocateplan/load',	['uses' =>'AllocatePlanController@load','middleware' => 'saveWorkspace']);
Route::post('allocateplan/save', 	'AllocatePlanController@save');

Route::get('me/setting',			['uses' =>'UserSettingController@index'/* ,'middleware' => 'checkRight:FP_ALLOCATE_PLAN' */]);
Route::post('me/setting/save', 		'UserSettingController@saveSetting');

Route::get('fp/loadplanforecast',		['uses' =>'ForecastPlanningController@loadplan','middleware' => 'checkRight:FP_LOAD_PLAN_FORECAST']);

Route::get('pd/cargoentry',			['uses' =>'ProductDeliveryController@cargoentry','middleware' => 'checkRight:PD_CARGO_ADMIN_ENTRY']);
Route::post('cargoentry/load',		['uses' =>	'Cargo\CargoEntryController@load','middleware' => 'saveWorkspace']);
Route::post('cargoentry/save', 		'Cargo\CargoEntryController@save');
Route::post('cargoentry/nominate', 		'Cargo\CargoEntryController@nominate');

Route::get('pd/cargonomination',			['uses' =>'ProductDeliveryController@cargonomination','middleware' => 'checkRight:PD_CARGO_ADMIN_NOMINATION']);
Route::post('cargonomination/load',			['uses' =>	'Cargo\CargoNominationController@load','middleware' => 'saveWorkspace']);
Route::post('cargonomination/save', 		'Cargo\CargoNominationController@save');
Route::post('cargonomination/loadsrc', 		'Cargo\CargoNominationController@loadsrc');
Route::post('cargonomination/confirm', 		'Cargo\CargoNominationController@confirm');
Route::post('cargonomination/reset', 		'Cargo\CargoNominationController@reset');

Route::get('pd/cargoschedule',			['uses' =>'ProductDeliveryController@cargoschedule','middleware' => 'checkRight:PD_CARGO_ADMIN_SCHEDULE']);
Route::post('cargoschedule/load',		['uses' =>	'Cargo\CargoScheduleController@load','middleware' => 'saveWorkspace']);
Route::post('cargoschedule/save', 		'Cargo\CargoScheduleController@save');

Route::get('pd/cargovoyage',			['uses' =>'ProductDeliveryController@cargovoyage',	'middleware' => 'checkRight:PD_CARGO_ACTION_VOYAGE']);
Route::post('cargovoyage/load',			['uses' =>'Cargo\CargoVoyageController@load',		'middleware' => 'saveWorkspace']);
Route::post('cargovoyage/save', 		'Cargo\CargoVoyageController@save');
Route::post('voyage/load', 				'Cargo\CargoVoyageController@loadDetail');
Route::post('voyage/save', 				'Cargo\CargoVoyageController@save');
Route::post('voyage/gentransport', 		'Cargo\CargoVoyageController@gentransport');

Route::get('pd/cargoload',				['uses' =>'ProductDeliveryController@cargoload',	'middleware' => 'checkRight:PD_CARGO_ACTION_LOAD']);
Route::post('cargoload/load',			['uses' =>'Cargo\CargoLoadController@load',			'middleware' => 'saveWorkspace']);
Route::post('cargoload/save', 			'Cargo\CargoLoadController@save');
Route::post('timesheet/load',			['uses' =>'Cargo\CargoLoadController@loadDetail']);
Route::post('timesheet/save', 			'Cargo\CargoLoadController@save');
Route::post('timesheet/activities', 	'Cargo\CargoLoadController@activities');

Route::get('pd/cargounload',			['uses' =>'ProductDeliveryController@cargounload',	'middleware' => 'checkRight:PD_CARGO_ACTION_UNLOAD']);
Route::post('cargounload/load',			['uses' =>'Cargo\CargoUnLoadController@load',			'middleware' => 'saveWorkspace']);
Route::post('cargounload/save', 		'Cargo\CargoUnLoadController@save');
Route::post('timesheet/unload',			['uses' =>'Cargo\CargoUnLoadController@loadDetail']);

Route::get('pd/voyagemarine',			['uses' =>'ProductDeliveryController@voyagemarine',	'middleware' => 'checkRight:PD_CARGO_ACTION_MARINE']);
Route::post('voyagemarine/load',		['uses' =>'Cargo\VoyageMarineController@load',			'middleware' => 'saveWorkspace']);
Route::post('voyagemarine/save', 		'Cargo\VoyageMarineController@save');
Route::post('voyagemarine/gen', 		['uses' =>'Cargo\VoyageMarineController@genBLMR',	'middleware' => 'checkRight:PD_CARGO_ACTION_MARINE']);
Route::post('shipport/load',			['uses' =>'Cargo\VoyageMarineController@loadDetail']);
Route::post('shipport/save', 			'Cargo\VoyageMarineController@save');

Route::get('pd/contractdata',			['uses' =>'ProductDeliveryController@contractdata','middleware' => 'checkRight:PD_CONTRACT_ADMIN_DATA']);
Route::post('contractdata/load',		['uses' =>'Contract\ContractDataController@load','middleware' => 'saveWorkspace']);
Route::post('contractdata/save', 		'Contract\ContractDataController@save');
Route::post('contractdetail/load',		'Contract\ContractDataController@loadDetail');
Route::post('contractdetail/save', 		'Contract\ContractDataController@save');

Route::get('pd/contractcalculate',			['uses' =>'ProductDeliveryController@contractcalculate','middleware' => 'checkRight:PD_CONTRACT_ADMIN_CALC']);
Route::post('contractcalculate/load',		['uses' =>'Contract\ContractCalculateController@load','middleware' => 'saveWorkspace']);
Route::post('contractcalculate/save', 		'Contract\ContractCalculateController@save');
Route::post('contractcalculate/addyear', 		'Contract\ContractCalculateController@addyear');

Route::get('pd/contracttemplate',			['uses' =>'ProductDeliveryController@contracttemplate','middleware' => 'checkRight:PD_CONTRACT_ADMIN_TEMP']);
Route::post('contracttemplate/load',		['uses' =>'Contract\ContractTemplateController@load','middleware' 	=> 'saveWorkspace']);
Route::post('contracttemplate/save', 		'Contract\ContractTemplateController@save');
Route::post('contracttemplateattribute/load',		['uses' =>'Contract\ContractTemplateController@loadAttributes','middleware' 	=> 'saveWorkspace']);
Route::post('contracttemplateattribute/save', 		'Contract\ContractTemplateController@save');

//----------admin
Route::get('am/users', 'AdminController@_index');
Route::post('am/loadData', 'AdminController@getData');
Route::post('am/selectedID', 'AdminController@selectedID');
Route::post('am/loadUserList', 'AdminController@getUsersList');
Route::get('am/delete', 'AdminController@deleteUser');
Route::get('am/editUser/{id}', 'AdminController@editUser');
Route::get('am/new', 'AdminController@addUser');
Route::post('am/save', 'AdminController@addNewUser');
Route::post('am/updateUser', 'AdminController@updateUser');

Route::get('am/roles', 'AdminController@_indexRoles');
Route::post('am/editRoles', 'AdminController@editRole');
Route::post('am/addRoles', 'AdminController@addRole');
Route::post('am/deleteRoles', 'AdminController@deleteRole');
Route::post('am/loadRightsList', 'AdminController@loadRightsList');
Route::post('am/removeOrGrant', 'AdminController@removeOrGrant');

Route::get('am/audittrail', 'AdminController@_indexAudittrail');
Route::post('am/loadAudittrail', 'AdminController@loadAudittrail');

Route::get('am/validatedata', 'AdminController@_indexValidatedata');
Route::post('am/loadValidateData', 'AdminController@loadValidateData');
Route::post('am/validateData', 'AdminController@validateData');

Route::get('am/approvedata', 'AdminController@_indexApprove');
Route::post('am/loadApproveData', 'AdminController@loadApproveData');
Route::post('am/approveData', 'AdminController@ApproveData');

Route::get('am/lockdata', 'AdminController@_indexLockData');
Route::post('am/loadLockData', 'AdminController@loadLockData');
Route::post('am/lockData', 'AdminController@lockData');

Route::get('am/userlog', 'AdminController@_indexUserlog');
Route::post('am/loadUserLog', 'AdminController@loadUserLog');

Route::get('am/editGroup', 'AdminController@_indexEditGroup');
Route::post('am/loadGroup', 'AdminController@loadGroup');
Route::post('am/saveGroup', 'AdminController@saveGroup');
Route::post('am/deleteGroup', 'AdminController@deleteGroup');

Route::get('am/helpeditor', 'AdminController@_helpEditor');
Route::post('am/getFunction', 'AdminController@getFunction');
Route::post('am/gethelp', 'AdminController@gethelp');
Route::post('am/savehelp', 'AdminController@savehelp');

//========== DATA VISUALIZATION
Route::get('diagram', 'DVController@_indexDiagram');
Route::post('getdiagram', 'DVController@getdiagram');
Route::get('loaddiagram/{id}', 'DVController@loaddiagram');
Route::post('savediagram', 'DVController@savediagram');
Route::post('deletediagram', 'DVController@deletediagram');

Route::post('onChangeObj', 'DVController@onChangeObj');
Route::post('getSurveillanceSetting', 'DVController@getSurveillanceSetting');
Route::post('getValueSurveillance', 'DVController@getValueSurveillance');
Route::post('uploadImg', 'DVController@uploadImg');
Route::get('workflow', 'DVController@_indexWorkFlow');
Route::post('getListWorkFlow', 'DVController@getListWorkFlow');
Route::post('getXMLCodeWF', 'DVController@getXMLCodeWF');
Route::post('workflowSave', 'DVController@workflowSave');
Route::post('loadConfigTask', 'DVController@loadConfigTask');
Route::post('changeRunTask', 'DVController@changeRunTask');
Route::post('loadFormSetting', 'DVController@loadFormSetting');
Route::post('getEntity', 'DVController@getEntity');
Route::post('workflowSaveTask', 'DVController@workflowSaveTask');
Route::post('deleteWorkFlow', 'DVController@deleteWorkFlow');
Route::post('stopWorkFlow', 'DVController@stopWorkFlow');
Route::post('runWorkFlow', 'DVController@runWorkFlow');
Route::post('getKey', 'DVController@getKey');
Route::resource('runAlloc', 'RunController@runAlloc');
Route::get('workreport', 'ReportController@_index');
Route::get('loadWfShow', 'wfShowController@loadData');
Route::post('reLoadtTmworkflow', 'wfShowController@reLoadtTmworkflow');
Route::post('finish_workflowtask', 'wfShowController@finish_workflowtask');
Route::post('upFile', 'DVController@uploadFile');
Route::post('openTask', 'wfShowController@openTask');
Route::post('countWorkflowTask', 'wfShowController@countWorkflowTask');

Route::get('graph', 'graphController@_index');
Route::post('loadVizObjects', 'graphController@loadVizObjects');
Route::post('loadEUPhase', 'graphController@loadEUPhase');
Route::get('loadchart/{param}/{minvalue}/{maxvalue}/{date_begin}/{date_end}/{input}', 'graphController@loadChart');
Route::post('listCharts', 'graphController@getListCharts');
Route::post('deleteChart', 'graphController@deleteChart');
Route::post('saveChart', 'graphController@saveChart');
Route::post('getProperty', 'graphController@getProperty');

Route::get('viewconfig', 'ViewConfigController@_indexViewConfig');
Route::post('loadPlotObjects', 'ViewConfigController@loadPlotObjects');
Route::post('getTableFields', 'ViewConfigController@getTableFields');
Route::post('getListPlotItems', 'ViewConfigController@getListPlotItems');
Route::post('deletePlotItems', 'ViewConfigController@deletePlotItems');
Route::post('savePlotItems', 'ViewConfigController@savePlotItems');
Route::post('genView', 'ViewConfigController@genView');

Route::get('allocrun', 'AllocationController@_index');
Route::post('getJobsRunAlloc', 'AllocationController@getJobsRunAlloc');
Route::post('run_runner', 'AllocationController@run_runner');

Route::get('allocset', 'AllocationController@_indexconfig');
Route::post('addJob', 'AllocationController@addJob');
Route::post('addrunner', 'AllocationController@addrunner');
Route::post('getrunnerslist', 'AllocationController@getrunnerslist');
Route::post('getconditionslist', 'AllocationController@getconditionslist');
Route::post('deletejob', 'AllocationController@deletejob');
Route::post('savecondition', 'AllocationController@savecondition');
Route::post('deleterunner', 'AllocationController@deleterunner');
Route::post('clonenetwork', 'AllocationController@clonenetwork');
Route::get('jobdiagram/{job_id}', 'AllocationController@jobdiagram');
Route::get('loadjobdiagram/{id}', 'AllocationController@loaddiagram');
Route::post('editJob', 'AllocationController@editJob');
Route::post('saveEditRunner', 'AllocationController@saveEditRunner');

Route::get('fieldsconfig', 'FieldsConfigController@_index');
Route::post('getColumn', 'FieldsConfigController@getColumn');
Route::post('saveconfig', 'FieldsConfigController@saveconfig');
Route::post('chckChange', 'FieldsConfigController@chckChange');
Route::post('getprop', 'FieldsConfigController@getprop');
Route::post('saveprop', 'FieldsConfigController@saveprop');

Route::get('formula', 'FormulaController@_index');
Route::post('editgroupname', 'FormulaController@editGroupName');
Route::post('addgroupname', 'FormulaController@addGroupName');
Route::post('deletegroup', 'FormulaController@deleteGroup');
Route::post('getformulaslist', 'FormulaController@getformulaslist');
Route::post('getvarlist', 'FormulaController@getVarList');
Route::post('deleteformula', 'FormulaController@deleteformula');
Route::post('saveformulaorder', 'FormulaController@saveFormulaOrder');
Route::post('saveformula', 'FormulaController@saveformula');
Route::post('testformula', 'FormulaController@testformula');
Route::post('deletevar', 'FormulaController@deletevar');
Route::post('savevarsorder', 'FormulaController@saveVarsOrder');

Route::get('dataview', 'DataViewController@_index');
Route::post('getsql', 'DataViewController@getsql');
Route::post('loaddataview', 'DataViewController@loaddata');
Route::post('deletesql', 'DataViewController@deletesql');
Route::post('checksql', 'DataViewController@checkSQL');
Route::get('downloadExcel/{sql}', 'DataViewController@downloadExcel');
Route::post('savesql', 'DataViewController@savesql');

Route::get('importdata', 'InterfaceController@_index');
Route::post('getimportsetting', 'InterfaceController@getImportSetting');
Route::post('doimport', 'InterfaceController@doImport');

Route::get('sourceconfig', 'InterfaceController@_indexConfig');
Route::post('saveimportsetting', 'InterfaceController@saveImportSetting');
Route::post('deletesetting', 'InterfaceController@deleteSetting');
Route::post('renamesetting', 'InterfaceController@renameSetting');
Route::post('loadintservers', 'InterfaceController@loadIntServers');
Route::post('detailsconnection', 'InterfaceController@detailsConnection');
Route::post('saveconn', 'InterfaceController@saveConn');
Route::post('renameconn', 'InterfaceController@renameConn');
Route::post('deleteconn', 'InterfaceController@deleteConn');
Route::post('loadtagset', 'InterfaceController@loadTagSet');
Route::post('savetagset', 'InterfaceController@saveTagSet');
Route::post('loadtagsets', 'InterfaceController@loadTagSets');
Route::post('renametagset', 'InterfaceController@renameTagSet');
Route::post('deletetagset', 'InterfaceController@deleteTagSet');
Route::post('pi', 'InterfaceController@pi');

Route::get('dataloader', 'InterfaceController@_indexDataloader');
Route::post('gettablefieldsall', 'InterfaceController@getTableFieldsAll');
Route::post('doimportdataloader', 'InterfaceController@doImportDataLoader');

Route::get('demurrageebo',		['uses' =>'ProductDeliveryController@demurrageebo','middleware' => 'checkRight:PD_CARGO_MAN_DEMUR']);
Route::post('demurragreebo/load',['uses' =>'Cargo\DemurrageeboController@load','middleware' => 'saveWorkspace']);
Route::post('demurragreebo/save', 		'Cargo\DemurrageeboController@saveDemurrage');
Route::post('demurragreebo/loadsrc', 		'Cargo\DemurrageeboController@loadsrc');

Route::get('cargodocuments',		['uses' =>'ProductDeliveryController@cargodocuments','middleware' => 'checkRight:PD_CARGO_MAN_DOC']);
Route::post('cargodocuments/load',['uses' =>'Cargo\CargoDocumentsController@load','middleware' => 'saveWorkspace']);

