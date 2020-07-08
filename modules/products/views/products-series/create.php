<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductsSeries */

$this->title = Yii::t('app', 'Create Products Series');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Products Series'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="products-series-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
