<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
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
