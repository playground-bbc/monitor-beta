<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Keywords */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', $model->dictionary->name), 'url' => ['/wordlists/view','id' => $model->dictionary->id]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="keywords-view">

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
            'id',
            [
                'label' => Yii::t('app','Diccionario'),
                'attribute' => 'dictionaryId',
                'format' => 'raw',
                'value' => function($model){
                    return $model->dictionary->name;
                }
            ],
            [
                'label' => Yii::t('app','Nombre'),
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model){
                    return $model->name;
                }
            ],
            [
                'label' => Yii::t('app','Creado en'),
                'attribute' => 'createdAt',
                'format' => 'raw',
                'value' => function($model){
                    return Yii::$app->formatter->asDatetime($model->createdAt,'yyyy/MM/dd');
                }
            ],
            [
                'label' => Yii::t('app','Actualizado en'),
                'attribute' => 'updatedAt',
                'format' => 'raw',
                'value' => function($model){
                    return Yii::$app->formatter->asDatetime($model->updatedAt,'yyyy/MM/dd');
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
