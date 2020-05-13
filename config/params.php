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

return [
	'adminEmail'  => 'eduardo@montana-studio.com',
	'senderEmail' => 'eduardo@montana-studio.com',
	'senderName'  => 'monitor-beta',
	'facebook'    => [ 
		'time_min_sleep'  => 5,  
		'business_id'     => '169441517247',
		'app_id'          => '446684435912359',
		'name_app'        => 'monitor-facebook',
		'name_account'    => 'Mundo LG',
		'app_secret'      => '541f2431cc1ad60c9d5bb4836eed1356'

	],
];

