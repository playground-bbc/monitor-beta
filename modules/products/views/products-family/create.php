<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductsFamily */

$this->title = Yii::t('app', 'Create Products Family');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Products Families'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="products-family-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
