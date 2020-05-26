<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\modules\user\models\UserLogsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'User Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-logs-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(['id' => 'logs', 'timeout' => false, 'enablePushState' => false]); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            //'userId',
            [
                'label' => Yii::t('app', 'username'),
                'attribute' => 'username',
                'format' => 'raw',
                'value' => function($model) { 
                    return $model->user->username;
                },
            ],
            [
                'label' => Yii::t('app','Action'),
                'format'    => 'raw',
                'attribute' => 'message',
                'filter' => Select2::widget([
                     'data' => ['Login' => 'Login','Logout'=> 'Logout'],
                     'name' => 'UserLogsSearch[message]',
                     'value' => $searchModel['message'],
                     'attribute' => 'message',
                     'options' => ['placeholder' => 'Select action...','multiple' => false],
                     'theme' => 'krajee',
                     'hideSearch' => true,
                     'pluginOptions' => [
                           'allowClear' => true,
                      ],
                ]),
                
            ],
            [
                'label' => Yii::t('app', 'Dirrecion Ip'),
                'attribute' => 'remote_addr',
                'format' => 'raw',
                'value' => function($model) { 
                    return $model->user_agent['ip'];
                },
            ],
            //'remote_addr',
            'log_date',
           // 'message:ntext',
            [
                'label' => Yii::t('app', 'Explorador'),
                'attribute' => 'user_agent',
                'format' => 'raw',
                'value' => function($model) { 
                    return $model->user_agent['browser'];
                },
            ],
            [
                'label' => Yii::t('app', 'Explorador version'),
                'attribute' => 'user_agent',
                'format' => 'raw',
                'value' => function($model) { 
                    return $model->user_agent['browser_version'];
                },
            ],
            //'user_agent',
            //'createdAt',
            //'updatedAt',
            //'createdBy',
            //'updatedBy',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
