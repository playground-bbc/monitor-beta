<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * GoogleChartAsset asset bundle.
 *
 */
class GoogleChartAsset extends AssetBundle
{

    public $js = [
        'https://www.gstatic.com/charts/loader.js'
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]; 
 
}
