<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\color\ColorInput

/* @var $this yii\web\View */
/* @var $model app\models\Dictionaries */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="dictionaries-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'color')->widget(ColorInput::classname(), [
                    'name' => 'color_11',
                    'useNative' => true,
                    'options' => ['placeholder' => 'Choose your color ...']
                ]) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
