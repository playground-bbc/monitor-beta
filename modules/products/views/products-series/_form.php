<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ProductsSeries */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="products-series-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true,'placeholder' => 'Ej: Video Game o Games']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'abbreviation_name')->textInput(['maxlength' => true,'placeholder' => 'Ej: HE']) ?>   
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Guardar'), ['class' => 'btn btn-success']) ?>
        <?= Html::submitButton(Yii::t('app', 'Guardar y Crear nuevo registro'), ['class' => 'btn btn-info','name' =>'redirect','value' => 'false']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
