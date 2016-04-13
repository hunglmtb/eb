<?php namespace App\Services;

class Statut  {

	/**
	 * Set the login user statut
	 * 
	 * @param  Illuminate\Auth\Events\Login $login
	 * @return void
	 */
	public function setLoginStatut($login)
	{
		/* $uur = $login->user->user_user_role->first();
		$userId =  $uur->USER_ID;
		$roleId =  $uur->ROLE_ID;
		$ur = $uur->user_role;
		$role = $ur->CODE;
		session()->put('statut', $role); */
		session()->put('statut', $login->user->role());
// 		session()->put('rights', $login->user->right());
		
// 		session()->put('statut', $login->user->role->slug);
	}

	/**
	 * Set the visitor user statut
	 * 
	 * @return void
	 */
	public function setVisitorStatut()
	{
		session()->put('statut', 'visitor');
	}

	/**
	 * Set the statut
	 * 
	 * @return void
	 */
	public function setStatut()
	{
		if(!session()->has('statut')) 
		{
			session()->put('statut', auth()->check() ?  auth()->user()->role->slug : 'visitor');
		}
	}

}