<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * DataTableAsset asset bundle.
 *
 */
class DataTableAsset extends AssetBundle
{
    public $sourcePath = '@npm/datatables/media';

    public function init()
    {
        parent::init();
        $this->js[] = YII_ENV_DEV ? 'js/jquery.dataTables.js':'js/jquery.dataTables.min.js';
    }
    public $css = [
        'css/dataTables.jqueryui.css',
        'css/jquery.dataTables.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]; 
   
}
