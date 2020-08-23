<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\color\ColorInput;

/* @var $this yii\web\View */
/* @var $model app\models\Dictionaries */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dictionaries'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="dictionaries-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            //'color',
            [
                'label' => Yii::t('app','Color'),
                'attribute' => 'color',
                'format' => 'raw',
                'value' => function($model){
                    $input = "<input type='color'  value='$model->color' disabled>";
                    return $input;
                }
            ],
            [
                'label' => Yii::t('app','Creado en'),
                'attribute' => 'createdAt',
                'format' => 'raw',
                'value' => function($model){
                    return Html::a(Yii::$app->formatter->asDatetime($model->createdAt,'yyyy/MM/dd'), ['view', 'id' => $model->id],['data-pjax' => 0]);
                }
            ],
            [
                'label' => Yii::t('app','Actualizado en'),
                'attribute' => 'updatedAt',
                'format' => 'raw',
                'value' => function($model){
                    return Html::a(Yii::$app->formatter->asDatetime($model->updatedAt,'yyyy/MM/dd'), ['view', 'id' => $model->id],['data-pjax' => 0]);
                }
            ],
            [
                'label' => Yii::t('app','Creado por'),
                'attribute' => 'createdBy',
                'format' => 'raw',
                'value' => function($model){
                    $user = \app\models\Users::findOne($model->createdBy);
                    return $user->username;
                }
            ],
            [
                'label' => Yii::t('app','Actualizado por'),
                'attribute' => 'updatedBy',
                'format' => 'raw',
                'value' => function($model){
                    $user = \app\models\Users::findOne($model->updatedBy);
                    return $user->username;
                }
            ],
        ],
    ]) ?>

</div>
<div class="keywords-index">
    <p>
        <?= Html::a(Yii::t('app', 'Crear Palabras clave'), ['keywords/create','dictionaryId' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
            //'createdBy',
            //'updatedBy',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete} {view}',
                'contentOptions' => ['style' => 'width: 10%;min-width: 20px'], 
                'buttons' => [
                    'delete' => function($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['keywords/delete', 'id' => $model->id], [
                            'data' => [
                                'confirm' => 'Are you absolutely sure ? You will lose all the information about this user with this action.',
                                'method' => 'post',
                            ],
                        ]);
                    },
                    'update' => function($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['keywords/update', 'id' => $model->id]);
                    },
                    'view' => function($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['keywords/view', 'id' => $model->id]);
                    }
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

