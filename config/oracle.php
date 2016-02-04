<?php

return [
    'oracle' => [
			'driver'   => 'oracle',
			'tns'      => env('DB_TNS', ''),
			'host'     => env('DB_HOST', ''),
			'port'     => env('DB_PORT', '1521'),
			'database' => env('DB_DATABASE', 'ENERGYBUILDE'),
			'username' => env('DB_USERNAME', 'SYSTEM'),
			'password' => env('DB_PASSWORD', '123456'),
			'charset'  => env('DB_CHARSET', 'AL32UTF8'),
			'prefix'   => env('DB_PREFIX', ''),
	],
];
