<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * detail application asset bundle.
 *
 */
class DetailAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
       
    ];
    public $js = [
        // include js for general variables
       // 'js/app/variables.js',
        // include js for Api call
        'js/app/service.js',
        // include js for Detail view
        'js/app/detail.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    public $publishOptions = [
        'forceCopy' => true,
         //you can also make it work only in debug mode: 'forceCopy' => YII_DEBUG
    ];
}
