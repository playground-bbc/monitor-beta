<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * SweetAlertAsset asset bundle.
 *
 */
class SweetAlertAsset extends AssetBundle
{
    public $sourcePath = '@npm/sweetalert2/dist';

    /**
     * @inheritdoc
     */
    public $js = [
        YII_ENV_DEV ? 'sweetalert2.js' : 'sweetalert2.min.js',
    ];

    public $css = [
       YII_ENV_DEV ? 'sweetalert2.css' : 'sweetalert2.min.css',
       
    ];


    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ]; 
}
