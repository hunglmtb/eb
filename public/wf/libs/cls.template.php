 <?php
class CLS_TEMPLATE{
	private $objmysql=null;
	public function CLS_TEMPLATE(){
		$this->objmysql=new CLS_MYSQL;
	}
	public function Load_Extension(){
		define("EDIT_FULL_PATH",EDI_PATH."innovar/scripts/innovaeditor.js");
	}
	public function Load_lang_default(){
		define('CURENT_LANG','en');
		require_once(LAG_PATH.CURENT_LANG.'/english.php');
	}
	public function Load_defaul_tem(){ return 'default';}
	public function WapperTem(){
		if($this->error()) return;
		require_once(THIS_TEM_PATH.'home.php');
	}
	private function error(){
		if(!is_file(THIS_TEM_PATH.'template.xml')){
			echo 'template.xml is not exist';
			return false;
		}
		if(!is_file(THIS_TEM_PATH."home.php")){
			echo 'home.php is not exist';
			return false;
		}
	}
	private function Security(){
	
	}
	// Check Module
	public function isModule($position){
		$sql="SELECT * FROM tbl_modules WHERE `isactive`=1 AND `position`='$position' ORDER BY `order`,`title`";
		$this->objmysql->Query($sql);
		if($this->objmysql->Num_rows()>0)
			return true;
		else 
			return false;
	}
	// Load Module
	public function loadModule($position,$site="site"){
		$position=trim($position);
		$site=trim($site);
		$sql="SELECT `mod_id`,`type`,`mnuids` FROM `tbl_modules` WHERE `isactive`=1 AND `position`='$position' ORDER BY `order`";
		$this->objmysql->Query($sql);
		while($rows=$this->objmysql->Fetch_Assoc()){
			$mnus=$rows['mnuids'];
			if(trim($rows['mnuids'])=='')
				continue;
			if($rows['mnuids']!='all'){
				$mnus=explode(',',$mnus);
				$cur_menu=$_SESSION['CUR_MENU'];
				if(!in_array($cur_menu, $mnus))
				continue;
			}
			if(is_file(MOD_PATH.'mod_'.trim($rows['type']).'/layout.php')==true) 
				include(MOD_PATH.'mod_'.trim($rows['type']).'/layout.php');
			else
				echo '<br> Module isn not exist!';
		}
	}
	public function isFrontpage(){
		$flag=true;
		if(isset($_GET['com']))
			$flag=false;
		return $flag;
	}
	function loadComponent(){
		$com='';
		if(isset($_GET['com']))	$com=addslashes($_GET['com']);
		if(!is_dir(COM_PATH.'com_'.$com))
			$com='task';
		include(COM_PATH.'com_'.$com.'/layout.php');
	}
	function getFullURL(){
		echo $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];
	}
}
?>