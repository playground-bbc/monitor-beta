<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\modules\products\models\ProductsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

// $this->title = Yii::t('app', 'Products');
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="products-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Crear Modelos'), ['products/create'], ['class' => 'btn btn-success']) ?>
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
                'label' => Yii::t('app','Producto'),
                'format'    => 'raw',
                'attribute' => 'categoryId',
                'filter' => kartik\select2\Select2::widget([
                     'data' => \yii\helpers\ArrayHelper::map(\app\models\ProductCategories::find()->all(),'id','name'),
                     'name' => 'ProductsSearch[categoryId]',
                     'value' => $searchModel['categoryId'],
                     'attribute' => 'categoryId',
                     'options' => ['placeholder' => 'Selecione un Producto...','multiple' => false],
                     'theme' => 'krajee',
                     'hideSearch' => false,
                     'pluginOptions' => [
                           'allowClear' => true,
                      ],
                ]),
                'value' => function($model) {
                   return $model->category->name;
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
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['products/delete', 'id' => $model->id], [
                            'class' => '',
                            'data' => [
                                'confirm' => 'Are you absolutely sure ? You will lose all the information about this user with this action.',
                                'method' => 'post',
                            ],
                        ]);
                    },
                    'update' => function($url,$model){
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['products/update', 'id' => $model->id]);
                    },
                    'view' => function($url,$model){
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['products/view', 'id' => $model->id]);
                    }
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
