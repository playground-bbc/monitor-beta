<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductsFamily */

$this->title = Yii::t('app', 'Crear Sub Categorias');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Productos'), 'url' => ['default/index','itemId'=>1]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="products-family-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
