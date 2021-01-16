<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * highmapsAsset asset bundle.
 *
 */
class highmapsAsset extends AssetBundle
{
    public $sourcePath = '@npm/highcharts';

    /**
     * @inheritdoc
     */
    public $js = [
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
