<?php
// define path to dirs
	define('ROOTHOST','http://'.$_SERVER['HTTP_HOST'].'/eb/taskman/');
	define('WEBSITE',$_SERVER['HTTP_HOST']);
	define('DOMAIN','tubitam.com');
	define('BASEVIRTUAL0',ROOTHOST.'images/');
	define('ROOT_PATH',''); 
	define('TEM_PATH',ROOT_PATH.'templates/');
	define('COM_PATH',ROOT_PATH.'components/');
	define('MOD_PATH',ROOT_PATH.'modules/');
	define('LAG_PATH',ROOT_PATH.'languages/');
	define('EXT_PATH',ROOT_PATH.'extensions/');
	define('EDI_PATH',EXT_PATH.'editor/');
	define('DOC_PATH',ROOT_PATH.'documents/');
	define('DAT_PATH',ROOT_PATH.'databases/');
	define('IMG_PATH',ROOT_PATH.'images/');
	define('MED_PATH',ROOT_PATH.'media/');
	define('LIB_PATH',ROOT_PATH.'libs/');
	define('JSC_PATH',ROOT_PATH.'js/');
	define('LOG_PATH',ROOT_PATH.'logs/');
	
	define('MAX_ROWS','50');
	define('MAX_ITEM','20'); // số bản ghi trên 1 trang
	define('TIMEOUT_LOGIN','60');
	define('URL_REWRITE','1');
	define('MAX_ROWS_INDEX',40);
	
	define('THUMB_WIDTH',285);
	define('THUMB_HEIGHT',285);
	
	$LANG_CODE='vi';
	
	define('SMTP_SERVER','smtp.gmail.com');
	define('SMTP_PORT','465');
	define('SMTP_USER','abc@gmail.com');
	define('SMTP_PASS','xyz');
	define('SMTP_MAIL','abc@gmail.com');
	define('IGF_LICENSE','77667050813dd94a49756d59de5cea88');
	
	$fun_config=array(
		'normal-fun'=>array(
			array('code'=>'NOR_SENDMESS','name'=>'Send mess'),
			array('code'=>'NOR_SENDMAIL','name'=>'Send email')
		),
		'product-fun'=>array(
			array('code'=>'PROD_FLOW','name'=>'Flow Stream'),
			array('code'=>'PROD_EU','name'=>'Energy Unit'),
			array('code'=>'PROD_STORAGE','name'=>'Tank & Storage'),
			array('code'=>'PROD_TEST','name'=>'EU Test'),
			array('code'=>'PROD_DEFERMENT','name'=>'Deferment'),
			array('code'=>'PROD_QUALITY','name'=>'Quality')
		),
		'operation-fun'=>array(
			array('code'=>'OPER_SAFETY','name'=>'Safety'),
			array('code'=>'OPER_COMMENT','name'=>'Comments'),
			array('code'=>'OPER_EQUIPMENT','name'=>'Equipment'),
			array('code'=>'OPER_CHEMICAL','name'=>'Chemical'),
			array('code'=>'OPER_PERSONNEL','name'=>'Personnel')
		),
		'visualization-fun'=>array(
			array('code'=>'VIS_NETWORK','name'=>'Network Model'),
			array('code'=>'VIS_DATAVIEW','name'=>'Data View'),
			array('code'=>'VIS_REPORT','name'=>'Report'),
			array('code'=>'VIS_GRAPH','name'=>'Advanced Graph'),
			array('code'=>'VIS_WORKFLOW','name'=>'Workflow'),
			array('code'=>'VIS_TASKMAN','name'=>'Task Manager')
		),
		'allocation-fun'=>array(
			array('code'=>'ALLOC_CHECK','name'=>'Check Allocation Data'),
			array('code'=>'ALLOC_PROD','name'=>'Production Allocation')
		),
		'interface-fun'=>array(
			array('code'=>'INT_IMPORT','name'=>'Import Data')
		)
	);
	
?>