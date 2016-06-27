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
Route::get('user/sort/{role}', 'UserController@indexSort');

Route::get('user/roles', 'UserController@getRoles');
Route::post('user/roles', 'UserController@postRoles');

Route::put('userseen/{user}', 'UserController@updateSeen');

Route::resource('user', 'UserController');

// Authentication routes...
Route::get('auth/login', 'HomeController@index');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::post('auth/eblogin', 'Auth\AuthController@postEBLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');
Route::get('auth/confirm/{token}', 'Auth\AuthController@getConfirm');
Route::get('login/success', 'HomeController@loginSuccess');

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

Route::get('dc/eu', 'ProductManagementController@eu');
Route::get('dc/eu',['uses' =>'ProductManagementController@eu','middleware' => 'checkRight:FDC_EU']);
Route::post('eu/load',['uses' =>'EuController@load','middleware' => 'saveWorkspace']);
Route::post('eu/save', 'EuController@save');

Route::get('dc/storage',['uses' =>'ProductManagementController@storage','middleware' => 'checkRight:FDC_STORAGE']);
Route::post('storage/load',['uses' =>'StorageController@load','middleware' => 'saveWorkspace']);
Route::post('storage/save', 'StorageController@save');

Route::get('dc/eutest', 'ProductManagementController@eutest');
Route::get('dc/eutest',['uses' =>'ProductManagementController@eutest','middleware' => 'checkRight:FDC_EU_TEST']);
Route::post('eutest/load',['uses' =>'EuTestController@load','middleware' => 'saveWorkspace']);
Route::post('eutest/save', 'EuTestController@save');

Route::get('dc/quality',['uses' =>'ProductManagementController@quality','middleware' => 'checkRight:FDC_QUALITY']);
Route::post('quality/load',['uses' =>'QualityController@load','middleware' => 'saveWorkspace']);
Route::post('quality/save', 'QualityController@save');
Route::post('quality/loadsrc', 'QualityController@loadsrc');
Route::post('quality/edit', 'QualityController@edit');
Route::post('quality/edit/saving', 'QualityController@editSaving');

Route::get('dc/deferment',['uses' =>'ProductManagementController@deferment','middleware' => 'checkRight:FDC_DEFER']);
Route::post('deferment/load',['uses' =>	'DefermentController@load','middleware' => 'saveWorkspace']);
Route::post('deferment/save', 			'DefermentController@save');
Route::post('deferment/loadsrc', 		'DefermentController@loadsrc');
Route::post('deferment/edit', 			'DefermentController@edit');
Route::post('deferment/edit/saving', 	'DefermentController@editSaving');

Route::get('dc/ticket',			['uses' =>'ProductManagementController@ticket','middleware' => 'checkRight:FDC_TICKET']);
Route::post('ticket/load',		['uses' =>	'TicketController@load','middleware' => 'saveWorkspace']);
Route::post('ticket/save', 		'TicketController@save');

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
Route::get('tagsMapping', 'SystemConfigController@tagsmapping');
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

Route::get('viewconfig', 'ViewConfigController@_indexViewConfig');
Route::post('loadPlotObjects', 'ViewConfigController@loadPlotObjects');
Route::post('getTableFields', 'ViewConfigController@getTableFields');
Route::post('getListPlotItems', 'ViewConfigController@getListPlotItems');
Route::post('deletePlotItems', 'ViewConfigController@deletePlotItems');
Route::post('savePlotItems', 'ViewConfigController@savePlotItems');
Route::post('genView', 'ViewConfigController@genView');

Route::get('runallocation', 'AllocationController@_index');
Route::post('getJobsRunAlloc', 'AllocationController@getJobsRunAlloc');
Route::post('run_runner', 'AllocationController@run_runner');
