<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * MomentAsset asset bundle.
 */
class MomentAsset extends AssetBundle
{
    public $sourcePath = '@bower/moment/min';

    public function init()
    {
        parent::init();
        $this->js[] = YII_ENV_DEV ? 'moment.min.js':'moment.min.js';
    }
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]; 

    public $publishOptions = [
      
    ];
}