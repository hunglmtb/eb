<?php

namespace App\Models;
use App\Models\CodeFlowPhase;

class ExtensionPhaseType extends CodeFlowPhase
{
	public static function all($columns = array())
	{
		return static::whereIn('CODE',['OIL','GAS','WTR'])->get($columns);
	}
}
