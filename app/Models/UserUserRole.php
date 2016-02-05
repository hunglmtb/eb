<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserUserRole extends Model  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_user_role';
	protected $primaryKey = 'ID';
	protected $user_id_col = 'USER_ID';
	protected $role_id_col = 'ROLE_ID';

	
	public function __construct() {
		parent::__construct();
		$cn = config('database.default');
		if ($cn==='oracle'){
			$this->primaryKey = 'id';
			$this->user_id_col = 'user_id';
			$this->role_id_col = 'role_id';
		}
	
	}
	/*
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user_role() 
	{
		return $this->belongsTo('App\Models\UserRole', $this->role_id_col, $this->primaryKey);
	}
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user()
	{
		return $this->belongsTo('App\Models\User',  $this->user_id_col, $this->primaryKey);
	}
	
}
