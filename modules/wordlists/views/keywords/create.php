<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Keywords */

$this->title = Yii::t('app', 'Create Keywords');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', $model->dictionary->name), 'url' => ['/wordlists/view','id' => $model->dictionary->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="keywords-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
