<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => "mysql:host={$_SERVER['DB_HOST']};dbname={$_SERVER['DB_NAME']}",
    'username' => $_SERVER['DB_USER'],
    'password' => $_SERVER['DB_PASS'],
    'charset' => 'utf8mb4',
    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 60,
    'schemaCache' => 'cache',
];

