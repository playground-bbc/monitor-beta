<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\modules\products\models\ProductCategoriesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

// $this->title = Yii::t('app', 'Product Categories');
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-categories-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Crear Modelos'), ['product-categories/create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            //'productsFamily',
            [
                'label' => Yii::t('app','Sub Categoria'),
                'format'    => 'raw',
                'attribute' => 'products_familyId',
                'filter' => kartik\select2\Select2::widget([
                     'data' => \yii\helpers\ArrayHelper::map(\app\models\ProductsFamily::find()->all(),'id','name'),
                     'name' => 'ProductCategoriesSearch[products_familyId]',
                     'value' => $searchModel['products_familyId'],
                     'attribute' => 'products_familyId',
                     'options' => ['placeholder' => 'Selecione una Sub Categoria...','multiple' => false],
                     'theme' => 'krajee',
                     'hideSearch' => true,
                     'pluginOptions' => [
                           'allowClear' => true,
                      ],
                ]),
                'value' => function($model) {
                   return $model->productsFamily->name;
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
                'template' => '{update}{delete}',
                'contentOptions' => ['style' => 'width: 10%;min-width: 20px'], 
                'buttons' => [
                    'delete' => function($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['product-categories/delete', 'id' => $model->id], [
                            'class' => '',
                            'data' => [
                                'confirm' => 'Are you absolutely sure ? You will lose all the information about this user with this action.',
                                'method' => 'post',
                            ],
                        ]);
                    },
                    'update' => function($url,$model){
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['product-categories/update', 'id' => $model->id]);
                    },
                    'view' => function($url,$model){
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['product-categories/view', 'id' => $model->id]);
                    }
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
