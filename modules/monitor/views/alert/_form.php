<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use macgyer\yii2materializecss\widgets\form\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Alerts */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="alerts-form">

    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'userId')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'createdAt')->textInput() ?>

    <?= $form->field($model, 'updatedAt')->textInput() ?>

    <?= $form->field($model, 'createdBy')->textInput() ?>

    <?= $form->field($model, 'updatedBy')->textInput() ?>
    

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>