<?php

// return [
//     'class' => 'yii\db\Connection',
//     'dsn' => 'mysql:host=localhost;dbname=monitor',
//     'username' => 'phpmyadmin',
//     'password' => 'deathnote',
//     //'charset' => 'utf8',
//     'charset' => 'utf8mb4',

//     // Schema cache options (for production environment)
//     'enableSchemaCache' => true,
//     'schemaCacheDuration' => 60,
//     'schemaCache' => 'cache',
// ];

$end_point = "ls-e16582e411c020dbab644786785a63b3d5135a31.cipwfc5j4a42.us-east-2.rds.amazonaws.com";

return [
    'class' => 'yii\db\Connection',
    'dsn' => "mysql:host={$end_point};dbname=monitor_db",
    'username' => 'dbmasteruser',
    'password' => '%j{#c(z$0(uz|<dSmv{vKQW5D7e~<0i9',
    'charset' => 'utf8',
    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 60,
    'schemaCache' => 'cache',
];

