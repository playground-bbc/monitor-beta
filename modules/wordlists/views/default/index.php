<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\modules\wordlists\models\DictionariesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Diccionarios');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dictionaries-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Crear diccionarios'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'label' => Yii::t('app','Nombre'),
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model){
                    return Html::a($model->name,['view', 'id' => $model->id],['data-pjax' => 0]);
                }
            ],
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
                'label' => Yii::t('app','Numero de Palabras'),
                'format' => 'raw',
                'value' => function($model){
                    return Html::a(count($model->keywords),['view', 'id' => $model->id],['data-pjax' => 0]);
                }
            ],
            
            

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete} {view} {add}',
                'contentOptions' => ['style' => 'width: 10%;min-width: 20px'], 
                'buttons' => [
                    'add' => function($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-plus"></span>', ['keywords/create','dictionaryId' => $model->id]);
                    },
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
