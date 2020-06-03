<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
?>
<h1><?= $resource->name ?></h1>

<p>
    <?= Html::a('Regresar', ['alert/view', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
</p>
