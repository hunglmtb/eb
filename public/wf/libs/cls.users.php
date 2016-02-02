<?php
class CLS_USERS{
	private $pro=array(
			'ID'=>'-1',
			'UserName'=>'',
			'Password'=>'',
			'FirstName'=>'',
			'LastName'=>'',
			'Birthday'=>'',
			'Gender'=>'',
			'Address'=>'',
			'Phone'=>'',
			'Mobile'=>'',
			'Email'=>'',
			'Joindate'=>'',
			'LastLogin'=>'',
			'Gmember'=>'',
			'isActive'=>'1'
			);
	private $objmysql=NULL;
	public function CLS_USERS(){
		$this->Joindate=date('Y-m-d h:i:s');
		$this->LastLogin=date('Y-m-d h:i:s');
		$this->objmysql=new CLS_MYSQL;
	}
	// property set value
	public function __set($proname,$value){
		if(!isset($this->pro[$proname])){
			echo ($proname.' is not member of CLS_USERS Class' );
			return;
		}
		$this->pro[$proname]=$value;
	}
	public function __get($proname){
		if(!isset($this->pro[$proname])){
			echo ($proname.' is not member of CLS_USERS Class' );
			return '';
		}
		return $this->pro[$proname];
	}
	function getMemberByID($memid){
		$sql="SELECT * FROM `tbl_member` WHERE `mem_id`=\"$memid\" ";
		$objdata=new CLS_MYSQL();
		$objdata->Query($sql);
		if($objdata->Num_rows()>0){
			$rows=$objdata->Fetch_Assoc();
			$this->pro['ID']=$rows['mem_id'];
			$this->pro['UserName']=$rows['username'];
			$this->pro['Password']=$rows['password'];
			$this->pro['FirstName']=$rows['firstname'];
			$this->pro['LastName']=$rows['lastname'];
			$this->pro['Birthday']=$rows['birthday'];
			$this->pro['Gender']=$rows['gender'];
			$this->pro['Address']=$rows['address'];
			$this->pro['Phone']=$rows['phone'];
			$this->pro['Mobile']=$rows['mobile'];
			$this->pro['Email']=$rows['email'];
			$this->pro['Joindate']=$rows['joindate'];
			$this->pro['LastLogin']=$rows['lastlogin'];
			$this->pro['Gmember']=$rows['gmem_id'];
			$this->pro['isActive']=$rows['isactive'];
		}
	}
	public function LOGIN($user,$pass,$type=''){
		$flag=true;
		$user=str_replace("'",'',$user);
		$pass=md5(sha1(trim($pass)));
		if($user=='' || $pass=='')
			$flag=false;
		$sql="SELECT * FROM `tbl_member` WHERE `username`='$user' AND isactive=1";
		$this->objmysql->Query($sql);
		if($this->objmysql->Num_rows()>0){
			$rows=$this->objmysql->Fetch_Assoc();
			if($rows['password']!=$pass)
				$flag=false;
		}
		else{
			$flag=false;
		}
		if($flag==true){
			$_SESSION[MD5($_SERVER['HTTP_HOST']).'_ISLOGIN']=true;
			$_SESSION[MD5($_SERVER['HTTP_HOST']).'_USERLOGIN']=$rows['firstname'].' '.$rows['lastname'];
			
			$_SESSION[MD5($_SERVER['HTTP_HOST']).'_USERID']=$rows['mem_id'];
			$_SESSION[MD5($_SERVER['HTTP_HOST']).'_USERNAME']=$rows['username'];
			$_SESSION[MD5($_SERVER['HTTP_HOST']).'_GROUPUSER']=$rows['gmem_id'];
			
			$this->updateLogin($user,1);
		}
		return $flag;
	}
	public function isLogin(){
		if(isset($_SESSION[MD5($_SERVER['HTTP_HOST']).'_ISLOGIN']))
			$this->autoLogout($_SESSION[MD5($_SERVER['HTTP_HOST']).'_USERLOGIN']);
		if(isset($_SESSION[MD5($_SERVER['HTTP_HOST']).'_ISLOGIN']) && $_SESSION[MD5($_SERVER['HTTP_HOST']).'_ISLOGIN']==true){
			$this->updateLogin($_SESSION[MD5($_SERVER['HTTP_HOST']).'_USERLOGIN'],1);
			return true;
		}
		return false;
	}
	public function isAdmin($gid=1) {
		if(isset($_SESSION[MD5($_SERVER['HTTP_HOST']).'_GROUPUSER']) && $_SESSION[MD5($_SERVER['HTTP_HOST']).'_GROUPUSER']==$gid)
			return true;
		return false;
	}
	public function LOGOUT(){
		$this->updateLogin($_SESSION[MD5($_SERVER['HTTP_HOST']).'_USERLOGIN'],0);
		unset($_SESSION[MD5($_SERVER['HTTP_HOST']).'_ISLOGIN']);
		unset($_SESSION[MD5($_SERVER['HTTP_HOST']).'_USERLOGIN']);
		unset($_SESSION[MD5($_SERVER['HTTP_HOST']).'_GROUPUSER']);
		unset($_SESSION[MD5($_SERVER['HTTP_HOST']).'_USERID']);
		unset($_SESSION[MD5($_SERVER['HTTP_HOST']).'_USERNAME']);
	}
	public function updateLogin($user,$flag){
		$value='';
		if($flag==1)
			$value=date('Y-m-d h:i:s');
		$sql="UPDATE `tbl_member` SET `lastLogin`='$value' WHERE `username`='$user'";
		return $this->objmysql->Query($sql);
	}
	public function autoLogout($user){
		if(!isset($user)||$user=='')
			return;
		$sql="SELECT `lastlogin` FROM `tbl_member` WHERE `username`='$user' ";
		$this->objmysql->Query($sql);
		$rows=$this->objmysql->Fetch_Assoc();
		if($rows['lastlogin']=='')
			return;
		$s=date('i')-date('i',strtotime($rows['lastlogin']));
		if($s>=TIMEOUT_LOGIN || $s<(-1*TIMEOUT_LOGIN)){
			$this->LOGOUT();
			echo "<p align='center'>Hệ thống tự động đăng xuất sau 60 phút. Bạn vui lòng đăng nhập lại.</p>";
		}
		return;
	}
	public function getList($where=''){
		$sql="SELECT * FROM `tbl_member` ".$where;
		$this->objmysql->Query($sql);
	}
	public function Num_rows() { 
		return $this->objmysql->Num_rows();
	}
	public function Fetch_Assoc(){
		return $this->objmysql->Fetch_Assoc();
	}
	public function checkUserExists($user){
		$sql = "SELECT `username` FROM `tbl_member` WHERE `username` ='$user'";
		$this->objmysql->Query($sql);
		if($this->objmysql->Num_rows()>0) {
			return true;
		}
		return false;
	}
	function Add_new(){
		$sql="INSERT INTO `tbl_member` (`username`,`password`,`firstname`,`lastname`,`birthday`,`gender`,`address`,`phone`,`mobile`,`email`,`joindate`,`gmem_id`,`isactive`) VALUES ";
		$sql.=" ('".$this->UserName."','".md5(sha1(trim($this->Password)))."','".$this->FirstName."','";
		$sql.=$this->LastName."','".$this->Birthday."','".$this->Gender."','".$this->Address."','";
		$sql.=$this->Phone."','".$this->Mobile."','".$this->Email."','";
		$sql.=$this->Joindate."','".$this->Gmember."','".$this->isActive."') ";
		return $this->objmysql->Query($sql);
	}
	function Update(){		 
		$sql="UPDATE `tbl_member` SET 	`firstname`='".$this->FirstName."',
										`lastname`='".$this->LastName."',
										`birthday`='".$this->Birthday."',
										`gender`='".$this->Gender."',
										`address`='".$this->Address."',
										`phone`='".$this->Phone."',
										`mobile`='".$this->Mobile."',
										`email`='".$this->Email."',
										`gmem_id`='".$this->Gmember."',
										`isactive`='".$this->isActive."' ";
		$sql.=" WHERE `mem_id`='".$this->ID."'";
		return $this->objmysql->Query($sql);
	}
	public function ChangePass_User($user,$newpass) {
		$sql="UPDATE `tbl_member` SET `password`='".md5(sha1(trim($newpass)))."'";
		$sql.=" WHERE username='$user'"; 
		return $this->objmysql->Exec($sql);
	}
	// set active template
	function setActive($ids,$status=''){
		$sql="UPDATE `tbl_member` SET `isactive`='$status' WHERE `mem_id` in ('$ids')";
		if($status=='')
			$sql="UPDATE `tbl_member` SET `isactive`=if(`isactive`=1,0,1) WHERE `mem_id` in ('$ids')";
		return $this->objmysql->Exec($sql);
	}
	function Delete($memid){
		$sql="DELETE FROM `tbl_member` WHERE `mem_id` in ('$memid')";
		return $this->objmysql->Query($sql);
	}
}
?>