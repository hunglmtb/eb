<?php
class CLS_WORKFLOW{
	private $pro=array(
				'Id'=>0,
				'Name'=>'',
				'Intro'=>'',
				'Cdate'=>'',
				'Author'=>'',
				'isRun'=>'',
				'Status'=>1
	);
	private $_tbl='tm_workflow';
	private $objmysql=null;
	private $lastid=-1;
	public function CLS_WORKFLOW(){
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
	public function LastInsertID(){
		return $this->lastid;
	}
	function Add_new(){
		$sql="INSERT INTO ".$this->_tbl." (`name`,`intro`,`author`,`cdate`,`isrun`,`status`) VALUES ";
		$sql.=" ('".$this->Name."','".$this->Intro."','".$this->Author."','".$this->Cdate."','".$this->isRun."','".$this->Status."') ";
		$this->objmysql->Exec($sql);
		$this->lastid=$this->objmysql->LastInsertID();
	}
	function Update(){
		$sql="UPDATE ".$this->_tbl." SET `name`='".$this->Name."',`intro`='".$this->Intro."',`author`='".$this->Author."',`cdate`='".$this->Cdate."',`isrun`='".$this->isRun."',`status`='".$this->Status."' ";
		$sql.=" WHERE `id`='".$this->Id."'";
		return $this->objmysql->Exec($sql);
	}
	function Delete($id){
		$sql="DELETE FROM ".$this->_tbl." WHERE `id` in ('$id')";
		return $this->objmysql->Exec($sql);
	}
}
?>