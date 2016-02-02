<?php
class CLS_WORKFLOW_TASK{
	private $pro=array(
				'Id'=>0,
				'WfId'=>0,
				'Name'=>'',
				'Intro'=>'',
				'TaskGroup'=>'',
				'TaskCode'=>'',
				'NodeConfig'=>'',
				'NextTaskConfig'=>'',
				'RunBy'=>'0',
				'User'=>'',
				'Result'=>0,
				'Cdate'=>'',
				'Status'=>0
	);
	private $_tbl='tm_workflow_task';
	private $objmysql=null;
	private $lastid=-1;
	public function CLS_WORKFLOW_TASK(){
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
	public function getIdTaskByWf($wfid){
		$sql="SELECT `id` FROM $this->_tbl WHERE `wf_id`=$wfid ";
		$objmysql=new CLS_MYSQL;
		$objmysql->Query($sql);
		$ar=array();
		while($r=$objmysql->Fetch_Assoc()){
			$ar[]=$r['id'];
		}
		return $ar;
	}
	public function LastInsertID(){
		return $this->lastid;
	}
	public function Add_new(){
		$sql="INSERT INTO ".$this->_tbl." (`wf_id`,`name`,`task_group`,`task_code`,`node_config`,`next_task_config`,`runby`,`user`,`cdate`,`status`) VALUES ";
		$sql.=" ('".$this->WfId."','".$this->Name."','".$this->TaskGroup."','".$this->TaskCode."','".$this->NodeConfig."','".$this->NextTaskConfig."','".$this->RunBy."','".$this->User."','".$this->Cdate."','".$this->Status."') ";
		$this->objmysql->Exec($sql);
		$this->lastid=$this->objmysql->LastInsertID();
	}
	public function Update(){
		$sql="UPDATE ".$this->_tbl." SET `wf_id`='".$this->WfId."',`name`='".$this->Name."',`task_group`='".$this->TaskGroup."',`task_code`='".$this->TaskCode."',`node_config`='".$this->NodeConfig."',`next_task_config`='".$this->NextTaskConfig."',`runby`='".$this->RunBy."',`user`='".$this->User."',`cdate`='".$this->Cdate."',`status`='".$this->Status."'";
		$sql.=" WHERE `id`='".$this->Id."'";
		return $this->objmysql->Exec($sql);
	}
	public function Delete($id){
		$sql="DELETE FROM ".$this->_tbl." WHERE `id` in ('$id')";
		return $this->objmysql->Exec($sql);
	}
	public function DeleteByWF($id){
		$sql="DELETE FROM ".$this->_tbl." WHERE `wf_id` in ('$id')";
		return $this->objmysql->Exec($sql);
	}
}
?>