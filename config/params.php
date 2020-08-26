<?php
// path to folder flat archives
$s = DIRECTORY_SEPARATOR;
$path = explode($s, dirname(__DIR__));
$folder = end($path);

Yii::setAlias('@data',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}data");
Yii::setAlias('@img',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}web{$s}img");
Yii::setAlias('@pdf',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}web{$s}pdf");
Yii::setAlias('@credencials',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}credentials{$s}monitor-app-96f0293a0153.json");
Yii::setAlias('@insights',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}widgets{$s}insights");
Yii::setAlias('@cacert',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}credentials{$s}cacert.pem");

// set env var
$dotenv = Dotenv\Dotenv::createImmutable( dirname(dirname(__DIR__)). "{$s}{$folder}{$s}");
$dotenv->load();

return [
	'adminEmail'  => 'eduardo@montana-studio.com',
	'senderEmail' => 'eduardo@montana-studio.com',
	'senderName'  => 'monitor-beta',
	'facebook'    => [ 
		'time_min_sleep'  => 5,  
		'business_id'     => $_ENV['BUSSINES_ID'],
		'app_id'          => $_ENV['APP_ID'],
		'name_app'        => $_ENV['NAME_APP'],
		'name_account'    => $_ENV['NAME_ACCOUNT'],
		'app_secret'      => $_ENV['APP_SECRET']

	],
	'dandelion' => [
		'token' => $_ENV['DANDELION_TOKEN']
	],
];

