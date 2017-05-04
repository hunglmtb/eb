<?php

return [
    'oracle' => [
			'driver'   => 'oracle',
			'tns'      => env('DB_TNS', ''),
			'port'     => env('DB_PORT', '1521'),
			'host'     => env('DB_HOST_ORACLE', ''),
			'database' => env('DB_DATABASE_ORACLE', 'ENERGY_BUILDER'),
			'username' => env('DB_USERNAME_ORACLE', 'SYSTEM'),
			'password' => env('DB_PASSWORD_ORACLE', '123456'),
			'charset'  => env('DB_CHARSET', 'AL32UTF8'),
			'prefix'   => env('DB_PREFIX', ''),
    		'options' => [
//     				PDO::ATTR_CASE => PDO::CASE_UPPER,
    		]
	],
];
