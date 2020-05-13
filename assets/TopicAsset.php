<?php

namespace app\assets;

use yii\web\AssetBundle;

class TopicAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
       'css/view.css',
    ];
    public $js = [
        // include js variables
        'js/topic/variables.js',
        // include js for View view
        'js/topic/view.js',
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
