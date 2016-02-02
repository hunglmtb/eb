<?php
class CLS_TASK{
	private $pro=array(
				'Id'=>0,
				'Name'=>'',
				'Runby'=>'',
				'User'=>'',
				'Intro'=>'',
				'TimeConfig'=>'',
				'ExpireDate'=>'',
				'TaskGroup'=>'',
				'TaskCode'=>'',
				'TaskConfig'=>'',
				'Author'=>'',
				'Cdate'=>'',
				'isRun'=>'',
				'Status'=>0
	);
	private $_tbl='tm_task';
	private $objmysql=null;
	public function CLS_TASK(){
		$this->objmysql=new CLS_MYSQL;
	}
	public function __set($proname,$value){
		if(!isset($this->pro[$proname])) echo "Can't found $proname in set function";
		$this->pro[$proname]=$value;
	}
	public function __get($proname){
		if(!isset($this->pro[$proname])) echo "Can't found $proname in get function";
		return $this->pro[$proname];
	}
	public function getList($str_where){
		$sql="SELECT * FROM ".$this->_tbl." ".$str_where;
		return $this->objmysql->Query($sql);
	}
	public function Fetch_Assoc(){
		return $this->objmysql->Fetch_Assoc();
	}
	public function Num_rows(){
		return $this->objmysql->Num_rows();
	}
	function Add_new(){
		$sql="INSERT INTO ".$this->_tbl." (`name`,`runby`,`user`,`intro`,`time_config`,`expire_date`,`task_group`,`task_code`,`task_config`,`author`,`cdate`) VALUES ";
		$sql.=" ('".$this->Name."','".$this->Runby."','".$this->User."','".$this->Intro."','".$this->TimeConfig."','".$this->ExpireDate."','".$this->TaskGroup."','".$this->TaskCode."','".$this->TaskConfig."','".$this->Author."','".$this->Cdate."') ";
		return $this->objmysql->Query($sql);
	}
	function Update(){
		$sql="UPDATE ".$this->_tbl." SET `name`='".$this->Name."',`runby`='".$this->Runby."',`user`='".$this->User."',`intro`='".$this->Intro."',`time_config`='".$this->TimeConfig."',`expire_date`='".$this->ExpireDate."',`task_group`='".$this->TaskGroup."',`task_code`='".$this->TaskCode."',`task_config`='".$this->TaskConfig."',`author`='".$this->Author."',`cdate`='".$this->Cdate."' ";
		$sql.=" WHERE `id`='".$this->Id."'";
		return $this->objmysql->Query($sql);
	}
	function Delete($id){
		$sql="DELETE FROM ".$this->_tbl." WHERE `id` in ('$id')";
		return $this->objmysql->Query($sql);
	}
}
?>