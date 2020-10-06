<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\widgets\insights\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 */
class InsightsAsset extends AssetBundle
{
    
    public $sourcePath = "@insights";
    public $css = [
        'css/insights.css',
    ];
    public $js = [
        'js/template.js',
        'js/main.js',
        //'js/widget/bootstrap.bundle.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        '\app\assets\VueAsset',
    ];

    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
         //you can also make it work only in debug mode: 'forceCopy' => YII_DEBUG
    ];
}
