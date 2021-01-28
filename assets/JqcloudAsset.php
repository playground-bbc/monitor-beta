<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * JqcloudAsset asset bundle.
 *
 */
class JqcloudAsset extends AssetBundle
{
    public $sourcePath = '@bower/jqcloud2/dist';

    public function init()
    {
        parent::init();
        $this->js[] = YII_ENV_DEV ? 'jqcloud.js':'jqcloud.min.js';
        $this->css[] = YII_ENV_DEV ? 'jqcloud.css':'jqcloud.min.css';
    }
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]; 
}
