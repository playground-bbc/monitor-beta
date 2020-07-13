<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\modules\products\models\ProductsFamilySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

// $this->title = Yii::t('app', 'Products Families');
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="products-family-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Crear Sub Categoria'), ['products-family/create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            [
                'label' => Yii::t('app','Categoria'),
                'format'    => 'raw',
                'attribute' => 'seriesId',
                'filter' => Select2::widget([
                     'data' => \yii\helpers\ArrayHelper::map(\app\models\ProductsSeries::find()->all(),'id','name'),
                     'name' => 'ProductsFamilySearch[seriesId]',
                     'value' => $searchModel['seriesId'],
                     'attribute' => 'seriesId',
                     'options' => ['placeholder' => 'Selecione una Categoria...','multiple' => false],
                     'theme' => 'krajee',
                     'hideSearch' => true,
                     'pluginOptions' => [
                           'allowClear' => true,
                      ],
                ]),
                'value' => function($model) {
                    return $model->series->name;
                }
            ],
            'name',
            // 'status',
            // 'createdAt',
            //'updatedAt',
            //'createdBy',
            //'updatedBy',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}',
                'contentOptions' => ['style' => 'width: 10%;min-width: 20px'], 
                'buttons' => [
                    'delete' => function($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['products-family/delete', 'id' => $model->id], [
                            'class' => '',
                            'data' => [
                                'confirm' => 'Are you absolutely sure ? You will lose all the information about this user with this action.',
                                'method' => 'post',
                            ],
                        ]);
                    },
                    'update' => function($url,$model){
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['products-family/update', 'id' => $model->id]);
                    },
                    'view' => function($url,$model){
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['products-family/view', 'id' => $model->id]);
                    }
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
