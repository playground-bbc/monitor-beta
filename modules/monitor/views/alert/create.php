<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Alerts */

$this->title = Yii::t('app', 'Crear Alerta');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Alertas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
		'alert'   => $alert,
		'config'  => $config,
    ]) ?>

</div>
