<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * highchartsAsset asset bundle.
 */
class highchartsAsset extends AssetBundle
{
    public $sourcePath = '@npm/highcharts';

    /**
     * @inheritdoc
     */
    public $js = [
        'highstock.js',
        'modules/exporting.js',
        
    ];


    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ]; 
}
