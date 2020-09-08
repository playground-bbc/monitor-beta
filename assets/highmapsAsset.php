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
class highmapsAsset extends AssetBundle
{
    public $sourcePath = '@npm/highcharts';

    /**
     * @inheritdoc
     */
    public $js = [
        //'highcharts.js',
        //'highstock.js',
        'highmaps.js',
        'modules/exporting.js',
        'https://code.highcharts.com/mapdata/countries/cl/cl-all.js',
        'https://code.highcharts.com/maps/modules/data.js'
        
    ];


    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ]; 
}
