<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * VueAsset asset bundle.
 */
class VueAsset extends AssetBundle
{
    public $sourcePath = '@npm/vue/dist';

    /**
     * @inheritdoc
     */
    public $js = [
        YII_ENV ? 'vue.js' : 'vue.min.js',
    ];


    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ]; 
}
