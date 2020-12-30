<?php
// path to folder flat archives
$s = DIRECTORY_SEPARATOR;
$path = explode($s, dirname(__DIR__));
$folder = end($path);

Yii::setAlias('@stopwords',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}stop-words{$s}data{$s}");
Yii::setAlias('@data',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}data");
// Yii::setAlias('@web',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}web");
// Yii::setAlias('@webroot',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}web");
Yii::setAlias('@img',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}web{$s}img");
Yii::setAlias('@pdf',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}web{$s}pdf");
Yii::setAlias('@credencials',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}credentials{$s}monitor-app-96f0293a0153.json");
Yii::setAlias('@insights',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}widgets{$s}insights");
Yii::setAlias('@cacert',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}credentials{$s}cacert.pem");

// set env var
$dotenv = Dotenv\Dotenv::createImmutable( dirname(dirname(__DIR__)). "{$s}{$folder}{$s}");
$dotenv->load();

return [
	'frontendUrl' => 'https://lg.mediatrendsgroup.com/web/',
	'adminEmail'  => 'eduardo@montana-studio.com',
	'senderEmail' => 'eduardo@montana-studio.com',
	'senderName'  => 'monitor-beta',
	'facebook'    => [ 
		'time_min_sleep'  => 5,  
		'business_id'     => $_SERVER['BUSSINES_ID'],
		'app_id'          => $_SERVER['APP_ID'],
		'name_app'        => $_SERVER['NAME_APP'],
		'name_account'    => $_SERVER['NAME_ACCOUNT'],
		'app_secret'      => $_SERVER['APP_SECRET']

	],
	'dandelion' => [
		'token' => $_SERVER['DANDELION_TOKEN']
	],
	// alias for resources
	'resourcesName' => [
		"Twitter" => "Twitter",
		"Live Chat" => "Live Chat (Tickets)",
		"Live Chat Conversations" => "Live Chat (Chats)",
		"Facebook Comments" => "Facebook Commentarios",
		"Instagram Comments" => "Instagram Commentarios",
		"Facebook Messages" => "Facebook Inbox",
		"Excel Document" => "Excel Documento",
		"Paginas Webs" => "Paginas Webs",
	],
	
];

