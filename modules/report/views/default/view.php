<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\presentation\models\Presentation */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Presentations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="presentation-view">

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
            'userId',
            'name',
            'head_title',
            'title',
            'date:datetime',
            'url_sheet:url',
            'url_presentation:url',
            [
                'label' => 'Status',
                'value' => function($model){
                    $status = ($model->status) ? 'Active' : 'Finish';
                    if(!$model->status && is_null($model->url_presentation)){
                        $status = 'Error generando la presentacion';
                    }
                    return $status;
                }
            ],
            // 'updated',
            // 'createdAt',
            // 'updatedAt',
            // 'createdBy',
            // 'updatedBy',
        ],
    ]) ?>

</div>
