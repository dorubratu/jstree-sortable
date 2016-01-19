<?php
date_default_timezone_set('UTC');

return [
	'name' => APP_NAME, 
	'basePath' => BASEPATH . '/protected/',
	'defaultController' => 'items/index',
	'homeUrl'           => APP_PATH . '/items/index',

	
	'preload' => ['db'],
	
	'components' => [
		'db' => [
			'connectionString' => DB_CONNECTION,
			'username'         => DB_USERNAME,
			'password'         => DB_PASSWORD,
			'emulatePrepare'   => TRUE,
			'charset'          => 'utf8',
    ],
		'urlManager' => [
			'urlFormat' => 'path',
			'rules' => [
				'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
			],
		],
  ],
];