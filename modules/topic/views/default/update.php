<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\topic\models\MTopics */

$this->title = Yii::t('app', 'Update M Topics: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'M Topics'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="mtopics-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'drive' => $drive,
    ]) ?>

</div>
