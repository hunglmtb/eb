<?php
class CLS_MODULE{
	private $objmysql=null;
	public function CLS_MODULE(){
		$this->objmysql=new CLS_MYSQL;
	}
	public function getList($strwhere='',$lagid=0){
		$sql=" SELECT tbl_modules.*,tbl_modules_text.title,tbl_modules_text.content 
				FROM tbl_modules INNER JOIN tbl_modules_text ON tbl_modules.mod_id= tbl_modules_text.mod_id
				WHERE tbl_modules_text.lag_id='$lagid' $strwhere";
		return $this->objmysql->Query($sql);
	}
	public function Num_rows(){
		return $this->objmysql->Num_rows();
	}
	public function Fetch_Assoc(){
		return $this->objmysql->Fetch_Assoc();
	}
}
?>