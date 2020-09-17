<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * view and form application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
       'css/view.css',
       
    ];
    public $js = [
        // include js for general variables
        'js/app/variables.js',
        // include js for general call api
        'js/app/service.js',
        // include js for general functions
        'js/app/functions.js',
        // include js for View view
        'js/app/view.js',
    ];
    public $depends = [
         'yii\web\YiiAsset',
         'yii\bootstrap\BootstrapAsset',
    ];

    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
         //you can also make it work only in debug mode: 'forceCopy' => YII_DEBUG
    ];
}
