<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductsModels */

$this->title = Yii::t('app', 'Create Products Code');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Productos'), 'url' => ['default/index','itemId'=>4]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="products-models-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
