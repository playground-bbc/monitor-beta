<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\topic\models\MTopics */

$this->title = Yii::t('app', 'Create M Topics');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'M Topics'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mtopics-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'drive' => $drive,
    ]) ?>

</div>
