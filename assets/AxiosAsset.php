<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * AxiosAsset asset bundle.
 *
 */
class AxiosAsset extends AssetBundle
{
    public $sourcePath = '@npm/axios/dist';

    public function init()
    {
        parent::init();
        $this->js[] = YII_ENV_DEV ? 'axios.js':'axios.min.js';
    }
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]; 
}
