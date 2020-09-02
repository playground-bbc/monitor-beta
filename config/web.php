<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'monitor-beta',
    'name' => 'LG monitor',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'America/Santiago',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'monitor' => [
            'class' => 'app\modules\monitor\Module',
        ],
        'topic' => [
            'class' => 'app\modules\topic\Module',
        ],
        'insights' => [
            'class' => 'app\modules\insights\Module',
        ],
        'user' => [
            'class' => 'app\modules\user\Module',
        ],
        'products' => [
            'class' => 'app\modules\products\Module',
        ],
        'wordlists' => [
            'class' => 'app\modules\wordlists\Module',
        ],
        // kartik
        'gridview' => [
            'class' => '\kartik\grid\Module',
            'bsVersion' => 3,
            //'downloadAction' => 'gridview/export/download',
        ]
    ],
    'components' => [
        'assetManager' => [
            'linkAssets' => true,
            'appendTimestamp' => true,
        ],
        'formatter' => [
           'dateFormat' => 'dd/mm/yyyy',
       ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'cwAyJzhAYoJKZywPh0oEVaVAk_akHdXR',
        ],
        'cache' => [
            // 'class' => 'yii\caching\DbCache',
            // 'db' => $db,
            // 'cacheTable' => 'cache',
            'class' => 'yii\caching\MemCache',
            'useMemcached' => true,
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 60,
                ]
            ],
        ],
        'user' => [
            'identityClass' => 'app\models\Users',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',  // e.g. smtp.mandrillapp.com or smtp.gmail.com
                'username' => '',
                'password' => '',
                'port' => '587', // Port 25 is a very common port too
                'encryption' => 'tls', // It is often used, check your provider or mail server specs
            ],
            //'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // rules module products
                'products' => 'products/default/index',
                // products-series = categorias
                'products/categorias/create' => 'products/products-series/create',
                'products/categorias/update' => 'products/products-series/update',
                // products-family = subcategorias
                'products/subcategorias/create' => 'products/products-family/create',
                'products/subcategorias/update' => 'products/products-family/update',
                // product-categories = product
                'products/product/create' => 'products/product-categories/create',
                'products/product/update' => 'products/product-categories/update',
                // products = models
                'products/models/create' => 'products/products/create',
                'products/models/update' => 'products/products/update',
                // products-models = products-code
                'products/products-code/create' => 'products/products-models/create',
                'products/products-code/update' => 'products/products-models/update',
                // end rules module products

                // rule for wordlists
                'wordlists' => 'wordlists/default/index',
                'wordlists/create' => 'wordlists/default/create',
                'wordlists/view' => 'wordlists/default/view',
                'wordlists/update' => 'wordlists/default/update',
                // end rules module wordlists

            ],
        ],
        
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

}

return $config;
